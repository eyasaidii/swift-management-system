

<?php $__env->startSection('title', 'Détail du message SWIFT'); ?>

<?php $__env->startSection('content'); ?>
<style>
/* ── BTL Swift Show ───────────────────────────────────────── */
:root {
    --btl-green:  #0a4d2b;
    --btl-radius: 12px;
    --btl-shadow: 0 2px 12px rgba(0,0,0,.06);
}

/* Top info card */
.swift-header-card {
    background: #fff;
    border-radius: var(--btl-radius);
    box-shadow: var(--btl-shadow);
    border: 1px solid #e9ecef;
    overflow: hidden;
    margin-bottom: 1.25rem;
}
.swift-header-top {
    padding: 1.1rem 1.4rem;
    border-bottom: 1px solid #f0f0f0;
    display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;
}
.swift-ref {
    font-family: monospace;
    font-size: 1.1rem;
    font-weight: 700;
    color: #1a1a2e;
    letter-spacing: .02em;
}
.swift-sub {
    font-size: .75rem;
    color: #8a92a0;
    margin-top: 3px;
}
.swift-header-body {
    padding: .9rem 1.4rem;
    display: flex; flex-wrap: wrap; gap: 0;
}
.sh-field {
    flex: 1 1 180px;
    padding: .55rem .8rem;
    border-right: 1px solid #f3f4f6;
}
.sh-field:last-child { border-right: none; }
.sh-label {
    font-size: .64rem; text-transform: uppercase; letter-spacing: .07em;
    color: #b0b7c3; font-weight: 700; margin-bottom: 4px;
}
.sh-value { font-size: .88rem; font-weight: 600; color: #212529; }

/* Tx section inside header */
.swift-tx-row {
    padding: .75rem 1.4rem;
    background: #fafbfc;
    border-top: 1px solid #f0f0f0;
    display: flex; flex-wrap: wrap; gap: 0;
}
.tx-cell {
    flex: 1 1 140px;
    padding: .4rem .7rem;
    border-right: 1px solid #eef0f2;
}
.tx-cell:last-child { border-right: none; }
.tx-cell-lbl { font-size: .64rem; text-transform: uppercase; letter-spacing: .06em; color: #b0b7c3; font-weight: 700; margin-bottom: 3px; }
.tx-cell-val { font-size: .88rem; font-weight: 700; color: #1a1a2e; }

/* Generic cards */
.btl-card {
    border: 1px solid #e9ecef;
    border-radius: var(--btl-radius);
    box-shadow: var(--btl-shadow);
    margin-bottom: 1rem;
    background: #fff;
    overflow: hidden;
}
.btl-card-hdr {
    padding: .7rem 1.1rem;
    border-bottom: 1px solid #f1f3f5;
    display: flex; align-items: center; justify-content: space-between; gap: .6rem;
    background: #fafafa;
}
.btl-section-title {
    font-size: .73rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .07em;
    color: #495057;
    display: flex; align-items: center; gap: .4rem;
}
.btl-section-title i { color: #6c757d; font-size: .85rem; }

/* Score ring */
.score-ring { position: relative; width: 96px; height: 96px; margin: 0 auto 6px; }
.score-ring svg { transform: rotate(-90deg); }
.score-ring .ring-text {
    position: absolute; inset: 0;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    font-size: 1.5rem; font-weight: 800; line-height: 1;
}
.ring-sub { font-size: .6rem; color: #adb5bd; font-weight: 400; }

/* Action bar */
.action-bar {
    background: #fff; border: 1px solid #e9ecef;
    border-radius: var(--btl-radius); padding: .85rem 1.1rem;
    box-shadow: var(--btl-shadow); display: flex; gap: .5rem; flex-wrap: wrap; align-items: center;
    margin-bottom: 1rem;
}

/* Details toggle */
.details-toggle {
    background: transparent; border: 1px solid #dee2e6; border-radius: 6px;
    color: #6c757d; font-size: .78rem; padding: .35rem .8rem;
    transition: all .12s; cursor: pointer; display: inline-flex; align-items: center; gap: .35rem;
}
.details-toggle:hover { border-color: #adb5bd; color: #343a40; background: #f8f9fa; }

/* Chat pills */
.chat-pill {
    border-radius: 20px !important; font-size: .76rem !important;
    padding: .3rem .75rem !important;
}
</style>

<div class="container-fluid py-4">
<div class="row"><div class="col-12">

    
    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i><?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if(session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo e(session('error')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    
    <?php if($message->status === 'authorized' && $message->authorization_note && auth()->user()->hasRole('swift-operator')): ?>
        <div class="alert alert-dismissible fade show border-0 shadow-sm mb-3 rounded-3"
             style="background:linear-gradient(135deg,#0d6efd,#0a58ca);">
            <div class="d-flex align-items-start gap-3">
                <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0"
                     style="width:40px;height:40px;background:rgba(255,255,255,.2);">
                    <i class="fas fa-bell text-white"></i>
                </div>
                <div class="flex-grow-1">
                    <h6 class="mb-1 text-white fw-bold">✅ Message autorisé — Note du Swift Manager</h6>
                    <p class="mb-2 text-white-50 small">
                        Autorisé par <strong class="text-white"><?php echo e(optional($message->authorizer)->name ?? 'Swift Manager'); ?></strong>
                        le <?php echo e(optional($message->authorized_at ?? $message->AUTHORIZED_AT)->format('d/m/Y à H:i') ?? '—'); ?>

                    </p>
                    <div class="p-2 rounded" style="background:rgba(255,255,255,.15);">
                        <i class="fas fa-quote-left text-white-50 me-1" style="font-size:10px"></i>
                        <span class="text-white fst-italic"><?php echo e($message->authorization_note); ?></span>
                        <i class="fas fa-quote-right text-white-50 ms-1" style="font-size:10px"></i>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>

    
    <div class="swift-header-card">

        
        <div class="swift-header-top">
            <div>
                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                    <span class="badge" style="background:#4f46e5;color:#fff;font-size:.72rem;padding:4px 10px;border-radius:6px;">
                        <i class="fas fa-envelope me-1"></i><?php echo e($message->type_message); ?>

                    </span>
                    <?php switch($message->status):
                        case ('pending'): ?>    <span class="badge bg-warning text-dark">⏳ En attente</span>   <?php break; ?>
                        <?php case ('processed'): ?>  <span class="badge" style="background:#0ea5e9;color:#fff;">🔵 À autoriser</span> <?php break; ?>
                        <?php case ('authorized'): ?> <span class="badge bg-success">✅ Autorisé</span>               <?php break; ?>
                        <?php case ('suspended'): ?>  <span class="badge bg-danger">⛔ Suspendu</span>                <?php break; ?>
                        <?php case ('rejected'): ?>   <span class="badge bg-danger">❌ Rejeté</span>                  <?php break; ?>
                        <?php default: ?>            <span class="badge bg-secondary"><?php echo e($message->status); ?></span>
                    <?php endswitch; ?>
                    <?php if($message->direction == 'IN'): ?>
                        <span class="badge" style="background:#0284c7;color:#fff;">↙ Reçu</span>
                    <?php else: ?>
                        <span class="badge" style="background:#374151;color:#fff;">↗ Émis</span>
                    <?php endif; ?>
                </div>
                <div class="swift-ref"><?php echo e($message->reference); ?></div>
                <div class="swift-sub">Détail du message SWIFT</div>
            </div>
            <div class="d-flex gap-2">
                <a href="<?php echo e(route('swift.pdf', $message->id)); ?>" target="_blank"
                   class="btn btn-sm" style="background:#f3f4f6;color:#374151;border:1px solid #e5e7eb;">
                    <i class="fas fa-file-pdf me-1" style="color:#ef4444;"></i>PDF
                </a>
                <a href="<?php echo e(url()->previous()); ?>"
                   class="btn btn-sm" style="background:#f3f4f6;color:#374151;border:1px solid #e5e7eb;">
                    <i class="fas fa-arrow-left me-1"></i>Retour
                </a>
            </div>
        </div>

        
        <div class="swift-header-body">
            <div class="sh-field">
                <div class="sh-label"><i class="fas fa-tag me-1"></i>Type</div>
                <div class="sh-value font-monospace"><?php echo e($message->type_message); ?></div>
            </div>
            <div class="sh-field">
                <div class="sh-label"><i class="fas fa-exchange-alt me-1"></i>Direction</div>
                <div class="sh-value">
                    <?php if($message->direction == 'IN'): ?>
                        <span class="badge" style="background:#dbeafe;color:#1d4ed8;">↙ Reçu</span>
                    <?php else: ?>
                        <span class="badge" style="background:#f3f4f6;color:#374151;">↗ Émis</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="sh-field">
                <div class="sh-label"><i class="fas fa-circle-notch me-1"></i>Statut</div>
                <div class="sh-value">
                    <?php switch($message->status):
                        case ('pending'): ?>    <span class="badge bg-warning text-dark">⏳ En attente</span> <?php break; ?>
                        <?php case ('processed'): ?>  <span class="badge" style="background:#e0f2fe;color:#0369a1;">🔵 À autoriser</span> <?php break; ?>
                        <?php case ('authorized'): ?> <span class="badge" style="background:#dcfce7;color:#15803d;">✅ Autorisé</span> <?php break; ?>
                        <?php case ('suspended'): ?>  <span class="badge bg-danger">⛔ Suspendu</span> <?php break; ?>
                        <?php case ('rejected'): ?>   <span class="badge bg-danger">❌ Rejeté</span> <?php break; ?>
                        <?php default: ?>            <span class="badge bg-secondary"><?php echo e($message->status); ?></span>
                    <?php endswitch; ?>
                </div>
            </div>
            <?php if($message->created_at): ?>
            <div class="sh-field">
                <div class="sh-label"><i class="fas fa-calendar me-1"></i>Créé le</div>
                <div class="sh-value"><?php echo e($message->created_at->format('d/m/Y')); ?></div>
            </div>
            <?php endif; ?>
        </div>

        
        <?php if($message->transaction): ?>
        <div class="swift-tx-row">
            <div class="tx-cell">
                <div class="tx-cell-lbl"><i class="fas fa-exchange-alt me-1"></i>Transaction</div>
                <div class="tx-cell-val" style="font-size:.75rem;color:#6b7280;font-weight:500;">Associée</div>
            </div>
            <div class="tx-cell">
                <div class="tx-cell-lbl">Montant</div>
                <div class="tx-cell-val"><?php echo e(number_format($message->transaction->montant, 2)); ?> <span style="color:#6b7280;font-size:.8rem;"><?php echo e($message->transaction->devise); ?></span></div>
            </div>
            <div class="tx-cell">
                <div class="tx-cell-lbl">Date</div>
                <div class="tx-cell-val"><?php echo e(\Carbon\Carbon::parse($message->transaction->date_transaction)->format('d/m/Y')); ?></div>
            </div>
            <div class="tx-cell">
                <div class="tx-cell-lbl">Émetteur</div>
                <div class="tx-cell-val" style="font-size:.82rem;"><?php echo e($message->transaction->emetteur); ?></div>
            </div>
            <div class="tx-cell">
                <div class="tx-cell-lbl">Récepteur</div>
                <div class="tx-cell-val" style="font-size:.82rem;"><?php echo e($message->transaction->recepteur); ?></div>
            </div>
        </div>
        <?php endif; ?>

    </div>

    
    <?php $anomaly = $message->anomaly; ?>

    <?php if($anomaly): ?>
        <?php
            $score       = (int) $anomaly->score;
            $niveau      = $anomaly->niveau_risque;
            $barColor    = $score >= 60 ? '#dc3545' : ($score >= 20 ? '#fd7e14' : '#198754');
            $borderColor = $score >= 60 ? '#dc3545' : ($score >= 20 ? '#fd7e14' : '#198754');
            $bgAlert     = $score >= 60 ? '#fff5f5' : ($score >= 20 ? '#fffbf0' : '#f0fff4');
            $niveauLabel = match($niveau) {
                'HIGH'   => '🔴 Risque Critique',
                'MEDIUM' => '🟡 Risque Moyen',
                default  => '🟢 Risque Faible',
            };
            $raisons = is_array($anomaly->raisons)
                ? $anomaly->raisons
                : json_decode($anomaly->raisons ?? '[]', true);
            $raisonLabels = [
                'MONTANT_ZERO'        => ['label' => 'Montant nul',         'icon' => 'fas fa-ban',                'color' => 'danger'],
                'MONTANT_ELEVE'       => ['label' => 'Montant élevé',       'icon' => 'fas fa-arrow-up',           'color' => 'warning'],
                'STATUT_REJETE'       => ['label' => 'Statut rejeté',       'icon' => 'fas fa-times-circle',       'color' => 'danger'],
                'TRANSLATION_ERROR'   => ['label' => 'Erreur XML',          'icon' => 'fas fa-code',               'color' => 'danger'],
                'TYPE_ERROR'          => ['label' => 'Type invalide',       'icon' => 'fas fa-exclamation',        'color' => 'danger'],
                'DOUBLON_REFERENCE'   => ['label' => 'Doublon référence',   'icon' => 'fas fa-copy',               'color' => 'warning'],
                'BIC_MANQUANT'        => ['label' => 'BIC manquant',        'icon' => 'fas fa-university',         'color' => 'warning'],
                'DEVISE_INHABITUELLE' => ['label' => 'Devise inhabituelle', 'icon' => 'fas fa-coins',              'color' => 'warning'],
                'IMPORT_FAILED'       => ['label' => 'Import échoué',       'icon' => 'fas fa-file-excel',         'color' => 'danger'],
                'PASSPORT_DETECTE'    => ['label' => 'Passeport détecté',   'icon' => 'fas fa-id-card',            'color' => 'danger'],
            ];
            $r  = 38;
            $c  = 2 * M_PI * $r;
            $off = $c - ($score / 100) * $c;
        ?>

        
        <?php if($niveau === 'HIGH' && !$anomaly->verifie_par && !$anomaly->rejetee_par): ?>
            <?php if (\Illuminate\Support\Facades\Blade::check('role', 'super-admin|swift-manager')): ?>
            <a href="<?php echo e(route('swift.anomalies.index', ['niveau_risque' => 'HIGH', 'verifie' => 'non'])); ?>"
               class="d-flex align-items-center gap-3 text-decoration-none mb-3 px-4 py-3 rounded-3"
               style="background:#dc3545; color:white; transition:opacity .2s;"
               onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
                <span style="font-size:1.3rem">🚨</span>
                <div class="flex-grow-1">
                    <strong>Anomalie critique non vérifiée</strong>
                    <span class="ms-2 small opacity-75">— Cliquez pour accéder au tableau de bord.</span>
                </div>
                <i class="fas fa-chevron-right"></i>
            </a>
            <?php endif; ?>
        <?php endif; ?>

        <div class="btl-card" style="border-left:5px solid <?php echo e($borderColor); ?>; background:<?php echo e($bgAlert); ?>;">
            <div class="btl-card-hdr" style="background:transparent;">
                <span class="btl-section-title">
                    <i class="fas fa-brain" style="color:<?php echo e($borderColor); ?>;"></i>
                    Analyse IA — Détection d'Anomalies
                    <?php if (\Illuminate\Support\Facades\Blade::check('role', 'super-admin|swift-manager')): ?>
                    <a href="<?php echo e(route('swift.anomalies.index', ['niveau_risque' => $niveau])); ?>"
                       class="badge text-decoration-none ms-1"
                       style="background:<?php echo e($borderColor); ?>;color:white;font-size:11px;padding:4px 10px;border-radius:20px;transition:opacity .15s;"
                       onmouseover="this.style.opacity='.75'" onmouseout="this.style.opacity='1'">
                        <?php echo e($niveauLabel); ?><i class="fas fa-external-link-alt ms-1" style="font-size:9px;"></i>
                    </a>
                    <?php else: ?>
                    <span class="badge ms-1" style="background:<?php echo e($borderColor); ?>;color:white;font-size:11px;padding:4px 10px;border-radius:20px;"><?php echo e($niveauLabel); ?></span>
                    <?php endif; ?>
                </span>
                <div class="d-flex gap-2">
                    <?php if (\Illuminate\Support\Facades\Blade::check('role', 'super-admin|swift-manager')): ?>
                    <a href="<?php echo e(route('swift.anomalies.index')); ?>" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-table me-1"></i><span class="d-none d-md-inline">Tableau</span>
                    </a>
                    <a href="<?php echo e(route('swift.anomalies.show', $anomaly->id)); ?>" class="btn btn-sm"
                       style="background:#1a7a45;color:#fff;border:none;">
                        <i class="fas fa-search-plus me-1"></i><span class="d-none d-md-inline">Détail</span>
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card-body pt-2">
                <div class="row g-3 align-items-center">

                    
                    <div class="col-md-3 text-center">
                        <div class="score-ring">
                            <svg width="96" height="96" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="<?php echo e($r); ?>" fill="none" stroke="#e9ecef" stroke-width="8"/>
                                <circle cx="50" cy="50" r="<?php echo e($r); ?>" fill="none"
                                        stroke="<?php echo e($barColor); ?>" stroke-width="8"
                                        stroke-linecap="round"
                                        stroke-dasharray="<?php echo e(number_format($c, 3)); ?>"
                                        stroke-dashoffset="<?php echo e(number_format($off, 3)); ?>"
                                        style="transition:stroke-dashoffset 1.2s ease;"/>
                            </svg>
                            <div class="ring-text" style="color:<?php echo e($barColor); ?>;">
                                <?php echo e($score); ?><span class="ring-sub">/100</span>
                            </div>
                        </div>
                        <small class="text-muted fw-semibold d-block" style="font-size:.68rem;text-transform:uppercase;letter-spacing:.05em;">Score de risque IA</small>
                    </div>

                    
                    <div class="col-md-5">
                        <div class="text-muted fw-bold mb-2 text-uppercase" style="font-size:.68rem;letter-spacing:.06em;">Anomalies détectées</div>
                        <?php if(count($raisons) > 0): ?>
                            <div class="d-flex flex-wrap gap-2">
                                <?php $__currentLoopData = $raisons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $raison): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php $info = $raisonLabels[$raison] ?? ['label'=>$raison,'icon'=>'fas fa-exclamation-triangle','color'=>'secondary']; ?>
                                    <span class="badge d-flex align-items-center gap-1 bg-<?php echo e($info['color']); ?>"
                                          style="font-size:11px;padding:6px 10px;border-radius:20px;">
                                        <i class="<?php echo e($info['icon']); ?>" style="font-size:9px;"></i><?php echo e($info['label']); ?>

                                    </span>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php else: ?>
                            <div class="d-flex align-items-center gap-2 text-success">
                                <i class="fas fa-check-circle"></i><span class="small">Aucune anomalie détectée</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    
                    <div class="col-md-4 border-start ps-4">
                        <div class="text-muted fw-bold mb-2 text-uppercase" style="font-size:.68rem;letter-spacing:.06em;">Vérification</div>
                        <?php if($anomaly->rejetee_par): ?>
                            <div class="d-flex align-items-center gap-2 text-danger mb-1">
                                <i class="fas fa-times-circle fs-5"></i><span class="fw-bold">Rejetée</span>
                            </div>
                            <div class="small text-muted"><i class="fas fa-user me-1"></i><?php echo e(optional($anomaly->rejecteur)->name ?? '—'); ?></div>
                            <div class="small text-muted"><i class="fas fa-calendar me-1"></i><?php echo e(optional($anomaly->rejetee_at)->format('d/m/Y à H:i')); ?></div>
                        <?php elseif($anomaly->verifie_par): ?>
                            <div class="d-flex align-items-center gap-2 text-success mb-1">
                                <i class="fas fa-check-circle fs-5"></i><span class="fw-bold">Vérifiée</span>
                            </div>
                            <div class="small text-muted"><i class="fas fa-user me-1"></i><?php echo e(optional($anomaly->verificateur)->name ?? '—'); ?></div>
                            <div class="small text-muted"><i class="fas fa-calendar me-1"></i><?php echo e(optional($anomaly->verifie_at)->format('d/m/Y à H:i')); ?></div>
                        <?php else: ?>
                            <div class="d-flex align-items-center gap-2 text-warning mb-2">
                                <i class="fas fa-clock fs-5"></i><span class="fw-bold">En attente</span>
                            </div>
                            <?php if (\Illuminate\Support\Facades\Blade::check('role', 'super-admin|swift-manager')): ?>
                            <form method="POST" action="<?php echo e(route('swift.anomalies.verify', $anomaly->id)); ?>" class="d-inline">
                                <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                                <button type="submit" class="btn btn-sm btn-outline-success me-1"><i class="fas fa-check me-1"></i>Accepter</button>
                            </form>
                            <form method="POST" action="<?php echo e(route('swift.anomalies.reject', $anomaly->id)); ?>" class="d-inline"
                                  onsubmit="return confirm('Rejeter ce message SWIFT ?')">
                                <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-times me-1"></i>Rejeter</button>
                            </form>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php if (\Illuminate\Support\Facades\Blade::check('role', 'super-admin|swift-manager')): ?>
                        <div class="mt-2">
                            <form method="POST" action="<?php echo e(route('swift.anomalies.reanalyze', $anomaly->id)); ?>" class="d-inline">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="btn btn-sm btn-outline-primary"><i class="fas fa-sync-alt me-1"></i>Re-analyser</button>
                            </form>
                        </div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </div>

    <?php else: ?>
        <?php if (\Illuminate\Support\Facades\Blade::check('role', 'super-admin|swift-manager')): ?>
        <div class="btl-card" style="border-left:4px solid #adb5bd;">
            <div class="card-body d-flex justify-content-between align-items-center py-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center justify-content-center rounded-circle"
                         style="width:42px;height:42px;background:#f3f4f6;">
                        <i class="fas fa-brain text-muted"></i>
                    </div>
                    <div>
                        <div class="fw-bold">Analyse IA non disponible</div>
                        <small class="text-muted">Ce message n'a pas encore été analysé par le moteur de détection.</small>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <form method="POST" action="<?php echo e(route('swift.anomalies.analyze-single', $message->id)); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="fas fa-play me-1"></i>Lancer l'analyse</button>
                    </form>
                    <a href="<?php echo e(route('swift.anomalies.index')); ?>" class="btn btn-sm btn-outline-dark">
                        <i class="fas fa-table me-1"></i>Tableau anomalies
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?>

    
    <?php if (\Illuminate\Support\Facades\Blade::check('role', 'super-admin|swift-manager')): ?>
    <div class="btl-card"
         style="border-left:4px solid var(--btl-green);"
         x-data="swiftChatbot('<?php echo e(route('swift.chat-ia', $message->id)); ?>', '<?php echo e(csrf_token()); ?>')"
         id="chatbot-ia">

        <div class="btl-card-hdr" style="background:linear-gradient(135deg,#0a4d2b,#1a7a45); cursor:pointer;" @click="open=!open">
            <span class="btl-section-title" style="color:#fff;">
                <i class="fas fa-robot" style="color:rgba(255,255,255,.8);"></i>
                <span style="color:#fff;">Assistant IA — Analyse SWIFT</span>
                <span class="badge rounded-pill ms-1" style="background:rgba(255,255,255,.2);color:#fff;font-size:.67rem;">Llama 3.3 · Groq</span>
            </span>
            <i class="fas text-white" :class="open ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
        </div>

        <div class="card-body p-3" x-show="open" x-transition>

            
            <div class="mb-3">
                <small class="text-muted fw-semibold d-block mb-2">
                    <i class="fas fa-bolt me-1" style="color:#f59e0b;"></i>Questions rapides
                </small>
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="chat-pill btn btn-sm btn-outline-secondary"
                            @click="ask('Pourquoi ce message a-t-il obtenu ce score de risque ? Explique les raisons détectées.')">
                        <i class="fas fa-question-circle me-1"></i>Pourquoi ce score ?
                    </button>
                    <button type="button" class="chat-pill btn btn-sm btn-outline-warning"
                            @click="ask('Quels sont les risques AML (Anti-Money Laundering) identifiés dans ce message SWIFT ? Y a-t-il des signaux de blanchiment ?')">
                        <i class="fas fa-shield-alt me-1"></i>Risques AML ?
                    </button>
                    <button type="button" class="chat-pill btn btn-sm btn-outline-danger"
                            @click="ask('Dois-je rejeter ce message SWIFT ? Justifie ta recommandation en tenant compte du score, du niveau de risque et des raisons détectées.')">
                        <i class="fas fa-ban me-1"></i>Dois-je rejeter ?
                    </button>
                    <button type="button" class="chat-pill btn btn-sm btn-outline-info"
                            @click="ask('Ce montant est-il cohérent avec le profil habituel de ce type de message SWIFT ? Y a-t-il un risque de fraude ?')">
                        <i class="fas fa-money-bill-wave me-1"></i>Montant suspect ?
                    </button>
                    <button type="button" class="chat-pill btn btn-sm btn-outline-primary"
                            @click="ask('Quelles actions de conformité recommandes-tu pour ce message avant de l\'autoriser ?')">
                        <i class="fas fa-tasks me-1"></i>Actions conformité
                    </button>
                </div>
            </div>

            
            <div class="mb-3" x-show="response || loading || errorMsg">
                <div class="rounded-3 p-3" style="background:#f8fffe; border:1px solid #d1e7dd; min-height:80px;">
                    <div x-show="loading" class="d-flex align-items-center gap-2 text-muted">
                        <div class="spinner-border spinner-border-sm" style="color:#0a4d2b;"></div>
                        <span class="small">Analyse en cours…</span>
                    </div>
                    <div x-show="errorMsg && !loading" class="d-flex align-items-start gap-2 text-danger small">
                        <i class="fas fa-exclamation-triangle mt-1"></i>
                        <span x-text="errorMsg"></span>
                    </div>
                    <div x-show="response && !loading && !errorMsg"
                         class="small lh-base" style="white-space:pre-wrap; color:#1a2e1a;"
                         x-text="response"></div>
                </div>
            </div>

            
            <div class="input-group">
                <input type="text" class="form-control form-control-sm"
                       placeholder="Posez votre question en français…"
                       x-model="question" :disabled="loading"
                       @keydown.enter.prevent="ask(question)" maxlength="500"
                       style="border-color:#0a4d2b; border-right:none; border-radius:8px 0 0 8px;">
                <button class="btn btn-sm" type="button"
                        style="background:#0a4d2b;color:#fff;border-color:#0a4d2b;border-radius:0 8px 8px 0;"
                        :disabled="loading || !question.trim()" @click="ask(question)">
                    <i class="fas fa-paper-plane me-1"></i>
                    <span x-show="!loading">Envoyer</span><span x-show="loading">…</span>
                </button>
            </div>
            <div class="d-flex justify-content-between mt-2">
                <small class="text-muted"><i class="fas fa-lock me-1"></i>Échanges confidentiels — non conservés</small>
                <small x-show="response">
                    <button class="btn btn-link btn-sm p-0 text-muted text-decoration-none" type="button"
                            @click="response=''; errorMsg=''; question=''">
                        <i class="fas fa-trash-alt me-1"></i>Effacer
                    </button>
                </small>
            </div>
        </div>
    </div>
    <?php endif; ?>

    
    <?php if($message->details && $message->details->count()): ?>
    <div class="btl-card">
        <div class="btl-card-hdr" style="cursor:pointer;"
             data-bs-toggle="collapse" data-bs-target="#collapseDetails" aria-expanded="false">
            <span class="btl-section-title">
                <i class="fas fa-code"></i>
                Détails spécifiques
                <span class="badge bg-secondary ms-1" style="font-size:.68rem;"><?php echo e($message->type_message); ?></span>
            </span>
            <button class="details-toggle" type="button">
                <i class="fas fa-eye me-1"></i>Voir les <?php echo e($message->details->count()); ?> champs MT
                <i class="fas fa-chevron-down ms-1" style="font-size:.65rem;"></i>
            </button>
        </div>
        <div class="collapse" id="collapseDetails">
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0" style="font-size:.82rem;">
                    <thead class="table-light">
                        <tr>
                            <th style="width:110px; padding-left:1.2rem;">Tag</th>
                            <th>Valeur</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $message->details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td class="font-monospace fw-bold text-success ps-4"><?php echo e($detail->tag_name); ?></td>
                            <td class="font-monospace"><?php echo e($detail->tag_value); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    
    <div class="action-bar">
        <?php
            $status       = $message->status;
            $direction    = $message->direction;
            $user         = auth()->user();
            $canAct       = $user->hasRole('super-admin')
                         || $user->hasRole('swift-manager')
                         || ($user->hasRole(['chef-agence', 'chargee']) && $direction === 'OUT');
            $canAuthorize = $user->hasRole(['super-admin', 'swift-manager']);
        ?>

        <?php if($status === 'pending' && $canAct): ?>
            <form method="POST" action="<?php echo e(route('swift.process', $message->id)); ?>"
                  onsubmit="return confirm('Confirmer le traitement ?')">
                <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-check me-1"></i>Traiter</button>
            </form>
            <form method="POST" action="<?php echo e(route('swift.reject', $message->id)); ?>"
                  onsubmit="return confirm('Confirmer le rejet ?')">
                <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-times me-1"></i>Rejeter</button>
            </form>
        <?php endif; ?>

        <?php if($status === 'processed' && $canAuthorize): ?>
            <button type="button" class="btn btn-sm btn-success"
                    data-bs-toggle="modal" data-bs-target="#modalAuthorize"
                    data-id="<?php echo e($message->id); ?>" data-ref="<?php echo e($message->reference); ?>">
                <i class="fas fa-shield-alt me-1"></i>Autoriser
            </button>
        <?php endif; ?>

        <?php if(in_array($status, ['processed', 'authorized']) && $canAuthorize): ?>
            <button type="button" class="btn btn-sm btn-warning"
                    data-bs-toggle="modal" data-bs-target="#modalSuspend"
                    data-id="<?php echo e($message->id); ?>" data-ref="<?php echo e($message->reference); ?>">
                <i class="fas fa-pause-circle me-1"></i>Suspendre
            </button>
        <?php endif; ?>

        <div class="d-flex gap-2 ms-auto flex-wrap">
            <button type="button" class="btn btn-sm btn-outline-secondary open-raw-file"
                    data-url="<?php echo e(route('swift.view-mt', $message->id)); ?>" data-title="MT Content">
                <i class="fas fa-file-alt me-1"></i>Voir MT
            </button>
            <a href="<?php echo e(route('swift.view-mx', $message->id)); ?>" target="_blank" class="btn btn-sm btn-outline-info">
                <i class="fas fa-code me-1"></i>Voir MX
            </a>
            <a href="<?php echo e(route('swift.pdf', $message->id)); ?>" target="_blank" class="btn btn-sm btn-outline-danger">
                <i class="fas fa-file-pdf me-1"></i>PDF
            </a>
            <?php if($user->hasRole('super-admin')): ?>
                <form method="POST" action="<?php echo e(route('swift.destroy', $message->id)); ?>"
                      onsubmit="return confirm('Supprimer définitivement ce message ?')">
                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                        <i class="fas fa-trash me-1"></i>Supprimer
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

</div>
</div>
</div>


<div class="modal fade" id="modalAuthorize" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-3 overflow-hidden shadow-lg">
            <form id="formAuthorize" method="POST" action="">
                <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                <div class="modal-header text-white border-0" style="background:linear-gradient(135deg,#198754,#0f6b43);">
                    <h5 class="modal-title"><i class="fas fa-shield-alt me-2"></i>Autoriser le virement</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4 py-3">
                    <p class="mb-1">Vous allez autoriser le message <strong id="authorizeRef"></strong>.</p>
                    <p class="text-muted small mb-3">Le virement est conforme aux règles de contrôle des changes et peut être transmis via SWIFT.</p>
                    <div class="mb-2">
                        <label class="form-label fw-bold mb-1">Note d'autorisation <span class="text-muted fw-normal">(optionnel)</span></label>
                        <textarea name="note" id="authorizeNote" class="form-control" rows="3"
                                  placeholder="ex: Vérifié — transaction conforme BCT"></textarea>
                        <div class="form-text"><i class="fas fa-info-circle me-1 text-muted"></i>Cette note sera visible par l'opérateur SWIFT.</div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-shield-alt me-1"></i>Confirmer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalSuspend" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-3 overflow-hidden shadow-lg">
            <form id="formSuspend" method="POST" action="">
                <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                <div class="modal-header text-dark border-0" style="background:linear-gradient(135deg,#ffc107,#e0a800);">
                    <h5 class="modal-title"><i class="fas fa-pause-circle me-2"></i>Suspendre le message</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body px-4 py-3">
                    <p class="mb-1">Vous allez suspendre le message <strong id="suspendRef"></strong>.</p>
                    <p class="text-muted small mb-3">Le message sera bloqué et ne pourra plus être transmis.</p>
                    <div class="mb-2">
                        <label class="form-label fw-bold mb-1">Motif de suspension <span class="text-danger">*</span></label>
                        <textarea name="note" class="form-control" rows="3" required
                                  placeholder="ex: En attente de documentation complémentaire"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-warning"><i class="fas fa-pause-circle me-1"></i>Confirmer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // Modal Autoriser
    const modalAuthorize = document.getElementById('modalAuthorize');
    if (modalAuthorize) {
        modalAuthorize.addEventListener('show.bs.modal', function (e) {
            const btn = e.relatedTarget;
            document.getElementById('authorizeRef').textContent = btn.getAttribute('data-ref');
            document.getElementById('formAuthorize').action =
                '/swift/' + btn.getAttribute('data-id') + '/authorize';
            // Vider la note à chaque ouverture
            const noteField = document.getElementById('authorizeNote');
            if (noteField) noteField.value = '';
        });
    }

    // Modal Suspendre
    const modalSuspend = document.getElementById('modalSuspend');
    if (modalSuspend) {
        modalSuspend.addEventListener('show.bs.modal', function (e) {
            const btn = e.relatedTarget;
            document.getElementById('suspendRef').textContent = btn.getAttribute('data-ref');
            document.getElementById('formSuspend').action =
                '/swift/' + btn.getAttribute('data-id') + '/suspend';
        });
    }

        // Raw file viewer — use global modal from layout
        // (no duplicate modal creation needed here)

});
</script>

<?php if(session('success') || session('error')): ?>
<script>
    setTimeout(function () {
        document.querySelectorAll('.alert').forEach(function (el) {
            bootstrap.Alert.getOrCreateInstance(el).close();
        });
    }, 5000);
</script>
<?php endif; ?>

<script>
/**
 * Composant Alpine.js — Chatbot IA SWIFT
 * @param {string} chatUrl   Route Laravel POST /{id}/chat-ia
 * @param {string} csrfToken Token CSRF Laravel
 */
function swiftChatbot(chatUrl, csrfToken) {
    return {
        open: true,
        question: '',
        response: '',
        errorMsg: '',
        loading: false,

        async ask(q) {
            q = (q || '').trim();
            if (!q) return;

            this.question  = q;
            this.response  = '';
            this.errorMsg  = '';
            this.loading   = true;

            try {
                const res = await fetch(chatUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept':       'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ question: q }),
                });

                const data = await res.json();

                if (!res.ok || data.error) {
                    this.errorMsg = data.error
                        || 'Le service IA a retourné une erreur (' + res.status + ').';
                } else {
                    this.response = data.response || 'Réponse vide.';
                    this.question = '';
                }
            } catch (err) {
                this.errorMsg = 'Impossible de joindre le service IA. Vérifiez votre connexion.';
            } finally {
                this.loading = false;
            }
        },
    };
}
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/swift/show.blade.php ENDPATH**/ ?>