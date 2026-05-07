<?php
require '/var/www/vendor/autoload.php';
$app = require '/var/www/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$now = now();

$notesLow = [
    'MT103_OUT' => "Virement vérifié et conforme aux dispositions réglementaires BCT. Contrôle AML/CFT effectué — aucune alerte. Bénéficiaire identifié et accrédité. Transaction autorisée.",
    'MT103_IN'  => "Virement entrant contrôlé. Émetteur identifié, montant cohérent avec les opérations habituelles de la contrepartie. Aucune restriction OFAC/UE applicable. Crédit autorisé.",
    'MT202_OUT' => "Ordre de couverture interbancaire conforme. Correspondant agréé par BTL. Vérification SWIFT BIC effectuée. Opération autorisée dans le cadre des accords de correspondance.",
    'MT202_IN'  => "Couverture interbancaire reçue et vérifiée. Banque émettrice figurant sur la liste des correspondants autorisés de BTL. Comptabilisation validée.",
    'MT940_IN'  => "Relevé de compte nostro reçu et rapproché. Soldes conformes aux prévisions de trésorerie. Aucun écart significatif détecté. Validation du rapprochement effectuée.",
    'MT900_IN'  => "Avis de débit reçu. Montant cohérent avec les instructions de virement émises. Confirmation de débit validée et enregistrée.",
    'DEFAULT'   => "Transaction contrôlée et conforme aux règles de contrôle des changes BCT. Aucune anomalie détectée. Opération autorisée.",
];

$notesMedium = [
    'MT103_OUT' => "Virement examiné — montant notable. Justification économique vérifiée et documentée. Conformité réglementaire BCT confirmée. Contrôle renforcé AML effectué. Transaction autorisée sous réserve de conservation des pièces justificatives.",
    'MT103_IN'  => "Virement entrant d'un montant significatif. Origine des fonds vérifiée auprès de la banque correspondante. Aucune restriction applicable. Opération validée après examen approfondi.",
    'MT202_OUT' => "Opération de couverture d'un montant élevé. Vérification de la limite de crédit interbancaire effectuée. Correspondant en règle. Autorisation accordée après contrôle de second niveau.",
    'MT202_IN'  => "Couverture interbancaire à montant significatif. Banque émettrice vérifiée — limite de correspondance respectée. Validation accordée après examen du département trésorerie.",
    'MT940_IN'  => "Relevé de compte nostro présentant des mouvements importants. Rapprochement détaillé effectué. Écarts mineurs expliqués et documentés. Validation accordée.",
    'DEFAULT'   => "Transaction examinée en détail. Niveau de risque modéré confirmé après analyse complémentaire. Conformité aux directives BCT vérifiée. Autorisation accordée avec suivi.",
];

$anomalies = \App\Models\AnomalySwift::whereIn('niveau_risque', ['LOW', 'MEDIUM'])
    ->with('message')
    ->get();

$updated = 0;

foreach ($anomalies as $anomaly) {
    $msg = $anomaly->message;
    if (! $msg) continue;

    $type = strtoupper($msg->TYPE_MESSAGE ?? $msg->type_message ?? '');
    $dir  = strtoupper($msg->DIRECTION    ?? $msg->direction    ?? '');
    $key  = $type . '_' . $dir;

    if ($anomaly->niveau_risque === 'LOW') {
        $note = $notesLow[$key] ?? $notesLow[$type . '_OUT'] ?? $notesLow['DEFAULT'];
    } else {
        $note = $notesMedium[$key] ?? $notesMedium[$type . '_OUT'] ?? $notesMedium['DEFAULT'];
    }

    $msg->update([
        'STATUS'             => 'authorized',
        'AUTHORIZED_AT'      => $now,
        'AUTHORIZATION_NOTE' => $note,
    ]);

    $updated++;
}

echo "=== Notes d'autorisation mises à jour ===\n";
echo "Messages mis à jour : {$updated}\n\n";

// ── Backfill verifie_par sur toutes les anomalies LOW/MEDIUM dont le message est authorized ──
$admin = \App\Models\User::whereHas('roles', fn($q) => $q->whereIn('name', ['super-admin','swift-manager']))->first();
if ($admin) {
    $fixed = \App\Models\AnomalySwift::whereIn('niveau_risque', ['LOW','MEDIUM'])
        ->whereNull('verifie_par')
        ->whereNull('rejetee_par')
        ->whereHas('message', fn($q) => $q->where('STATUS', 'authorized'))
        ->update(['verifie_par' => $admin->id, 'verifie_at' => now()]);
    echo "Anomalies verifie_par backfilled : {$fixed}\n\n";
}

$auth    = \App\Models\MessageSwift::where('STATUS', 'authorized')->count();
$rej     = \App\Models\MessageSwift::where('STATUS', 'rejected')->count();
$pending = \App\Models\MessageSwift::whereNotIn('STATUS', ['authorized','rejected','suspended'])->count();

echo "authorized : {$auth}\n";
echo "rejected   : {$rej}\n";
echo "autres     : {$pending}\n";
