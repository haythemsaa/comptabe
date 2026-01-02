<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Partner;
use App\Models\Product;
use App\Models\Quote;
use App\Models\Employee;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Global search across multiple entities.
     */
    public function global(Request $request)
    {
        $query = $request->input('q', '');

        if (strlen($query) < 2) {
            return response()->json([
                'results' => [],
                'query' => $query,
            ]);
        }

        $companyId = auth()->user()->currentCompany->id;
        $results = [];

        // Search Invoices (Sales)
        $invoices = Invoice::where('company_id', $companyId)
            ->where('type', 'out')
            ->where(function ($q) use ($query) {
                $q->where('invoice_number', 'LIKE', "%{$query}%")
                    ->orWhereHas('partner', function ($q) use ($query) {
                        $q->where('name', 'LIKE', "%{$query}%");
                    });
            })
            ->with('partner:id,name')
            ->limit(5)
            ->get()
            ->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'type' => 'invoice',
                    'title' => $invoice->invoice_number,
                    'subtitle' => $invoice->partner->name ?? '',
                    'description' => number_format($invoice->total_incl_vat, 2) . ' € - ' . $invoice->status_label,
                    'url' => route('invoices.show', $invoice),
                    'icon' => 'document',
                ];
            });

        if ($invoices->isNotEmpty()) {
            $results[] = [
                'category' => 'Factures',
                'items' => $invoices->toArray(),
            ];
        }

        // Search Quotes
        $quotes = Quote::where('company_id', $companyId)
            ->where(function ($q) use ($query) {
                $q->where('quote_number', 'LIKE', "%{$query}%")
                    ->orWhereHas('partner', function ($q) use ($query) {
                        $q->where('name', 'LIKE', "%{$query}%");
                    });
            })
            ->with('partner:id,name')
            ->limit(5)
            ->get()
            ->map(function ($quote) {
                return [
                    'id' => $quote->id,
                    'type' => 'quote',
                    'title' => $quote->quote_number,
                    'subtitle' => $quote->partner->name ?? '',
                    'description' => number_format($quote->total_incl_vat, 2) . ' € - ' . $quote->status_label,
                    'url' => route('quotes.show', $quote),
                    'icon' => 'document-duplicate',
                ];
            });

        if ($quotes->isNotEmpty()) {
            $results[] = [
                'category' => 'Devis',
                'items' => $quotes->toArray(),
            ];
        }

        // Search Partners (Customers & Suppliers)
        $partners = Partner::where('company_id', $companyId)
            ->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                    ->orWhere('vat_number', 'LIKE', "%{$query}%")
                    ->orWhere('email', 'LIKE', "%{$query}%");
            })
            ->limit(5)
            ->get()
            ->map(function ($partner) {
                return [
                    'id' => $partner->id,
                    'type' => 'partner',
                    'title' => $partner->name,
                    'subtitle' => $partner->type === 'customer' ? 'Client' : 'Fournisseur',
                    'description' => $partner->email ?? $partner->vat_number ?? '',
                    'url' => route('partners.show', $partner),
                    'icon' => $partner->type === 'customer' ? 'user-group' : 'building-office',
                ];
            });

        if ($partners->isNotEmpty()) {
            $results[] = [
                'category' => 'Clients & Fournisseurs',
                'items' => $partners->toArray(),
            ];
        }

        // Search Products
        $products = Product::where('company_id', $companyId)
            ->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                    ->orWhere('reference', 'LIKE', "%{$query}%")
                    ->orWhere('description', 'LIKE', "%{$query}%");
            })
            ->limit(5)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'type' => 'product',
                    'title' => $product->name,
                    'subtitle' => $product->type === 'service' ? 'Service' : 'Produit',
                    'description' => number_format($product->unit_price, 2) . ' € - ' . ($product->reference ?? ''),
                    'url' => route('products.edit', $product),
                    'icon' => 'cube',
                ];
            });

        if ($products->isNotEmpty()) {
            $results[] = [
                'category' => 'Produits & Services',
                'items' => $products->toArray(),
            ];
        }

        // Search Employees
        $employees = Employee::where('company_id', $companyId)
            ->where(function ($q) use ($query) {
                $q->where('first_name', 'LIKE', "%{$query}%")
                    ->orWhere('last_name', 'LIKE', "%{$query}%")
                    ->orWhere('email', 'LIKE', "%{$query}%");
            })
            ->limit(5)
            ->get()
            ->map(function ($employee) {
                return [
                    'id' => $employee->id,
                    'type' => 'employee',
                    'title' => $employee->full_name,
                    'subtitle' => 'Employé',
                    'description' => $employee->email ?? '',
                    'url' => route('employees.show', $employee),
                    'icon' => 'user',
                ];
            });

        if ($employees->isNotEmpty()) {
            $results[] = [
                'category' => 'Employés',
                'items' => $employees->toArray(),
            ];
        }

        // Quick Actions (always show)
        $quickActions = [
            [
                'id' => 'new-invoice',
                'type' => 'action',
                'title' => 'Nouvelle facture',
                'subtitle' => 'Créer une nouvelle facture client',
                'description' => '',
                'url' => route('invoices.create'),
                'icon' => 'plus-circle',
            ],
            [
                'id' => 'new-quote',
                'type' => 'action',
                'title' => 'Nouveau devis',
                'subtitle' => 'Créer un nouveau devis',
                'description' => '',
                'url' => route('quotes.create'),
                'icon' => 'plus-circle',
            ],
            [
                'id' => 'new-partner',
                'type' => 'action',
                'title' => 'Nouveau client',
                'subtitle' => 'Ajouter un nouveau client',
                'description' => '',
                'url' => route('partners.create'),
                'icon' => 'plus-circle',
            ],
        ];

        $results[] = [
            'category' => 'Actions rapides',
            'items' => $quickActions,
        ];

        return response()->json([
            'results' => $results,
            'query' => $query,
            'total' => collect($results)->sum(fn($cat) => count($cat['items'])),
        ]);
    }
}
