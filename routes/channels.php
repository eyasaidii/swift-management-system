<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Canal privé — swift-managers
|--------------------------------------------------------------------------
| Seuls les utilisateurs ayant le rôle "swift-manager" ou "super-admin"
| peuvent s'abonner au canal de notifications SWIFT.
*/

Broadcast::channel('swift-managers', function ($user) {
    return $user->hasRole('swift-manager');
});
