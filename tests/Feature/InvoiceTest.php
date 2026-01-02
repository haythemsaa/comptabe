<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->company = Company::factory()->create();
        $this->user->companies()->attach($this->company->id, [
            'role' => 'owner',
            'is_default' => true,
        ]);

        $this->actingAs($this->user);
        session(['current_tenant_id' => $this->company->id]);
    }

    public function test_invoice_list_can_be_rendered(): void
    {
        $response = $this->get(route('invoices.index'));

        $response->assertStatus(200);
    }

    public function test_invoice_create_form_can_be_rendered(): void
    {
        $response = $this->get(route('invoices.create'));

        $response->assertStatus(200);
    }

    public function test_invoice_can_be_created(): void
    {
        $partner = Partner::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->post(route('invoices.store'), [
            'partner_id' => $partner->id,
            'invoice_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'lines' => [
                [
                    'description' => 'Test service',
                    'quantity' => 1,
                    'unit_price' => 100.00,
                    'vat_rate' => 21,
                ],
            ],
        ]);

        $this->assertDatabaseHas('invoices', [
            'partner_id' => $partner->id,
            'company_id' => $this->company->id,
        ]);
    }

    public function test_invoice_can_be_viewed(): void
    {
        $invoice = Invoice::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->get(route('invoices.show', $invoice));

        $response->assertStatus(200);
    }

    public function test_invoice_pdf_can_be_generated(): void
    {
        $invoice = Invoice::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->get(route('invoices.pdf', $invoice));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_user_cannot_access_other_company_invoices(): void
    {
        $otherCompany = Company::factory()->create();
        $invoice = Invoice::factory()->create([
            'company_id' => $otherCompany->id,
        ]);

        $response = $this->get(route('invoices.show', $invoice));

        $response->assertStatus(403);
    }
}
