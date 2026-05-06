<?php
// Quick check: severity distribution for seeded SW* messages
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$rows = DB::select("
    SELECT a.niveau_risque as sev, count(*) cnt
    FROM anomalies_swift a
    JOIN messages_swift m ON a.message_id = m.id
    WHERE m.reference LIKE 'SW%'
    GROUP BY a.niveau_risque
    ORDER BY cnt DESC
");

echo "=== Anomaly severity for seeded messages ===\n";
$total = 0;
foreach ($rows as $row) {
    echo sprintf("  %-8s : %d\n", $row->sev ?? 'NULL', $row->cnt ?? $row->CNT ?? 0);
    $total += $row->cnt;
}
echo "  Total anomaly records : $total\n";
echo "  Total messages (SW%)  : " . DB::table('messages_swift')->where('REFERENCE','like','SW%')->count() . "\n";

// Top HIGH messages
echo "\n=== HIGH severity messages ===\n";
$highs = DB::select("
    SELECT m.reference, m.direction, m.type_message, m.amount, m.currency, m.status,
           m.sender_bic, m.receiver_bic, a.score
    FROM anomalies_swift a
    JOIN messages_swift m ON a.message_id = m.id
    WHERE a.niveau_risque = 'HIGH' AND m.reference LIKE 'SW%'
    ORDER BY a.score DESC
");
foreach ($highs as $h) {
    $ref  = $h->reference  ?? '?';
    $dir  = $h->direction  ?? '?';
    $type = $h->type_message ?? '?';
    $amt  = $h->amount     ?? 0;
    $cur  = $h->currency   ?? '?';
    $sta  = $h->status     ?? '?';
    $score= $h->score      ?? '?';
    $sbic = $h->sender_bic ?? '?';
    $rbic = $h->receiver_bic ?? '?';
    echo sprintf("  [%s] %s %s %.0f %s status=%s score=%s | %s->%s\n",
        $ref, $dir, $type, $amt, $cur, $sta, $score, $sbic, $rbic);
}
