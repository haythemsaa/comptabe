<div class="flex flex-col h-full bg-white dark:bg-dark-400 border-r border-secondary-200 dark:border-dark-100">
    <!-- Logo -->
    <div class="flex items-center justify-between h-16 px-4 border-b border-secondary-200 dark:border-dark-100">
        <a href="<?php echo e(route('dashboard')); ?>" class="flex items-center gap-3">
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
    <?php if(isset($currentTenant)): ?>
    <div class="px-4 py-3 border-b border-secondary-200 dark:border-dark-100">
        <a href="<?php echo e(route('tenant.select')); ?>" class="flex items-center gap-3 p-2 rounded-xl hover:bg-secondary-100 dark:hover:bg-dark-100 transition-colors">
            <div class="w-10 h-10 bg-primary-100 dark:bg-primary-900/30 rounded-xl flex items-center justify-center">
                <span class="font-bold text-primary-600 dark:text-primary-400"><?php echo e(substr($currentTenant->name, 0, 2)); ?></span>
            </div>
            <div class="flex-1 min-w-0">
                <div class="font-medium text-secondary-900 dark:text-white truncate"><?php echo e($currentTenant->name); ?></div>
                <div class="text-xs text-secondary-500 dark:text-secondary-400 truncate"><?php echo e($currentTenant->formatted_vat_number); ?></div>
            </div>
            <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
            </svg>
        </a>
    </div>
    <?php endif; ?>

    <!-- Navigation -->
    <nav class="flex-1 p-4 space-y-1 overflow-y-auto scrollbar-hide">
        <!-- Dashboard -->
        <a href="<?php echo e(route('dashboard')); ?>" class="nav-link <?php echo e(request()->routeIs('dashboard') ? 'nav-link-active' : ''); ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span>Tableau de bord</span>
        </a>

        <!-- Sales Section -->
        <div class="pt-4">
            <div class="px-4 mb-2 text-xs font-semibold text-secondary-400 uppercase tracking-wider">Ventes</div>

            <a href="<?php echo e(route('invoices.index')); ?>" class="nav-link <?php echo e(request()->routeIs('invoices.*') ? 'nav-link-active' : ''); ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Factures de vente</span>
                <?php $draftCount = \App\Models\Invoice::sales()->where('status', 'draft')->count(); ?>
                <?php if($draftCount > 0): ?>
                    <span class="ml-auto badge badge-warning"><?php echo e($draftCount); ?></span>
                <?php endif; ?>
            </a>

            <a href="<?php echo e(route('quotes.index')); ?>" class="nav-link <?php echo e(request()->routeIs('quotes.*') ? 'nav-link-active' : ''); ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Devis</span>
                <?php $quoteCount = \App\Models\Quote::where('status', 'sent')->count(); ?>
                <?php if($quoteCount > 0): ?>
                    <span class="ml-auto badge badge-info"><?php echo e($quoteCount); ?></span>
                <?php endif; ?>
            </a>

            <a href="<?php echo e(route('recurring-invoices.index')); ?>" class="nav-link <?php echo e(request()->routeIs('recurring-invoices.*') ? 'nav-link-active' : ''); ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <span>Recurrentes</span>
                <?php $dueCount = \App\Models\RecurringInvoice::due()->count(); ?>
                <?php if($dueCount > 0): ?>
                    <span class="ml-auto badge badge-success"><?php echo e($dueCount); ?></span>
                <?php endif; ?>
            </a>

            <a href="<?php echo e(route('credit-notes.index')); ?>" class="nav-link <?php echo e(request()->routeIs('credit-notes.*') ? 'nav-link-active' : ''); ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                </svg>
                <span>Notes de crédit</span>
                <?php $draftCreditNotes = \App\Models\CreditNote::where('status', 'draft')->count(); ?>
                <?php if($draftCreditNotes > 0): ?>
                    <span class="ml-auto badge badge-danger"><?php echo e($draftCreditNotes); ?></span>
                <?php endif; ?>
            </a>

            <a href="<?php echo e(route('partners.index', ['type' => 'customer'])); ?>" class="nav-link <?php echo e(request()->routeIs('partners.*') && request('type') === 'customer' ? 'nav-link-active' : ''); ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span>Clients</span>
            </a>

            <a href="<?php echo e(route('products.index')); ?>" class="nav-link <?php echo e(request()->routeIs('products.*') ? 'nav-link-active' : ''); ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <span>Produits & Services</span>
            </a>
        </div>

        <!-- Purchases Section -->
        <div class="pt-4">
            <div class="px-4 mb-2 text-xs font-semibold text-secondary-400 uppercase tracking-wider">Achats</div>

            <a href="<?php echo e(route('purchases.index')); ?>" class="nav-link <?php echo e(request()->routeIs('purchases.*') ? 'nav-link-active' : ''); ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <span>Factures d'achat</span>
                <?php $peppolCount = \App\Models\Invoice::purchases()->where('peppol_status', 'received')->where('is_booked', false)->count(); ?>
                <?php if($peppolCount > 0): ?>
                    <span class="ml-auto badge badge-primary"><?php echo e($peppolCount); ?></span>
                <?php endif; ?>
            </a>

            <a href="<?php echo e(route('partners.index', ['type' => 'supplier'])); ?>" class="nav-link <?php echo e(request()->routeIs('partners.*') && request('type') === 'supplier' ? 'nav-link-active' : ''); ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <span>Fournisseurs</span>
            </a>

            <a href="<?php echo e(route('scanner.index')); ?>" class="nav-link <?php echo e(request()->routeIs('scanner.*') ? 'nav-link-active' : ''); ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
                </svg>
                <span>Scanner facture</span>
                <span class="ml-auto badge badge-primary">OCR</span>
            </a>

            <a href="<?php echo e(route('email-invoices.index')); ?>" class="nav-link <?php echo e(request()->routeIs('email-invoices.*') ? 'nav-link-active' : ''); ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <span>Import email</span>
                <?php $pendingEmails = \App\Models\EmailInvoice::where('company_id', $currentTenant->id ?? null)->pending()->count(); ?>
                <?php if($pendingEmails > 0): ?>
                    <span class="ml-auto badge badge-warning"><?php echo e($pendingEmails); ?></span>
                <?php endif; ?>
            </a>
        </div>

        <!-- Bank Section -->
        <div class="pt-4">
            <div class="px-4 mb-2 text-xs font-semibold text-secondary-400 uppercase tracking-wider">Banque</div>

            <a href="<?php echo e(route('bank.index')); ?>" class="nav-link <?php echo e(request()->routeIs('bank.index') ? 'nav-link-active' : ''); ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
                <span>Transactions</span>
            </a>

            <a href="<?php echo e(route('bank.reconciliation')); ?>" class="nav-link <?php echo e(request()->routeIs('bank.reconciliation') ? 'nav-link-active' : ''); ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                <span>Réconciliation</span>
                <?php $pendingRecon = \App\Models\BankTransaction::pending()->count(); ?>
                <?php if($pendingRecon > 0): ?>
                    <span class="ml-auto badge badge-warning"><?php echo e($pendingRecon); ?></span>
                <?php endif; ?>
            </a>
        </div>

        <!-- Documents Section -->
        <div class="pt-4">
            <div class="px-4 mb-2 text-xs font-semibold text-secondary-400 uppercase tracking-wider">Documents</div>

            <a href="<?php echo e(route('documents.index')); ?>" class="nav-link <?php echo e(request()->routeIs('documents.*') ? 'nav-link-active' : ''); ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                </svg>
                <span>Archive papier</span>
                <?php $starredDocs = \App\Models\Document::starred()->count(); ?>
                <?php if($starredDocs > 0): ?>
                    <span class="ml-auto badge badge-info"><?php echo e($starredDocs); ?></span>
                <?php endif; ?>
            </a>
        </div>

        <!-- Accounting Section -->
        <div class="pt-4">
            <div class="px-4 mb-2 text-xs font-semibold text-secondary-400 uppercase tracking-wider">Comptabilité</div>

            <a href="<?php echo e(route('accounting.index')); ?>" class="nav-link <?php echo e(request()->routeIs('accounting.index') ? 'nav-link-active' : ''); ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                <span>Écritures</span>
            </a>

            <a href="<?php echo e(route('accounting.chart')); ?>" class="nav-link <?php echo e(request()->routeIs('accounting.chart') ? 'nav-link-active' : ''); ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
                <span>Plan comptable</span>
            </a>

            <a href="<?php echo e(route('vat.index')); ?>" class="nav-link <?php echo e(request()->routeIs('vat.*') ? 'nav-link-active' : ''); ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"/>
                </svg>
                <span>TVA</span>
            </a>

            <a href="<?php echo e(route('accounting.export')); ?>" class="nav-link <?php echo e(request()->routeIs('accounting.export*') ? 'nav-link-active' : ''); ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                <span>Export comptable</span>
            </a>

            <a href="<?php echo e(route('tax-payments.index')); ?>" class="nav-link <?php echo e(request()->routeIs('tax-payments.*') ? 'nav-link-active' : ''); ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Impôts</span>
                <?php
                    try {
                        $overdueTaxes = \App\Models\TaxPayment::where('status', 'overdue')->count();
                    } catch (\Exception $e) {
                        $overdueTaxes = 0;
                    }
                ?>
                <?php if($overdueTaxes > 0): ?>
                    <span class="ml-auto badge badge-danger"><?php echo e($overdueTaxes); ?></span>
                <?php endif; ?>
            </a>

            <a href="<?php echo e(route('reports.balance-sheet')); ?>" class="nav-link <?php echo e(request()->routeIs('reports.balance-sheet') ? 'nav-link-active' : ''); ?>">
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

            <a href="<?php echo e(route('payroll.index')); ?>" class="nav-link <?php echo e(request()->routeIs('payroll.index') ? 'nav-link-active' : ''); ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                <span>Tableau de bord</span>
            </a>

            <a href="<?php echo e(route('payroll.employees.index')); ?>" class="nav-link <?php echo e(request()->routeIs('payroll.employees.*') ? 'nav-link-active' : ''); ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span>Employés</span>
                <?php $activeEmployees = \App\Models\Employee::where('status', 'active')->count(); ?>
                <?php if($activeEmployees > 0): ?>
                    <span class="ml-auto badge badge-info"><?php echo e($activeEmployees); ?></span>
                <?php endif; ?>
            </a>

            <a href="<?php echo e(route('payroll.payslips.index')); ?>" class="nav-link <?php echo e(request()->routeIs('payroll.payslips.*') ? 'nav-link-active' : ''); ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Fiches de paie</span>
                <?php $draftPayslips = \App\Models\Payslip::where('status', 'draft')->count(); ?>
                <?php if($draftPayslips > 0): ?>
                    <span class="ml-auto badge badge-warning"><?php echo e($draftPayslips); ?></span>
                <?php endif; ?>
            </a>

            <a href="<?php echo e(route('social-security.index')); ?>" class="nav-link <?php echo e(request()->routeIs('social-security.*') ? 'nav-link-active' : ''); ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                <span>Cotisations <?php echo e($companySocialSecurityOrg); ?></span>
                <?php
                    try {
                        $pendingSS = \App\Models\SocialSecurityPayment::where('status', 'pending_payment')->count();
                    } catch (\Exception $e) {
                        $pendingSS = 0;
                    }
                ?>
                <?php if($pendingSS > 0): ?>
                    <span class="ml-auto badge badge-warning"><?php echo e($pendingSS); ?></span>
                <?php endif; ?>
            </a>
        </div>
    </nav>

    <!-- Footer -->
    <div class="p-4 border-t border-secondary-200 dark:border-dark-100">
        <a href="<?php echo e(route('settings.index')); ?>" class="nav-link <?php echo e(request()->routeIs('settings.*') ? 'nav-link-active' : ''); ?>">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span>Paramètres</span>
        </a>

        <!-- Peppol Status -->
        <div class="mt-3 p-3 rounded-xl bg-secondary-50 dark:bg-dark-100">
            <div class="flex items-center gap-2 text-sm">
                <?php if(isset($currentTenant) && $currentTenant->peppol_registered): ?>
                    <span class="w-2 h-2 bg-success-500 rounded-full animate-pulse"></span>
                    <span class="text-success-600 dark:text-success-400 font-medium">Peppol actif</span>
                <?php else: ?>
                    <span class="w-2 h-2 bg-warning-500 rounded-full"></span>
                    <span class="text-warning-600 dark:text-warning-400 font-medium">Peppol non configuré</span>
                <?php endif; ?>
            </div>
            <div class="mt-1 text-xs text-secondary-500 dark:text-secondary-400">
                Obligatoire dès janvier 2026
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\laragon\www\compta\resources\views/layouts/partials/sidebar.blade.php ENDPATH**/ ?>