<header class="sticky top-0 z-20 h-16 bg-white/80 dark:bg-dark-200/80 backdrop-blur-xl border-b border-secondary-200 dark:border-dark-100">
    <div class="flex items-center justify-between h-full px-4 lg:px-6">
        <!-- Left: Menu toggle & Breadcrumb -->
        <div class="flex items-center gap-4">
            <button
                @click="$store.app.toggleSidebar()"
                class="lg:hidden btn-ghost btn-icon"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            <!-- Breadcrumb -->
            <?php if (! empty(trim($__env->yieldContent('breadcrumb')))): ?>
                <nav class="hidden sm:flex items-center gap-2 text-sm">
                    <?php echo $__env->yieldContent('breadcrumb'); ?>
                </nav>
            <?php endif; ?>
        </div>

        <!-- Right: Actions -->
        <div class="flex items-center gap-2">
            <!-- Search -->
            <button
                @click="$dispatch('open-search')"
                class="btn-ghost btn-icon hidden sm:flex"
                title="Rechercher (Ctrl+K)"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </button>

            <!-- Quick Add -->
            <div x-data="dropdown()" class="relative">
                <button @click="toggle()" class="btn btn-primary btn-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span class="hidden sm:inline">Nouveau</span>
                </button>
                <div
                    x-show="open"
                    @click.away="close()"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="dropdown right-0"
                    :class="open ? 'dropdown-show' : ''"
                >
                    <a href="<?php echo e(route('invoices.create')); ?>" class="dropdown-item flex items-center gap-3">
                        <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Facture de vente
                    </a>
                    <a href="<?php echo e(route('purchases.create')); ?>" class="dropdown-item flex items-center gap-3">
                        <svg class="w-4 h-4 text-warning-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                        </svg>
                        Facture d'achat
                    </a>
                    <a href="<?php echo e(route('partners.create')); ?>" class="dropdown-item flex items-center gap-3">
                        <svg class="w-4 h-4 text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                        Client / Fournisseur
                    </a>
                    <div class="border-t border-secondary-100 dark:border-dark-100 my-1"></div>
                    <a href="<?php echo e(route('bank.import')); ?>" class="dropdown-item flex items-center gap-3">
                        <svg class="w-4 h-4 text-secondary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        Importer CODA
                    </a>
                </div>
            </div>

            <!-- Dark Mode Toggle -->
            <button
                @click="$store.app.toggleDarkMode()"
                class="btn-ghost btn-icon"
                title="Mode sombre"
            >
                <svg x-show="!$store.app.darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
                <svg x-show="$store.app.darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </button>

            <!-- Notifications Center -->
            <?php if (isset($component)) { $__componentOriginalf9dbfe08f0d919e77ec21ff2387521b1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf9dbfe08f0d919e77ec21ff2387521b1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.notifications.notification-center','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('notifications.notification-center'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf9dbfe08f0d919e77ec21ff2387521b1)): ?>
<?php $attributes = $__attributesOriginalf9dbfe08f0d919e77ec21ff2387521b1; ?>
<?php unset($__attributesOriginalf9dbfe08f0d919e77ec21ff2387521b1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf9dbfe08f0d919e77ec21ff2387521b1)): ?>
<?php $component = $__componentOriginalf9dbfe08f0d919e77ec21ff2387521b1; ?>
<?php unset($__componentOriginalf9dbfe08f0d919e77ec21ff2387521b1); ?>
<?php endif; ?>

            <!-- User Menu -->
            <div x-data="dropdown()" class="relative">
                <button @click="toggle()" class="flex items-center gap-2 p-1 rounded-xl hover:bg-secondary-100 dark:hover:bg-dark-100 transition-colors">
                    <div class="avatar avatar-sm bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400">
                        <?php echo e(auth()->user()->initials); ?>

                    </div>
                    <span class="hidden md:block text-sm font-medium text-secondary-700 dark:text-secondary-300">
                        <?php echo e(auth()->user()->first_name); ?>

                    </span>
                    <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div
                    x-show="open"
                    @click.away="close()"
                    x-transition
                    class="dropdown right-0"
                    :class="open ? 'dropdown-show' : ''"
                >
                    <div class="px-4 py-3 border-b border-secondary-100 dark:border-dark-100">
                        <div class="font-medium text-secondary-900 dark:text-white"><?php echo e(auth()->user()->full_name); ?></div>
                        <div class="text-sm text-secondary-500 dark:text-secondary-400"><?php echo e(auth()->user()->email); ?></div>
                    </div>
                    <a href="<?php echo e(route('settings.index')); ?>" class="dropdown-item">Mon profil</a>
                    <a href="<?php echo e(route('tenant.select')); ?>" class="dropdown-item">Changer d'entreprise</a>
                    <div class="border-t border-secondary-100 dark:border-dark-100 my-1"></div>
                    <form method="POST" action="<?php echo e(route('logout')); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="dropdown-item w-full text-left text-danger-600 dark:text-danger-400">
                            Déconnexion
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
<?php /**PATH C:\laragon\www\compta\resources\views/layouts/partials/header.blade.php ENDPATH**/ ?>