<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 11px;
        color: #2c2c2c;
        background: #fff;
    }

    /* ── EN-TÊTE ─────────────────────────────── */
    .header {
        background: #1A5C38;
        padding: 20px 25px;
        color: white;
    }
    .header-top {
        display: table;
        width: 100%;
    }
    .header-logo {
        display: table-cell;
        vertical-align: middle;
        width: 50%;
    }
    .header-logo h1 {
        font-size: 20px;
        font-weight: bold;
        letter-spacing: 1px;
    }
    .header-logo p {
        font-size: 9px;
        opacity: 0.8;
        margin-top: 3px;
    }
    .header-ref {
        display: table-cell;
        vertical-align: middle;
        text-align: right;
        width: 50%;
    }
    .header-ref .doc-title {
        font-size: 14px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 2px;
    }
    .header-ref .doc-ref {
        font-size: 10px;
        opacity: 0.85;
        margin-top: 4px;
    }

    /* ── BANDE STATUT ────────────────────────── */
    .status-bar {
        padding: 8px 25px;
        font-size: 10px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: white;
        text-align: center;
    }
    .status-authorized { background: #2E7D32; }
    .status-processed  { background: #1565C0; }
    .status-pending    { background: #F57F17; color: #333; }
    .status-suspended  { background: #C62828; }
    .status-rejected   { background: #C62828; }

    /* ── CORPS ───────────────────────────────── */
    .body { padding: 20px 25px; }

    /* ── SECTION ─────────────────────────────── */
    .section {
        margin-bottom: 16px;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        overflow: hidden;
    }
    .section-title {
        background: #f5f5f5;
        padding: 7px 12px;
        font-size: 10px;
        font-weight: bold;
        color: #1A5C38;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid #e0e0e0;
    }
    .section-body {
        padding: 10px 12px;
    }

    /* ── TABLE INFO ──────────────────────────── */
    .info-table {
        width: 100%;
        border-collapse: collapse;
    }
    .info-table td {
        padding: 5px 8px;
        vertical-align: top;
        border-bottom: 1px solid #f0f0f0;
    }
    .info-table td:last-child { border-bottom: none; }
    .info-table .label {
        color: #666;
        width: 35%;
        font-size: 10px;
    }
    .info-table .value {
        font-weight: bold;
        font-size: 10px;
    }

    /* ── DEUX COLONNES ───────────────────────── */
    .two-col {
        display: table;
        width: 100%;
        table-layout: fixed;
    }
    .col-left  { display: table-cell; width: 49%; padding-right: 8px; }
    .col-right { display: table-cell; width: 49%; padding-left: 8px; }

    /* ── MONTANT HIGHLIGHT ───────────────────── */
    .amount-box {
        background: #E8F5E9;
        border: 2px solid #1A5C38;
        border-radius: 4px;
        padding: 14px;
        text-align: center;
        margin-bottom: 16px;
    }
    .amount-box .amount-label {
        font-size: 10px;
        color: #555;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .amount-box .amount-value {
        font-size: 26px;
        font-weight: bold;
        color: #1A5C38;
        margin: 4px 0;
    }
    .amount-box .amount-date {
        font-size: 9px;
        color: #777;
    }

    /* ── TAGS MT ─────────────────────────────── */
    .tags-table {
        width: 100%;
        border-collapse: collapse;
    }
    .tags-table tr:nth-child(even) { background: #f9f9f9; }
    .tags-table td {
        padding: 4px 8px;
        border: 1px solid #eee;
        font-size: 10px;
        vertical-align: top;
    }
    .tags-table .tag-name {
        font-weight: bold;
        color: #1A5C38;
        width: 15%;
        white-space: nowrap;
    }

    /* ── WATERMARK ───────────────────────────── */
    .watermark {
        position: fixed;
        top: 45%;
        left: 20%;
        font-size: 60px;
        color: rgba(26, 92, 56, 0.05);
        font-weight: bold;
        transform: rotate(-30deg);
        text-transform: uppercase;
        letter-spacing: 8px;
        z-index: -1;
    }

    /* ── PIED DE PAGE ────────────────────────── */
    .footer {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 8px 25px;
        background: #f5f5f5;
        border-top: 1px solid #ddd;
        font-size: 8px;
        color: #888;
        text-align: center;
    }
    .footer-inner {
        display: table;
        width: 100%;
    }
    .footer-left  { display: table-cell; text-align: left; }
    .footer-right { display: table-cell; text-align: right; }

    /* ── QR / NOTICE ─────────────────────────── */
    .notice {
        background: #FFF8E1;
        border-left: 3px solid #F57F17;
        padding: 8px 10px;
        font-size: 9px;
        color: #555;
        margin-top: 16px;
    }
</style>
</head>
<body>

<div class="watermark">BTL BANK</div>


<div class="header">
    <div class="header-top">
        <div class="header-logo">
            <h1>BTL BANK</h1>
            <p>Tunisian Libyan Bank — Plateforme SWIFT</p>
        </div>
        <div class="header-ref">
            <div class="doc-title">Avis de virement SWIFT</div>
            <div class="doc-ref">
                Réf : <?php echo e($message->REFERENCE ?? $message->reference ?? '—'); ?><br>
                Émis le : <?php echo e(now()->format('d/m/Y à H:i')); ?>

            </div>
        </div>
    </div>
</div>


<?php
    $status = $message->STATUS ?? $message->status ?? 'pending';
    $statusLabel = match($status) {
        'authorized' => 'Autorisé — Virement approuvé',
        'processed'  => 'Traité — En attente d\'autorisation',
        'pending'    => 'En attente de traitement',
        'suspended'  => 'Suspendu — Virement bloqué',
        'rejected'   => 'Rejeté',
        default      => strtoupper($status),
    };
?>
<div class="status-bar status-<?php echo e($status); ?>">
    <?php echo e($statusLabel); ?>

</div>

<div class="body">

    
    <div class="amount-box">
        <div class="amount-label">Montant du virement</div>
        <div class="amount-value">
            <?php echo e(number_format((float)($message->AMOUNT ?? $message->amount ?? 0), 2, ',', ' ')); ?>

            <?php echo e($message->CURRENCY ?? $message->currency ?? ''); ?>

        </div>
        <div class="amount-date">
            Date valeur :
            <?php echo e(optional($message->VALUE_DATE ?? $message->value_date)->format('d/m/Y') ?? '—'); ?>

        </div>
    </div>

    
    <div class="two-col">
        <div class="col-left">
            <div class="section">
                <div class="section-title">Émetteur / Donneur d'ordre</div>
                <div class="section-body">
                    <table class="info-table">
                        <tr>
                            <td class="label">Nom</td>
                            <td class="value"><?php echo e($message->SENDER_NAME ?? $message->sender_name ?? '—'); ?></td>
                        </tr>
                        <tr>
                            <td class="label">BIC</td>
                            <td class="value"><?php echo e($message->SENDER_BIC ?? $message->sender_bic ?? '—'); ?></td>
                        </tr>
                        <tr>
                            <td class="label">Compte</td>
                            <td class="value"><?php echo e($message->SENDER_ACCOUNT ?? $message->sender_account ?? '—'); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-right">
            <div class="section">
                <div class="section-title">Bénéficiaire / Récepteur</div>
                <div class="section-body">
                    <table class="info-table">
                        <tr>
                            <td class="label">Nom</td>
                            <td class="value"><?php echo e($message->RECEIVER_NAME ?? $message->receiver_name ?? '—'); ?></td>
                        </tr>
                        <tr>
                            <td class="label">BIC</td>
                            <td class="value"><?php echo e($message->RECEIVER_BIC ?? $message->receiver_bic ?? '—'); ?></td>
                        </tr>
                        <tr>
                            <td class="label">Compte</td>
                            <td class="value"><?php echo e($message->RECEIVER_ACCOUNT ?? $message->receiver_account ?? '—'); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    
    <div class="section">
        <div class="section-title">Informations du message</div>
        <div class="section-body">
            <div class="two-col">
                <div class="col-left">
                    <table class="info-table">
                        <tr>
                            <td class="label">Type message</td>
                            <td class="value"><?php echo e($message->TYPE_MESSAGE ?? $message->type_message ?? '—'); ?></td>
                        </tr>
                        <tr>
                            <td class="label">Direction</td>
                            <td class="value">
                                <?php echo e(($message->DIRECTION ?? $message->direction) === 'IN' ? 'Reçu (IN)' : 'Émis (OUT)'); ?>

                            </td>
                        </tr>
                        <tr>
                            <td class="label">Catégorie</td>
                            <td class="value"><?php echo e($message->CATEGORIE ?? $message->categorie ?? '—'); ?></td>
                        </tr>
                    </table>
                </div>
                <div class="col-right">
                    <table class="info-table">
                        <tr>
                            <td class="label">Créé le</td>
                            <td class="value">
                                <?php echo e(optional($message->CREATED_AT ?? $message->created_at)->format('d/m/Y H:i') ?? '—'); ?>

                            </td>
                        </tr>
                        <tr>
                            <td class="label">Traité le</td>
                            <td class="value">
                                <?php echo e(optional($message->PROCESSED_AT ?? $message->processed_at)->format('d/m/Y H:i') ?? '—'); ?>

                            </td>
                        </tr>
                        <?php if($message->AUTHORIZED_AT ?? $message->authorized_at): ?>
                        <tr>
                            <td class="label">Autorisé le</td>
                            <td class="value">
                                <?php echo e(optional($message->AUTHORIZED_AT ?? $message->authorized_at)->format('d/m/Y H:i')); ?>

                            </td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
    </div>

    
    <?php if($message->AUTHORIZATION_NOTE ?? $message->authorization_note): ?>
    <div class="section">
        <div class="section-title">Note d'autorisation</div>
        <div class="section-body">
            <p style="font-size:10px; color:#333;">
                <?php echo e($message->AUTHORIZATION_NOTE ?? $message->authorization_note); ?>

            </p>
        </div>
    </div>
    <?php endif; ?>

    
    <?php if($message->details && $message->details->count()): ?>
    <div class="section">
        <div class="section-title">
            Champs spécifiques (<?php echo e($message->TYPE_MESSAGE ?? $message->type_message); ?>)
        </div>
        <div class="section-body">
            <table class="tags-table">
                <?php $__currentLoopData = $message->details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td class="tag-name"><?php echo e($detail->tag_name); ?></td>
                    <td><?php echo e($detail->tag_value); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </table>
        </div>
    </div>
    <?php endif; ?>

    
    <div class="notice">
        Ce document est généré automatiquement par la Plateforme des Messages SWIFT de BTL Bank.
        Il constitue un avis informatif et ne remplace pas les documents officiels de virement.
        Référence unique : <?php echo e($message->REFERENCE ?? $message->reference); ?>.
    </div>

</div>


<div class="footer">
    <div class="footer-inner">
        <div class="footer-left">
            BTL Bank — Tunisian Libyan Bank | Plateforme SWIFT
        </div>
        <div class="footer-right">
            Généré le <?php echo e(now()->format('d/m/Y à H:i')); ?> | Document confidentiel
        </div>
    </div>
</div>

</body>
</html><?php /**PATH C:\Users\eya saidi\Desktop\btl-swift-platform\btl-swift-platform-main\btl-swift-platform-main\resources\views/swift/pdf-transaction.blade.php ENDPATH**/ ?>