<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    /**
     * Determine whether the user can view any invoices.
     */
    public function viewAny(User $user): bool
    {
        return $user->current_company_id !== null;
    }

    /**
     * Determine whether the user can view the invoice.
     */
    public function view(User $user, Invoice $invoice): bool
    {
        return $invoice->company_id === $user->current_company_id;
    }

    /**
     * Determine whether the user can create invoices.
     */
    public function create(User $user): bool
    {
        return $user->current_company_id !== null;
    }

    /**
     * Determine whether the user can update the invoice.
     */
    public function update(User $user, Invoice $invoice): bool
    {
        if ($invoice->company_id !== $user->current_company_id) {
            return false;
        }

        // Only draft invoices can be edited (except by admins)
        if (!$invoice->isEditable() && !$user->isAdminInCurrentTenant()) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can delete the invoice.
     */
    public function delete(User $user, Invoice $invoice): bool
    {
        if ($invoice->company_id !== $user->current_company_id) {
            return false;
        }

        // Only admins/owners can delete, and only if invoice is draft
        if (!$user->isAdminInCurrentTenant()) {
            return false;
        }

        return $invoice->status === 'draft';
    }

    /**
     * Determine whether the user can validate the invoice.
     */
    public function validate(User $user, Invoice $invoice): bool
    {
        if ($invoice->company_id !== $user->current_company_id) {
            return false;
        }

        return $invoice->status === 'draft';
    }

    /**
     * Determine whether the user can send the invoice.
     */
    public function send(User $user, Invoice $invoice): bool
    {
        if ($invoice->company_id !== $user->current_company_id) {
            return false;
        }

        return in_array($invoice->status, ['validated', 'sent']);
    }

    /**
     * Determine whether the user can book the invoice.
     */
    public function book(User $user, Invoice $invoice): bool
    {
        if ($invoice->company_id !== $user->current_company_id) {
            return false;
        }

        // Only admins or accountants can book invoices
        $role = $user->getRoleInCompany($user->current_company_id);
        return in_array($role, ['owner', 'admin', 'accountant']);
    }

    /**
     * Determine whether the user can download the invoice PDF.
     */
    public function download(User $user, Invoice $invoice): bool
    {
        return $invoice->company_id === $user->current_company_id;
    }

    /**
     * Determine whether the user can send invoice via Peppol.
     */
    public function sendViaPeppol(User $user, Invoice $invoice): bool
    {
        if ($invoice->company_id !== $user->current_company_id) {
            return false;
        }

        // Check if company has Peppol enabled
        $company = $invoice->company;
        if (!$company || !$company->isPeppolEnabled()) {
            return false;
        }

        // Check if partner is Peppol capable
        if (!$invoice->partner || !$invoice->partner->peppol_capable) {
            return false;
        }

        // Check quota
        if (!$company->hasPeppolQuota()) {
            return false;
        }

        // Invoice must be validated or sent
        return in_array($invoice->status, ['validated', 'sent']);
    }

    /**
     * Determine whether the user can mark invoice as paid.
     */
    public function markAsPaid(User $user, Invoice $invoice): bool
    {
        if ($invoice->company_id !== $user->current_company_id) {
            return false;
        }

        // Only accountants and above can mark as paid
        $role = $user->getRoleInCompany($user->current_company_id);
        return in_array($role, ['owner', 'admin', 'accountant']);
    }
}
