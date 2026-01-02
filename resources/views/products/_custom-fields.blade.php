@if($customFields->isNotEmpty())
    <x-dynamic-fields
        :fields="$customFields"
        :values="$values ?? []"
        prefix="custom_fields"
    />
@else
    <p class="text-sm text-secondary-500 dark:text-secondary-400 italic">
        Aucun champ personnalisé pour ce type de produit.
    </p>
@endif
