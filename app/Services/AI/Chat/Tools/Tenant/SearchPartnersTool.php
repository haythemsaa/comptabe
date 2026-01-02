<?php

namespace App\Services\AI\Chat\Tools\Tenant;

use App\Models\Partner;
use App\Services\AI\Chat\Tools\AbstractTool;
use App\Services\AI\Chat\Tools\ToolContext;

class SearchPartnersTool extends AbstractTool
{
    public function getName(): string
    {
        return 'search_partners';
    }

    public function getDescription(): string
    {
        return 'Search for customers or suppliers by name, VAT number, email, or other criteria. Use this before creating invoices/quotes to find the partner ID.';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'search' => [
                    'type' => 'string',
                    'description' => 'General search term (searches in name, email, VAT number, reference)',
                ],
                'name' => [
                    'type' => 'string',
                    'description' => 'Search by partner name (partial match)',
                ],
                'vat_number' => [
                    'type' => 'string',
                    'description' => 'Search by Belgian VAT number (e.g., BE0123456789)',
                ],
                'email' => [
                    'type' => 'string',
                    'description' => 'Search by email address',
                ],
                'type' => [
                    'type' => 'string',
                    'enum' => ['customer', 'supplier', 'both'],
                    'description' => 'Filter by partner type (default: customer)',
                ],
                'peppol_capable' => [
                    'type' => 'boolean',
                    'description' => 'Filter partners that can receive Peppol invoices',
                ],
                'limit' => [
                    'type' => 'integer',
                    'description' => 'Maximum number of results to return (default: 10, max: 50)',
                ],
            ],
            'required' => [],
        ];
    }

    public function execute(array $input, ToolContext $context): array
    {
        // Validate tenant access
        $this->validateTenantAccess($context->user, $context->company);

        // Build query
        $query = Partner::where('company_id', $context->company->id);

        // General search
        if (!empty($input['search'])) {
            $search = $input['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('vat_number', 'like', "%{$search}%")
                    ->orWhere('reference', 'like', "%{$search}%");
            });
        }

        // Specific filters
        if (!empty($input['name'])) {
            $query->where('name', 'like', '%' . $input['name'] . '%');
        }

        if (!empty($input['vat_number'])) {
            $vatClean = preg_replace('/[^0-9]/', '', $input['vat_number']);
            $query->where('vat_number', 'like', '%' . $vatClean . '%');
        }

        if (!empty($input['email'])) {
            $query->where('email', 'like', '%' . $input['email'] . '%');
        }

        // Type filter
        $type = $input['type'] ?? 'customer';
        if ($type !== 'both') {
            $query->where('type', $type);
        }

        // Peppol filter
        if (isset($input['peppol_capable']) && $input['peppol_capable']) {
            $query->whereNotNull('peppol_id');
        }

        // Limit
        $limit = min($input['limit'] ?? 10, 50);

        // Get results
        $partners = $query->orderBy('name')
            ->limit($limit)
            ->get();

        // Format results
        $results = $partners->map(function ($partner) {
            return [
                'id' => $partner->id,
                'name' => $partner->name,
                'type' => $partner->type,
                'vat_number' => $partner->vat_number,
                'email' => $partner->email,
                'phone' => $partner->phone,
                'city' => $partner->city,
                'country' => $partner->country,
                'peppol_capable' => !empty($partner->peppol_id),
                'peppol_id' => $partner->peppol_id,
                'payment_terms' => $partner->payment_terms,
            ];
        })->toArray();

        $message = count($results) > 0
            ? "Trouvé " . count($results) . " partenaire(s)"
            : "Aucun partenaire trouvé avec ces critères";

        if (count($results) === 1) {
            $message .= ". Utilisez cet ID pour créer une facture ou un devis.";
        } elseif (count($results) > 1) {
            $message .= ". Affinez votre recherche ou choisissez l'ID approprié.";
        }

        return [
            'count' => count($results),
            'partners' => $results,
            'message' => $message,
        ];
    }
}
