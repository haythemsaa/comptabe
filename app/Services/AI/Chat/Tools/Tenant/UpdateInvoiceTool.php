<?php

namespace App\Services\AI\Chat\Tools\Tenant;

use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Services\AI\Chat\Tools\AbstractTool;
use App\Services\AI\Chat\Tools\ToolContext;
use Illuminate\Support\Facades\DB;

class UpdateInvoiceTool extends AbstractTool
{
    public function getName(): string
    {
        return 'update_invoice';
    }

    public function getDescription(): string
    {
        return 'Updates an existing invoice. Use this when the user wants to modify, change, or update invoice details like dates, amounts, status, or notes.';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'invoice_id' => [
                    'type' => 'string',
                    'description' => 'UUID of the invoice to update',
                ],
                'invoice_number' => [
                    'type' => 'string',
                    'description' => 'Invoice number if invoice_id is unknown (e.g., F-2025-001)',
                ],
                'invoice_date' => [
                    'type' => 'string',
                    'format' => 'date',
                    'description' => 'New invoice date (YYYY-MM-DD)',
                ],
                'due_date' => [
                    'type' => 'string',
                    'format' => 'date',
                    'description' => 'New payment due date (YYYY-MM-DD)',
                ],
                'status' => [
                    'type' => 'string',
                    'enum' => ['draft', 'validated', 'sent', 'paid', 'cancelled'],
                    'description' => 'New invoice status',
                ],
                'reference' => [
                    'type' => 'string',
                    'description' => 'Update reference or description',
                ],
                'notes' => [
                    'type' => 'string',
                    'description' => 'Update public notes',
                ],
                'internal_notes' => [
                    'type' => 'string',
                    'description' => 'Update internal notes',
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

        // Check if invoice is paid - warn about changing paid invoices
        if ($invoice->status === 'paid' && !empty($input['status']) && $input['status'] !== 'paid') {
            return [
                'error' => "La facture {$invoice->invoice_number} est marquée comme payée. Modifier une facture payée peut causer des problèmes comptables.",
                'suggestion' => 'Êtes-vous sûr de vouloir changer le statut d\'une facture payée ?',
            ];
        }

        // Track changes for response
        $changes = [];

        return DB::transaction(function () use ($invoice, $input, &$changes) {
            // Update invoice date
            if (!empty($input['invoice_date'])) {
                $oldDate = $invoice->invoice_date->format('Y-m-d');
                $invoice->invoice_date = $input['invoice_date'];
                $changes[] = "Date: {$oldDate} → {$input['invoice_date']}";
            }

            // Update due date
            if (!empty($input['due_date'])) {
                $oldDueDate = $invoice->due_date ? $invoice->due_date->format('Y-m-d') : 'non définie';
                $invoice->due_date = $input['due_date'];
                $changes[] = "Échéance: {$oldDueDate} → {$input['due_date']}";
            }

            // Update status
            if (!empty($input['status'])) {
                $oldStatus = $invoice->status;
                $invoice->status = $input['status'];
                $changes[] = "Statut: {$oldStatus} → {$input['status']}";
            }

            // Update reference
            if (isset($input['reference'])) {
                $invoice->reference = $input['reference'];
                $changes[] = "Référence mise à jour";
            }

            // Update notes
            if (isset($input['notes'])) {
                $invoice->notes = $input['notes'];
                $changes[] = "Notes publiques mises à jour";
            }

            // Update internal notes
            if (isset($input['internal_notes'])) {
                $invoice->internal_notes = $input['internal_notes'];
                $changes[] = "Notes internes mises à jour";
            }

            // Save changes
            $invoice->save();
            $invoice->refresh();
            $invoice->load('partner');

            if (empty($changes)) {
                return [
                    'warning' => 'Aucune modification spécifiée.',
                    'invoice' => [
                        'id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                    ],
                ];
            }

            return [
                'success' => true,
                'message' => "Facture {$invoice->invoice_number} mise à jour avec succès",
                'changes' => $changes,
                'invoice' => [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'partner' => $invoice->partner?->name,
                    'date' => $invoice->invoice_date->format('d/m/Y'),
                    'due_date' => $invoice->due_date?->format('d/m/Y'),
                    'status' => $invoice->status,
                    'reference' => $invoice->reference,
                    'total_incl_vat' => (float) $invoice->total_incl_vat,
                    'currency' => 'EUR',
                ],
            ];
        });
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
