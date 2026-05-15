<?php

// scripts/test_notification.php
define('LARAVEL_START', microtime(true));
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\MessageSwift;
use App\Models\User;
use App\Notifications\SwiftMessagePendingNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

try {
    $msg = MessageSwift::latest()->first();
    $managers = User::role(['swift-manager', 'super-admin'])->get();

    echo "Message : id={$msg->id} ref='{$msg->REFERENCE}'\n";
    echo 'Managers found: '.$managers->count()."\n";

    foreach ($managers as $u) {
        echo "  - {$u->name} (id={$u->id}, role=".$u->getRoleNames()->implode(',').")\n";
    }

    // Send to all managers
    Notification::send($managers, new SwiftMessagePendingNotification($msg));

    echo "\nAfter send:\n";
    foreach ($managers as $u) {
        echo "  {$u->name} unread: ".$u->unreadNotifications()->count()."\n";
    }
    echo 'Total in notifications table: '.DB::table('notifications')->count()."\n";

} catch (\Throwable $e) {
    echo 'ERROR: '.$e->getMessage()."\n";
    echo $e->getFile().':'.$e->getLine()."\n";
    echo substr($e->getTraceAsString(), 0, 800)."\n";
}
