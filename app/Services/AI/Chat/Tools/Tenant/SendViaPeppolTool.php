<?php

namespace App\Services\AI\Chat\Tools\Tenant;

use App\Models\Invoice;
use App\Services\AI\Chat\Tools\AbstractTool;
use App\Services\AI\Chat\Tools\ToolContext;
use App\Services\PeppolService;

class SendViaPeppolTool extends AbstractTool
{
    public function __construct(
        protected PeppolService $peppolService
    ) {}

    public function getName(): string
    {
        return 'send_via_peppol';
    }

    public function getDescription(): string
    {
        return 'Sends an invoice via the Peppol network for e-invoicing. Use this when the customer is Peppol-capable and you want to send the invoice electronically through the official Peppol network.';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'invoice_id' => [
                    'type' => 'string',
                    'description' => 'UUID of the invoice to send',
                ],
                'invoice_number' => [
                    'type' => 'string',
                    'description' => 'Invoice number if invoice_id is unknown (e.g., F-2025-001)',
                ],
            ],
            'required' => [],
        ];
    }

    public function requiresConfirmation(): bool
    {
        return true;
    }

    public function execute(array $input, ToolContext $context): array
    {
        // Validate tenant access
        $this->validateTenantAccess($context->user, $context->company);

        // Find invoice
        $invoice = $this->findInvoice($input, $context);

        if (!$invoice) {
            return [
                'error' => 'Invoice not found. Please provide a valid invoice_id or invoice_number.',
                'suggestion' => 'Use the read_invoices tool to find the invoice first.',
            ];
        }

        // Load partner
        $invoice->load('partner');

        if (!$invoice->partner) {
            return [
                'error' => "La facture {$invoice->invoice_number} n'a pas de client associé.",
            ];
        }

        // Check if invoice is in draft status
        if ($invoice->status === 'draft') {
            return [
                'error' => "La facture {$invoice->invoice_number} est en brouillon. Validez-la d'abord avant de l'envoyer via Peppol.",
                'suggestion' => 'Changez le statut de la facture à "validated" ou "sent".',
            ];
        }

        // Check if partner is Peppol-capable
        if (empty($invoice->partner->peppol_id)) {
            return [
                'error' => "Le client {$invoice->partner->name} n'a pas d'identifiant Peppol.",
                'suggestion' => 'Vérifiez si le client est enregistré sur le réseau Peppol ou envoyez la facture par email classique.',
                'partner' => [
                    'name' => $invoice->partner->name,
                    'vat_number' => $invoice->partner->vat_number,
                    'peppol_capable' => false,
                ],
            ];
        }

        // Check if already sent via Peppol
        if ($invoice->peppol_status === 'delivered') {
            return [
                'warning' => "La facture {$invoice->invoice_number} a déjà été envoyée via Peppol.",
                'peppol_info' => [
                    'message_id' => $invoice->peppol_message_id,
                    'sent_at' => $invoice->peppol_sent_at?->format('d/m/Y H:i'),
                    'delivered_at' => $invoice->peppol_delivered_at?->format('d/m/Y H:i'),
                ],
                'suggestion' => 'Voulez-vous renvoyer la facture ?',
            ];
        }

        try {
            // Send via Peppol
            $result = $this->peppolService->sendInvoice($invoice);

            if ($result['success']) {
                return [
                    'success' => true,
                    'message' => "Facture {$invoice->invoice_number} envoyée avec succès via Peppol",
                    'invoice' => [
                        'id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                        'partner' => $invoice->partner->name,
                        'status' => $invoice->status,
                        'total_incl_vat' => (float) $invoice->total_incl_vat,
                        'currency' => 'EUR',
                    ],
                    'peppol' => [
                        'message_id' => $result['message_id'] ?? null,
                        'transmission_id' => $result['transmission_id'] ?? null,
                        'status' => $result['status'] ?? 'pending',
                        'sent_at' => now()->format('d/m/Y H:i'),
                        'recipient_peppol_id' => $invoice->partner->peppol_id,
                    ],
                    'next_steps' => [
                        'La facture est en cours de transmission sur le réseau Peppol',
                        'Vous recevrez une confirmation de livraison sous quelques minutes',
                        'Le client pourra consulter la facture dans son système Peppol',
                    ],
                ];
            } else {
                return [
                    'error' => "Échec de l'envoi via Peppol : " . ($result['error'] ?? 'Erreur inconnue'),
                    'details' => $result['details'] ?? null,
                    'suggestion' => 'Vérifiez la configuration Peppol ou contactez le support.',
                ];
            }

        } catch (\Exception $e) {
            \Log::error('Failed to send invoice via Peppol', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'error' => "Erreur lors de l'envoi via Peppol : " . $e->getMessage(),
                'suggestion' => 'Vérifiez la configuration Peppol et le quota disponible.',
            ];
        }
    }

    /**
     * Find invoice by ID or number.
     */
    protected function findInvoice(array $input, ToolContext $context): ?Invoice
    {
        // Try to find by ID first
        if (!empty($input['invoice_id'])) {
            return Invoice::where('id', $input['invoice_id'])
                ->where('company_id', $context->company->id)
                ->first();
        }

        // Try to find by invoice number
        if (!empty($input['invoice_number'])) {
            return Invoice::where('company_id', $context->company->id)
                ->where('invoice_number', $input['invoice_number'])
                ->first();
        }

        return null;
    }
}
