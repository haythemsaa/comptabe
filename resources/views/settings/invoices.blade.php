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
