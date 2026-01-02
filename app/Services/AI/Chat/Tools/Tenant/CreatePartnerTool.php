<?php

namespace App\Services\AI\Chat\Tools\Tenant;

use App\Models\Partner;
use App\Services\AI\Chat\Tools\AbstractTool;
use App\Services\AI\Chat\Tools\ToolContext;

class CreatePartnerTool extends AbstractTool
{
    public function getName(): string
    {
        return 'create_partner';
    }

    public function getDescription(): string
    {
        return 'Creates a new customer or supplier. Use this when you need to add a new business partner (client or fournisseur).';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'name' => [
                    'type' => 'string',
                    'description' => 'Company name or person name',
                ],
                'type' => [
                    'type' => 'string',
                    'enum' => ['customer', 'supplier', 'both'],
                    'description' => 'Partner type (default: customer)',
                ],
                'vat_number' => [
                    'type' => 'string',
                    'description' => 'Belgian VAT number (format: BE0123456789)',
                ],
                'email' => [
                    'type' => 'string',
                    'format' => 'email',
                    'description' => 'Email address',
                ],
                'phone' => [
                    'type' => 'string',
                    'description' => 'Phone number',
                ],
                'address' => [
                    'type' => 'string',
                    'description' => 'Street address',
                ],
                'postal_code' => [
                    'type' => 'string',
                    'description' => 'Postal code',
                ],
                'city' => [
                    'type' => 'string',
                    'description' => 'City',
                ],
                'country' => [
                    'type' => 'string',
                    'description' => 'Country code (default: BE for Belgium)',
                ],
                'payment_terms' => [
                    'type' => 'integer',
                    'description' => 'Payment terms in days (default: 30)',
                ],
                'notes' => [
                    'type' => 'string',
                    'description' => 'Internal notes about this partner',
                ],
            ],
            'required' => ['name'],
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

        // Check if partner already exists with same name
        $existingPartner = Partner::where('company_id', $context->company->id)
            ->where('name', 'like', $input['name'])
            ->first();

        if ($existingPartner) {
            return [
                'error' => "Un partenaire avec le nom '{$input['name']}' existe déjà.",
                'existing_partner' => [
                    'id' => $existingPartner->id,
                    'name' => $existingPartner->name,
                    'type' => $existingPartner->type,
                    'vat_number' => $existingPartner->vat_number,
                ],
                'suggestion' => 'Utilisez un nom différent ou modifiez le partenaire existant.',
            ];
        }

        // Validate Belgian VAT number if provided
        if (!empty($input['vat_number'])) {
            $vatNumber = $this->cleanVatNumber($input['vat_number']);

            if (!$this->isValidBelgianVat($vatNumber)) {
                return [
                    'error' => "Le numéro de TVA '{$input['vat_number']}' n'est pas valide.",
                    'suggestion' => 'Format attendu : BE0123456789 (10 chiffres après BE)',
                ];
            }

            // Check if VAT already exists
            $existingVat = Partner::where('company_id', $context->company->id)
                ->where('vat_number', $vatNumber)
                ->first();

            if ($existingVat) {
                return [
                    'error' => "Un partenaire avec le numéro de TVA {$vatNumber} existe déjà : {$existingVat->name}",
                    'existing_partner' => [
                        'id' => $existingVat->id,
                        'name' => $existingVat->name,
                    ],
                ];
            }

            $input['vat_number'] = $vatNumber;
        }

        // Create partner
        $partner = Partner::create([
            'company_id' => $context->company->id,
            'type' => $input['type'] ?? 'customer',
            'name' => $input['name'],
            'vat_number' => $input['vat_number'] ?? null,
            'email' => $input['email'] ?? null,
            'phone' => $input['phone'] ?? null,
            'address' => $input['address'] ?? null,
            'postal_code' => $input['postal_code'] ?? null,
            'city' => $input['city'] ?? null,
            'country' => $input['country'] ?? 'BE',
            'payment_terms' => $input['payment_terms'] ?? 30,
            'notes' => $input['notes'] ?? null,
        ]);

        $typeLabel = [
            'customer' => 'client',
            'supplier' => 'fournisseur',
            'both' => 'client et fournisseur',
        ][$partner->type] ?? $partner->type;

        return [
            'success' => true,
            'message' => "Partenaire '{$partner->name}' créé avec succès comme {$typeLabel}",
            'partner' => [
                'id' => $partner->id,
                'name' => $partner->name,
                'type' => $partner->type,
                'vat_number' => $partner->vat_number,
                'email' => $partner->email,
                'phone' => $partner->phone,
                'city' => $partner->city,
                'country' => $partner->country,
                'payment_terms' => $partner->payment_terms,
            ],
            'next_steps' => [
                "Utilisez cet ID ({$partner->id}) pour créer des factures ou devis",
                "Vous pouvez mettre à jour les informations si nécessaire",
            ],
        ];
    }

    /**
     * Clean VAT number (remove spaces, dots, hyphens).
     */
    protected function cleanVatNumber(string $vat): string
    {
        // Remove spaces, dots, hyphens
        $clean = preg_replace('/[\s\.\-]/', '', strtoupper($vat));

        // Ensure BE prefix
        if (!str_starts_with($clean, 'BE')) {
            $clean = 'BE' . $clean;
        }

        return $clean;
    }

    /**
     * Validate Belgian VAT number.
     */
    protected function isValidBelgianVat(string $vat): bool
    {
        // Must start with BE followed by 10 digits
        if (!preg_match('/^BE[0-9]{10}$/', $vat)) {
            return false;
        }

        // Extract digits
        $digits = substr($vat, 2);

        // Validate checksum
        $base = (int) substr($digits, 0, 8);
        $check = (int) substr($digits, 8, 2);

        $modulo = 97 - ($base % 97);

        return $modulo === $check;
    }
}
