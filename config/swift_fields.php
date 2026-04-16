<?php

return [
    'MT103' => [
        'label' => 'Virement client (MT103)',
        'common_mapping' => [
            'REFERENCE'     => '20',
            'SENDER_NAME'   => '50',
            'RECEIVER_NAME' => '59',
            'AMOUNT'        => '32A',
            'CURRENCY'      => '32A',
            'VALUE_DATE'    => '32A',
            'DESCRIPTION'   => '70',
        ],
        'fields' => [
            '20' => ['label' => 'Référence transaction (20)', 'type' => 'text', 'required' => true, 'maxlength' => 16],
            '23B' => ['label' => 'Code opération (23B)', 'type' => 'select', 'options' => ['CRED' => 'CRED', 'SPAY' => 'SPAY', 'SPRI' => 'SPRI'], 'required' => true],
            '32A' => ['label' => 'Date valeur / Devise / Montant (32A)', 'type' => 'text', 'required' => true, 'placeholder' => 'JJMMAA DEV MONTANT (ex: 060326EUR1250,50)'],
            '33B' => ['label' => 'Devise / Montant d\'instruction (33B)', 'type' => 'text', 'required' => false],
            '50'  => ['label' => 'Donneur d\'ordre (50)', 'type' => 'textarea', 'required' => true],
            '52A' => ['label' => 'Institution ordonnatrice (52A)', 'type' => 'text', 'required' => false],
            '57A' => ['label' => 'Institution bénéficiaire (57A)', 'type' => 'text', 'required' => false],
            '59'  => ['label' => 'Bénéficiaire (59)', 'type' => 'textarea', 'required' => true],
            '70'  => ['label' => 'Informations de paiement (70)', 'type' => 'textarea', 'required' => false],
            '71A' => ['label' => 'Frais (71A)', 'type' => 'select', 'options' => ['SHA' => 'SHA', 'OUR' => 'OUR', 'BEN' => 'BEN'], 'required' => true],
        ],
    ],
    'MT101' => [
        'label' => 'Demande de transfert (MT101)',
        'common_mapping' => [
            'REFERENCE'   => '20',
        ],
        'fields' => [
            '20' => ['label' => 'Référence du message (20)', 'type' => 'text', 'required' => true],
            '28D' => ['label' => 'Numéro de message/page (28D)', 'type' => 'text', 'required' => true],
            '30' => ['label' => 'Date d\'exécution demandée (30)', 'type' => 'date', 'required' => true],
            '50H' => ['label' => 'Compte du donneur d\'ordre (50H)', 'type' => 'text', 'required' => true],
            '52A' => ['label' => 'Institution émettrice (52A)', 'type' => 'text', 'required' => false],
        ],
    ],
    'MT202' => [
        'label' => 'Transfert interbancaire (MT202)',
        'common_mapping' => [
            'REFERENCE'     => '20',
            'SENDER_NAME'   => '52A',   // Ajouté pour que l'expéditeur s'affiche dans la colonne SENDER
            'RECEIVER_NAME' => '58A',
            'AMOUNT'        => '32A',
            'CURRENCY'      => '32A',
            'VALUE_DATE'    => '32A',
        ],
        'fields' => [
            '20' => ['label' => 'Référence transaction (20)', 'type' => 'text', 'required' => true],
            '21' => ['label' => 'Référence liée (21)', 'type' => 'text', 'required' => true],
            '32A' => ['label' => 'Date / Devise / Montant (32A)', 'type' => 'text', 'required' => true],
            '52A' => ['label' => 'Institution ordonnatrice (52A)', 'type' => 'text', 'required' => false],
            '53A' => ['label' => 'Compte de l\'émetteur (53A)', 'type' => 'text', 'required' => false],
            '58A' => ['label' => 'Institution bénéficiaire (58A)', 'type' => 'text', 'required' => true],
        ],
    ],
    'MT210' => [
        'label' => 'Avis d\'encaissement (MT210)',
        'common_mapping' => [
            'REFERENCE' => '20',
            'AMOUNT'    => '32B',
            'CURRENCY'  => '32B',
            'VALUE_DATE' => '30',
        ],
        'fields' => [
            '20' => ['label' => 'Référence transaction (20)', 'type' => 'text', 'required' => true],
            '25' => ['label' => 'Identification du compte (25)', 'type' => 'text', 'required' => true],
            '30' => ['label' => 'Date valeur attendue (30)', 'type' => 'date', 'required' => true],
            '32B' => ['label' => 'Devise / Montant attendu (32B)', 'type' => 'text', 'required' => true],
        ],
    ],
    'MT300' => [
        'label' => 'Confirmation de change (MT300)',
        'common_mapping' => [
            'REFERENCE' => '20',
        ],
        'fields' => [
            '15A' => ['label' => 'Nouveau contrat (15A)', 'type' => 'text', 'required' => true],
            '20' => ['label' => 'Référence opération (20)', 'type' => 'text', 'required' => true],
            '22A' => ['label' => 'Type d\'opération (22A)', 'type' => 'select', 'options' => ['NEW' => 'NEW', 'AMND' => 'AMND', 'CANC' => 'CANC'], 'required' => true],
            '30V' => ['label' => 'Date valeur du change (30V)', 'type' => 'date', 'required' => true],
            '32B' => ['label' => 'Devise / Montant acheté (32B)', 'type' => 'text', 'required' => true],
            '33B' => ['label' => 'Devise / Montant vendu (33B)', 'type' => 'text', 'required' => true],
            '36' => ['label' => 'Taux de change (36)', 'type' => 'text', 'required' => true],
        ],
    ],
    'MT320' => [
        'label' => 'Confirmation de prêt/dépôt (MT320)',
        'common_mapping' => [
            'REFERENCE' => '20',
        ],
        'fields' => [
            '20' => ['label' => 'Référence (20)', 'type' => 'text', 'required' => true],
            '22A' => ['label' => 'Type de contrat (22A)', 'type' => 'text', 'required' => true],
            '30P' => ['label' => 'Date début (30P)', 'type' => 'date', 'required' => true],
            '30X' => ['label' => 'Date échéance (30X)', 'type' => 'date', 'required' => true],
            '37G' => ['label' => 'Taux d\'intérêt (37G)', 'type' => 'text', 'required' => true],
        ],
    ],
    'MT700' => [
        'label' => 'Ouverture de crédit documentaire (MT700)',
        'common_mapping' => [
            'REFERENCE'     => '20',
            'SENDER_NAME'   => '50',
            'RECEIVER_NAME' => '59',
            'AMOUNT'        => '32B',
            'CURRENCY'      => '32B',
            'DESCRIPTION'   => '45A',
        ],
        'fields' => [
            '27' => ['label' => 'Séquence du message (27)', 'type' => 'text', 'required' => true],
            '40A' => ['label' => 'Forme du crédit (40A)', 'type' => 'text', 'required' => true],
            '20' => ['label' => 'Référence du crédit (20)', 'type' => 'text', 'required' => true],
            '31D' => ['label' => 'Date et lieu d\'expiration (31D)', 'type' => 'text', 'required' => true],
            '50' => ['label' => 'Donneur d\'ordre (50)', 'type' => 'textarea', 'required' => true],
            '59' => ['label' => 'Bénéficiaire (59)', 'type' => 'textarea', 'required' => true],
            '32B' => ['label' => 'Devise et Montant (32B)', 'type' => 'text', 'required' => true],
            '44E' => ['label' => 'Port d\'embarquement (44E)', 'type' => 'text', 'required' => false],
            '44F' => ['label' => 'Port de déchargement (44F)', 'type' => 'text', 'required' => false],
            '45A' => ['label' => 'Description des marchandises (45A)', 'type' => 'textarea', 'required' => false],
            '46A' => ['label' => 'Documents requis (46A)', 'type' => 'textarea', 'required' => false],
            '47A' => ['label' => 'Conditions additionnelles (47A)', 'type' => 'textarea', 'required' => false],
        ],
    ],
    'MT760' => [
        'label' => 'Garantie / SBLC (MT760)',
        'common_mapping' => [
            'REFERENCE'   => '20',
            'DESCRIPTION' => '77C',
        ],
        'fields' => [
            '20' => ['label' => 'Référence transaction (20)', 'type' => 'text', 'required' => true],
            '23' => ['label' => 'Type de garantie (23)', 'type' => 'text', 'required' => true],
            '30' => ['label' => 'Date d\'émission (30)', 'type' => 'date', 'required' => false],
            '77C' => ['label' => 'Détails de la garantie (77C)', 'type' => 'textarea', 'required' => true],
        ],
    ],
    'MT940' => [
        'label' => 'Relevé de compte détaillé (MT940)',
        'common_mapping' => [
            'REFERENCE'   => '20',
            'DESCRIPTION' => '61',
            'AMOUNT'      => '62F',
            'CURRENCY'    => '62F',
            'VALUE_DATE'  => '62F',
        ],
        'fields' => [
            '20' => ['label' => 'Référence transaction (20)', 'type' => 'text', 'required' => true],
            '25' => ['label' => 'Identification du compte (25)', 'type' => 'text', 'required' => true],
            '28C' => ['label' => 'Numéro du relevé (28C)', 'type' => 'text', 'required' => true],
            '60F' => ['label' => 'Solde d\'ouverture (60F)', 'type' => 'text', 'required' => true],
            '61' => ['label' => 'Lignes de relevé (61)', 'type' => 'textarea', 'required' => true, 'help' => 'Une ligne par mouvement'],
            '62F' => ['label' => 'Solde de clôture (62F)', 'type' => 'text', 'required' => true],
        ],
    ],
    'MT910' => [
        'label' => 'Avis de crédit (MT910)',
        'common_mapping' => [
            'REFERENCE' => '20',
            'AMOUNT'    => '32A',
            'CURRENCY'  => '32A',
            'VALUE_DATE' => '32A',
        ],
        'fields' => [
            '20' => ['label' => 'Référence transaction (20)', 'type' => 'text', 'required' => true],
            '21' => ['label' => 'Référence liée (21)', 'type' => 'text', 'required' => true],
            '25' => ['label' => 'Identification du compte (25)', 'type' => 'text', 'required' => true],
            '32A' => ['label' => 'Date valeur / Montant (32A)', 'type' => 'text', 'required' => true],
            '52A' => ['label' => 'Institution ordonnatrice (52A)', 'type' => 'text', 'required' => false],
        ],
    ],
];