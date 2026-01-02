<x-app-layout>
    <x-slot name="title">Scanner un Document</x-slot>

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-secondary-900 dark:text-white">
                    Scanner un Document Intelligent
                </h1>
                <p class="text-secondary-600 dark:text-secondary-400">
                    Upload automatique avec OCR, détection de doublons et extraction IA des données
                </p>
            </div>
            <div class="flex gap-2">
                <span class="badge badge-success">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    IA Activée
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6" x-data="intelligentScannerApp()">
            <!-- Left: Upload & Preview -->
            <div class="space-y-6">
                <!-- Document Type Selection -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="font-semibold text-secondary-900 dark:text-white">
                            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                            </svg>
                            Type de document
                        </h2>
                    </div>
                    <div class="card-body">
                        <div class="grid grid-cols-2 gap-3">
                            <button
                                @click="documentType = 'invoice'"
                                :class="{'ring-2 ring-primary-500 bg-primary-50 dark:bg-primary-900/30': documentType === 'invoice'}"
                                class="p-4 border-2 border-secondary-200 dark:border-secondary-700 rounded-lg hover:border-primary-500 transition-all"
                            >
                                <svg class="w-8 h-8 mx-auto mb-2 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span class="text-sm font-medium">Facture</span>
                            </button>

                            <button
                                @click="documentType = 'expense'"
                                :class="{'ring-2 ring-primary-500 bg-primary-50 dark:bg-primary-900/30': documentType === 'expense'}"
                                class="p-4 border-2 border-secondary-200 dark:border-secondary-700 rounded-lg hover:border-primary-500 transition-all"
                            >
                                <svg class="w-8 h-8 mx-auto mb-2 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span class="text-sm font-medium">Dépense</span>
                            </button>

                            <button
                                @click="documentType = 'receipt'"
                                :class="{'ring-2 ring-primary-500 bg-primary-50 dark:bg-primary-900/30': documentType === 'receipt'}"
                                class="p-4 border-2 border-secondary-200 dark:border-secondary-700 rounded-lg hover:border-primary-500 transition-all"
                            >
                                <svg class="w-8 h-8 mx-auto mb-2 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                </svg>
                                <span class="text-sm font-medium">Reçu</span>
                            </button>

                            <button
                                @click="documentType = 'quote'"
                                :class="{'ring-2 ring-primary-500 bg-primary-50 dark:bg-primary-900/30': documentType === 'quote'}"
                                class="p-4 border-2 border-secondary-200 dark:border-secondary-700 rounded-lg hover:border-primary-500 transition-all"
                            >
                                <svg class="w-8 h-8 mx-auto mb-2 text-info-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span class="text-sm font-medium">Devis</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Upload Area -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="font-semibold text-secondary-900 dark:text-white">
                            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Upload de document
                        </h2>
                    </div>
                    <div class="card-body">
                        <div
                            @drop.prevent="handleDrop"
                            @dragover.prevent="dragOver = true"
                            @dragleave.prevent="dragOver = false"
                            :class="{'border-primary-500 bg-primary-50 dark:bg-primary-900/20': dragOver}"
                            class="border-2 border-dashed border-secondary-300 dark:border-secondary-700 rounded-xl p-8 text-center transition-all cursor-pointer hover:border-primary-500"
                            @click="$refs.fileInput.click()"
                        >
                            <input
                                type="file"
                                x-ref="fileInput"
                                @change="handleFileSelect"
                                accept="image/*,.pdf"
                                class="hidden"
                            >

                            <template x-if="!file">
                                <div>
                                    <svg class="w-16 h-16 mx-auto mb-4 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                    <p class="text-lg font-medium text-secondary-700 dark:text-secondary-300 mb-2">
                                        Glissez-déposez votre <span x-text="getDocumentTypeName()"></span> ici
                                    </p>
                                    <p class="text-sm text-secondary-500 dark:text-secondary-400">
                                        ou cliquez pour sélectionner un fichier
                                    </p>
                                    <p class="text-xs text-secondary-400 mt-2">
                                        Formats: JPG, PNG, PDF (max 10MB) • Multi-langue FR/NL/EN
                                    </p>
                                </div>
                            </template>

                            <template x-if="file">
                                <div>
                                    <svg class="w-16 h-16 mx-auto mb-4 text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <p class="text-lg font-medium text-secondary-700 dark:text-secondary-300 mb-2" x-text="file.name"></p>
                                    <p class="text-sm text-secondary-500" x-text="formatFileSize(file.size)"></p>
                                    <button
                                        @click.stop="file = null; previewUrl = null"
                                        class="btn btn-secondary btn-sm mt-3"
                                    >
                                        Changer de fichier
                                    </button>
                                </div>
                            </template>
                        </div>

                        <!-- Processing Progress -->
                        <template x-if="scanning">
                            <div class="mt-6">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-medium text-secondary-700 dark:text-secondary-300">Analyse IA en cours...</span>
                                    <span class="text-sm text-secondary-500" x-text="scanProgress + '%'"></span>
                                </div>
                                <div class="w-full bg-secondary-200 dark:bg-secondary-700 rounded-full h-2">
                                    <div
                                        class="bg-primary-600 h-2 rounded-full transition-all duration-300"
                                        :style="'width: ' + scanProgress + '%'"
                                    ></div>
                                </div>
                                <p class="text-xs text-secondary-500 mt-2" x-text="scanStep"></p>
                            </div>
                        </template>

                        <template x-if="file && !scanning">
                            <div class="mt-6">
                                <button
                                    @click="scanDocument"
                                    class="btn btn-primary w-full"
                                >
                                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
                                    </svg>
                                    Scanner avec IA
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Preview -->
                <div class="card" x-show="previewUrl">
                    <div class="card-header">
                        <h2 class="font-semibold text-secondary-900 dark:text-white">Prévisualisation</h2>
                    </div>
                    <div class="card-body">
                        <img :src="previewUrl" class="w-full h-auto rounded-lg shadow-lg" alt="Preview">
                    </div>
                </div>
            </div>

            <!-- Right: Extracted Data -->
            <div class="space-y-6">
                <!-- Duplicate Detection Alerts -->
                <template x-if="duplicateWarning">
                    <div class="alert alert-warning">
                        <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <p class="font-medium">Doublon potentiel détecté</p>
                            <p class="text-sm mt-1" x-text="duplicateWarning.message"></p>
                            <div class="mt-2 flex gap-2">
                                <a :href="duplicateWarning.url" target="_blank" class="text-sm underline">Voir le document existant</a>
                                <button @click="duplicateWarning = null" class="text-sm underline">Ignorer et continuer</button>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Results -->
                <div class="card" x-show="scannedData">
                    <div class="card-header flex items-center justify-between">
                        <h2 class="font-semibold text-secondary-900 dark:text-white">Données extraites</h2>
                        <div class="flex items-center gap-2">
                            <template x-if="scannedData">
                                <span
                                    :class="{
                                        'badge-success': scannedData?.overall_confidence >= 0.85,
                                        'badge-warning': scannedData?.overall_confidence >= 0.65 && scannedData?.overall_confidence < 0.85,
                                        'badge-danger': scannedData?.overall_confidence < 0.65
                                    }"
                                    class="badge"
                                >
                                    <span x-text="Math.round((scannedData?.overall_confidence || 0) * 100)"></span>% confiance
                                </span>
                            </template>
                            <button @click="showConfidenceDetails = !showConfidenceDetails" class="text-xs text-primary-600 hover:underline">
                                Détails
                            </button>
                        </div>
                    </div>

                    <!-- Confidence Breakdown -->
                    <template x-if="showConfidenceDetails && scannedData?.extracted_data">
                        <div class="border-b border-secondary-200 dark:border-secondary-700 px-6 py-4 bg-secondary-50 dark:bg-secondary-900/50">
                            <h3 class="text-sm font-semibold mb-3">Confiance par champ :</h3>
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <template x-for="(field, key) in scannedData.extracted_data" :key="key">
                                    <div class="flex items-center justify-between" x-show="field.confidence !== undefined">
                                        <span x-text="formatFieldName(key)" class="text-secondary-600 dark:text-secondary-400"></span>
                                        <span
                                            :class="{
                                                'text-success-600': field.confidence >= 0.85,
                                                'text-warning-600': field.confidence >= 0.65 && field.confidence < 0.85,
                                                'text-danger-600': field.confidence < 0.65
                                            }"
                                            class="font-medium"
                                            x-text="Math.round(field.confidence * 100) + '%'"
                                        ></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    <div class="card-body">
                        <form @submit.prevent="createDocument" class="space-y-4">
                            <!-- Supplier Info -->
                            <div>
                                <label class="form-label flex items-center justify-between">
                                    <span>Fournisseur *</span>
                                    <template x-if="scannedData?.matched_partner">
                                        <span class="badge badge-success text-xs">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                            Trouvé automatiquement
                                        </span>
                                    </template>
                                </label>
                                <input
                                    type="text"
                                    x-model="scannedData.supplier_name"
                                    class="form-input"
                                    required
                                >
                                <template x-if="aiSuggestions.supplier">
                                    <p class="text-xs text-primary-600 mt-1">
                                        💡 IA suggère: <button @click="scannedData.supplier_name = aiSuggestions.supplier" type="button" class="underline" x-text="aiSuggestions.supplier"></button>
                                    </p>
                                </template>
                            </div>

                            <div>
                                <label class="form-label">N° TVA fournisseur</label>
                                <div class="relative">
                                    <input
                                        type="text"
                                        x-model="scannedData.supplier_vat"
                                        @blur="validateVat"
                                        class="form-input"
                                        :class="{'border-success-500': vatValidated === true, 'border-danger-500': vatValidated === false}"
                                        placeholder="BE 0123.456.789"
                                    >
                                    <template x-if="vatValidated === true">
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-success-500">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                        </span>
                                    </template>
                                </div>
                            </div>

                            <!-- Invoice Info -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="form-label">N° Facture</label>
                                    <input
                                        type="text"
                                        x-model="scannedData.invoice_number"
                                        class="form-input"
                                    >
                                </div>

                                <div>
                                    <label class="form-label">Date facture *</label>
                                    <input
                                        type="date"
                                        x-model="scannedData.invoice_date"
                                        class="form-input"
                                        required
                                    >
                                </div>
                            </div>

                            <div>
                                <label class="form-label">Date d'échéance</label>
                                <input
                                    type="date"
                                    x-model="scannedData.due_date"
                                    class="form-input"
                                >
                            </div>

                            <!-- Amounts -->
                            <div class="border-t border-secondary-200 dark:border-secondary-700 pt-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="form-label">Montant HTVA *</label>
                                        <div class="relative">
                                            <input
                                                type="number"
                                                step="0.01"
                                                x-model="scannedData.subtotal"
                                                class="form-input pr-8"
                                                required
                                            >
                                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-500">€</span>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="form-label">Taux TVA *</label>
                                        <select x-model="scannedData.vat_rate" class="form-input">
                                            <option value="0">0% (exempt)</option>
                                            <option value="6">6% (réduit)</option>
                                            <option value="12">12% (intermédiaire)</option>
                                            <option value="21" selected>21% (normal)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4 mt-4">
                                    <div>
                                        <label class="form-label">Montant TVA *</label>
                                        <div class="relative">
                                            <input
                                                type="number"
                                                step="0.01"
                                                x-model="scannedData.vat_amount"
                                                class="form-input pr-8"
                                                required
                                            >
                                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-500">€</span>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="form-label">Total TTC *</label>
                                        <div class="relative">
                                            <input
                                                type="number"
                                                step="0.01"
                                                x-model="scannedData.total_amount"
                                                class="form-input pr-8 font-bold"
                                                required
                                            >
                                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-secondary-500">€</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Description -->
                            <div>
                                <label class="form-label">Description</label>
                                <textarea
                                    x-model="scannedData.description"
                                    class="form-input"
                                    rows="3"
                                    placeholder="Description de la facture..."
                                ></textarea>
                            </div>

                            <!-- AI Suggestions -->
                            <template x-if="aiSuggestions.category">
                                <div class="alert alert-info">
                                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM15.657 5.757a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM5.05 6.464A1 1 0 106.464 5.05l-.707-.707a1 1 0 00-1.414 1.414l.707.707zM5 10a1 1 0 01-1 1H3a1 1 0 110-2h1a1 1 0 011 1zM8 16v-1h4v1a2 2 0 11-4 0zM12 14c.015-.34.208-.646.477-.859a4 4 0 10-4.954 0c.27.213.462.519.476.859h4.002z"/>
                                    </svg>
                                    <div>
                                        <p class="font-medium">💡 Suggestion IA</p>
                                        <p class="text-sm">Catégorie suggérée: <strong x-text="aiSuggestions.category"></strong></p>
                                        <p class="text-xs text-secondary-600 mt-1">Basé sur vos factures similaires passées</p>
                                    </div>
                                </div>
                            </template>

                            <!-- Validation Errors -->
                            <div x-show="validationErrors.length > 0" class="alert alert-warning">
                                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                <div>
                                    <p class="font-medium">Avertissements :</p>
                                    <ul class="list-disc list-inside mt-2">
                                        <template x-for="error in validationErrors" :key="error">
                                            <li x-text="error"></li>
                                        </template>
                                    </ul>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex gap-3">
                                <button
                                    type="submit"
                                    :disabled="creating"
                                    class="btn btn-primary flex-1"
                                >
                                    <span x-show="!creating">
                                        <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        Créer <span x-text="getDocumentTypeName()"></span>
                                    </span>
                                    <span x-show="creating" class="flex items-center justify-center">
                                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Création en cours...
                                    </span>
                                </button>

                                <button
                                    type="button"
                                    @click="reset"
                                    class="btn btn-secondary"
                                >
                                    Annuler
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Help Card -->
                <div class="card bg-primary-50 dark:bg-primary-900/20 border-primary-200 dark:border-primary-800" x-show="!scannedData">
                    <div class="card-body">
                        <h3 class="font-semibold text-primary-900 dark:text-primary-100 mb-3">
                            <svg class="w-5 h-5 inline-block mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM15.657 5.757a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM5.05 6.464A1 1 0 106.464 5.05l-.707-.707a1 1 0 00-1.414 1.414l.707.707zM5 10a1 1 0 01-1 1H3a1 1 0 110-2h1a1 1 0 011 1zM8 16v-1h4v1a2 2 0 11-4 0zM12 14c.015-.34.208-.646.477-.859a4 4 0 10-4.954 0c.27.213.462.519.476.859h4.002z"/>
                            </svg>
                            Intelligence Artificielle Avancée
                        </h3>
                        <ul class="list-none space-y-2 text-sm text-primary-800 dark:text-primary-200">
                            <li class="flex items-start">
                                <svg class="w-5 h-5 mr-2 text-success-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span><strong>OCR Multi-langue</strong> - Détection automatique FR/NL/EN</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 mr-2 text-success-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span><strong>Détection doublons</strong> - Hash, numéro facture, communication structurée</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 mr-2 text-success-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span><strong>Matching automatique</strong> - Fournisseurs existants par VAT/nom</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 mr-2 text-success-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span><strong>Apprentissage continu</strong> - L'IA s'améliore avec vos corrections</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 mr-2 text-success-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span><strong>Auto-création</strong> - ≥85% confiance → Aucune validation requise</span>
                            </li>
                        </ul>

                        <div class="mt-4 p-3 bg-white dark:bg-primary-950 rounded-lg">
                            <p class="text-xs text-primary-700 dark:text-primary-300">
                                <strong>Conseils pour de meilleurs résultats:</strong>
                            </p>
                            <ul class="list-disc list-inside text-xs text-primary-600 dark:text-primary-400 mt-2 space-y-1">
                                <li>Photo bien éclairée et nette</li>
                                <li>Document à plat sans plis</li>
                                <li>Tous les coins visibles</li>
                                <li>PDF natif préféré au scan</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function intelligentScannerApp() {
            return {
                file: null,
                previewUrl: null,
                dragOver: false,
                scanning: false,
                creating: false,
                scannedData: null,
                validationErrors: [],
                documentType: 'invoice',
                scanProgress: 0,
                scanStep: '',
                showConfidenceDetails: false,
                duplicateWarning: null,
                aiSuggestions: {},
                vatValidated: null,

                getDocumentTypeName() {
                    const types = {
                        'invoice': 'facture',
                        'expense': 'dépense',
                        'receipt': 'reçu',
                        'quote': 'devis'
                    };
                    return types[this.documentType] || 'document';
                },

                formatFieldName(key) {
                    const names = {
                        'invoice_number': 'N° facture',
                        'invoice_date': 'Date',
                        'vat_number': 'N° TVA',
                        'amounts': 'Montants',
                        'supplier_name': 'Fournisseur',
                        'total_amount': 'Total'
                    };
                    return names[key] || key;
                },

                handleDrop(e) {
                    this.dragOver = false;
                    const files = e.dataTransfer.files;
                    if (files.length > 0) {
                        this.handleFile(files[0]);
                    }
                },

                handleFileSelect(e) {
                    const files = e.target.files;
                    if (files.length > 0) {
                        this.handleFile(files[0]);
                    }
                },

                handleFile(file) {
                    const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
                    if (!validTypes.includes(file.type)) {
                        window.showToast('Type de fichier non supporté. Utilisez JPG, PNG ou PDF.', 'error');
                        return;
                    }

                    if (file.size > 10 * 1024 * 1024) {
                        window.showToast('Le fichier est trop volumineux. Max 10MB.', 'error');
                        return;
                    }

                    this.file = file;

                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.previewUrl = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    } else {
                        this.previewUrl = null;
                    }

                    this.scannedData = null;
                    this.validationErrors = [];
                    this.duplicateWarning = null;
                },

                formatFileSize(bytes) {
                    if (bytes === 0) return '0 B';
                    const k = 1024;
                    const sizes = ['B', 'KB', 'MB', 'GB'];
                    const i = Math.floor(Math.log(bytes) / Math.log(k));
                    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
                },

                async scanDocument() {
                    if (!this.file) {
                        window.showToast('Veuillez sélectionner un fichier', 'error');
                        return;
                    }

                    this.scanning = true;
                    this.scanProgress = 0;
                    this.scanStep = 'Préparation du document...';

                    const formData = new FormData();
                    formData.append('document', this.file);
                    formData.append('document_type', this.documentType);

                    // Simulate progress
                    const progressInterval = setInterval(() => {
                        if (this.scanProgress < 90) {
                            this.scanProgress += Math.random() * 15;
                            if (this.scanProgress < 30) {
                                this.scanStep = 'Détection du texte (OCR)...';
                            } else if (this.scanProgress < 60) {
                                this.scanStep = 'Extraction des données structurées...';
                            } else {
                                this.scanStep = 'Validation et matching...';
                            }
                        }
                    }, 500);

                    try {
                        const response = await fetch('{{ route("scanner.scan") }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: formData
                        });

                        const data = await response.json();
                        clearInterval(progressInterval);
                        this.scanProgress = 100;
                        this.scanStep = 'Terminé !';

                        if (data.success) {
                            this.scannedData = data.data;
                            this.validationErrors = data.validation_errors || [];
                            this.duplicateWarning = data.duplicate_warning || null;
                            this.aiSuggestions = data.ai_suggestions || {};

                            window.showToast(
                                data.message || 'Document scanné avec succès',
                                this.validationErrors.length > 0 ? 'warning' : 'success'
                            );
                        } else {
                            window.showToast(data.message || 'Erreur lors du scan', 'error');
                        }
                    } catch (error) {
                        clearInterval(progressInterval);
                        console.error('Scan error:', error);
                        window.showToast('Erreur lors du scan du document', 'error');
                    } finally {
                        this.scanning = false;
                        setTimeout(() => {
                            this.scanProgress = 0;
                            this.scanStep = '';
                        }, 2000);
                    }
                },

                async validateVat() {
                    if (!this.scannedData.supplier_vat) {
                        this.vatValidated = null;
                        return;
                    }

                    try {
                        const response = await fetch('/api/validate-vat', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify({ vat_number: this.scannedData.supplier_vat })
                        });

                        const data = await response.json();
                        this.vatValidated = data.valid;

                        if (data.valid && data.company_name) {
                            this.aiSuggestions.supplier = data.company_name;
                        }
                    } catch (error) {
                        console.error('VAT validation error:', error);
                    }
                },

                async createDocument() {
                    this.creating = true;

                    const formData = new FormData();

                    Object.keys(this.scannedData).forEach(key => {
                        if (this.scannedData[key] !== null && this.scannedData[key] !== undefined) {
                            formData.append(key, this.scannedData[key]);
                        }
                    });

                    formData.append('document', this.file);
                    formData.append('document_type', this.documentType);

                    try {
                        const response = await fetch('{{ route("scanner.create-invoice") }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: formData
                        });

                        const data = await response.json();

                        if (data.success) {
                            window.showToast(data.message, 'success');
                            setTimeout(() => {
                                window.location.href = data.invoice.url;
                            }, 1500);
                        } else {
                            window.showToast(data.message || 'Erreur lors de la création', 'error');
                        }
                    } catch (error) {
                        console.error('Create document error:', error);
                        window.showToast('Erreur lors de la création du document', 'error');
                    } finally {
                        this.creating = false;
                    }
                },

                reset() {
                    this.file = null;
                    this.previewUrl = null;
                    this.scannedData = null;
                    this.validationErrors = [];
                    this.duplicateWarning = null;
                    this.aiSuggestions = {};
                    this.vatValidated = null;
                }
            };
        }
    </script>
    @endpush
</x-app-layout>
