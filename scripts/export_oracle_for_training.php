<?php

/**
 * Export Oracle → CSV pour ré-entraînement IA
 * Exporte MESSAGES_SWIFT jointée avec ANOMALIES_SWIFT (labels)
 *
 * Usage : docker exec btl_swift_app php scripts/export_oracle_for_training.php
 */

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$outputPath = '/var/www/data/oracle_training_data.csv';

echo "Connexion Oracle...\n";

// ─── Requête : jointure MESSAGES_SWIFT + ANOMALIES_SWIFT ─────────────────────
$rows = DB::connection('oracle')->select("
    SELECT
        m.ID,
        m.REFERENCE,
        m.TYPE_MESSAGE,
        m.DIRECTION,
        m.SENDER_BIC,
        m.RECEIVER_BIC,
        m.SENDER_NAME,
        m.RECEIVER_NAME,
        m.AMOUNT,
        m.CURRENCY,
        m.STATUS,
        m.CATEGORIE,
        m.TRANSLATION_ERRORS,
        TO_CHAR(m.CREATED_AT, 'YYYY-MM-DD HH24:MI:SS') AS CREATED_AT,
        a.SCORE         AS anomaly_score,
        a.NIVEAU_RISQUE AS anomaly_niveau,
        a.RAISONS       AS anomaly_raisons,
        CASE WHEN a.SCORE >= 20 THEN 1 ELSE 0 END AS is_anomaly
    FROM MESSAGES_SWIFT m
    LEFT JOIN ANOMALIES_SWIFT a ON a.MESSAGE_ID = m.ID
    ORDER BY m.ID
");

if (empty($rows)) {
    echo "ERREUR : aucune donnée dans MESSAGES_SWIFT.\n";
    exit(1);
}

echo 'Lignes trouvées : '.count($rows)."\n";

// ─── Écriture CSV ─────────────────────────────────────────────────────────────
$fh = fopen($outputPath, 'w');

// En-tête (même nommage que generate_synthetic_data.py)
fputcsv($fh, [
    'id', 'reference', 'type_message', 'direction',
    'sender_bic', 'receiver_bic', 'sender_name', 'receiver_name',
    'amount', 'currency', 'status', 'category',
    'translation_errors', 'created_at',
    'anomaly_score', 'anomaly_niveau', 'anomaly_raisons', 'is_anomaly',
]);

$exported = 0;
foreach ($rows as $row) {
    $r = (array) $row;

    // Normaliser les clés en minuscules (Oracle retourne en majuscules)
    $r = array_change_key_case($r, CASE_LOWER);

    fputcsv($fh, [
        $r['id'] ?? '',
        $r['reference'] ?? '',
        $r['type_message'] ?? '',
        $r['direction'] ?? 'OUT',
        $r['sender_bic'] ?? '',
        $r['receiver_bic'] ?? '',
        $r['sender_name'] ?? '',
        $r['receiver_name'] ?? '',
        $r['amount'] ?? 0,
        $r['currency'] ?? 'EUR',
        $r['status'] ?? '',
        $r['categorie'] ?? '',
        $r['translation_errors'] ?? '',
        $r['created_at'] ?? '',
        $r['anomaly_score'] ?? '',
        $r['anomaly_niveau'] ?? '',
        $r['anomaly_raisons'] ?? '',
        $r['is_anomaly'] ?? 0,
    ]);
    $exported++;
}

fclose($fh);

echo "CSV exporté : {$outputPath}\n";
echo "Total lignes : {$exported}\n";

// ─── Stats rapides ────────────────────────────────────────────────────────────
$withLabel = array_filter($rows, fn ($r) => isset(((array) $r)['anomaly_score']) && ((array) $r)['anomaly_score'] !== null);
$high = array_filter($withLabel, fn ($r) => (((array) $r)['anomaly_niveau'] ?? '') === 'HIGH');
$medium = array_filter($withLabel, fn ($r) => (((array) $r)['anomaly_niveau'] ?? '') === 'MEDIUM');
$low = array_filter($withLabel, fn ($r) => (((array) $r)['anomaly_niveau'] ?? '') === 'LOW');

echo "\n=== Distribution des labels ===\n";
echo '  Avec label anomalie : '.count($withLabel).'/'.count($rows)."\n";
echo '  HIGH   : '.count($high)."\n";
echo '  MEDIUM : '.count($medium)."\n";
echo '  LOW    : '.count($low)."\n";
echo "\nExport terminé ✔\n";
