<?php
/**
 * Test de détection IA — scénarios internationaux réels
 * Inspirés de typologies AML/fraude reconnues (FATF, Wolfsberg Group, FinCEN)
 *
 * Usage :
 *   docker exec btl_swift_app sh -c "cd /var/www && php scripts/test_ia_scenarios.php"
 */

$API_URL = 'http://python-api:8001/api/predict';

// ─────────────────────────────────────────────────────────────────────────
// SCÉNARIOS — [description, payload, expected_level, expected_rules]
// ─────────────────────────────────────────────────────────────────────────
$scenarios = [

    // ── HAUTE RISQUE — doivent déclencher HIGH (≥ 60) ──────────────────

    [
        'id'          => 'T-001',
        'name'        => 'Panama Papers — Société écran îles Caïmans, 1.2M USD',
        'description' => 'MT103 sortant vers BIC inconnu (société écran), gros montant USD, rejeté par compliance. '
                       . 'Similaire aux transferts détectés dans les Panama Papers (2016).',
        'expected'    => 'HIGH',
        'payload'     => [
            'id'               => 9001,
            'type_message'     => 'MT103',
            'direction'        => 'OUT',
            'sender_bic'       => 'BTLMTNTT',
            'receiver_bic'     => null,           // BIC manquant = société écran non-identifiée
            'sender_name'      => 'BTL TUNISIAN LIBYAN BANK',
            'receiver_name'    => 'PALMERA HOLDINGS INC',
            'amount'           => 1_200_000.00,
            'currency'         => 'USD',
            'status'           => 'rejected',
            'reference'        => 'T001PANAMA',
            'created_at'       => date('Y-m-d 02:15:00', strtotime('-2 days')),
            'sender_country'   => 'TN',
            'receiver_country' => null,
        ],
    ],

    [
        'id'          => 'T-002',
        'name'        => 'Évasion sanctions Russie — 3.5M RUB, rejeté',
        'description' => 'Transfert interbank MT202 en RUB, montant élevé, rejeté par screening sanctions (OFAC SDN). '
                       . 'Typique des tentatives de contournement post-2022.',
        'expected'    => 'HIGH',
        'payload'     => [
            'id'               => 9002,
            'type_message'     => 'MT202',
            'direction'        => 'OUT',
            'sender_bic'       => 'STBKTNTT',
            'receiver_bic'     => 'SBERRUММ',
            'sender_name'      => 'STB SOCIETE TUNISIENNE DE BANQUE',
            'receiver_name'    => 'SBERBANK MOSCOW',
            'amount'           => 3_500_000.00,
            'currency'         => 'RUB',
            'status'           => 'rejected',
            'reference'        => 'T002SANCTION',
            'created_at'       => date('Y-m-d 23:45:00', strtotime('-1 day')),
            'sender_country'   => 'TN',
            'receiver_country' => 'RU',
        ],
    ],

    [
        'id'          => 'T-003',
        'name'        => 'Trade-Based ML — Surfacturation 2.8M AED Émirats',
        'description' => 'Paiement commercial MT103 vers Dubaï, montant inhabituellement élevé en AED, '
                       . 'rejeté après contrôle documentaire. Technique de trade-based money laundering (TBML).',
        'expected'    => 'HIGH',
        'payload'     => [
            'id'               => 9003,
            'type_message'     => 'MT103',
            'direction'        => 'OUT',
            'sender_bic'       => 'BIATTNTT',
            'receiver_bic'     => 'ADCBAEAA',
            'sender_name'      => 'BIAT BANQUE INTERNATIONALE ARABE DE TUNISIE',
            'receiver_name'    => 'ABU DHABI COMMERCIAL BANK',
            'amount'           => 2_800_000.00,
            'currency'         => 'AED',
            'status'           => 'rejected',
            'reference'        => 'T003TBML',
            'created_at'       => date('Y-m-d 22:30:00', strtotime('-3 days')),
            'sender_country'   => 'TN',
            'receiver_country' => 'AE',
        ],
    ],

    [
        'id'          => 'T-004',
        'name'        => 'Probe transaction — 0.00 EUR sonde de compte, rejeté',
        'description' => 'Virement de 0 EUR entrant, rejeté. Technique utilisée avant une vraie fraude : '
                       . 'tester si le compte est actif (FinCEN advisory FIN-2016-A003).',
        'expected'    => 'HIGH',
        'payload'     => [
            'id'               => 9004,
            'type_message'     => 'MT103',
            'direction'        => 'IN',
            'sender_bic'       => 'BNPAFRPP',
            'receiver_bic'     => 'BTLMTNTT',
            'sender_name'      => 'BNP PARIBAS PARIS',
            'receiver_name'    => 'BTL TUNISIAN LIBYAN BANK',
            'amount'           => 0.00,
            'currency'         => 'EUR',
            'status'           => 'rejected',
            'reference'        => 'T004PROBE',
            'created_at'       => date('Y-m-d 04:00:00', strtotime('-1 day')),
            'sender_country'   => 'FR',
            'receiver_country' => 'TN',
        ],
    ],

    [
        'id'          => 'T-005',
        'name'        => 'Layering — 5.8M JPY heure nocturne, rejeté',
        'description' => 'Réception MT940 de 5.8M JPY en pleine nuit depuis Tokyo, rejeté. '
                       . 'Schéma de layering : multiplication des devises exotiques pour brouiller les pistes.',
        'expected'    => 'HIGH',
        'payload'     => [
            'id'               => 9005,
            'type_message'     => 'MT940',
            'direction'        => 'IN',
            'sender_bic'       => 'MHCBJPJT',
            'receiver_bic'     => 'BTLMTNTT',
            'sender_name'      => 'MIZUHO CORPORATE BANK TOKYO',
            'receiver_name'    => 'BTL TREASURY',
            'amount'           => 5_800_000.00,
            'currency'         => 'JPY',
            'status'           => 'rejected',
            'reference'        => 'T005LAYER',
            'created_at'       => date('Y-m-d 01:10:00', strtotime('-5 days')),
            'sender_country'   => 'JP',
            'receiver_country' => 'TN',
        ],
    ],

    // ── RISQUE MOYEN — doivent déclencher MEDIUM (20–59) ──────────────

    [
        'id'          => 'T-006',
        'name'        => 'Commerce légitime — 280k USD de Deutsche Bank',
        'description' => 'Importation légale de machines industrielles, paiement MT103 de Deutsche Bank. '
                       . 'Seul signal : montant élevé (MONTANT_ELEVE). Statut: processed.',
        'expected'    => 'MEDIUM',
        'payload'     => [
            'id'               => 9006,
            'type_message'     => 'MT103',
            'direction'        => 'IN',
            'sender_bic'       => 'DEUTDEDB',
            'receiver_bic'     => 'BTLMTNTT',
            'sender_name'      => 'DEUTSCHE BANK FRANKFURT',
            'receiver_name'    => 'BTL TUNISIAN LIBYAN BANK',
            'amount'           => 280_000.00,
            'currency'         => 'USD',
            'status'           => 'processed',
            'reference'        => 'T006TRADE',
            'created_at'       => date('Y-m-d 10:30:00', strtotime('-4 days')),
            'sender_country'   => 'DE',
            'receiver_country' => 'TN',
        ],
    ],

    [
        'id'          => 'T-007',
        'name'        => 'Devise exotique faible montant — 18k CNY vers Chine',
        'description' => 'Petit virement en yuan chinois (CNY). Devise inhabituelle mais montant raisonnable. '
                       . 'Statut pending (pas encore validé).',
        'expected'    => 'MEDIUM',
        'payload'     => [
            'id'               => 9007,
            'type_message'     => 'MT103',
            'direction'        => 'OUT',
            'sender_bic'       => 'UBCITNTT',
            'receiver_bic'     => 'ICBKCNBJ',
            'sender_name'      => 'UIB UNION INTERNATIONALE DE BANQUES',
            'receiver_name'    => 'INDUSTRIAL COMMERCIAL BANK OF CHINA',
            'amount'           => 18_000.00,
            'currency'         => 'CNY',
            'status'           => 'pending',
            'reference'        => 'T007CNY',
            'created_at'       => date('Y-m-d 14:00:00', strtotime('-2 days')),
            'sender_country'   => 'TN',
            'receiver_country' => 'CN',
        ],
    ],

    // ── FAIBLE RISQUE — doivent rester LOW (< 20) ─────────────────────

    [
        'id'          => 'T-008',
        'name'        => 'Transaction normale — Salaire 3 500 EUR en EUR',
        'description' => 'Virement de salaire standard en euros depuis une banque française. '
                       . 'Montant normal, devise commune, statut traité. Ne doit PAS être signalé.',
        'expected'    => 'LOW',
        'payload'     => [
            'id'               => 9008,
            'type_message'     => 'MT103',
            'direction'        => 'IN',
            'sender_bic'       => 'SOGEFRPP',
            'receiver_bic'     => 'BTLMTNTT',
            'sender_name'      => 'SOCIETE GENERALE PARIS',
            'receiver_name'    => 'BTL CLIENT ACCOUNT',
            'amount'           => 3_500.00,
            'currency'         => 'EUR',
            'status'           => 'processed',
            'reference'        => 'T008SALARY',
            'created_at'       => date('Y-m-d 09:00:00', strtotime('-1 day')),
            'sender_country'   => 'FR',
            'receiver_country' => 'TN',
        ],
    ],

    [
        'id'          => 'T-009',
        'name'        => 'Commerce ordinaire — Facture fournisseur 22k EUR',
        'description' => 'Règlement fournisseur européen classique. Montant, devise et statut normaux. '
                       . 'Heure de bureau. Ne doit PAS être signalé.',
        'expected'    => 'LOW',
        'payload'     => [
            'id'               => 9009,
            'type_message'     => 'MT103',
            'direction'        => 'OUT',
            'sender_bic'       => 'BTLMTNTT',
            'receiver_bic'     => 'BARCGB22',
            'sender_name'      => 'BTL TUNISIAN LIBYAN BANK',
            'receiver_name'    => 'BARCLAYS BANK LONDON',
            'amount'           => 22_000.00,
            'currency'         => 'EUR',
            'status'           => 'processed',
            'reference'        => 'T009INVOICE',
            'created_at'       => date('Y-m-d 11:30:00', strtotime('-3 days')),
            'sender_country'   => 'TN',
            'receiver_country' => 'GB',
        ],
    ],

    [
        'id'          => 'T-010',
        'name'        => 'Règlement nostro banque correspondante — 50k EUR BNP',
        'description' => 'MT202 de routine entre banques correspondantes. '
                       . 'Opération normale de gestion de liquidités, tout à fait légitime.',
        'expected'    => 'LOW',
        'payload'     => [
            'id'               => 9010,
            'type_message'     => 'MT202',
            'direction'        => 'IN',
            'sender_bic'       => 'BNPAFRPP',
            'receiver_bic'     => 'BTLMTNTT',
            'sender_name'      => 'BNP PARIBAS',
            'receiver_name'    => 'BTL NOSTRO ACCOUNT',
            'amount'           => 50_000.00,
            'currency'         => 'EUR',
            'status'           => 'processed',
            'reference'        => 'T010NOSTRO',
            'created_at'       => date('Y-m-d 08:45:00', strtotime('-2 days')),
            'sender_country'   => 'FR',
            'receiver_country' => 'TN',
        ],
    ],
];

