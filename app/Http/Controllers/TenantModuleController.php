<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\ModuleRequest;
use App\Models\User;
use App\Notifications\ModuleRequestSubmittedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class TenantModuleController extends Controller
{
    public function marketplace()
    {
        $company = Auth::user()->currentCompany;

        $allModules = Module::active()->orderBy('category')->orderBy('sort_order')->get()->groupBy('category');
        $enabledModuleIds = $company->enabledModules->pluck('id')->toArray();
        $requestedModuleIds = $company->moduleRequests()->pending()->pluck('module_id')->toArray();

        return view('modules.marketplace', compact('allModules', 'enabledModuleIds', 'requestedModuleIds'));
    }

    public function myModules()
    {
        $company = Auth::user()->currentCompany;

        $modules = $company->modules()
            ->withPivot(['is_enabled', 'is_visible', 'status', 'trial_ends_at', 'enabled_at'])
            ->orderBy('category')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('category');

        $requests = $company->moduleRequests()->with(['module', 'requestedBy', 'reviewedBy'])->latest()->get();

        return view('modules.my-modules', compact('modules', 'requests'));
    }

    public function request(Request $request, Module $module)
    {
        $company = Auth::user()->currentCompany;

        if ($company->hasModule($module->code)) {
            return redirect()->back()->with('error', 'Ce module est déjà activé');
        }

        $existing = $company->moduleRequests()->where('module_id', $module->id)->pending()->first();
        if ($existing) {
            return redirect()->back()->with('error', 'Demande déjà en attente');
        }

        $moduleRequest = ModuleRequest::create([
            'company_id' => $company->id,
            'module_id' => $module->id,
            'message' => $request->input('message'),
            'requested_by' => Auth::id(),
            'status' => 'pending',
        ]);

        // Notify all superadmins
        $superadmins = User::where('is_superadmin', true)->get();
        Notification::send($superadmins, new ModuleRequestSubmittedNotification($moduleRequest));

        return redirect()->back()->with('success', 'Demande envoyée !');
    }

    public function toggleVisibility(Module $module)
    {
        $company = Auth::user()->currentCompany;
        $pivot = $company->modules()->where('module_id', $module->id)->first();

        if (!$pivot || !$pivot->pivot->is_enabled) {
            return response()->json(['error' => 'Module not available'], 400);
        }

        $newVisibility = !$pivot->pivot->is_visible;
        $company->modules()->updateExistingPivot($module->id, ['is_visible' => $newVisibility]);

        return response()->json(['success' => true, 'visible' => $newVisibility]);
    }
}
