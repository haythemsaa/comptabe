<?php

namespace App\Policies;

use App\Models\BankTransaction;
use App\Models\User;

class BankTransactionPolicy
{
    /**
     * Determine whether the user can view any bank transactions.
     */
    public function viewAny(User $user): bool
    {
        return $user->current_company_id !== null;
    }

    /**
     * Determine whether the user can view the bank transaction.
     */
    public function view(User $user, BankTransaction $transaction): bool
    {
        return $transaction->bankAccount->company_id === $user->current_company_id;
    }

    /**
     * Determine whether the user can update the bank transaction.
     */
    public function update(User $user, BankTransaction $transaction): bool
    {
        return $transaction->bankAccount->company_id === $user->current_company_id;
    }

    /**
     * Determine whether the user can reconcile the bank transaction.
     */
    public function reconcile(User $user, BankTransaction $transaction): bool
    {
        if ($transaction->bankAccount->company_id !== $user->current_company_id) {
            return false;
        }

        // Only pending or unreconciled transactions can be reconciled
        return in_array($transaction->reconciliation_status, ['pending', 'manual']);
    }

    /**
     * Determine whether the user can import bank statements.
     */
    public function import(User $user): bool
    {
        return $user->current_company_id !== null;
    }

    /**
     * Determine whether the user can book the bank transaction.
     */
    public function book(User $user, BankTransaction $transaction): bool
    {
        if ($transaction->bankAccount->company_id !== $user->current_company_id) {
            return false;
        }

        // Only admins or accountants can book transactions
        $role = $user->getRoleInCompany($user->current_company_id);
        return in_array($role, ['owner', 'admin', 'accountant']);
    }
}