// ─────────────────────────────────────────────────────────────────────────
// Appel API et affichage résultats
// ─────────────────────────────────────────────────────────────────────────

$pass = 0;
$fail = 0;

$levelColor = [
    'HIGH'   => "\033[31m",   // rouge
    'MEDIUM' => "\033[33m",   // jaune
    'LOW'    => "\033[32m",   // vert
];
$reset = "\033[0m";

echo "\n";
echo "╔══════════════════════════════════════════════════════════════════════╗\n";
echo "║       TEST IA — SCÉNARIOS INTERNATIONAUX RÉELS (AML/FRAUDE)         ║\n";
echo "╚══════════════════════════════════════════════════════════════════════╝\n\n";

foreach ($scenarios as $scenario) {
    $ch = curl_init($API_URL);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($scenario['payload']),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 10,
    ]);
    $body   = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error  = curl_error($ch);
    curl_close($ch);

    echo "─────────────────────────────────────────────────────────────────────\n";
    echo "[ {$scenario['id']} ]  {$scenario['name']}\n";
    echo "         {$scenario['description']}\n\n";

    if ($error || $status !== 200) {
        echo "  ⚠ ERREUR API : HTTP $status — $error\n\n";
        $fail++;
        continue;
    }

    $result = json_decode($body, true);
    if (! $result) {
        echo "  ⚠ Réponse invalide : $body\n\n";
        $fail++;
        continue;
    }

    $rawScore  = isset($result['score']) ? (int) round($result['score'] * 100) : 0;
    $mlScore   = $result['score_ml']  ?? 'N/A';
    $isAnomaly = $result['is_anomaly'] ?? false;
    $reasons   = array_column($result['reasons'] ?? [], 'rule');
    $niveau    = match (true) {
        $rawScore >= 60 => 'HIGH',
        $rawScore >= 20 => 'MEDIUM',
        default         => 'LOW',
    };

    $expectedColor = $levelColor[$scenario['expected']] ?? '';
    $actualColor   = $levelColor[$niveau] ?? '';
    $ok = ($niveau === $scenario['expected']);
    $status_icon = $ok ? "\033[32m✔ PASS\033[0m" : "\033[31m✘ FAIL\033[0m";

    echo "  Score   : {$rawScore}/100   (ML brut: {$mlScore})   Anomalie: " . ($isAnomaly ? 'OUI' : 'NON') . "\n";
    echo "  Attendu : {$expectedColor}{$scenario['expected']}{$reset}   →   Obtenu : {$actualColor}{$niveau}{$reset}   {$status_icon}\n";
    echo "  Règles  : " . (empty($reasons) ? '(aucune)' : implode(', ', $reasons)) . "\n\n";

    if ($ok) {
        $pass++;
    } else {
        $fail++;
    }
}

