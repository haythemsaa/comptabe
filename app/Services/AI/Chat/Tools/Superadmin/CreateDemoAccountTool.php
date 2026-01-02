<?php

namespace App\Services\AI\Chat\Tools\Superadmin;

use App\Models\Company;
use App\Models\User;
use App\Models\Partner;
use App\Models\Product;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Services\AI\Chat\Tools\AbstractTool;
use App\Services\AI\Chat\Tools\ToolContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateDemoAccountTool extends AbstractTool
{
    public function getName(): string
    {
        return 'create_demo_account';
    }

    public function getDescription(): string
    {
        return 'Creates a complete demo company account with sample data (invoices, customers, products). Only available for superadmins.';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'company_name' => [
                    'type' => 'string',
                    'description' => 'Name of the demo company',
                ],
                'user_email' => [
                    'type' => 'string',
                    'format' => 'email',
                    'description' => 'Email address for the demo user account',
                ],
                'user_name' => [
                    'type' => 'string',
                    'description' => 'Name of the demo user (first and last name)',
                ],
                'language' => [
                    'type' => 'string',
                    'enum' => ['fr', 'nl', 'en'],
                    'description' => 'Language for the demo account (default: fr)',
                ],
                'include_sample_data' => [
                    'type' => 'boolean',
                    'description' => 'Include sample invoices, customers, and products (default: true)',
                ],
            ],
            'required' => ['company_name', 'user_email', 'user_name'],
        ];
    }

    public function requiresConfirmation(): bool
    {
        return true;
    }

    public function execute(array $input, ToolContext $context): array
    {
        // Check if user is superadmin
        if (!$context->user->isSuperadmin()) {
            return [
                'error' => 'Seuls les superadministrateurs peuvent créer des comptes de démonstration.',
                'required_permission' => 'superadmin',
            ];
        }

        $email = strtolower(trim($input['user_email']));

        // Check if email already exists
        if (User::where('email', $email)->exists()) {
            return [
                'error' => "Un utilisateur avec l'email {$email} existe déjà.",
                'suggestion' => 'Utilisez un email différent ou supprimez le compte existant.',
            ];
        }

        return DB::transaction(function () use ($input, $context, $email) {
            // Generate random demo password
            $demoPassword = 'demo' . rand(1000, 9999);

            // Create company
            $company = Company::create([
                'name' => $input['company_name'],
                'vat_number' => 'BE' . str_pad(rand(100000000, 999999999), 10, '0', STR_PAD_LEFT),
                'email' => $email,
                'language' => $input['language'] ?? 'fr',
                'currency' => 'EUR',
                'country' => 'BE',
                'settings' => [
                    'is_demo' => true,
                    'demo_created_at' => now()->toIso8601String(),
                    'demo_created_by' => $context->user->id,
                ],
            ]);

            // Create user
            $user = User::create([
                'name' => $input['user_name'],
                'email' => $email,
                'password' => Hash::make($demoPassword),
                'email_verified_at' => now(),
            ]);

            // Attach user to company as owner
            $user->companies()->attach($company->id, [
                'role' => 'owner',
                'joined_at' => now(),
            ]);

            // Set as current company
            $user->update(['current_company_id' => $company->id]);

            $sampleDataCreated = false;

            // Create sample data if requested
            if ($input['include_sample_data'] ?? true) {
                $this->createSampleData($company, $user);
                $sampleDataCreated = true;
            }

            return [
                'success' => true,
                'message' => "Compte de démonstration créé avec succès pour {$company->name}",
                'company' => [
                    'id' => $company->id,
                    'name' => $company->name,
                    'vat_number' => $company->vat_number,
                    'email' => $company->email,
                    'language' => $company->language,
                ],
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => 'owner',
                ],
                'credentials' => [
                    'email' => $email,
                    'password' => $demoPassword,
                    'warning' => 'Conservez ces identifiants. Le mot de passe ne sera plus affiché.',
                ],
                'sample_data_created' => $sampleDataCreated,
                'login_url' => url('/login'),
                'next_steps' => [
                    "Envoyer les identifiants à l'utilisateur",
                    "L'utilisateur peut se connecter et explorer les fonctionnalités",
                    "Configurer Peppol si nécessaire",
                    "Paramétrer les intégrations bancaires",
                ],
            ];
        });
    }

    /**
     * Create sample data for demo account.
     */
    protected function createSampleData(Company $company, User $user): void
    {
        // Create sample customers
        $customers = [
            ['name' => 'SPRL TechCorp', 'vat' => 'BE0123456789', 'city' => 'Bruxelles'],
            ['name' => 'Services Pro SA', 'vat' => 'BE0987654321', 'city' => 'Liège'],
            ['name' => 'Entreprise Dupont', 'vat' => 'BE0555666777', 'city' => 'Gand'],
        ];

        $createdCustomers = [];
        foreach ($customers as $customerData) {
            $createdCustomers[] = Partner::create([
                'company_id' => $company->id,
                'type' => 'customer',
                'name' => $customerData['name'],
                'vat_number' => $customerData['vat'],
                'email' => strtolower(str_replace(' ', '', $customerData['name'])) . '@example.be',
                'city' => $customerData['city'],
                'country' => 'BE',
                'payment_terms' => 30,
            ]);
        }

        // Create sample products
        $products = [
            ['name' => 'Consultation', 'price' => 85.00],
            ['name' => 'Développement web', 'price' => 450.00],
            ['name' => 'Maintenance mensuelle', 'price' => 150.00],
        ];

        $createdProducts = [];
        foreach ($products as $productData) {
            $createdProducts[] = Product::create([
                'company_id' => $company->id,
                'name' => $productData['name'],
                'selling_price' => $productData['price'],
                'vat_rate' => 21,
                'status' => 'active',
            ]);
        }

        // Create sample invoices
        foreach ($createdCustomers as $index => $customer) {
            $invoice = Invoice::create([
                'company_id' => $company->id,
                'partner_id' => $customer->id,
                'type' => 'sale',
                'document_type' => 'invoice',
                'status' => $index === 0 ? 'paid' : ($index === 1 ? 'sent' : 'draft'),
                'invoice_date' => now()->subDays(30 - ($index * 10)),
                'due_date' => now()->subDays($index * 10),
                'currency' => 'EUR',
                'created_by' => $user->id,
            ]);

            // Add lines to invoice
            InvoiceLine::create([
                'invoice_id' => $invoice->id,
                'line_number' => 1,
                'description' => $createdProducts[$index]->name ?? 'Service professionnel',
                'quantity' => rand(1, 10),
                'unit_price' => $createdProducts[$index]->selling_price ?? 100,
                'vat_rate' => 21,
            ]);

            // Generate invoice number
            $invoice->generateInvoiceNumber();

            // Mark first invoice as paid
            if ($index === 0) {
                $invoice->amount_paid = $invoice->total_incl_vat;
                $invoice->amount_due = 0;
            }

            $invoice->save();
        }
    }
}
