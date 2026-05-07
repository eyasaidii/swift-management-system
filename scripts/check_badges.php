<?php

require '/var/www/vendor/autoload.php';
$app = require '/var/www/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$p = \App\Models\AnomalySwift::whereNull('verifie_par')
    ->whereNull('rejetee_par')
    ->whereIn('niveau_risque', ['LOW', 'MEDIUM'])
    ->count();

$h = \App\Models\AnomalySwift::where('niveau_risque', 'HIGH')
    ->whereNull('verifie_par')
    ->whereNull('rejetee_par')
    ->count();

$total = \App\Models\AnomalySwift::count();

echo "pendingIaCount (LOW/MEDIUM non verifies) : $p\n";
echo "criticalCount  (HIGH non verifies)       : $h\n";
echo "total anomalies                          : $total\n";
