<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        $companyNumber = fake()->numerify('0#########');

        return [
            'id' => Str::uuid(),
            'name' => fake()->company(),
            'legal_name' => fake()->company() . ' ' . fake()->randomElement(['SA', 'SPRL', 'SRL', 'ASBL']),
            'legal_form' => fake()->randomElement(['SA', 'SPRL', 'SRL', 'ASBL', 'SNC', 'SCS']),
            'vat_number' => 'BE' . $companyNumber,
            'company_number' => $companyNumber,
            'email' => fake()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'website' => fake()->url(),
            'address_street' => fake()->streetAddress(),
            'address_city' => fake()->city(),
            'address_postal_code' => fake()->postcode(),
            'address_country' => 'BE',
            'iban' => fake()->iban('BE'),
            'bic' => fake()->swiftBicNumber(),
            'is_active' => true,
            'peppol_enabled' => false,
        ];
    }

    public function peppolEnabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'peppol_enabled' => true,
            'peppol_id' => '0208:' . Str::replace('BE', '', $attributes['vat_number']),
        ]);
    }
}
