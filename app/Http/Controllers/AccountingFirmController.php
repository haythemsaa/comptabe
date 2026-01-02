<?php

namespace App\Http\Controllers;

use App\Mail\UserInvitation;
use App\Models\AccountingFirm;
use App\Models\ClientMandate;
use App\Models\Company;
use App\Models\InvitationToken;
use App\Models\MandateTask;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class AccountingFirmController extends Controller
{
    /**
     * Display the firm dashboard.
     */
    public function dashboard()
    {
        $firm = AccountingFirm::current();

        if (!$firm) {
            return redirect()->route('firm.setup');
        }

        // Get statistics
        $stats = [
            'total_clients' => $firm->clientMandates()->where('status', 'active')->count(),
            'pending_tasks' => MandateTask::whereHas('clientMandate', fn($q) => $q->where('accounting_firm_id', $firm->id))
                ->whereIn('status', ['pending', 'in_progress'])
                ->count(),
            'overdue_tasks' => MandateTask::whereHas('clientMandate', fn($q) => $q->where('accounting_firm_id', $firm->id))
                ->where('due_date', '<', now())
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->count(),
            'unread_messages' => $firm->clientMandates()
                ->withCount(['communications as unread_count' => fn($q) => $q->where('is_read', false)->where('sender_type', 'client')])
                ->get()
                ->sum('unread_count'),
        ];

        // Recent activities
        $recentActivities = $firm->clientMandates()
            ->with(['activities' => fn($q) => $q->with('user')->latest()->limit(5)])
            ->get()
            ->pluck('activities')
            ->flatten()
            ->sortByDesc('created_at')
            ->take(10);

        // Upcoming tasks
        $upcomingTasks = MandateTask::whereHas('clientMandate', fn($q) => $q->where('accounting_firm_id', $firm->id))
            ->whereIn('status', ['pending', 'in_progress'])
            ->whereNotNull('due_date')
            ->orderBy('due_date')
            ->with(['clientMandate.company', 'assignedUser'])
            ->limit(10)
            ->get();

        // Recent clients
        $recentClients = $firm->clientMandates()
            ->with('company')
            ->where('status', 'active')
            ->latest()
            ->limit(5)
            ->get();

        return view('firm.dashboard', compact('firm', 'stats', 'recentActivities', 'upcomingTasks', 'recentClients'));
    }

    /**
     * Show firm setup page.
     */
    public function setup()
    {
        return view('firm.setup');
    }

    /**
     * Store a new firm (initial setup).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'itaa_number' => 'nullable|string|max:50',
            'ire_number' => 'nullable|string|max:50',
            'vat_number' => 'nullable|string|max:20',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'street' => 'nullable|string|max:255',
            'house_number' => 'nullable|string|max:20',
            'postal_code' => 'nullable|string|max:10',
            'city' => 'nullable|string|max:100',
        ]);

        DB::transaction(function () use ($validated) {
            $firm = AccountingFirm::create([
                ...$validated,
                'country_code' => 'BE',
                'subscription_plan' => 'cabinet_starter',
                'subscription_status' => 'trial',
                'trial_ends_at' => now()->addDays(30),
            ]);

            // Add current user as owner
            $firm->users()->attach(Auth::id(), [
                'role' => 'cabinet_owner',
                'is_active' => true,
                'started_at' => now(),
            ]);

            // Update user profile
            Auth::user()->update([
                'user_type' => 'accountant',
                'default_firm_id' => $firm->id,
            ]);
        });

        return redirect()->route('firm.dashboard')
            ->with('success', 'Cabinet créé avec succès!');
    }

    /**
     * Show firm settings.
     */
    public function settings()
    {
        $firm = AccountingFirm::current();

        if (!$firm) {
            return redirect()->route('firm.setup');
        }

        return view('firm.settings', compact('firm'));
    }

    /**
     * Update firm settings.
     */
    public function updateSettings(Request $request)
    {
        $firm = AccountingFirm::current();

        if (!$firm) {
            return redirect()->route('firm.setup');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'itaa_number' => 'nullable|string|max:50',
            'ire_number' => 'nullable|string|max:50',
            'vat_number' => 'nullable|string|max:20',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'website' => 'nullable|url|max:255',
            'street' => 'nullable|string|max:255',
            'house_number' => 'nullable|string|max:20',
            'box' => 'nullable|string|max:10',
            'postal_code' => 'nullable|string|max:10',
            'city' => 'nullable|string|max:100',
            'logo_path' => 'nullable|string|max:255',
        ]);

        $firm->update($validated);

        return back()->with('success', 'Paramètres mis à jour avec succès.');
    }

    /**
     * List all clients (mandates).
     */
    public function clients(Request $request)
    {
        $firm = AccountingFirm::current();

        if (!$firm) {
            return redirect()->route('firm.setup');
        }

        $query = $firm->clientMandates()
            ->with(['company', 'manager'])
            ->latest();

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('manager')) {
            $query->where('manager_user_id', $request->manager);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('company', fn($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('vat_number', 'like', "%{$search}%"));
        }

        $clients = $query->paginate(20);

        // Get managers for filter
        $managers = $firm->users()->wherePivot('is_active', true)->get();

        return view('firm.clients.index', compact('firm', 'clients', 'managers'));
    }

    /**
     * Show add client form.
     */
    public function createClient()
    {
        $firm = AccountingFirm::current();

        if (!$firm) {
            return redirect()->route('firm.setup');
        }

        // Get firm users for assignment
        $users = $firm->users()->wherePivot('is_active', true)->get();

        return view('firm.clients.create', compact('firm', 'users'));
    }

    /**
     * Store a new client mandate.
     */
    public function storeClient(Request $request)
    {
        $firm = AccountingFirm::current();

        if (!$firm) {
            return redirect()->route('firm.setup');
        }

        // Check limits
        if (!$firm->canAddClient()) {
            return back()->with('error', 'Limite de clients atteinte pour votre abonnement.');
        }

        $validated = $request->validate([
            // Company info
            'company_name' => 'required|string|max:255',
            'company_vat' => 'required|string|max:20',
            'company_email' => 'required|email|max:255',
            'company_phone' => 'nullable|string|max:20',
            'company_street' => 'nullable|string|max:255',
            'company_house_number' => 'nullable|string|max:20',
            'company_postal_code' => 'nullable|string|max:10',
            'company_city' => 'nullable|string|max:100',
            // Mandate info
            'mandate_type' => 'required|string|in:' . implode(',', array_keys(ClientMandate::TYPE_LABELS)),
            'manager_user_id' => 'nullable|exists:users,id',
            'services' => 'nullable|array',
            'billing_type' => 'required|string|in:hourly,monthly,annual,project',
            'hourly_rate' => 'nullable|numeric|min:0',
            'monthly_fee' => 'nullable|numeric|min:0',
            'annual_fee' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated, $firm) {
            // Create or find company
            $company = Company::firstOrCreate(
                ['vat_number' => $validated['company_vat']],
                [
                    'name' => $validated['company_name'],
                    'email' => $validated['company_email'],
                    'phone' => $validated['company_phone'] ?? null,
                    'street' => $validated['company_street'] ?? null,
                    'house_number' => $validated['company_house_number'] ?? null,
                    'postal_code' => $validated['company_postal_code'] ?? null,
                    'city' => $validated['company_city'] ?? null,
                    'country_code' => 'BE',
                    'company_type' => 'firm_client',
                    'managed_by_firm_id' => $firm->id,
                    'accepts_firm_management' => true,
                    'firm_access_level' => 'full',
                ]
            );

            // Update existing company to be managed by firm
            if ($company->wasRecentlyCreated === false) {
                $company->assignToFirm($firm);
            }

            // Create mandate
            $mandate = ClientMandate::create([
                'accounting_firm_id' => $firm->id,
                'company_id' => $company->id,
                'mandate_type' => $validated['mandate_type'],
                'status' => 'active',
                'start_date' => now(),
                'manager_user_id' => $validated['manager_user_id'] ?? Auth::id(),
                'services' => $validated['services'] ?? ClientMandate::DEFAULT_SERVICES,
                'billing_type' => $validated['billing_type'],
                'hourly_rate' => $validated['hourly_rate'] ?? null,
                'monthly_fee' => $validated['monthly_fee'] ?? null,
                'annual_fee' => $validated['annual_fee'] ?? null,
                'client_can_view' => true,
                'client_can_edit' => false,
                'client_can_validate' => false,
            ]);

            // Log activity
            $mandate->logActivity('mandate_created', "Mandat créé pour {$company->name}");
        });

        return redirect()->route('firm.clients.index')
            ->with('success', 'Client ajouté avec succès!');
    }

    /**
     * Show client detail (mandate).
     */
    public function showClient(ClientMandate $mandate)
    {
        $this->authorizeFirmAccess($mandate);

        $mandate->load([
            'company',
            'manager',
            'tasks' => fn($q) => $q->whereIn('status', ['pending', 'in_progress', 'review'])->orderBy('due_date'),
            'documents' => fn($q) => $q->latest()->limit(10),
            'activities' => fn($q) => $q->with('user')->latest()->limit(10),
            'communications' => fn($q) => $q->root()->with('replies')->latest()->limit(5),
        ]);

        return view('firm.clients.show', compact('mandate'));
    }

    /**
     * Edit client mandate.
     */
    public function editClient(ClientMandate $mandate)
    {
        $this->authorizeFirmAccess($mandate);

        $firm = AccountingFirm::current();

        if (!$firm) {
            return redirect()->route('firm.setup');
        }

        $users = $firm->users()->wherePivot('is_active', true)->get();

        return view('firm.clients.edit', compact('mandate', 'users'));
    }

    /**
     * Update client mandate.
     */
    public function updateClient(Request $request, ClientMandate $mandate)
    {
        $this->authorizeFirmAccess($mandate);

        $validated = $request->validate([
            'mandate_type' => 'required|string|in:' . implode(',', array_keys(ClientMandate::TYPE_LABELS)),
            'manager_user_id' => 'nullable|exists:users,id',
            'services' => 'nullable|array',
            'billing_type' => 'required|string|in:hourly,monthly,annual,project',
            'hourly_rate' => 'nullable|numeric|min:0',
            'monthly_fee' => 'nullable|numeric|min:0',
            'annual_fee' => 'nullable|numeric|min:0',
            'client_can_view' => 'boolean',
            'client_can_edit' => 'boolean',
            'client_can_validate' => 'boolean',
            'internal_notes' => 'nullable|string',
        ]);

        $mandate->update($validated);

        return redirect()->route('firm.clients.show', $mandate)
            ->with('success', 'Mandat mis à jour avec succès.');
    }

    /**
     * List team members.
     */
    public function team()
    {
        $firm = AccountingFirm::current();

        if (!$firm) {
            return redirect()->route('firm.setup');
        }

        $members = $firm->users()
            ->withPivot(['role', 'is_active', 'joined_at'])
            ->orderByPivot('role')
            ->get();

        return view('firm.team.index', compact('firm', 'members'));
    }

    /**
     * Invite team member.
     */
    public function inviteTeamMember(Request $request)
    {
        $firm = AccountingFirm::current();

        if (!$firm) {
            return redirect()->route('firm.setup');
        }

        // Check limits
        if (!$firm->canAddUser()) {
            return back()->with('error', 'Limite de collaborateurs atteinte pour votre abonnement.');
        }

        $validated = $request->validate([
            'email' => 'required|email',
            'role' => 'required|string|in:cabinet_admin,cabinet_manager,cabinet_accountant,cabinet_assistant',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
        ]);

        // Find or create user
        $user = User::firstOrCreate(
            ['email' => $validated['email']],
            [
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'password' => bcrypt(str()->random(16)), // Temporary password
                'user_type' => 'collaborator',
            ]
        );

        // Attach to firm
        $firm->users()->syncWithoutDetaching([
            $user->id => [
                'role' => $validated['role'],
                'is_active' => true,
                'started_at' => now(),
            ],
        ]);

        // Generate invitation token
        $invitation = InvitationToken::generate(
            user: $user,
            invitedBy: auth()->user(),
            company: null, // No specific company for accounting firm members
            role: $validated['role'],
            validHours: 72 // 3 days
        );

        // Send invitation email
        Mail::to($user->email)->send(new UserInvitation($invitation));

        return back()->with('success', "Invitation envoyée à {$user->email} avec succès.");
    }

    /**
     * Update team member role.
     */
    public function updateTeamMember(Request $request, User $user)
    {
        $firm = AccountingFirm::current();

        if (!$firm) {
            return redirect()->route('firm.setup');
        }

        // Check user belongs to firm
        if (!$user->hasAccessToFirm($firm->id)) {
            abort(403);
        }

        // Cannot change owner
        if ($user->getRoleInFirm($firm->id) === 'cabinet_owner') {
            return back()->with('error', 'Impossible de modifier le rôle du propriétaire.');
        }

        $validated = $request->validate([
            'role' => 'required|string|in:cabinet_admin,cabinet_manager,cabinet_accountant,cabinet_assistant',
            'is_active' => 'boolean',
        ]);

        $firm->users()->updateExistingPivot($user->id, [
            'role' => $validated['role'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return back()->with('success', 'Collaborateur mis à jour.');
    }

    /**
     * Remove team member.
     */
    public function removeTeamMember(User $user)
    {
        $firm = AccountingFirm::current();

        if (!$firm) {
            return redirect()->route('firm.setup');
        }

        // Check user belongs to firm
        if (!$user->hasAccessToFirm($firm->id)) {
            abort(403);
        }

        // Cannot remove owner
        if ($user->getRoleInFirm($firm->id) === 'cabinet_owner') {
            return back()->with('error', 'Impossible de retirer le propriétaire.');
        }

        $firm->users()->updateExistingPivot($user->id, [
            'is_active' => false,
            'ended_at' => now(),
        ]);

        return back()->with('success', 'Collaborateur retiré.');
    }

    /**
     * Check if current user can access this mandate.
     */
    protected function authorizeFirmAccess(ClientMandate $mandate): void
    {
        $firm = AccountingFirm::current();

        if (!$firm || $mandate->accounting_firm_id !== $firm->id) {
            abort(403, 'Accès non autorisé à ce client.');
        }
    }
}
