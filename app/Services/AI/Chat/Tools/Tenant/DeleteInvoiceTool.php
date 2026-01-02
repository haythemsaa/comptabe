<?php

namespace App\Services\AI\Chat\Tools\Tenant;

use App\Models\Invoice;
use App\Services\AI\Chat\Tools\AbstractTool;
use App\Services\AI\Chat\Tools\ToolContext;

class DeleteInvoiceTool extends AbstractTool
{
    public function getName(): string
    {
        return 'delete_invoice';
    }

    public function getDescription(): string
    {
        return 'Deletes an invoice (soft delete). Use this when the user wants to delete, remove, or cancel an invoice. Only draft invoices can be truly deleted; others are marked as cancelled.';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'invoice_id' => [
                    'type' => 'string',
                    'description' => 'UUID of the invoice to delete',
                ],
                'invoice_number' => [
                    'type' => 'string',
                    'description' => 'Invoice number if invoice_id is unknown (e.g., F-2025-001)',
                ],
                'reason' => [
                    'type' => 'string',
                    'description' => 'Reason for deletion (recommended for audit trail)',
                ],
            ],
            'required' => [],
        ];
    }

    public function requiresConfirmation(): bool
    {
        return true; // Always require confirmation for deletion
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

        $invoiceNumber = $invoice->invoice_number;
        $invoiceStatus = $invoice->status;

        // Check if invoice is paid - cannot delete paid invoices
        if ($invoice->status === 'paid') {
            return [
                'error' => "Impossible de supprimer la facture {$invoiceNumber} : elle est marquée comme payée.",
                'suggestion' => "Pour une facture payée, vous devez :\n1. D'abord annuler le paiement\n2. Créer une note de crédit si nécessaire\n3. Puis marquer comme 'cancelled'",
                'invoice' => [
                    'invoice_number' => $invoiceNumber,
                    'status' => 'paid',
                    'amount_paid' => (float) $invoice->amount_paid,
                ],
            ];
        }

        // Check if sent via Peppol - warn
        if (!empty($invoice->peppol_message_id)) {
            return [
                'error' => "La facture {$invoiceNumber} a été envoyée via Peppol.",
                'warning' => "Supprimer une facture envoyée via Peppol peut causer des problèmes de conformité.",
                'suggestion' => "Recommandation : Marquez plutôt la facture comme 'cancelled' au lieu de la supprimer.",
                'peppol_info' => [
                    'sent_at' => $invoice->peppol_sent_at?->format('d/m/Y H:i'),
                    'status' => $invoice->peppol_status,
                ],
            ];
        }

        // For draft invoices: truly delete (soft delete)
        // For others: mark as cancelled
        if ($invoiceStatus === 'draft') {
            // Add deletion reason to internal notes
            if (!empty($input['reason'])) {
                $invoice->internal_notes = ($invoice->internal_notes ? $invoice->internal_notes . "\n\n" : '')
                    . "Supprimé le " . now()->format('d/m/Y H:i') . " par " . $context->user->name
                    . "\nRaison : " . $input['reason'];
                $invoice->save();
            }

            // Soft delete
            $invoice->delete();

            return [
                'success' => true,
                'message' => "Facture {$invoiceNumber} supprimée avec succès (statut: brouillon)",
                'action' => 'deleted',
                'invoice' => [
                    'invoice_number' => $invoiceNumber,
                    'was_status' => 'draft',
                ],
            ];
        } else {
            // Change status to cancelled instead of deleting
            $invoice->status = 'cancelled';

            // Add cancellation reason to internal notes
            if (!empty($input['reason'])) {
                $invoice->internal_notes = ($invoice->internal_notes ? $invoice->internal_notes . "\n\n" : '')
                    . "Annulé le " . now()->format('d/m/Y H:i') . " par " . $context->user->name
                    . "\nRaison : " . $input['reason'];
            }

            $invoice->save();

            return [
                'success' => true,
                'message' => "Facture {$invoiceNumber} marquée comme annulée (était: {$invoiceStatus})",
                'action' => 'cancelled',
                'warning' => "La facture n'a pas été supprimée mais marquée comme 'cancelled' pour préserver l'historique comptable.",
                'invoice' => [
                    'invoice_number' => $invoiceNumber,
                    'previous_status' => $invoiceStatus,
                    'current_status' => 'cancelled',
                ],
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
