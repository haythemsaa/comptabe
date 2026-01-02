<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Company;
use App\Models\ModuleRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminModuleController extends Controller
{
    /**
     * Display list of all modules (catalog)
     */
    public function index()
    {
        $modules = Module::orderBy('category')->orderBy('sort_order')->get()->groupBy('category');

        $stats = [
            'total' => Module::count(),
            'core' => Module::core()->count(),
            'premium' => Module::premium()->count(),
            'active' => Module::active()->count(),
        ];

        return view('admin.modules.index', compact('modules', 'stats'));
    }

    /**
     * Show module details with companies using it
     */
    public function show(Module $module)
    {
        $module->load(['companies' => function($query) {
            $query->withPivot(['is_enabled', 'is_visible', 'status', 'trial_ends_at', 'enabled_at'])
                  ->orderBy('name');
        }]);

        return view('admin.modules.show', compact('module'));
    }

    /**
     * Show form to assign modules to a company
     */
    public function assignForm(Company $company)
    {
        $company->load(['modules', 'moduleRequests']);

        $allModules = Module::active()->orderBy('category')->orderBy('sort_order')->get();
        $enabledModuleIds = $company->modules->pluck('id')->toArray();

        return view('admin.modules.assign', compact('company', 'allModules', 'enabledModuleIds'));
    }

    /**
     * Assign modules to a company
     */
    public function assign(Request $request, Company $company)
    {
        $request->validate([
            'modules' => 'required|array',
            'modules.*' => 'exists:modules,id',
            'status' => 'required|in:trial,active',
            'trial_days' => 'required_if:status,trial|integer|min:1|max:365',
        ]);

        $moduleIds = $request->input('modules');
        $status = $request->input('status');
        $trialDays = $request->input('trial_days', 30);

        $syncData = [];
        foreach ($moduleIds as $moduleId) {
            $syncData[$moduleId] = [
                'is_enabled' => true,
                'is_visible' => true,
                'enabled_at' => now(),
                'enabled_by' => Auth::id(),
                'status' => $status,
                'trial_ends_at' => $status === 'trial' ? now()->addDays($trialDays) : null,
            ];
        }

        $company->modules()->sync($syncData);

        return redirect()->route('admin.modules.assign-form', $company)
            ->with('success', count($moduleIds) . ' module(s) assigné(s) avec succès à ' . $company->name);
    }

    /**
     * Enable/disable module for a company
     */
    public function toggleEnable(Company $company, Module $module)
    {
        $pivot = $company->modules()->where('module_id', $module->id)->first();

        if (!$pivot) {
            return response()->json(['error' => 'Module not assigned to this company'], 404);
        }

        $newStatus = !$pivot->pivot->is_enabled;

        $company->modules()->updateExistingPivot($module->id, [
            'is_enabled' => $newStatus,
            'enabled_at' => $newStatus ? now() : null,
            'disabled_at' => $newStatus ? null : now(),
        ]);

        return response()->json([
            'success' => true,
            'enabled' => $newStatus,
            'message' => $newStatus ? 'Module activé' : 'Module désactivé',
        ]);
    }

    /**
     * Remove module from company
     */
    public function detach(Company $company, Module $module)
    {
        $company->modules()->detach($module->id);

        return redirect()->back()->with('success', 'Module retiré avec succès');
    }

    /**
     * Show module requests from tenants
     */
    public function requests()
    {
        $requests = ModuleRequest::with(['company', 'module', 'requestedBy', 'reviewedBy'])
            ->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected', 'cancelled')")
            ->latest()
            ->paginate(20);

        $stats = [
            'pending' => ModuleRequest::pending()->count(),
            'approved' => ModuleRequest::approved()->count(),
            'rejected' => ModuleRequest::rejected()->count(),
        ];

        return view('admin.modules.requests', compact('requests', 'stats'));
    }

    /**
     * Approve module request
     */
    public function approveRequest(Request $request, ModuleRequest $moduleRequest)
    {
        $request->validate([
            'response' => 'nullable|string|max:500',
            'trial_days' => 'required|integer|min:1|max:365',
        ]);

        $moduleRequest->approve(
            Auth::user(),
            $request->input('response'),
            $request->input('trial_days', 30)
        );

        return redirect()->back()->with('success', 'Demande approuvée et module activé en essai');
    }

    /**
     * Reject module request
     */
    public function rejectRequest(Request $request, ModuleRequest $moduleRequest)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $moduleRequest->reject(Auth::user(), $request->input('reason'));

        return redirect()->back()->with('success', 'Demande refusée');
    }

    /**
     * Bulk assign core modules to all companies
     */
    public function assignCoreToAll()
    {
        $coreModules = Module::core()->get();
        $companies = Company::all();

        $count = 0;
        foreach ($companies as $company) {
            foreach ($coreModules as $module) {
                if (!$company->modules()->where('module_id', $module->id)->exists()) {
                    $company->modules()->attach($module->id, [
                        'is_enabled' => true,
                        'is_visible' => true,
                        'enabled_at' => now(),
                        'enabled_by' => Auth::id(),
                        'status' => 'active',
                    ]);
                    $count++;
                }
            }
        }

        return redirect()->back()->with('success', "$count modules core assignés à toutes les entreprises");
    }
}
