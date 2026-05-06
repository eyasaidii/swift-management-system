

<?php $__env->startSection('title', 'Anomalies Détectées — IA'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">

    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <span style="color:#1A5C38">&#9888;</span>
                Anomalies Détectées par l'IA
            </h4>
            <small class="text-muted">Moteur de détection — Règles métier SWIFT</small>
        </div>
        <div class="d-flex gap-2">
            <a href="<?php echo e(route('swift.index')); ?>" class="btn btn-outline-secondary btn-sm">
                &#8592; Retour Messages
            </a>
            <?php if (\Illuminate\Support\Facades\Blade::check('role', 'super-admin|swift-manager')): ?>
            <form method="POST" action="<?php echo e(route('swift.anomalies.analyze-all')); ?>"
                  onsubmit="return confirm('Analyser tous les messages SWIFT existants ?')">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn btn-sm text-white" style="background:#1A5C38">
                    &#9654; Analyser tout
                </button>
            </form>
            <?php endif; ?>
        </div>
    </div>

    
    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if(session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo e(session('error')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="fs-2 fw-bold text-dark"><?php echo e($stats['total']); ?></div>
                    <div class="text-muted small">Total anomalies</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #dc3545 !important;">
                <div class="card-body text-center">
                    <div class="fs-2 fw-bold text-danger"><?php echo e($stats['high']); ?></div>
                    <div class="text-muted small">Risque critique</div>
                    <?php if($stats['non_verifiees'] > 0): ?>
                        <span class="badge bg-danger"><?php echo e($stats['non_verifiees']); ?> non vérifiées</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #fd7e14 !important;">
                <div class="card-body text-center">
                    <div class="fs-2 fw-bold text-warning"><?php echo e($stats['medium']); ?></div>
                    <div class="text-muted small">Risque moyen</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #198754 !important;">
                <div class="card-body text-center">
                    <div class="fs-2 fw-bold text-success"><?php echo e($stats['low']); ?></div>
                    <div class="text-muted small">Risque faible</div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Niveau de risque</label>
                    <select name="niveau_risque" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        <option value="HIGH"   <?php echo e(request('niveau_risque') === 'HIGH'   ? 'selected' : ''); ?>>🔴 Critique</option>
                        <option value="MEDIUM" <?php echo e(request('niveau_risque') === 'MEDIUM' ? 'selected' : ''); ?>>🟡 Moyen</option>
                        <option value="LOW"    <?php echo e(request('niveau_risque') === 'LOW'    ? 'selected' : ''); ?>>🟢 Faible</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Vérification</label>
                    <select name="verifie" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        <option value="non" <?php echo e(request('verifie') === 'non' ? 'selected' : ''); ?>>Non vérifiées</option>
                        <option value="oui" <?php echo e(request('verifie') === 'oui' ? 'selected' : ''); ?>>Vérifiées</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted">Date début</label>
                    <input type="date" name="date_from" value="<?php echo e(request('date_from')); ?>"
                           class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted">Date fin</label>
                    <input type="date" name="date_to" value="<?php echo e(request('date_to')); ?>"
                           class="form-control form-control-sm">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-sm text-white w-100" style="background:#1A5C38">
                        Filtrer
                    </button>
                    <a href="<?php echo e(route('swift.anomalies.index')); ?>" class="btn btn-sm btn-outline-secondary w-100">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead style="background:#1A5C38; color:white;">
                        <tr>
                            <th class="px-3 py-3">Référence SWIFT</th>
                            <th class="py-3">Type</th>
                            <th class="py-3">Direction</th>
                            <th class="py-3">Montant</th>
                            <th class="py-3">Score IA</th>
                            <th class="py-3">Niveau</th>
                            <th class="py-3">Raisons</th>
                            <th class="py-3">Vérifié</th>
                            <th class="py-3">Détecté le</th>
                            <th class="py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $anomalies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $anomaly): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $msg     = $anomaly->message;
                            $raisons = is_array($anomaly->raisons) ? $anomaly->raisons : json_decode($anomaly->raisons ?? '[]', true);
                            $rowBg   = match($anomaly->niveau_risque) {
                                'HIGH'   => '#fff5f5',
                                'MEDIUM' => '#fffbf0',
                                default  => 'white',
                            };
                        ?>
                        <tr style="background: <?php echo e($rowBg); ?>">
                            
                            <td class="px-3">
                                <?php if($msg): ?>
                                    <a href="<?php echo e(route('swift.show', $msg->id)); ?>"
                                       class="fw-bold text-decoration-none" style="color:#1A5C38">
                                        <?php echo e($msg->REFERENCE ?? $msg->reference ?? "#{$msg->id}"); ?>

                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>

                            
                            <td>
                                <?php if($msg): ?>
                                    <span class="badge rounded-pill"
                                          style="background:#1A5C38; color:white; font-size:11px">
                                        <?php echo e($msg->TYPE_MESSAGE ?? $msg->type_message ?? '—'); ?>

                                    </span>
                                <?php endif; ?>
                            </td>

                            
                            <td>
                                <?php if($msg): ?>
                                    <?php $dir = $msg->DIRECTION ?? $msg->direction; ?>
                                    <span class="badge <?php echo e($dir === 'IN' ? 'bg-primary' : 'bg-dark'); ?>">
                                        <?php echo e($dir === 'IN' ? 'REÇU' : 'ÉMIS'); ?>

                                    </span>
                                <?php endif; ?>
                            </td>

                            
                            <td class="fw-bold">
                                <?php if($msg): ?>
                                    <?php echo e(number_format($msg->AMOUNT ?? $msg->amount ?? 0, 2, ',', ' ')); ?>

                                    <small class="text-muted"><?php echo e($msg->CURRENCY ?? $msg->currency); ?></small>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>

                            
                            <td>
                                <?php
                                    $score    = (int) $anomaly->score;
                                    $barColor = $score >= 60 ? '#dc3545' : ($score >= 30 ? '#fd7e14' : '#198754');
                                ?>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height:8px; width:80px">
                                        <div class="progress-bar" role="progressbar"
                                             style="width:<?php echo e($score); ?>%; background:<?php echo e($barColor); ?>"
                                             aria-valuenow="<?php echo e($score); ?>" aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                    <span class="fw-bold" style="color:<?php echo e($barColor); ?>; min-width:30px">
                                        <?php echo e($score); ?>

                                    </span>
                                </div>
                            </td>

                            
                            <td>
                                <?php
                                    $badgeStyle = match($anomaly->niveau_risque) {
                                        'HIGH'   => 'background:#dc3545; color:white',
                                        'MEDIUM' => 'background:#fd7e14; color:white',
                                        default  => 'background:#198754; color:white',
                                    };
                                    $niveauLabel = match($anomaly->niveau_risque) {
                                        'HIGH'   => '🔴 Critique',
                                        'MEDIUM' => '🟡 Moyen',
                                        default  => '🟢 Faible',
                                    };
                                ?>
                                <span class="badge" style="<?php echo e($badgeStyle); ?>">
                                    <?php echo e($niveauLabel); ?>

                                </span>
                            </td>

                            
                            <td>
                                <?php $__currentLoopData = $raisons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $raison): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <span class="badge bg-secondary me-1 mb-1" style="font-size:10px">
                                        <?php echo e($raison); ?>

                                    </span>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </td>

                            
                            <td>
                                <?php if($anomaly->verifie_par): ?>
                                    <span class="text-success fw-bold">✓</span>
                                    <small class="text-muted d-block">
                                        <?php echo e(optional($anomaly->verificateur)->name ?? '—'); ?>

                                    </small>
                                    <small class="text-muted">
                                        <?php echo e(optional($anomaly->verifie_at)->format('d/m/Y')); ?>

                                    </small>
                                <?php else: ?>
                                    <span class="text-danger fw-bold">✗ En attente</span>
                                <?php endif; ?>
                            </td>

                            
                            <td>
                                <small><?php echo e(optional($anomaly->created_at)->format('d/m/Y H:i')); ?></small>
                            </td>

                            
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <?php if($msg): ?>
                                        <a href="<?php echo e(route('swift.show', $msg->id)); ?>"
                                           class="btn btn-sm btn-outline-secondary" title="Voir message">
                                            &#128065;
                                        </a>
                                    <?php endif; ?>

                                    <?php if (\Illuminate\Support\Facades\Blade::check('role', 'super-admin|swift-manager')): ?>
                                    <?php if(!$anomaly->verifie_par): ?>
                                        <form method="POST"
                                              action="<?php echo e(route('swift.anomalies.verify', $anomaly->id)); ?>">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('PATCH'); ?>
                                            <button type="submit"
                                                    class="btn btn-sm btn-outline-success"
                                                    title="Marquer vérifié">✓</button>
                                        </form>
                                    <?php endif; ?>

                                    <form method="POST"
                                          action="<?php echo e(route('swift.anomalies.reanalyze', $anomaly->id)); ?>">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit"
                                                class="btn btn-sm btn-outline-primary"
                                                title="Re-analyser">&#8635;</button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <div class="fs-4">&#9989;</div>
                                <div>Aucune anomalie détectée pour le moment.</div>
                                <small>Cliquez sur "Analyser tout" pour lancer la première analyse.</small>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if($anomalies->hasPages()): ?>
        <div class="card-footer bg-white border-0 d-flex justify-content-between align-items-center px-3 py-2">
            <small class="text-muted">
                <?php echo e($anomalies->firstItem()); ?>–<?php echo e($anomalies->lastItem()); ?>

                sur <?php echo e($anomalies->total()); ?> anomalie(s)
            </small>
            <?php echo e($anomalies->links()); ?>

        </div>
        <?php endif; ?>
    </div>

</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/swift/anomalies/index.blade.php ENDPATH**/ ?>