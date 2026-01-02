<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        $invoiceDate = fake()->dateTimeBetween('-3 months', 'now');
        $dueDate = (clone $invoiceDate)->modify('+30 days');
        $subtotal = fake()->randomFloat(2, 100, 10000);
        $vatAmount = $subtotal * 0.21;

        return [
            'id' => Str::uuid(),
            'company_id' => Company::factory(),
            'partner_id' => Partner::factory(),
            'created_by' => User::factory(),
            'type' => 'sale',
            'invoice_number' => 'INV-' . fake()->unique()->numerify('####-####'),
            'invoice_date' => $invoiceDate,
            'due_date' => $dueDate,
            'status' => fake()->randomElement(['draft', 'sent', 'paid', 'overdue']),
            'subtotal' => $subtotal,
            'vat_amount' => $vatAmount,
            'total_incl_vat' => $subtotal + $vatAmount,
            'currency' => 'EUR',
            'structured_communication' => $this->generateStructuredCommunication(),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }

    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'sent',
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'overdue',
            'due_date' => now()->subDays(15),
        ]);
    }

    public function purchase(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'purchase',
            'invoice_number' => 'PUR-' . fake()->unique()->numerify('####-####'),
        ]);
    }

    protected function generateStructuredCommunication(): string
    {
        $random = str_pad(mt_rand(0, 9999999999), 10, '0', STR_PAD_LEFT);
        $modulo = (int) $random % 97;
        $checkDigits = $modulo === 0 ? '97' : str_pad($modulo, 2, '0', STR_PAD_LEFT);
        $full = $random . $checkDigits;

        return '+++' . substr($full, 0, 3) . '/' . substr($full, 3, 4) . '/' . substr($full, 7) . '+++';
    }
}
