<?php

namespace App\Http\Requests;

use App\Models\Invoice;
use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $invoice = $this->route('invoice');

        if (!$invoice instanceof Invoice) {
            return false;
        }

        // Check company ownership and editability
        return $invoice->company_id === $this->user()->current_company_id
            && $invoice->isEditable();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'partner_id' => ['required', 'uuid', 'exists:partners,id'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:invoice_date'],
            'reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.description' => ['required', 'string', 'max:500'],
            'lines.*.quantity' => ['required', 'numeric', 'min:0.0001', 'max:999999999'],
            'lines.*.unit_price' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'lines.*.vat_rate' => ['required', 'numeric', 'in:0,6,12,21'],
            'lines.*.discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'partner_id.required' => 'Le partenaire est obligatoire.',
            'partner_id.exists' => 'Le partenaire sélectionné n\'existe pas.',
            'invoice_date.required' => 'La date de facture est obligatoire.',
            'due_date.after_or_equal' => 'La date d\'échéance doit être égale ou postérieure à la date de facture.',
            'lines.required' => 'La facture doit contenir au moins une ligne.',
            'lines.min' => 'La facture doit contenir au moins une ligne.',
            'lines.*.description.required' => 'La description est obligatoire pour chaque ligne.',
            'lines.*.quantity.required' => 'La quantité est obligatoire.',
            'lines.*.quantity.min' => 'La quantité doit être supérieure à 0.',
            'lines.*.unit_price.required' => 'Le prix unitaire est obligatoire.',
            'lines.*.vat_rate.required' => 'Le taux de TVA est obligatoire.',
            'lines.*.vat_rate.in' => 'Le taux de TVA doit être 0%, 6%, 12% ou 21%.',
        ];
    }
}