echo "═══════════════════════════════════════════════════════════════════════\n";
$total = $pass + $fail;
$pct   = $total > 0 ? round($pass / $total * 100) : 0;
echo "  RÉSULTATS : {$pass}/{$total} scénarios corrects ({$pct}%)\n";
echo "  PASS: \033[32m{$pass}\033[0m   FAIL: \033[31m{$fail}\033[0m\n";
echo "═══════════════════════════════════════════════════════════════════════\n\n";

echo "Légende des typologies :\n";
echo "  T-001  Panama Papers  (société écran, BIC manquant)\n";
echo "  T-002  Sanctions OFAC (RUB post-2022)\n";
echo "  T-003  TBML           (Trade-Based Money Laundering, AED surfacturé)\n";
echo "  T-004  Probe Tx       (FinCEN FIN-2016-A003, montant zéro)\n";
echo "  T-005  Layering       (JPY nocturne, multiplication devises)\n";
echo "  T-006  Commerce légal (MONTANT_ELEVE seul, Deutsche Bank)\n";
echo "  T-007  Devise exot.   (CNY faible montant, signal partiel)\n";
echo "  T-008  Salaire normal (3 500 EUR, doit rester LOW)\n";
echo "  T-009  Fournisseur    (22k EUR standard, doit rester LOW)\n";
echo "  T-010  Nostro BNP     (50k EUR interbank légal, doit rester LOW)\n\n";
