<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceApiController extends Controller
{
    /**
     * List invoices.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Invoice::with('partner:id,name,vat_number');

        if ($request->filled('type')) {
            $query->where('type', $request->type === 'sale' ? 'out' : 'in');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $invoices = $query->orderByDesc('invoice_date')
            ->paginate($request->integer('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $invoices->items(),
            'meta' => [
                'current_page' => $invoices->currentPage(),
                'last_page' => $invoices->lastPage(),
                'total' => $invoices->total(),
            ],
        ]);
    }

    /**
     * Get invoice lines.
     */
    public function lines(Invoice $invoice): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $invoice->lines,
        ]);
    }

    /**
     * Add a line to invoice.
     */
    public function addLine(Request $request, Invoice $invoice): JsonResponse
    {
        if (!$invoice->isEditable()) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice cannot be modified',
            ], 422);
        }

        $validated = $request->validate([
            'description' => 'required|string',
            'quantity' => 'required|numeric|min:0.0001',
            'unit_price' => 'required|numeric|min:0',
            'vat_rate' => 'required|numeric|in:0,6,12,21',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        $lineNumber = $invoice->lines()->max('line_number') + 1;

        $line = $invoice->lines()->create([
            ...$validated,
            'line_number' => $lineNumber,
        ]);

        return response()->json([
            'success' => true,
            'data' => $line,
        ], 201);
    }

    /**
     * Update an invoice line.
     */
    public function updateLine(Request $request, Invoice $invoice, InvoiceLine $line): JsonResponse
    {
        if (!$invoice->isEditable()) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice cannot be modified',
            ], 422);
        }

        $validated = $request->validate([
            'description' => 'sometimes|required|string',
            'quantity' => 'sometimes|required|numeric|min:0.0001',
            'unit_price' => 'sometimes|required|numeric|min:0',
            'vat_rate' => 'sometimes|required|numeric|in:0,6,12,21',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        $line->update($validated);

        return response()->json([
            'success' => true,
            'data' => $line->fresh(),
        ]);
    }

    /**
     * Delete an invoice line.
     */
    public function deleteLine(Invoice $invoice, InvoiceLine $line): JsonResponse
    {
        if (!$invoice->isEditable()) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice cannot be modified',
            ], 422);
        }

        $line->delete();

        return response()->json([
            'success' => true,
            'message' => 'Line deleted',
        ]);
    }
}
