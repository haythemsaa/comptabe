<?php

namespace App\Http\Controllers;

use App\Models\VatDeclaration;
use App\Services\VatDeclarationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VatDeclarationController extends Controller
{
    protected VatDeclarationService $vatService;

    public function __construct(VatDeclarationService $vatService)
    {
        $this->vatService = $vatService;
    }

    /**
     * Page principale des déclarations TVA
     *
     * GET /vat/declarations
     */
    public function index()
    {
        $currentYear = now()->year;

        $declarations = VatDeclaration::query()
            ->where('company_id', auth()->user()->currentCompany->id)
            ->whereYear('period_year', $currentYear)
            ->orderBy('period_year', 'desc')
            ->orderBy('period_number', 'desc')
            ->get();

        $stats = $this->vatService->getStats($currentYear);

        return view('vat.declarations.index', [
            'declarations' => $declarations,
            'stats' => $stats,
            'currentYear' => $currentYear,
        ]);
    }

    /**
     * Afficher une déclaration
     *
     * GET /vat/declarations/{id}
     */
    public function show(VatDeclaration $declaration)
    {
        // Vérifier ownership
        if ($declaration->company_id !== auth()->user()->currentCompany->id) {
            abort(403);
        }

        return view('vat.declarations.show', [
            'declaration' => $declaration,
        ]);
    }

    /**
     * Générer une nouvelle déclaration
     *
     * POST /vat/declarations/generate
     */
    public function generate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'period' => 'required|string|regex:/^\d{4}-(Q[1-4]|[01]\d)$/',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $declaration = $this->vatService->generate($request->period);

            return redirect()
                ->route('vat.declarations.show', $declaration)
                ->with('success', 'Déclaration TVA générée avec succès!');
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Erreur lors de la génération: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Télécharger XML Intervat
     *
     * GET /vat/declarations/{id}/download-xml
     */
    public function downloadXML(VatDeclaration $declaration)
    {
        // Vérifier ownership
        if ($declaration->company_id !== auth()->user()->currentCompany->id) {
            abort(403);
        }

        if (!$declaration->xml_content) {
            return back()->with('error', 'XML non disponible');
        }

        $filename = sprintf(
            'declaration_tva_%s_%s.xml',
            $declaration->period_year,
            $declaration->period_type === 'monthly' ? 'M' . str_pad($declaration->period_number, 2, '0', STR_PAD_LEFT) : 'Q' . $declaration->period_number
        );

        return response($declaration->xml_content)
            ->header('Content-Type', 'application/xml')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Télécharger PDF
     *
     * GET /vat/declarations/{id}/download-pdf
     */
    public function downloadPDF(VatDeclaration $declaration)
    {
        // Vérifier ownership
        if ($declaration->company_id !== auth()->user()->currentCompany->id) {
            abort(403);
        }

        // TODO: Générer PDF
        $pdf = $this->vatService->exportPDF($declaration);

        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="declaration_tva.pdf"');
    }

    /**
     * Soumettre à Intervat
     *
     * POST /vat/declarations/{id}/submit
     */
    public function submit(VatDeclaration $declaration)
    {
        // Vérifier ownership
        if ($declaration->company_id !== auth()->user()->currentCompany->id) {
            abort(403);
        }

        if ($declaration->status === 'submitted') {
            return back()->with('error', 'Déclaration déjà soumise');
        }

        try {
            $result = $this->vatService->submit($declaration);

            if ($result['success']) {
                return back()->with('success', $result['message'] ?? 'Déclaration soumise avec succès!');
            } else {
                return back()->with('error', $result['message'] ?? 'Erreur lors de la soumission');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Supprimer une déclaration (brouillon uniquement)
     *
     * DELETE /vat/declarations/{id}
     */
    public function destroy(VatDeclaration $declaration)
    {
        // Vérifier ownership
        if ($declaration->company_id !== auth()->user()->currentCompany->id) {
            abort(403);
        }

        if ($declaration->status !== 'draft') {
            return back()->with('error', 'Seules les déclarations brouillon peuvent être supprimées');
        }

        $declaration->delete();

        return redirect()
            ->route('vat.declarations.index')
            ->with('success', 'Déclaration supprimée');
    }

    // === API ENDPOINTS ===

    /**
     * API: Générer déclaration
     *
     * POST /api/v1/vat/generate
     */
    public function apiGenerate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'period' => 'required|string|regex:/^\d{4}-(Q[1-4]|[01]\d)$/',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $declaration = $this->vatService->generate($request->period);

            return response()->json([
                'success' => true,
                'declaration' => $declaration,
                'message' => 'Déclaration générée avec succès',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Liste des déclarations
     *
     * GET /api/v1/vat/declarations
     */
    public function apiIndex(Request $request)
    {
        $year = $request->query('year', now()->year);

        $declarations = VatDeclaration::query()
            ->where('company_id', auth()->user()->currentCompany->id)
            ->whereYear('period_year', $year)
            ->orderBy('period_year', 'desc')
            ->orderBy('period_number', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $declarations,
        ]);
    }

    /**
     * API: Détails d'une déclaration
     *
     * GET /api/v1/vat/declarations/{id}
     */
    public function apiShow(VatDeclaration $declaration)
    {
        // Vérifier ownership
        if ($declaration->company_id !== auth()->user()->currentCompany->id) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $declaration,
        ]);
    }

    /**
     * API: Soumettre déclaration
     *
     * POST /api/v1/vat/declarations/{id}/submit
     */
    public function apiSubmit(VatDeclaration $declaration)
    {
        // Vérifier ownership
        if ($declaration->company_id !== auth()->user()->currentCompany->id) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé',
            ], 403);
        }

        try {
            $result = $this->vatService->submit($declaration);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API: Statistiques
     *
     * GET /api/v1/vat/stats
     */
    public function apiStats(Request $request)
    {
        $year = $request->query('year', now()->year);

        $stats = $this->vatService->getStats($year);

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }
}
