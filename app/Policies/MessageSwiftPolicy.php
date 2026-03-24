<?php
// app/Policies/MessageSwiftPolicy.php

namespace App\Policies;

use App\Models\User;
use App\Models\MessageSwift;

class MessageSwiftPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            'admin',
            'international-admin',
            'international-user',
            'backoffice',
            'monetique',
            'chef-agence',
            'chargee'
        ]);
    }

    public function view(User $user, MessageSwift $messageSwift): bool
    {
        return $messageSwift->isReadableBy($user);
    }

    public function create(User $user): bool
    {
        return !empty(MessageSwift::getAvailableTypes($user, 'OUT'));
    }

    public function update(User $user, MessageSwift $messageSwift): bool
    {
        return $user->hasRole(['admin', 'international-admin']);
    }

   public function delete(User $user, MessageSwift $messageSwift): bool
{
    return $user->hasRole(['admin', 'international-admin']);
}

    public function import(User $user): bool
    {
        return $user->hasAnyRole([
            'admin',
            'international-admin',
            'international-user',
            'chargee',
            'chef-agence'
        ]);
    }

    public function export(User $user): bool
    {
        return $user->hasAnyRole([
            'admin',
            'international-admin',
            'international-user',
            'backoffice',
            'monetique',
            'chef-agence',
            'chargee'
        ]);
    }

    public function process(User $user, MessageSwift $messageSwift): bool
    {
        return $user->hasRole('international-admin') || $user->hasRole('admin');
    }

    public function authorize(User $user, MessageSwift $messageSwift): bool
    {
        return $user->hasAnyRole([
            'admin',
            'international-admin'
        ]);
    }

    public function suspend(User $user, MessageSwift $messageSwift): bool
    {
        return $user->hasAnyRole([
            'admin',
            'international-admin'
        ]);
    }
}