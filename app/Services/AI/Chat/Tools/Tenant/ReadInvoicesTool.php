<?php

namespace App\Services\AI\Chat\Tools\Tenant;

use App\Models\Invoice;
use App\Services\AI\Chat\Tools\AbstractTool;
use App\Services\AI\Chat\Tools\ToolContext;

class ReadInvoicesTool extends AbstractTool
{
    public function getName(): string
    {
        return 'read_invoices';
    }

    public function getDescription(): string
    {
        return 'Retrieves invoices with optional filters. Use this to see invoice lists, search for specific invoices, or get invoice details.';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'status' => [
                    'type' => 'string',
                    'enum' => ['draft', 'sent', 'paid', 'overdue', 'cancelled'],
                    'description' => 'Filter by invoice status',
                ],
                'partner_name' => [
                    'type' => 'string',
                    'description' => 'Search invoices by customer/partner name',
                ],
                'date_from' => [
                    'type' => 'string',
                    'format' => 'date',
                    'description' => 'Filter invoices from this date (YYYY-MM-DD)',
                ],
                'date_to' => [
                    'type' => 'string',
                    'format' => 'date',
                    'description' => 'Filter invoices until this date (YYYY-MM-DD)',
                ],
                'limit' => [
                    'type' => 'integer',
                    'description' => 'Maximum number of invoices to return (default: 10, max: 50)',
                    'default' => 10,
                ],
            ],
            'required' => [],
        ];
    }

    public function execute(array $input, ToolContext $context): array
    {
        // Validate tenant access
        $this->validateTenantAccess($context->user, $context->company);

        // Build query (tenant scope applied automatically)
        $query = Invoice::query();

        // Apply filters
        if (!empty($input['status'])) {
            $query->where('status', $input['status']);
        }

        if (!empty($input['partner_name'])) {
            $query->whereHas('partner', function ($q) use ($input) {
                $q->where('name', 'like', '%' . $input['partner_name'] . '%');
            });
        }

        if (!empty($input['date_from'])) {
            $query->where('invoice_date', '>=', $input['date_from']);
        }

        if (!empty($input['date_to'])) {
            $query->where('invoice_date', '<=', $input['date_to']);
        }

        // Limit
        $limit = min($input['limit'] ?? 10, 50);

        // Get invoices with partner info
        $invoices = $query->with('partner:id,name')
            ->orderBy('invoice_date', 'desc')
            ->limit($limit)
            ->get();

        // Format results
        $results = $invoices->map(function ($invoice) {
            return [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'partner' => $invoice->partner?->name,
                'date' => $invoice->invoice_date->format('Y-m-d'),
                'due_date' => $invoice->due_date?->format('Y-m-d'),
                'status' => $invoice->status,
                'total_excl_vat' => (float) $invoice->total_excl_vat,
                'total_incl_vat' => (float) $invoice->total_incl_vat,
                'currency' => 'EUR',
            ];
        })->toArray();

        return [
            'count' => count($results),
            'invoices' => $results,
            'message' => count($results) > 0
                ? "Trouvé " . count($results) . " facture(s)"
                : "Aucune facture trouvée avec ces critères",
        ];
    }
}
