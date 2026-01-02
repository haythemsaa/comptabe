<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('title', null, []); ?> Factures d'achat <?php $__env->endSlot(); ?>

    <?php $__env->startSection('breadcrumb'); ?>
        <a href="<?php echo e(route('dashboard')); ?>" class="text-secondary-500 hover:text-primary-500 dark:text-secondary-400 dark:hover:text-primary-400">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-900 dark:text-white font-medium">Factures d'achat</span>
    <?php $__env->stopSection(); ?>

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">Factures d'achat</h1>
                <p class="text-secondary-600 dark:text-secondary-400">Factures reçues de vos fournisseurs</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="<?php echo e(route('purchases.create')); ?>" class="btn btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nouvelle facture
                </a>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="card p-4">
                <div class="text-sm text-secondary-500 dark:text-secondary-400">Total factures</div>
                <div class="text-2xl font-bold text-secondary-900 dark:text-white"><?php echo e($stats['total']); ?></div>
            </div>
            <div class="card p-4">
                <div class="text-sm text-secondary-500 dark:text-secondary-400">A comptabiliser</div>
                <div class="text-2xl font-bold text-warning-600"><?php echo e($stats['pending']); ?></div>
            </div>
            <div class="card p-4">
                <div class="text-sm text-secondary-500 dark:text-secondary-400">Montant dû</div>
                <div class="text-2xl font-bold text-danger-600"><?php echo \App\Helpers\FormatHelper::currency($stats['total_due']); ?></div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card">
            <div class="p-4 flex flex-wrap items-center gap-4">
                <!-- Search -->
                <div class="flex-1 min-w-[200px]">
                    <form action="<?php echo e(route('purchases.index')); ?>" method="GET" class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input
                            type="text"
                            name="search"
                            value="<?php echo e(request('search')); ?>"
                            placeholder="Rechercher par numéro ou fournisseur..."
                            class="form-input pl-10"
                        >
                    </form>
                </div>

                <!-- Status Filter -->
                <form action="<?php echo e(route('purchases.index')); ?>" method="GET" id="statusForm">
                    <select name="status" onchange="this.form.submit()" class="form-select w-auto">
                        <option value="">Tous les statuts</option>
                        <option value="draft" <?php echo e(request('status') === 'draft' ? 'selected' : ''); ?>>Brouillon</option>
                        <option value="validated" <?php echo e(request('status') === 'validated' ? 'selected' : ''); ?>>Validé</option>
                        <option value="paid" <?php echo e(request('status') === 'paid' ? 'selected' : ''); ?>>Payé</option>
                    </select>
                </form>

                <!-- Peppol Filter -->
                <form action="<?php echo e(route('purchases.index')); ?>" method="GET">
                    <select name="peppol" onchange="this.form.submit()" class="form-select w-auto">
                        <option value="">Toutes sources</option>
                        <option value="pending" <?php echo e(request('peppol') === 'pending' ? 'selected' : ''); ?>>Peppol non traité</option>
                    </select>
                </form>
            </div>
        </div>

        <!-- Table -->
        <div class="card overflow-hidden">
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Numéro</th>
                            <th>Fournisseur</th>
                            <th>Date</th>
                            <th>Échéance</th>
                            <th>Montant TTC</th>
                            <th>Statut</th>
                            <th>Comptabilisé</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $invoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="animate-fade-in" style="animation-delay: <?php echo e($loop->index * 50); ?>ms">
                                <td>
                                    <a href="<?php echo e(route('invoices.show', $invoice)); ?>" class="font-medium text-primary-600 hover:text-primary-700">
                                        <?php echo e($invoice->invoice_number); ?>

                                    </a>
                                    <?php if($invoice->peppol_status === 'received'): ?>
                                        <div class="text-xs text-success-600 flex items-center gap-1 mt-1">
                                            <span class="w-1.5 h-1.5 bg-success-500 rounded-full"></span>
                                            Peppol
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="avatar avatar-sm bg-secondary-100 dark:bg-dark-100">
                                            <?php echo e(substr($invoice->partner->name ?? '?', 0, 2)); ?>

                                        </div>
                                        <div>
                                            <div class="font-medium text-secondary-900 dark:text-white"><?php echo e($invoice->partner->name ?? 'Fournisseur inconnu'); ?></div>
                                            <?php if($invoice->partner?->vat_number): ?>
                                                <div class="text-xs text-secondary-500 dark:text-secondary-400"><?php echo e($invoice->partner->vat_number); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo \App\Helpers\FormatHelper::date($invoice->invoice_date); ?></td>
                                <td>
                                    <?php if($invoice->due_date): ?>
                                        <span class="<?php echo e($invoice->isOverdue() ? 'text-danger-600 font-medium' : ''); ?>">
                                            <?php echo \App\Helpers\FormatHelper::date($invoice->due_date); ?>
                                        </span>
                                        <?php if($invoice->isOverdue()): ?>
                                            <div class="text-xs text-danger-500"><?php echo e(abs($invoice->days_until_due)); ?>j retard</div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td class="font-medium"><?php echo \App\Helpers\FormatHelper::currency($invoice->total_incl_vat); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo e($invoice->status_color); ?>">
                                        <?php echo e($invoice->status_label); ?>

                                    </span>
                                </td>
                                <td>
                                    <?php if($invoice->is_booked): ?>
                                        <span class="badge badge-success">Oui</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Non</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="<?php echo e(route('invoices.show', $invoice)); ?>" class="btn-ghost btn-icon btn-sm" title="Voir">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8" class="text-center py-12">
                                    <svg class="w-16 h-16 mx-auto text-secondary-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p class="text-secondary-500 dark:text-secondary-400 text-lg">Aucune facture d'achat</p>
                                    <p class="text-secondary-400 dark:text-secondary-500 text-sm mt-2">Les factures Peppol apparaîtront automatiquement ici</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if($invoices->hasPages()): ?>
                <div class="px-6 py-4 border-t border-secondary-200 dark:border-dark-100">
                    <?php echo e($invoices->links()); ?>

                </div>
            <?php endif; ?>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php /**PATH C:\laragon\www\compta\resources\views/invoices/purchases.blade.php ENDPATH**/ ?>