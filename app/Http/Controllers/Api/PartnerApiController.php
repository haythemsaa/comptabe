<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PartnerApiController extends Controller
{
    /**
     * Search partners.
     */
    public function search(Request $request): JsonResponse
    {
        $query = Partner::query();

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('vat_number', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            if ($request->type === 'customer') {
                $query->customers();
            } elseif ($request->type === 'supplier') {
                $query->suppliers();
            }
        }

        $partners = $query->limit(20)->get(['id', 'name', 'vat_number', 'type', 'peppol_capable']);

        return response()->json([
            'success' => true,
            'data' => $partners,
        ]);
    }

    /**
     * Lookup partner by VAT number.
     */
    public function lookupByVat(Request $request): JsonResponse
    {
        $vatNumber = $request->get('vat');

        if (!$vatNumber) {
            return response()->json([
                'success' => false,
                'message' => 'VAT number required',
            ], 400);
        }

        // Clean VAT number
        $cleanVat = preg_replace('/[^0-9]/', '', $vatNumber);

        // Check if partner exists
        $partner = Partner::where('vat_number', 'like', "%{$cleanVat}%")->first();

        if ($partner) {
            return response()->json([
                'success' => true,
                'exists' => true,
                'data' => $partner->only(['id', 'name', 'vat_number', 'peppol_capable']),
                'peppol_capable' => $partner->peppol_capable,
            ]);
        }

        // TODO: Lookup in external service (VIES, KBO)
        return response()->json([
            'success' => true,
            'exists' => false,
            'peppol_capable' => false,
        ]);
    }

    /**
     * Get Peppol status for a partner.
     */
    public function peppolStatus(Partner $partner): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'peppol_capable' => $partner->peppol_capable,
                'peppol_id' => $partner->peppol_id,
                'peppol_verified_at' => $partner->peppol_verified_at?->toIso8601String(),
            ],
        ]);
    }
}
