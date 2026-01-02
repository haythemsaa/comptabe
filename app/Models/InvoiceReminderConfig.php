<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceReminderConfig extends Model
{
    use HasFactory, HasUuid, HasTenant;

    protected $fillable = [
        'company_id',
        'is_enabled',
        'send_before_due',
        'days_before_due',
        'send_on_due_date',
        'send_first_overdue',
        'days_after_due_first',
        'send_second_overdue',
        'days_after_due_second',
        'send_final_reminder',
        'days_after_due_final',
        'apply_late_fees',
        'late_fee_percentage',
        'late_fee_fixed',
        'apply_interest',
        'interest_rate_annual',
        'email_subject_before_due',
        'email_body_before_due',
        'email_subject_on_due',
        'email_body_on_due',
        'email_subject_overdue',
        'email_body_overdue',
        'excluded_partner_ids',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'send_before_due' => 'boolean',
        'days_before_due' => 'integer',
        'send_on_due_date' => 'boolean',
        'send_first_overdue' => 'boolean',
        'days_after_due_first' => 'integer',
        'send_second_overdue' => 'boolean',
        'days_after_due_second' => 'integer',
        'send_final_reminder' => 'boolean',
        'days_after_due_final' => 'integer',
        'apply_late_fees' => 'boolean',
        'late_fee_percentage' => 'decimal:2',
        'late_fee_fixed' => 'decimal:2',
        'apply_interest' => 'boolean',
        'interest_rate_annual' => 'decimal:2',
        'excluded_partner_ids' => 'array',
    ];

    /**
     * Relationships
     */

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Methods
     */

    /**
     * Get default configuration for a company
     */
    public static function getOrCreateForCompany(Company $company): self
    {
        return self::firstOrCreate(
            ['company_id' => $company->id],
            [
                'is_enabled' => false,
                'send_before_due' => true,
                'days_before_due' => 7,
                'send_on_due_date' => true,
                'send_first_overdue' => true,
                'days_after_due_first' => 7,
                'send_second_overdue' => true,
                'days_after_due_second' => 15,
                'send_final_reminder' => true,
                'days_after_due_final' => 30,
                'apply_late_fees' => false,
                'apply_interest' => false,
                'interest_rate_annual' => 10.00, // Belgian legal rate
                'email_subject_before_due' => 'Rappel: Échéance de votre facture {invoice_number}',
                'email_body_before_due' => self::getDefaultEmailTemplate('before_due'),
                'email_subject_on_due' => 'Facture {invoice_number} - Échéance aujourd\'hui',
                'email_body_on_due' => self::getDefaultEmailTemplate('on_due'),
                'email_subject_overdue' => 'Rappel: Facture {invoice_number} en retard',
                'email_body_overdue' => self::getDefaultEmailTemplate('overdue'),
            ]
        );
    }

    /**
     * Get default email templates
     */
    protected static function getDefaultEmailTemplate(string $type): string
    {
        $templates = [
            'before_due' => 'Bonjour,

Nous vous rappelons que votre facture {invoice_number} d\'un montant de {total} € arrivera à échéance le {due_date}.

Pour éviter tout frais supplémentaire, nous vous remercions d\'effectuer le paiement avant cette date.

Vous pouvez effectuer le virement sur le compte suivant:
{company_iban}
Communication: {structured_communication}

Nous restons à votre disposition pour toute question.

Cordialement,
{company_name}',

            'on_due' => 'Bonjour,

Votre facture {invoice_number} d\'un montant de {total} € arrive à échéance aujourd\'hui.

Nous vous remercions d\'effectuer le paiement dans les meilleurs délais.

Coordonnées de paiement:
IBAN: {company_iban}
Communication: {structured_communication}

Cordialement,
{company_name}',

            'overdue' => 'Bonjour,

Nous constatons que votre facture {invoice_number} d\'un montant de {total} €, échue le {due_date}, n\'a toujours pas été réglée ({days_overdue} jours de retard).

Nous vous demandons de bien vouloir régulariser cette situation au plus vite.

{late_fees_text}

Coordonnées de paiement:
IBAN: {company_iban}
Communication: {structured_communication}

Si le paiement a déjà été effectué, veuillez ne pas tenir compte de ce message.

Cordialement,
{company_name}',
        ];

        return $templates[$type] ?? '';
    }

    /**
     * Check if a partner is excluded from reminders
     */
    public function isPartnerExcluded(string $partnerId): bool
    {
        return in_array($partnerId, $this->excluded_partner_ids ?? []);
    }

    /**
     * Add partner to exclusion list
     */
    public function excludePartner(string $partnerId): void
    {
        $excluded = $this->excluded_partner_ids ?? [];

        if (!in_array($partnerId, $excluded)) {
            $excluded[] = $partnerId;
            $this->update(['excluded_partner_ids' => $excluded]);
        }
    }

    /**
     * Remove partner from exclusion list
     */
    public function includePartner(string $partnerId): void
    {
        $excluded = $this->excluded_partner_ids ?? [];
        $excluded = array_values(array_diff($excluded, [$partnerId]));
        $this->update(['excluded_partner_ids' => $excluded]);
    }
}
