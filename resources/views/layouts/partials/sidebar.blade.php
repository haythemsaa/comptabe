<div class="flex flex-col h-full bg-white dark:bg-dark-400 border-r border-secondary-200 dark:border-dark-100">
    <!-- Logo -->
    <div class="flex items-center justify-between h-16 px-4 border-b border-secondary-200 dark:border-dark-100">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl flex items-center justify-center shadow-lg shadow-primary-500/30">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <span class="font-bold text-lg text-secondary-900 dark:text-white">ComptaBE</span>
                <span class="block text-xs text-secondary-500 dark:text-secondary-400">Peppol 2026</span>
            </div>
        </a>
        <button @click="$store.app.toggleSidebar()" class="lg:hidden btn-ghost btn-icon">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <!-- Company Selector -->
    @if(isset($currentTenant))
    <div class="px-4 py-3 border-b border-secondary-200 dark:border-dark-100">
        <a href="{{ route('tenant.select') }}" class="flex items-center gap-3 p-2 rounded-xl hover:bg-secondary-100 dark:hover:bg-dark-100 transition-colors">
            <div class="w-10 h-10 bg-primary-100 dark:bg-primary-900/30 rounded-xl flex items-center justify-center">
                <span class="font-bold text-primary-600 dark:text-primary-400">{{ substr($currentTenant->name, 0, 2) }}</span>
            </div>
            <div class="flex-1 min-w-0">
                <div class="font-medium text-secondary-900 dark:text-white truncate">{{ $currentTenant->name }}</div>
                <div class="text-xs text-secondary-500 dark:text-secondary-400 truncate">{{ $currentTenant->formatted_vat_number }}</div>
            </div>
            <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
            </svg>
        </a>
    </div>
    @endif

    <!-- Navigation -->
    <nav class="flex-1 p-4 space-y-1 overflow-y-auto scrollbar-hide">
        <!-- Dashboard -->
        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'nav-link-active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span>Tableau de bord</span>
        </a>

        <!-- Sales Section -->
        <div class="pt-4">
            <div class="px-4 mb-2 text-xs font-semibold text-secondary-400 uppercase tracking-wider">Ventes</div>

            <a href="{{ route('invoices.index') }}" class="nav-link {{ request()->routeIs('invoices.*') ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Factures de vente</span>
                @php $draftCount = \App\Models\Invoice::sales()->where('status', 'draft')->count(); @endphp
                @if($draftCount > 0)
                    <span class="ml-auto badge badge-warning">{{ $draftCount }}</span>
                @endif
            </a>

            <a href="{{ route('quotes.index') }}" class="nav-link {{ request()->routeIs('quotes.*') ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Devis</span>
                @php $quoteCount = \App\Models\Quote::where('status', 'sent')->count(); @endphp
                @if($quoteCount > 0)
                    <span class="ml-auto badge badge-info">{{ $quoteCount }}</span>
                @endif
            </a>

            <a href="{{ route('recurring-invoices.index') }}" class="nav-link {{ request()->routeIs('recurring-invoices.*') ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <span>Recurrentes</span>
                @php $dueCount = \App\Models\RecurringInvoice::due()->count(); @endphp
                @if($dueCount > 0)
                    <span class="ml-auto badge badge-success">{{ $dueCount }}</span>
                @endif
            </a>

            <a href="{{ route('credit-notes.index') }}" class="nav-link {{ request()->routeIs('credit-notes.*') ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                </svg>
                <span>Notes de crédit</span>
                @php $draftCreditNotes = \App\Models\CreditNote::where('status', 'draft')->count(); @endphp
                @if($draftCreditNotes > 0)
                    <span class="ml-auto badge badge-danger">{{ $draftCreditNotes }}</span>
                @endif
            </a>

            <a href="{{ route('partners.index', ['type' => 'customer']) }}" class="nav-link {{ request()->routeIs('partners.*') && request('type') === 'customer' ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span>Clients</span>
            </a>

            <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.*') ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <span>Produits & Services</span>
            </a>
        </div>

        <!-- CRM Section -->
        @if(isset($currentTenant) && $currentTenant->hasModule('crm'))
        <div class="pt-4">
            <div class="px-4 mb-2 text-xs font-semibold text-secondary-400 uppercase tracking-wider">CRM</div>

            <a href="{{ route('crm.pipeline') }}" class="nav-link {{ request()->routeIs('crm.pipeline') ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                </svg>
                <span>Pipeline</span>
                @php
                    try {
                        $openOpps = \App\Models\Opportunity::open()->count();
                    } catch (\Exception $e) {
                        $openOpps = 0;
                    }
                @endphp
                @if($openOpps > 0)
                    <span class="ml-auto badge badge-primary">{{ $openOpps }}</span>
                @endif
            </a>

            <a href="{{ route('crm.opportunities.index') }}" class="nav-link {{ request()->routeIs('crm.opportunities.*') ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
                <span>Opportunités</span>
            </a>

            <a href="{{ route('crm.activities.index') }}" class="nav-link {{ request()->routeIs('crm.activities.*') ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                <span>Activités</span>
                @php
                    try {
                        $todayActivities = \App\Models\Activity::whereDate('due_date', today())->where('is_completed', false)->count();
                    } catch (\Exception $e) {
                        $todayActivities = 0;
                    }
                @endphp
                @if($todayActivities > 0)
                    <span class="ml-auto badge badge-warning">{{ $todayActivities }}</span>
                @endif
            </a>

            <a href="{{ route('crm.dashboard') }}" class="nav-link {{ request()->routeIs('crm.dashboard') ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <span>Tableau de bord CRM</span>
            </a>
        </div>
        @endif

        <!-- Stock Section -->
        @if(isset($currentTenant) && $currentTenant->hasModule('stock'))
        <div class="pt-4">
            <div class="px-4 mb-2 text-xs font-semibold text-secondary-400 uppercase tracking-wider">Stock</div>

            <a href="{{ route('stock.dashboard') }}" class="nav-link {{ request()->routeIs('stock.dashboard') ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <span>Tableau de bord</span>
            </a>

            <a href="{{ route('stock.warehouses.index') }}" class="nav-link {{ request()->routeIs('stock.warehouses.*') ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <span>Entrepôts</span>
            </a>

            <a href="{{ route('stock.levels') }}" class="nav-link {{ request()->routeIs('stock.levels*') ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                </svg>
                <span>Niveaux stock</span>
                @php
                    try {
                        $lowStockCount = \App\Models\ProductStock::whereRaw('quantity <= min_quantity')->where('min_quantity', '>', 0)->count();
                    } catch (\Exception $e) {
                        $lowStockCount = 0;
                    }
                @endphp
                @if($lowStockCount > 0)
                    <span class="ml-auto badge badge-warning">{{ $lowStockCount }}</span>
                @endif
            </a>

            <a href="{{ route('stock.movements.index') }}" class="nav-link {{ request()->routeIs('stock.movements.*') ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
                <span>Mouvements</span>
            </a>

            <a href="{{ route('stock.inventories.index') }}" class="nav-link {{ request()->routeIs('stock.inventories.*') ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                <span>Inventaires</span>
                @php
                    try {
                        $activeInventories = \App\Models\InventorySession::whereIn('status', ['draft', 'in_progress'])->count();
                    } catch (\Exception $e) {
                        $activeInventories = 0;
                    }
                @endphp
                @if($activeInventories > 0)
                    <span class="ml-auto badge badge-info">{{ $activeInventories }}</span>
                @endif
            </a>

            <a href="{{ route('stock.alerts.index') }}" class="nav-link {{ request()->routeIs('stock.alerts.*') ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <span>Alertes</span>
                @php
                    try {
                        $unresolvedAlerts = \App\Models\StockAlert::unresolved()->count();
                    } catch (\Exception $e) {
                        $unresolvedAlerts = 0;
                    }
                @endphp
                @if($unresolvedAlerts > 0)
                    <span class="ml-auto badge badge-danger">{{ $unresolvedAlerts }}</span>
                @endif
            </a>
        </div>
        @endif

        <!-- Expenses Section -->
        @if(isset($currentTenant) && $currentTenant->hasModule('expenses'))
        <div class="pt-4">
            <div class="px-4 mb-2 text-xs font-semibold text-secondary-400 uppercase tracking-wider">Notes de frais</div>

            <a href="{{ route('expenses.dashboard') }}" class="nav-link {{ request()->routeIs('expenses.dashboard') ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <span>Tableau de bord</span>
            </a>

            <a href="{{ route('expenses.index') }}" class="nav-link {{ request()->routeIs('expenses.index') || request()->routeIs('expenses.create') || request()->routeIs('expenses.show') || request()->routeIs('expenses.edit') ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span>Mes dépenses</span>
                @php
                    try {
                        $pendingExpenses = \App\Models\EmployeeExpense::forUser(auth()->id())->pending()->count();
                    } catch (\Exception $e) {
                        $pendingExpenses = 0;
                    }
                @endphp
                @if($pendingExpenses > 0)
                    <span class="ml-auto badge badge-warning">{{ $pendingExpenses }}</span>
                @endif
            </a>

            <a href="{{ route('expenses.reports.index') }}" class="nav-link {{ request()->routeIs('expenses.reports.*') ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Mes rapports</span>
            </a>

            <a href="{{ route('expenses.approval.index') }}" class="nav-link {{ request()->routeIs('expenses.approval.*') ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Approbations</span>
                @php
                    try {
                        $pendingApproval = \App\Models\ExpenseReport::awaitingApproval()->count();
                    } catch (\Exception $e) {
                        $pendingApproval = 0;
                    }
                @endphp
                @if($pendingApproval > 0)
                    <span class="ml-auto badge badge-danger">{{ $pendingApproval }}</span>
                @endif
            </a>
        </div>
        @endif

        <!-- Projects Section -->
        @if(isset($currentTenant) && $currentTenant->hasModule('projects'))
        <div class="pt-4">
            <div class="px-4 mb-2 text-xs font-semibold text-secondary-400 uppercase tracking-wider">Projets</div>

            <a href="{{ route('projects.index') }}" class="nav-link {{ request()->routeIs('projects.index') ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <span>Tous les projets</span>
            </a>

            <a href="{{ route('projects.create') }}" class="nav-link {{ request()->routeIs('projects.create') ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Nouveau projet</span>
            </a>
        </div>
        @endif

        <!-- Timesheets Section -->
        @if(isset($currentTenant) && $currentTenant->hasModule('timesheet'))
        <div class="pt-4">
            <div class="px-4 mb-2 text-xs font-semibold text-secondary-400 uppercase tracking-wider">Feuilles de temps</div>

            <a href="{{ route('timesheets.week') }}" class="nav-link {{ request()->routeIs('timesheets.week') ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span>Ma semaine</span>
            </a>

            <a href="{{ route('timesheets.index') }}" class="nav-link {{ request()->routeIs('timesheets.index') ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Historique</span>
            </a>
        </div>
        @endif

        <!-- Purchases Section -->
        <div class="pt-4">
            <div class="px-4 mb-2 text-xs font-semibold text-secondary-400 uppercase tracking-wider">Achats</div>

            <a href="{{ route('purchases.index') }}" class="nav-link {{ request()->routeIs('purchases.*') ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <span>Factures d'achat</span>
                @php $peppolCount = \App\Models\Invoice::purchases()->where('peppol_status', 'received')->where('is_booked', false)->count(); @endphp
                @if($peppolCount > 0)
                    <span class="ml-auto badge badge-primary">{{ $peppolCount }}</span>
                @endif
            </a>

            <a href="{{ route('partners.index', ['type' => 'supplier']) }}" class="nav-link {{ request()->routeIs('partners.*') && request('type') === 'supplier' ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <span>Fournisseurs</span>
            </a>

            <a href="{{ route('scanner.index') }}" class="nav-link {{ request()->routeIs('scanner.*') ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
                </svg>
                <span>Scanner facture</span>
                <span class="ml-auto badge badge-primary">OCR</span>
            </a>

            <a href="{{ route('email-invoices.index') }}" class="nav-link {{ request()->routeIs('email-invoices.*') ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <span>Import email</span>
                @php $pendingEmails = $currentTenant ? \App\Models\EmailInvoice::where('company_id', $currentTenant->id)->pending()->count() : 0; @endphp
                @if($pendingEmails > 0)
                    <span class="ml-auto badge badge-warning">{{ $pendingEmails }}</span>
                @endif
            </a>
        </div>

        <!-- Bank Section -->
        @if(isset($currentTenant) && $currentTenant->hasModule('bank'))
        <div class="pt-4">
            <div class="px-4 mb-2 text-xs font-semibold text-secondary-400 uppercase tracking-wider">Trésorerie & Banque</div>

            <a href="{{ route('bank.index') }}" class="nav-link {{ request()->routeIs('bank.index') ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
                <span>Transactions</span>
            </a>

            <a href="{{ route('bank.reconciliation') }}" class="nav-link {{ request()->routeIs('bank.reconciliation') ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                <span>Réconciliation</span>
                @php $pendingRecon = \App\Models\BankTransaction::pending()->count(); @endphp
                @if($pendingRecon > 0)
                    <span class="ml-auto badge badge-warning">{{ $pendingRecon }}</span>
                @endif
            </a>

            <a href="{{ route('bank.accounts') }}" class="nav-link {{ request()->routeIs('bank.accounts') ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <span>Comptes bancaires</span>
            </a>

            <a href="{{ route('openbanking.index') }}" class="nav-link {{ request()->routeIs('openbanking.*') ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                </svg>
                <span>Open Banking</span>
                <span class="ml-auto badge badge-info">PSD2</span>
            </a>

            <a href="{{ route('bank.import') }}" class="nav-link {{ request()->routeIs('bank.import') ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                <span>Import CODA/MT940</span>
            </a>
        </div>
        @endif

        <!-- Documents Section -->
        <div class="pt-4">
            <div class="px-4 mb-2 text-xs font-semibold text-secondary-400 uppercase tracking-wider">Documents</div>

            <a href="{{ route('documents.index') }}" class="nav-link {{ request()->routeIs('documents.*') ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                </svg>
                <span>Archive papier</span>
                @php $starredDocs = \App\Models\Document::starred()->count(); @endphp
                @if($starredDocs > 0)
                    <span class="ml-auto badge badge-info">{{ $starredDocs }}</span>
                @endif
            </a>
        </div>

        <!-- AI & Analytics Section -->
        <div class="pt-4">
            <div class="px-4 mb-2 text-xs font-semibold text-secondary-400 uppercase tracking-wider">Intelligence</div>

            <a href="{{ route('ai.analytics') }}" class="nav-link {{ request()->routeIs('ai.analytics') ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <span>Analytics AI</span>
                <span class="ml-auto badge badge-primary">AI</span>
            </a>

            <a href="{{ route('ai.categorization') }}" class="nav-link {{ request()->routeIs('ai.categorization') ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
                <span>Catégorisation</span>
            </a>

            <a href="{{ route('ai.anomalies') }}" class="nav-link {{ request()->routeIs('ai.anomalies') ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span>Détection anomalies</span>
            </a>
        </div>

        <!-- Compliance Section -->
        <div class="pt-4">
            <div class="px-4 mb-2 text-xs font-semibold text-secondary-400 uppercase tracking-wider">Conformité</div>

            <a href="{{ route('compliance.index') }}" class="nav-link {{ request()->routeIs('compliance.index') ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                <span>Tableau de bord</span>
            </a>

            <a href="{{ route('compliance.fiscal-calendar') }}" class="nav-link {{ request()->routeIs('compliance.fiscal-calendar') ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span>Calendrier fiscal</span>
            </a>
        </div>

        <!-- Accounting Section -->
        <div class="pt-4">
            <div class="px-4 mb-2 text-xs font-semibold text-secondary-400 uppercase tracking-wider">Comptabilité</div>

            <a href="{{ route('accounting.index') }}" class="nav-link {{ request()->routeIs('accounting.index') ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                <span>Écritures</span>
            </a>

            <a href="{{ route('accounting.chart') }}" class="nav-link {{ request()->routeIs('accounting.chart') ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
                <span>Plan comptable</span>
            </a>

            <a href="{{ route('vat.index') }}" class="nav-link {{ request()->routeIs('vat.*') ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"/>
                </svg>
                <span>TVA</span>
            </a>

            <a href="{{ route('accounting.export') }}" class="nav-link {{ request()->routeIs('accounting.export*') ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                <span>Export comptable</span>
            </a>

            <a href="{{ route('tax-payments.index') }}" class="nav-link {{ request()->routeIs('tax-payments.*') ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Impôts</span>
                @php
                    try {
                        $overdueTaxes = \App\Models\TaxPayment::where('status', 'overdue')->count();
                    } catch (\Exception $e) {
                        $overdueTaxes = 0;
                    }
                @endphp
                @if($overdueTaxes > 0)
                    <span class="ml-auto badge badge-danger">{{ $overdueTaxes }}</span>
                @endif
            </a>

            <a href="{{ route('reports.balance-sheet') }}" class="nav-link {{ request()->routeIs('reports.balance-sheet') ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <span>Bilan avec AI</span>
                <span class="ml-auto badge badge-primary">AI</span>
            </a>
        </div>

        <!-- Payroll Section -->
        <div class="pt-4">
            <div class="px-4 mb-2 text-xs font-semibold text-secondary-400 uppercase tracking-wider">Paie</div>

            <a href="{{ route('payroll.index') }}" class="nav-link {{ request()->routeIs('payroll.index') ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                <span>Tableau de bord</span>
            </a>

            <a href="{{ route('payroll.employees.index') }}" class="nav-link {{ request()->routeIs('payroll.employees.*') ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span>Employés</span>
                @php $activeEmployees = \App\Models\Employee::where('status', 'active')->count(); @endphp
                @if($activeEmployees > 0)
                    <span class="ml-auto badge badge-info">{{ $activeEmployees }}</span>
                @endif
            </a>

            <a href="{{ route('payroll.payslips.index') }}" class="nav-link {{ request()->routeIs('payroll.payslips.*') ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Fiches de paie</span>
                @php $draftPayslips = \App\Models\Payslip::where('status', 'draft')->count(); @endphp
                @if($draftPayslips > 0)
                    <span class="ml-auto badge badge-warning">{{ $draftPayslips }}</span>
                @endif
            </a>

            <a href="{{ route('social-security.index') }}" class="nav-link {{ request()->routeIs('social-security.*') ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                <span>Cotisations {{ $companySocialSecurityOrg }}</span>
                @php
                    try {
                        $pendingSS = \App\Models\SocialSecurityPayment::where('status', 'pending_payment')->count();
                    } catch (\Exception $e) {
                        $pendingSS = 0;
                    }
                @endphp
                @if($pendingSS > 0)
                    <span class="ml-auto badge badge-warning">{{ $pendingSS }}</span>
                @endif
            </a>
        </div>

        <!-- Modules Section -->
        <div class="pt-4">
            <div class="px-4 mb-2 text-xs font-semibold text-secondary-400 uppercase tracking-wider">Modules</div>

            <a href="{{ route('modules.marketplace') }}" class="nav-link {{ request()->routeIs('modules.marketplace') ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                <span>Marketplace</span>
                @php
                    $tenantModuleIds = $currentTenant ? $currentTenant->modules()->pluck('modules.id')->toArray() : [];
                    $availableModulesCount = \App\Models\Module::active()
                        ->whereNotIn('id', $tenantModuleIds)
                        ->count();
                @endphp
                @if($availableModulesCount > 0)
                    <span class="ml-auto badge badge-primary">{{ $availableModulesCount }}</span>
                @endif
            </a>

            <a href="{{ route('modules.my-modules') }}" class="nav-link {{ request()->routeIs('modules.my-modules') ? 'nav-link-active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
                <span>Mes Modules</span>
                @php
                    $enabledModulesCount = $currentTenant ? $currentTenant->enabledModules()->count() : 0;
                    $pendingRequests = $currentTenant ? $currentTenant->moduleRequests()->where('status', 'pending')->count() : 0;
                @endphp
                @if($enabledModulesCount > 0)
                    <span class="ml-auto badge badge-success">{{ $enabledModulesCount }}</span>
                @endif
                @if($pendingRequests > 0)
                    <span class="ml-1 badge badge-warning">{{ $pendingRequests }}</span>
                @endif
            </a>
        </div>
    </nav>

    <!-- Footer -->
    <div class="p-4 border-t border-secondary-200 dark:border-dark-100">
        <a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'nav-link-active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span>Paramètres</span>
        </a>

        <!-- Peppol Status -->
        <div class="mt-3 p-3 rounded-xl bg-secondary-50 dark:bg-dark-100">
            <div class="flex items-center gap-2 text-sm">
                @if(isset($currentTenant) && $currentTenant->peppol_registered)
                    <span class="w-2 h-2 bg-success-500 rounded-full animate-pulse"></span>
                    <span class="text-success-600 dark:text-success-400 font-medium">Peppol actif</span>
                @else
                    <span class="w-2 h-2 bg-warning-500 rounded-full"></span>
                    <span class="text-warning-600 dark:text-warning-400 font-medium">Peppol non configuré</span>
                @endif
            </div>
            <div class="mt-1 text-xs text-secondary-500 dark:text-secondary-400">
                Obligatoire dès janvier 2026
            </div>
        </div>
    </div>
</div>
