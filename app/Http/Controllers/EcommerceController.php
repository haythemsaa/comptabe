<?php

namespace App\Http\Controllers;

use App\Models\EcommerceConnection;
use App\Models\EcommerceOrder;
use App\Models\EcommerceProductMapping;
use App\Models\EcommerceSyncLog;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;

class EcommerceController extends Controller
{
    public function index()
    {
        $connections = EcommerceConnection::where('company_id', auth()->user()->current_company_id)
            ->withCount(['orders', 'productMappings'])
            ->orderByDesc('created_at')
            ->get();

        $stats = [
            'total_orders' => EcommerceOrder::where('company_id', auth()->user()->current_company_id)->count(),
            'pending_orders' => EcommerceOrder::where('company_id', auth()->user()->current_company_id)
                ->where('sync_status', 'pending')
                ->count(),
            'invoiced_orders' => EcommerceOrder::where('company_id', auth()->user()->current_company_id)
                ->where('sync_status', 'invoiced')
                ->count(),
            'total_revenue' => EcommerceOrder::where('company_id', auth()->user()->current_company_id)
                ->where('sync_status', 'invoiced')
                ->sum('total'),
        ];

        return view('ecommerce.index', compact('connections', 'stats'));
    }

    public function createConnection()
    {
        return view('ecommerce.connections.create');
    }

