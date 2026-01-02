<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Partner;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PartnerFactory extends Factory
{
    protected $model = Partner::class;

    public function definition(): array
    {
        $companyNumber = fake()->numerify('0#########');

        return [
            'id' => Str::uuid(),
            'company_id' => Company::factory(),
            'type' => fake()->randomElement(['customer', 'supplier', 'both']),
            'name' => fake()->company(),
            'vat_number' => 'BE' . $companyNumber,
            'email' => fake()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'address_street' => fake()->streetAddress(),
            'address_city' => fake()->city(),
            'address_postal_code' => fake()->postcode(),
            'address_country' => 'BE',
            'payment_term_days' => fake()->randomElement([0, 15, 30, 60]),
            'is_active' => true,
            'peppol_capable' => false,
        ];
    }

    public function customer(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'customer',
        ]);
    }

    public function supplier(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'supplier',
        ]);
    }

    public function peppolCapable(): static
    {
        return $this->state(fn (array $attributes) => [
            'peppol_capable' => true,
            'peppol_id' => '0208:' . Str::replace('BE', '', $attributes['vat_number']),
        ]);
    }
}
