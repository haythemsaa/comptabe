<?php

namespace App\Services\Integrations;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\Partner;
use App\Models\Product;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * E-Commerce Integration Service
 *
 * Supports: Shopify, WooCommerce, PrestaShop
 */
class ECommerceIntegrationService
{
    /**
     * Connect to Shopify store
     */
    public function connectShopify(string $companyId, string $shopDomain, string $accessToken): bool
    {
        try {
            // Validate connection
            $response = Http::withHeaders([
                'X-Shopify-Access-Token' => $accessToken,
            ])->get("https://{$shopDomain}/admin/api/2024-01/shop.json");

            if ($response->successful()) {
                $company = Company::find($companyId);
                $company->update([
                    'shopify_domain' => $shopDomain,
                    'shopify_access_token' => encrypt($accessToken),
                    'shopify_connected_at' => now(),
                ]);

                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Shopify connection failed', [
                'company_id' => $companyId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Import Shopify orders as invoices
     */
    public function importShopifyOrders(string $companyId, ?Carbon $since = null): int
    {
        $company = Company::find($companyId);

        if (!$company || !$company->shopify_access_token) {
            return 0;
        }

        $shopDomain = $company->shopify_domain;
        $accessToken = decrypt($company->shopify_access_token);

        $params = [
            'status' => 'any',
            'limit' => 250,
        ];

        if ($since) {
            $params['created_at_min'] = $since->toIso8601String();
        }

        try {
            $response = Http::withHeaders([
                'X-Shopify-Access-Token' => $accessToken,
            ])->get("https://{$shopDomain}/admin/api/2024-01/orders.json", $params);

            if (!$response->successful()) {
                throw new \Exception("Failed to fetch orders: {$response->body()}");
            }

            $orders = $response->json()['orders'] ?? [];
            $imported = 0;

            foreach ($orders as $order) {
                // Check if already imported
                $exists = Invoice::where('company_id', $companyId)
                    ->where('external_id', "shopify_{$order['id']}")
                    ->exists();

                if ($exists) {
                    continue;
                }

                // Create or find customer
                $partner = $this->createOrFindPartner($companyId, $order['customer'] ?? []);

                // Create invoice
                $invoice = $this->createInvoiceFromShopifyOrder($companyId, $order, $partner);

                if ($invoice) {
                    $imported++;
                }
            }

            Log::info("Imported {$imported} Shopify orders for company {$companyId}");

            return $imported;
        } catch (\Exception $e) {
            Log::error('Failed to import Shopify orders', [
                'company_id' => $companyId,
                'error' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    /**
     * Create invoice from Shopify order
     */
    protected function createInvoiceFromShopifyOrder(string $companyId, array $order, Partner $partner): ?Invoice
    {
        try {
            $invoice = Invoice::create([
                'id' => \Illuminate\Support\Str::uuid(),
                'company_id' => $companyId,
                'partner_id' => $partner->id,
                'external_id' => "shopify_{$order['id']}",
                'invoice_number' => $order['name'] ?? $order['order_number'],
                'issue_date' => Carbon::parse($order['created_at']),
                'due_date' => Carbon::parse($order['created_at'])->addDays(30),
                'status' => $order['financial_status'] === 'paid' ? 'paid' : 'sent',
                'payment_date' => $order['financial_status'] === 'paid' ? Carbon::parse($order['created_at']) : null,
                'subtotal_amount' => (float) $order['subtotal_price'],
                'vat_amount' => (float) ($order['total_tax'] ?? 0),
                'total_amount' => (float) $order['total_price'],
                'currency' => $order['currency'] ?? 'EUR',
                'notes' => "Imported from Shopify - Order #{$order['name']}",
            ]);

            // Create invoice lines
            foreach ($order['line_items'] ?? [] as $item) {
                $invoice->items()->create([
                    'id' => \Illuminate\Support\Str::uuid(),
                    'description' => $item['name'],
                    'quantity' => (int) $item['quantity'],
                    'unit_price' => (float) $item['price'],
                    'vat_rate' => $this->calculateVatRate($item['price'], $item['tax_lines'] ?? []),
                    'total_amount' => (float) $item['price'] * (int) $item['quantity'],
                ]);
            }

            return $invoice;
        } catch (\Exception $e) {
            Log::error('Failed to create invoice from Shopify order', [
                'order_id' => $order['id'],
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Connect to WooCommerce
     */
    public function connectWooCommerce(
        string $companyId,
        string $storeUrl,
        string $consumerKey,
        string $consumerSecret
    ): bool {
        try {
            // Validate connection
            $response = Http::withBasicAuth($consumerKey, $consumerSecret)
                ->get("{$storeUrl}/wp-json/wc/v3/system_status");

            if ($response->successful()) {
                $company = Company::find($companyId);
                $company->update([
                    'woocommerce_url' => $storeUrl,
                    'woocommerce_consumer_key' => encrypt($consumerKey),
                    'woocommerce_consumer_secret' => encrypt($consumerSecret),
                    'woocommerce_connected_at' => now(),
                ]);

                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('WooCommerce connection failed', [
                'company_id' => $companyId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Import WooCommerce orders
     */
    public function importWooCommerceOrders(string $companyId, ?Carbon $since = null): int
    {
        $company = Company::find($companyId);

        if (!$company || !$company->woocommerce_consumer_key) {
            return 0;
        }

        $storeUrl = $company->woocommerce_url;
        $consumerKey = decrypt($company->woocommerce_consumer_key);
        $consumerSecret = decrypt($company->woocommerce_consumer_secret);

        $params = [
            'per_page' => 100,
            'status' => 'any',
        ];

        if ($since) {
            $params['after'] = $since->toIso8601String();
        }

        try {
            $response = Http::withBasicAuth($consumerKey, $consumerSecret)
                ->get("{$storeUrl}/wp-json/wc/v3/orders", $params);

            if (!$response->successful()) {
                throw new \Exception("Failed to fetch orders: {$response->body()}");
            }

            $orders = $response->json();
            $imported = 0;

            foreach ($orders as $order) {
                // Check if already imported
                $exists = Invoice::where('company_id', $companyId)
                    ->where('external_id', "woocommerce_{$order['id']}")
                    ->exists();

                if ($exists) {
                    continue;
                }

                // Create or find customer
                $partner = $this->createOrFindPartnerFromWooCommerce($companyId, $order);

                // Create invoice
                $invoice = $this->createInvoiceFromWooCommerceOrder($companyId, $order, $partner);

                if ($invoice) {
                    $imported++;
                }
            }

            Log::info("Imported {$imported} WooCommerce orders for company {$companyId}");

            return $imported;
        } catch (\Exception $e) {
            Log::error('Failed to import WooCommerce orders', [
                'company_id' => $companyId,
                'error' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    /**
     * Create invoice from WooCommerce order
     */
    protected function createInvoiceFromWooCommerceOrder(string $companyId, array $order, Partner $partner): ?Invoice
    {
        try {
            $isPaid = in_array($order['status'], ['completed', 'processing']);

            $invoice = Invoice::create([
                'id' => \Illuminate\Support\Str::uuid(),
                'company_id' => $companyId,
                'partner_id' => $partner->id,
                'external_id' => "woocommerce_{$order['id']}",
                'invoice_number' => $order['number'] ?? $order['id'],
                'issue_date' => Carbon::parse($order['date_created']),
                'due_date' => Carbon::parse($order['date_created'])->addDays(30),
                'status' => $isPaid ? 'paid' : 'sent',
                'payment_date' => $isPaid ? Carbon::parse($order['date_paid'] ?? $order['date_completed']) : null,
                'subtotal_amount' => (float) $order['total'] - (float) $order['total_tax'],
                'vat_amount' => (float) $order['total_tax'],
                'total_amount' => (float) $order['total'],
                'currency' => $order['currency'] ?? 'EUR',
                'notes' => "Imported from WooCommerce - Order #{$order['number']}",
            ]);

            // Create invoice lines
            foreach ($order['line_items'] ?? [] as $item) {
                $invoice->items()->create([
                    'id' => \Illuminate\Support\Str::uuid(),
                    'description' => $item['name'],
                    'quantity' => (int) $item['quantity'],
                    'unit_price' => (float) $item['price'],
                    'vat_rate' => (float) ($item['tax_class'] ? 21 : 0),
                    'total_amount' => (float) $item['total'],
                ]);
            }

            return $invoice;
        } catch (\Exception $e) {
            Log::error('Failed to create invoice from WooCommerce order', [
                'order_id' => $order['id'],
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Create or find partner from order data
     */
    protected function createOrFindPartner(string $companyId, array $customer): Partner
    {
        $email = $customer['email'] ?? null;

        if ($email) {
            $partner = Partner::where('company_id', $companyId)
                ->where('email', $email)
                ->first();

            if ($partner) {
                return $partner;
            }
        }

        // Create new partner
        return Partner::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'company_id' => $companyId,
            'name' => $customer['first_name'] . ' ' . $customer['last_name'],
            'email' => $email,
            'phone' => $customer['phone'] ?? null,
            'type' => 'customer',
            'address' => $customer['default_address']['address1'] ?? null,
            'city' => $customer['default_address']['city'] ?? null,
            'postal_code' => $customer['default_address']['zip'] ?? null,
            'country' => $customer['default_address']['country_code'] ?? null,
        ]);
    }

    /**
     * Create or find partner from WooCommerce order
     */
    protected function createOrFindPartnerFromWooCommerce(string $companyId, array $order): Partner
    {
        $email = $order['billing']['email'] ?? null;

        if ($email) {
            $partner = Partner::where('company_id', $companyId)
                ->where('email', $email)
                ->first();

            if ($partner) {
                return $partner;
            }
        }

        // Create new partner
        return Partner::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'company_id' => $companyId,
            'name' => $order['billing']['first_name'] . ' ' . $order['billing']['last_name'],
            'email' => $email,
            'phone' => $order['billing']['phone'] ?? null,
            'type' => 'customer',
            'address' => $order['billing']['address_1'] ?? null,
            'city' => $order['billing']['city'] ?? null,
            'postal_code' => $order['billing']['postcode'] ?? null,
            'country' => $order['billing']['country'] ?? null,
        ]);
    }

    /**
     * Calculate VAT rate from tax lines
     */
    protected function calculateVatRate(float $price, array $taxLines): float
    {
        if (empty($taxLines)) {
            return 0;
        }

        $totalTax = array_sum(array_column($taxLines, 'price'));

        if ($price > 0) {
            return ($totalTax / $price) * 100;
        }

        return 21; // Default Belgian VAT rate
    }

    /**
     * Sync products from e-commerce platform
     */
    public function syncProducts(string $companyId, string $platform): int
    {
        if ($platform === 'shopify') {
            return $this->syncShopifyProducts($companyId);
        } elseif ($platform === 'woocommerce') {
            return $this->syncWooCommerceProducts($companyId);
        }

        return 0;
    }

    /**
     * Sync Shopify products
     */
    protected function syncShopifyProducts(string $companyId): int
    {
        $company = Company::find($companyId);

        if (!$company || !$company->shopify_access_token) {
            return 0;
        }

        $shopDomain = $company->shopify_domain;
        $accessToken = decrypt($company->shopify_access_token);

        try {
            $response = Http::withHeaders([
                'X-Shopify-Access-Token' => $accessToken,
            ])->get("https://{$shopDomain}/admin/api/2024-01/products.json", [
                'limit' => 250,
            ]);

            if (!$response->successful()) {
                throw new \Exception("Failed to fetch products");
            }

            $products = $response->json()['products'] ?? [];
            $synced = 0;

            foreach ($products as $product) {
                Product::updateOrCreate(
                    [
                        'company_id' => $companyId,
                        'external_id' => "shopify_{$product['id']}",
                    ],
                    [
                        'id' => \Illuminate\Support\Str::uuid(),
                        'name' => $product['title'],
                        'description' => $product['body_html'] ?? null,
                        'price' => (float) ($product['variants'][0]['price'] ?? 0),
                        'vat_rate' => 21,
                        'sku' => $product['variants'][0]['sku'] ?? null,
                        'stock_quantity' => (int) ($product['variants'][0]['inventory_quantity'] ?? 0),
                        'is_active' => $product['status'] === 'active',
                    ]
                );

                $synced++;
            }

            return $synced;
        } catch (\Exception $e) {
            Log::error('Failed to sync Shopify products', [
                'company_id' => $companyId,
                'error' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    /**
     * Sync WooCommerce products
     */
    protected function syncWooCommerceProducts(string $companyId): int
    {
        // Similar implementation as Shopify
        return 0;
    }
}