    public function storeConnection(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'platform' => 'required|in:woocommerce,shopify,prestashop,magento,custom',
            'store_url' => 'required|url',
            'api_key' => 'required|string',
            'api_secret' => 'nullable|string',
            'auto_sync_orders' => 'boolean',
            'auto_sync_products' => 'boolean',
            'auto_sync_customers' => 'boolean',
            'auto_create_invoices' => 'boolean',
            'sync_interval_minutes' => 'integer|min:5|max:1440',
        ]);

        $validated['company_id'] = auth()->user()->current_company_id;
        $validated['is_active'] = true;

        // Test connection
        $testResult = $this->testConnection($validated);
        if (!$testResult['success']) {
            return back()->with('error', 'Connexion échouée: ' . $testResult['message'])->withInput();
        }

        EcommerceConnection::create($validated);

        return redirect()
            ->route('ecommerce.index')
            ->with('success', 'Connexion e-commerce créée avec succès.');
    }

    public function editConnection(EcommerceConnection $connection)
    {
        $this->authorize('update', $connection);

        return view('ecommerce.connections.edit', compact('connection'));
    }

    public function updateConnection(Request $request, EcommerceConnection $connection)
    {
        $this->authorize('update', $connection);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'store_url' => 'required|url',
            'api_key' => 'nullable|string',
            'api_secret' => 'nullable|string',
            'is_active' => 'boolean',
            'auto_sync_orders' => 'boolean',
            'auto_sync_products' => 'boolean',
            'auto_sync_customers' => 'boolean',
            'auto_create_invoices' => 'boolean',
            'sync_interval_minutes' => 'integer|min:5|max:1440',
        ]);

        // Keep existing keys if not provided
        if (empty($validated['api_key'])) {
            unset($validated['api_key']);
        }
        if (empty($validated['api_secret'])) {
            unset($validated['api_secret']);
        }

        $connection->update($validated);

        return redirect()
            ->route('ecommerce.index')
            ->with('success', 'Connexion mise à jour.');
    }

    public function destroyConnection(EcommerceConnection $connection)
    {
        $this->authorize('delete', $connection);

        $connection->delete();

        return redirect()
            ->route('ecommerce.index')
            ->with('success', 'Connexion supprimée.');
    }

    public function orders(Request $request)
    {
        $query = EcommerceOrder::where('company_id', auth()->user()->current_company_id)
            ->with(['connection', 'partner', 'invoice']);

        if ($request->filled('connection_id')) {
            $query->where('connection_id', $request->connection_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('sync_status')) {
            $query->where('sync_status', $request->sync_status);
        }

        if ($request->filled('search')) {
            $query->where('order_number', 'like', "%{$request->search}%");
        }

        $orders = $query->orderByDesc('order_date')->paginate(20);

        $connections = EcommerceConnection::where('company_id', auth()->user()->current_company_id)->get();

        return view('ecommerce.orders.index', compact('orders', 'connections'));
    }

    public function showOrder(EcommerceOrder $order)
    {
        $this->authorize('view', $order);

        $order->load(['connection', 'partner', 'invoice', 'items']);

        return view('ecommerce.orders.show', compact('order'));
    }

    public function createInvoiceFromOrder(EcommerceOrder $order)
    {
        $this->authorize('update', $order);

        if (!$order->canBeInvoiced()) {
            return back()->with('error', 'Cette commande ne peut pas être facturée.');
        }

        $invoice = $order->createInvoice();

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', 'Facture créée depuis la commande e-commerce.');
    }

    public function createInvoicesFromOrders(Request $request)
    {
        $validated = $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:ecommerce_orders,id',
        ]);

        $created = 0;
        $errors = [];

        foreach ($validated['order_ids'] as $orderId) {
            $order = EcommerceOrder::find($orderId);
            if ($order && $order->canBeInvoiced()) {
                try {
                    $order->createInvoice();
                    $created++;
                } catch (\Exception $e) {
                    $errors[] = "#{$order->order_number}: {$e->getMessage()}";
                }
            }
        }

        $message = "{$created} facture(s) créée(s).";
        if (!empty($errors)) {
            $message .= ' Erreurs: ' . implode(', ', $errors);
        }

        return back()->with($errors ? 'warning' : 'success', $message);
    }

    public function syncOrders(EcommerceConnection $connection)
    {
        $this->authorize('update', $connection);

        $log = EcommerceSyncLog::create([
            'connection_id' => $connection->id,
            'type' => 'orders',
            'direction' => 'import',
            'status' => 'started',
            'started_at' => now(),
        ]);

        try {
            $result = $this->fetchOrders($connection);

            $log->update([
                'status' => 'completed',
                'items_processed' => $result['processed'],
                'items_created' => $result['created'],
                'items_updated' => $result['updated'],
                'items_failed' => $result['failed'],
                'completed_at' => now(),
            ]);

            $connection->update(['last_sync_at' => now()]);

            return back()->with('success', "Synchronisation terminée: {$result['created']} créées, {$result['updated']} mises à jour.");

        } catch (\Exception $e) {
            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            return back()->with('error', 'Erreur de synchronisation: ' . $e->getMessage());
        }
    }

    public function productMappings(EcommerceConnection $connection)
    {
        $this->authorize('view', $connection);

        $mappings = $connection->productMappings()
            ->with('product')
            ->paginate(20);

        $unmappedProducts = Product::where('company_id', auth()->user()->current_company_id)
            ->whereNotIn('id', $connection->productMappings()->pluck('product_id'))
            ->orderBy('name')
            ->get();

        return view('ecommerce.mappings.index', compact('connection', 'mappings', 'unmappedProducts'));
    }

    public function storeMapping(Request $request, EcommerceConnection $connection)
    {
        $this->authorize('update', $connection);

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'external_id' => 'required|string',
            'external_sku' => 'nullable|string',
            'sync_stock' => 'boolean',
            'sync_price' => 'boolean',
        ]);

        $validated['connection_id'] = $connection->id;

        EcommerceProductMapping::create($validated);

        return back()->with('success', 'Mapping créé.');
    }

    public function destroyMapping(EcommerceProductMapping $mapping)
    {
        $this->authorize('delete', $mapping->connection);

        $mapping->delete();

        return back()->with('success', 'Mapping supprimé.');
    }

    public function syncLogs(EcommerceConnection $connection)
    {
        $this->authorize('view', $connection);

        $logs = $connection->syncLogs()
            ->orderByDesc('created_at')
            ->paginate(50);

        return view('ecommerce.logs.index', compact('connection', 'logs'));
    }

    protected function testConnection(array $config): array
    {
        try {
            switch ($config['platform']) {
                case 'woocommerce':
                    $response = Http::withBasicAuth($config['api_key'], $config['api_secret'] ?? '')
                        ->get(rtrim($config['store_url'], '/') . '/wp-json/wc/v3/system_status');
                    break;

                case 'shopify':
                    $response = Http::withHeaders([
                        'X-Shopify-Access-Token' => $config['api_key'],
                    ])->get(rtrim($config['store_url'], '/') . '/admin/api/2024-01/shop.json');
                    break;

                default:
                    return ['success' => true, 'message' => 'Test non disponible pour cette plateforme'];
            }

            if ($response->successful()) {
                return ['success' => true, 'message' => 'Connexion réussie'];
            }

            return ['success' => false, 'message' => 'Erreur ' . $response->status()];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    protected function fetchOrders(EcommerceConnection $connection): array
    {
        $result = ['processed' => 0, 'created' => 0, 'updated' => 0, 'failed' => 0];

        switch ($connection->platform) {
            case 'woocommerce':
                $result = $this->fetchWooCommerceOrders($connection);
                break;

            case 'shopify':
                $result = $this->fetchShopifyOrders($connection);
                break;
        }

        return $result;
    }

    protected function fetchWooCommerceOrders(EcommerceConnection $connection): array
    {
        $result = ['processed' => 0, 'created' => 0, 'updated' => 0, 'failed' => 0];

        $response = Http::withBasicAuth($connection->api_key, $connection->api_secret ?? '')
            ->get(rtrim($connection->store_url, '/') . '/wp-json/wc/v3/orders', [
                'per_page' => 100,
                'orderby' => 'date',
                'order' => 'desc',
            ]);

        if (!$response->successful()) {
            throw new \Exception('Erreur API WooCommerce: ' . $response->status());
        }

        foreach ($response->json() as $orderData) {
            $result['processed']++;

            try {
                $existing = EcommerceOrder::where('connection_id', $connection->id)
                    ->where('external_id', $orderData['id'])
                    ->first();

                $data = [
                    'company_id' => $connection->company_id,
                    'connection_id' => $connection->id,
                    'external_id' => $orderData['id'],
                    'order_number' => $orderData['number'],
                    'status' => $orderData['status'],
                    'currency' => $orderData['currency'],
                    'subtotal' => $orderData['total'] - $orderData['total_tax'] - $orderData['shipping_total'],
                    'tax_total' => $orderData['total_tax'],
                    'shipping_total' => $orderData['shipping_total'],
                    'discount_total' => $orderData['discount_total'],
                    'total' => $orderData['total'],
                    'payment_method' => $orderData['payment_method_title'],
                    'billing_address' => $orderData['billing'],
                    'shipping_address' => $orderData['shipping'],
                    'customer_data' => [
                        'name' => $orderData['billing']['first_name'] . ' ' . $orderData['billing']['last_name'],
                        'email' => $orderData['billing']['email'],
                        'phone' => $orderData['billing']['phone'],
                    ],
                    'customer_note' => $orderData['customer_note'],
                    'order_date' => $orderData['date_created'],
                    'raw_data' => $orderData,
                ];

                if ($existing) {
                    $existing->update($data);
                    $result['updated']++;
                } else {
                    $order = EcommerceOrder::create($data + ['sync_status' => 'synced']);

                    // Import items
                    foreach ($orderData['line_items'] as $item) {
                        $order->items()->create([
                            'external_product_id' => $item['product_id'],
                            'sku' => $item['sku'],
                            'name' => $item['name'],
                            'quantity' => $item['quantity'],
                            'unit_price' => $item['price'],
                            'tax_amount' => $item['total_tax'],
                            'total' => $item['total'],
                        ]);
                    }

                    $result['created']++;
                }
            } catch (\Exception $e) {
                $result['failed']++;
            }
        }

        return $result;
    }

    protected function fetchShopifyOrders(EcommerceConnection $connection): array
    {
        $result = ['processed' => 0, 'created' => 0, 'updated' => 0, 'failed' => 0];

        $response = Http::withHeaders([
            'X-Shopify-Access-Token' => $connection->api_key,
        ])->get(rtrim($connection->store_url, '/') . '/admin/api/2024-01/orders.json', [
            'limit' => 100,
            'status' => 'any',
        ]);

        if (!$response->successful()) {
            throw new \Exception('Erreur API Shopify: ' . $response->status());
        }

        foreach ($response->json()['orders'] ?? [] as $orderData) {
            $result['processed']++;

            try {
                $existing = EcommerceOrder::where('connection_id', $connection->id)
                    ->where('external_id', $orderData['id'])
                    ->first();

                $data = [
                    'company_id' => $connection->company_id,
                    'connection_id' => $connection->id,
                    'external_id' => $orderData['id'],
                    'order_number' => $orderData['name'],
                    'status' => $orderData['financial_status'],
                    'currency' => $orderData['currency'],
                    'subtotal' => $orderData['subtotal_price'],
                    'tax_total' => $orderData['total_tax'],
                    'shipping_total' => collect($orderData['shipping_lines'])->sum('price'),
                    'discount_total' => $orderData['total_discounts'],
                    'total' => $orderData['total_price'],
                    'payment_method' => $orderData['payment_gateway_names'][0] ?? null,
                    'billing_address' => $orderData['billing_address'] ?? [],
                    'shipping_address' => $orderData['shipping_address'] ?? [],
                    'customer_data' => $orderData['customer'] ?? [],
                    'customer_note' => $orderData['note'],
                    'order_date' => $orderData['created_at'],
                    'raw_data' => $orderData,
                ];

                if ($existing) {
                    $existing->update($data);
                    $result['updated']++;
                } else {
                    $order = EcommerceOrder::create($data + ['sync_status' => 'synced']);

                    foreach ($orderData['line_items'] as $item) {
                        $order->items()->create([
                            'external_product_id' => $item['product_id'],
                            'sku' => $item['sku'],
                            'name' => $item['title'],
                            'quantity' => $item['quantity'],
                            'unit_price' => $item['price'],
                            'tax_amount' => collect($item['tax_lines'])->sum('price'),
                            'total' => $item['price'] * $item['quantity'],
                        ]);
                    }

                    $result['created']++;
                }
            } catch (\Exception $e) {
                $result['failed']++;
            }
        }

        return $result;
    }
}
