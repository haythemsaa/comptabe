<x-app-layout>
    <x-slot name="title">Paramètres - Facturation</x-slot>

    @section('breadcrumb')
        <a href="{{ route('dashboard') }}" class="text-secondary-500 hover:text-secondary-700">Accueil</a>
        <svg class="w-4 h-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-secondary-900 dark:text-white font-medium">Paramètres</span>
    @endsection

    <div class="space-y-6">
        <!-- Header -->
        <div>
            <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">Paramètres</h1>
            <p class="text-secondary-600 dark:text-secondary-400">Configurez votre entreprise et vos préférences</p>
        </div>

        <div class="flex flex-col lg:flex-row gap-6">
            <!-- Sidebar Navigation -->
            <div class="lg:w-64 flex-shrink-0">
                <nav class="space-y-1">
                    <a href="{{ route('settings.company') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-secondary-600 hover:bg-secondary-50 dark:text-secondary-400 dark:hover:bg-secondary-800 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        Entreprise
                    </a>
                    <a href="{{ route('settings.peppol') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-secondary-600 hover:bg-secondary-50 dark:text-secondary-400 dark:hover:bg-secondary-800 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Peppol
                    </a>
                    <a href="{{ route('settings.invoices') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-primary-50 dark:bg-primary-900/20 text-primary-600 font-medium">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Facturation
                    </a>
                    <a href="{{ route('settings.users') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-secondary-600 hover:bg-secondary-50 dark:text-secondary-400 dark:hover:bg-secondary-800 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                        </svg>
                        Utilisateurs
                    </a>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="flex-1 space-y-6">
                <form action="{{ route('settings.invoices.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Invoice Template Selection -->
                    <div class="card mb-6">
                        <div class="card-header">
                            <h2 class="font-semibold text-secondary-900 dark:text-white">Template de facture PDF</h2>
                            <p class="text-sm text-secondary-500 dark:text-secondary-400 mt-1">Choisissez le design de vos factures</p>
                        </div>
                        <div class="card-body">
                            <!-- Template Grid -->
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
                                @php
                                    $templates = \App\Services\InvoiceTemplateService::getTemplates();
                                    $currentTemplate = $company->invoice_template ?? 'modern';
                                @endphp
                                @foreach($templates as $key => $template)
                                    <label class="relative cursor-pointer group">
                                        <input type="radio" name="invoice_template" value="{{ $key }}" class="sr-only peer" {{ $currentTemplate === $key ? 'checked' : '' }}>
                                        <div class="border-2 rounded-xl p-3 transition-all peer-checked:border-primary-500 peer-checked:bg-primary-50 dark:peer-checked:bg-primary-900/20 border-secondary-200 dark:border-secondary-700 hover:border-secondary-300 dark:hover:border-secondary-600">
                                            <!-- Preview -->
                                            <div class="aspect-[3/4] rounded-lg mb-2 overflow-hidden border border-secondary-200 dark:border-secondary-700" style="background: linear-gradient(135deg, {{ $template['default_colors']['primary'] }}20, {{ $template['default_colors']['secondary'] }}10);">
                                                <div class="h-full p-2 flex flex-col">
                                                    <div class="h-2 rounded-full mb-2" style="background: {{ $template['default_colors']['primary'] }};"></div>
                                                    <div class="flex-1 space-y-1">
                                                        <div class="h-1 w-3/4 rounded bg-secondary-300 dark:bg-secondary-600"></div>
                                                        <div class="h-1 w-1/2 rounded bg-secondary-200 dark:bg-secondary-700"></div>
                                                        <div class="mt-2 space-y-0.5">
                                                            <div class="h-0.5 w-full rounded bg-secondary-200 dark:bg-secondary-700"></div>
                                                            <div class="h-0.5 w-full rounded bg-secondary-200 dark:bg-secondary-700"></div>
                                                            <div class="h-0.5 w-full rounded bg-secondary-200 dark:bg-secondary-700"></div>
                                                        </div>
                                                    </div>
                                                    <div class="h-1.5 w-1/3 rounded ml-auto" style="background: {{ $template['default_colors']['secondary'] }};"></div>
                                                </div>
                                            </div>
                                            <!-- Name -->
                                            <p class="text-xs font-medium text-center text-secondary-700 dark:text-secondary-300">{{ $template['name'] }}</p>
                                        </div>
                                        <!-- Checkmark -->
                                        <div class="absolute top-1 right-1 w-5 h-5 rounded-full bg-primary-500 text-white flex items-center justify-center opacity-0 peer-checked:opacity-100 transition-opacity">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                    </label>
                                @endforeach
                            </div>

                            <!-- Color Customization -->
                            <div class="border-t border-secondary-200 dark:border-secondary-700 pt-4">
                                <h3 class="text-sm font-medium text-secondary-900 dark:text-white mb-3">Personnalisation des couleurs</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="invoice_primary_color" class="form-label">Couleur principale</label>
                                        <div class="flex items-center gap-3">
                                            <input
                                                type="color"
                                                id="invoice_primary_color"
                                                name="invoice_primary_color"
                                                value="{{ old('invoice_primary_color', $company->invoice_primary_color ?? '#6366f1') }}"
                                                class="w-12 h-10 rounded border border-secondary-300 dark:border-secondary-600 cursor-pointer"
                                            >
                                            <input
                                                type="text"
                                                value="{{ old('invoice_primary_color', $company->invoice_primary_color ?? '#6366f1') }}"
                                                class="form-input font-mono text-sm flex-1"
                                                pattern="^#[0-9A-Fa-f]{6}$"
                                                placeholder="#6366f1"
                                                oninput="document.getElementById('invoice_primary_color').value = this.value"
                                            >
                                        </div>
                                        <p class="text-xs text-secondary-500 mt-1">En-tetes, accents principaux</p>
                                    </div>
                                    <div>
                                        <label for="invoice_secondary_color" class="form-label">Couleur secondaire</label>
                                        <div class="flex items-center gap-3">
                                            <input
                                                type="color"
                                                id="invoice_secondary_color"
                                                name="invoice_secondary_color"
                                                value="{{ old('invoice_secondary_color', $company->invoice_secondary_color ?? '#1e293b') }}"
                                                class="w-12 h-10 rounded border border-secondary-300 dark:border-secondary-600 cursor-pointer"
                                            >
                                            <input
                                                type="text"
                                                value="{{ old('invoice_secondary_color', $company->invoice_secondary_color ?? '#1e293b') }}"
                                                class="form-input font-mono text-sm flex-1"
                                                pattern="^#[0-9A-Fa-f]{6}$"
                                                placeholder="#1e293b"
                                                oninput="document.getElementById('invoice_secondary_color').value = this.value"
                                            >
                                        </div>
                                        <p class="text-xs text-secondary-500 mt-1">Textes, tableaux, pieds de page</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Preview Button -->
                            <div class="mt-4 pt-4 border-t border-secondary-200 dark:border-secondary-700">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm text-secondary-600 dark:text-secondary-400">
                                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Les modifications seront appliquees aux nouvelles factures PDF generees.
                                    </p>
                                    <button type="button" onclick="previewTemplate()" class="btn btn-secondary btn-sm">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        Apercu PDF
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <script>
                        function previewTemplate() {
                            const template = document.querySelector('input[name="invoice_template"]:checked')?.value || 'modern';
                            const primaryColor = document.getElementById('invoice_primary_color')?.value || '#6366f1';
                            const secondaryColor = document.getElementById('invoice_secondary_color')?.value || '#1e293b';

                            const url = `{{ route('settings.invoices.preview-template') }}?template=${template}&primary_color=${encodeURIComponent(primaryColor)}&secondary_color=${encodeURIComponent(secondaryColor)}`;
                            window.open(url, '_blank', 'width=800,height=1000');
                        }
                    </script>

                    <!-- Invoice Numbering -->
                    <div class="card mb-6">
                        <div class="card-header">
                            <h2 class="font-semibold text-secondary-900 dark:text-white">Numérotation des factures</h2>
                        </div>
                        <div class="card-body space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="invoice_prefix" class="form-label">Préfixe</label>
                                    <input
                                        type="text"
                                        id="invoice_prefix"
                                        name="invoice_prefix"
                                        value="{{ old('invoice_prefix', $company->invoice_prefix ?? 'INV-') }}"
                                        class="form-input font-mono"
                                        placeholder="INV-"
                                    >
                                    <p class="text-xs text-secondary-500 mt-1">Ex: INV-, FAC-, 2025-</p>
                                </div>
                                <div>
                                    <label for="invoice_next_number" class="form-label">Prochain numéro</label>
                                    <input
                                        type="number"
                                        id="invoice_next_number"
                                        name="invoice_next_number"
                                        value="{{ old('invoice_next_number', $company->invoice_next_number ?? 1) }}"
                                        min="1"
                                        class="form-input font-mono"
                                    >
                                    <p class="text-xs text-secondary-500 mt-1">Le prochain numéro sera: <span class="font-medium">{{ $company->invoice_prefix ?? 'INV-' }}{{ str_pad($company->invoice_next_number ?? 1, 5, '0', STR_PAD_LEFT) }}</span></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Default Values -->
                    <div class="card mb-6">
                        <div class="card-header">
                            <h2 class="font-semibold text-secondary-900 dark:text-white">Valeurs par défaut</h2>
                        </div>
                        <div class="card-body space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="default_payment_terms_days" class="form-label">Délai de paiement (jours)</label>
                                    <input
                                        type="number"
                                        id="default_payment_terms_days"
                                        name="default_payment_terms_days"
                                        value="{{ old('default_payment_terms_days', $company->default_payment_terms_days ?? 30) }}"
                                        min="0"
                                        max="365"
                                        class="form-input"
                                    >
                                    <p class="text-xs text-secondary-500 mt-1">Délai par défaut pour les nouvelles factures</p>
                                </div>
                                <div>
                                    <label for="default_vat_rate" class="form-label">Taux TVA par défaut (%)</label>
                                    <select name="default_vat_rate" id="default_vat_rate" class="form-select">
                                        <option value="21" {{ old('default_vat_rate', $company->default_vat_rate ?? 21) == 21 ? 'selected' : '' }}>21% - Standard</option>
                                        <option value="12" {{ old('default_vat_rate', $company->default_vat_rate ?? 21) == 12 ? 'selected' : '' }}>12% - Réduit</option>
                                        <option value="6" {{ old('default_vat_rate', $company->default_vat_rate ?? 21) == 6 ? 'selected' : '' }}>6% - Super réduit</option>
                                        <option value="0" {{ old('default_vat_rate', $company->default_vat_rate ?? 21) == 0 ? 'selected' : '' }}>0% - Exonéré</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Invoice Footer -->
                    <div class="card mb-6">
                        <div class="card-header">
                            <h2 class="font-semibold text-secondary-900 dark:text-white">Mentions légales</h2>
                        </div>
                        <div class="card-body space-y-4">
                            <div>
                                <label for="invoice_footer" class="form-label">Pied de page des factures</label>
                                <textarea
                                    id="invoice_footer"
                                    name="invoice_footer"
                                    rows="4"
                                    class="form-input"
                                    placeholder="Conditions de paiement, mentions légales, etc."
                                >{{ old('invoice_footer', $company->invoice_footer ?? '') }}</textarea>
                                <p class="text-xs text-secondary-500 mt-1">Ce texte apparaîtra en bas de vos factures</p>
                            </div>

                            <div>
                                <label for="quote_footer" class="form-label">Pied de page des devis</label>
                                <textarea
                                    id="quote_footer"
                                    name="quote_footer"
                                    rows="4"
                                    class="form-input"
                                    placeholder="Conditions de validité, mentions légales, etc."
                                >{{ old('quote_footer', $company->quote_footer ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Email Templates -->
                    <div class="card mb-6">
                        <div class="card-header">
                            <h2 class="font-semibold text-secondary-900 dark:text-white">Email de facture</h2>
                        </div>
                        <div class="card-body space-y-4">
                            <div>
                                <label for="invoice_email_subject" class="form-label">Objet de l'email</label>
                                <input
                                    type="text"
                                    id="invoice_email_subject"
                                    name="invoice_email_subject"
                                    value="{{ old('invoice_email_subject', $company->invoice_email_subject ?? 'Facture {invoice_number} - {company_name}') }}"
                                    class="form-input"
                                    placeholder="Facture {invoice_number} - {company_name}"
                                >
                                <p class="text-xs text-secondary-500 mt-1">Variables: {invoice_number}, {company_name}, {client_name}, {amount}</p>
                            </div>

                            <div>
                                <label for="invoice_email_body" class="form-label">Corps de l'email</label>
                                <textarea
                                    id="invoice_email_body"
                                    name="invoice_email_body"
                                    rows="6"
                                    class="form-input"
                                    placeholder="Bonjour,

Veuillez trouver ci-joint votre facture {invoice_number} d'un montant de {amount}.

Cordialement,
{company_name}"
                                >{{ old('invoice_email_body', $company->invoice_email_body ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="flex justify-end">
                        <button type="submit" class="btn btn-primary">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
