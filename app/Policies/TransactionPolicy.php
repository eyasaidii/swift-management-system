<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;

class TransactionPolicy
{
    /**
     * Determine whether the user can view any transactions.
     */
    public function viewAny(User $user): bool
    {
        // Seuls certains rôles peuvent lister les transactions
        return $user->hasAnyRole([
            'super-admin',
            'swift-manager',
            'swift-operator',
            'backoffice',
            'monetique',
            'chef-agence',
            'chargee',
        ]);
    }

    /**
     * Determine whether the user can view the transaction.
     */
    public function view(User $user, Transaction $transaction): bool
    {
        // L'utilisateur peut voir la transaction s'il peut voir le message associé
        return $user->can('view', $transaction->messageSwift);
    }

    /**
     * Les transactions sont en lecture seule, donc les méthodes de modification retournent false.
     */
    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Transaction $transaction): bool
    {
        return false;
    }

    public function delete(User $user, Transaction $transaction): bool
    {
        return false;
    }
}
