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
            'super-admin',
            'swift-manager',
            'swift-operator',
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
        return $user->hasRole(['super-admin', 'swift-manager']);
    }

    public function delete(User $user, MessageSwift $messageSwift): bool
    {
        return $user->hasRole(['super-admin', 'swift-manager']);
    }

    public function import(User $user): bool
    {
        return $user->hasAnyRole([
            'super-admin',
            'swift-manager',
            'swift-operator',
            'chargee',
            'chef-agence'
        ]);
    }

    public function export(User $user): bool
    {
        return $user->hasAnyRole([
            'super-admin',
            'swift-manager',
            'swift-operator',
            'backoffice',
            'monetique',
            'chef-agence',
            'chargee'
        ]);
    }

    public function process(User $user, MessageSwift $messageSwift): bool
    {
        return $user->hasRole('swift-manager') || $user->hasRole('super-admin');
    }

    public function authorize(User $user, MessageSwift $messageSwift): bool
    {
        return $user->hasAnyRole([
            'super-admin',
            'swift-manager'
        ]);
    }

    public function suspend(User $user, MessageSwift $messageSwift): bool
    {
        return $user->hasAnyRole([
            'super-admin',
            'swift-manager'
        ]);
    }
}