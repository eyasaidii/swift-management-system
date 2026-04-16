

<?php $__env->startSection('title', 'Dashboard Swift Manager - BTL Bank'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">

    
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h1 class="h2 mb-0 fw-bold">
                <i class="fas fa-globe-americas text-primary me-2"></i>Swift Manager
            </h1>
            <p class="text-muted mb-0">Opérations transfrontalières &amp; correspondants</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            
            <a href="<?php echo e(route('swift.index')); ?>" class="btn btn-info">
                <i class="fas fa-list me-2"></i>Messages SWIFT
            </a>

            
            <?php
                $criticalCount = \App\Models\AnomalySwift::where('niveau_risque', 'HIGH')
                                  ->whereNull('verifie_par')
                                  ->count();
            ?>
            <a href="<?php echo e(route('swift.anomalies.index', ['niveau_risque' => 'HIGH', 'verifie' => 'non'])); ?>"
               class="btn <?php echo e($criticalCount > 0 ? 'btn-danger' : 'btn-outline-danger'); ?> position-relative">
                <i class="fas fa-brain me-2"></i>Anomalies IA
                <?php if($criticalCount > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark">
                        <?php echo e($criticalCount); ?>

                    </span>
                <?php endif; ?>
            </a>

            
            <a href="<?php echo e(route('swift.index', ['status' => 'processed'])); ?>"
               class="btn btn-success position-relative">
                <i class="fas fa-check-double me-2"></i>Autorisations
                <?php if(isset($pendingAuth) && $pendingAuth > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        <?php echo e($pendingAuth); ?>

                    </span>
                <?php endif; ?>
            </a>
        </div>
    </div>

    
    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2">
            <i class="fas fa-check-circle fs-5"></i>
            <div><?php echo e(session('success')); ?></div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if(session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2">
            <i class="fas fa-exclamation-circle fs-5"></i>
            <div><?php echo e(session('error')); ?></div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    
    <?php if($criticalCount > 0): ?>
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%);">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <i class="fas fa-skull-crossbow fs-1 text-white"></i>
                <div>
                    <h5 class="mb-0 text-white fw-bold">🚨 ALERTE IA CRITIQUE</h5>
                    <p class="mb-0 text-white-50">
                        <?php echo e($criticalCount); ?> anomalie(s) critique(s) non vérifiée(s) détectée(s) par l'IA.
                        Une action immédiate est requise.
                    </p>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="<?php echo e(route('swift.anomalies.index', ['niveau_risque' => 'HIGH', 'verifie' => 'non'])); ?>"
                   class="btn btn-light btn-sm">
                    <i class="fas fa-eye me-1"></i>Voir les anomalies
                </a>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-sliders-h me-2 text-primary"></i>Filtres</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('swift-manager.dashboard')); ?>" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Devise</label>
                    <select name="currency" class="form-select">
                        <option value="">Toutes</option>
                        <?php $__currentLoopData = ['USD','EUR','GBP','TND','LYD']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ccy): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($ccy); ?>" <?php echo e(request('currency') === $ccy ? 'selected' : ''); ?>><?php echo e($ccy); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Statut</label>
                    <select name="status" class="form-select">
                        <option value="">Tous</option>
                        <option value="pending"    <?php echo e(request('status') === 'pending'    ? 'selected' : ''); ?>>En attente</option>
                        <option value="processed"  <?php echo e(request('status') === 'processed'  ? 'selected' : ''); ?>>Traité (à autoriser)</option>
                        <option value="authorized" <?php echo e(request('status') === 'authorized' ? 'selected' : ''); ?>>Autorisé</option>
                        <option value="suspended"  <?php echo e(request('status') === 'suspended'  ? 'selected' : ''); ?>>Suspendu</option>
                        <option value="rejected"   <?php echo e(request('status') === 'rejected'   ? 'selected' : ''); ?>>Rejeté</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Direction</label>
                    <select name="direction" class="form-select">
                        <option value="">Toutes</option>
                        <option value="RECU" <?php echo e(request('direction') === 'RECU' ? 'selected' : ''); ?>>Reçus</option>
                        <option value="EMIS" <?php echo e(request('direction') === 'EMIS' ? 'selected' : ''); ?>>Émis</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date début</label>
                    <input type="date" name="date_from" class="form-control" value="<?php echo e(request('date_from')); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date fin</label>
                    <input type="date" name="date_to" class="form-control" value="<?php echo e(request('date_to')); ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-50">
                        <i class="fas fa-filter me-1"></i>Filtrer
                    </button>
                    <a href="<?php echo e(route('swift-manager.dashboard')); ?>" class="btn btn-outline-danger w-50">
                        <i class="fas fa-times me-1"></i>Réinitialiser
                    </a>
                </div>
            </form>
        </div>
    </div>

    
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white text-center shadow h-100">
                <div class="card-body py-3">
                    <h6><i class="fas fa-exchange-alt me-1"></i>Transactions</h6>
                    <h2 class="mb-0 fw-bold"><?php echo e($transCount ?? 0); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white text-center shadow h-100">
                <div class="card-body py-3">
                    <h6><i class="fas fa-dollar-sign me-1"></i>Volume Traité</h6>
                    <h2 class="mb-0 fw-bold"><?php echo e($volumeFormatted ?? '0'); ?></h2>
                    <?php if(isset($volumeByDevise) && count($volumeByDevise) > 1): ?>
                        <div class="mt-1" style="font-size:11px;opacity:.85">
                            <?php $__currentLoopData = $volumeByDevise; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $devise => $vol): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <span><?php echo e($devise); ?>: <?php echo e(number_format($vol, 0)); ?></span><br>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white text-center shadow h-100">
                <div class="card-body py-3">
                    <h6><i class="fas fa-university me-1"></i>Banques Correspondantes</h6>
                    <h2 class="mb-0 fw-bold"><?php echo e($bankCount ?? 0); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white text-center shadow h-100">
                <div class="card-body py-3">
                    <h6><i class="fas fa-clock me-1"></i>En attente autorisation</h6>
                    <h2 class="mb-0 fw-bold"><?php echo e($pendingAuth ?? 0); ?></h2>
                </div>
            </div>
        </div>
    </div>

    
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0">
                <i class="fas fa-table me-2 text-primary"></i>Transactions SWIFT internationales
            </h5>
            <span class="badge bg-secondary fs-6">
                <?php echo e(isset($transactions) ? $transactions->total() : 0); ?> message(s)
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>DATE</th>
                            <th>DIR</th>
                            <th>TYPE</th>
                            <th>RÉFÉRENCE</th>
                            <th class="text-end">MONTANT</th>
                            <th>DEVISE</th>
                            <th>ÉMETTEUR</th>
                            <th>BÉNÉFICIAIRE</th>
                            <th>STATUT</th>
                            <th>ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $transactions ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tx): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $status    = $tx->STATUS    ?? $tx->status    ?? null;
                            $direction = $tx->DIRECTION ?? $tx->direction  ?? null;
                        ?>
                        <tr class="<?php echo e(match($status) { 'pending' => 'table-warning', 'processed' => 'table-info', 'suspended' => 'table-danger', default => '' }); ?>">

                            <td class="text-muted small text-nowrap">
<?php echo e(\Carbon\Carbon::parse($tx->CREATED_AT ?? $tx->created_at)->format('d/m/Y H:i')); ?>

                            </td>

                            <td>
                                <?php if($direction === 'IN'): ?>
                                    <span class="badge bg-primary">Reçu</span>
                                <?php elseif($direction === 'OUT'): ?>
                                    <span class="badge bg-dark">Émis</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">—</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php
                                    $type = $tx->TYPE_MESSAGE ?? $tx->type_message ?? null;
                                    $badgeClass = match(true) {
                                        str_starts_with($type ?? '', 'PACS') => 'bg-primary',
                                        str_starts_with($type ?? '', 'CAMT') => 'bg-secondary',
                                        str_starts_with($type ?? '', 'MT')   => 'bg-info text-dark',
                                        default => 'bg-dark',
                                    };
                                ?>
                                <span class="badge <?php echo e($badgeClass); ?> font-monospace"><?php echo e($type ?? '—'); ?></span>
                            </td>

                            <td class="font-monospace small"><?php echo e($tx->REFERENCE ?? $tx->reference ?? '—'); ?></td>

                            <td class="fw-bold text-end text-nowrap">
                                <?php echo e(number_format((float)($tx->AMOUNT ?? $tx->amount ?? 0), 2, ',', ' ')); ?>

                            </td>

                            <td class="font-monospace"><?php echo e($tx->CURRENCY ?? $tx->currency ?? '—'); ?></td>

                            <td>
                                <div class="fw-semibold"><?php echo e($tx->SENDER_NAME ?? $tx->sender_name ?? '—'); ?></div>
                                <?php if($tx->SENDER_BIC ?? $tx->sender_bic): ?>
                                    <small class="text-muted font-monospace"><?php echo e($tx->SENDER_BIC ?? $tx->sender_bic); ?></small>
                                <?php endif; ?>
                            </td>

                            <td>
                                <div class="fw-semibold"><?php echo e($tx->RECEIVER_NAME ?? $tx->receiver_name ?? '—'); ?></div>
                                <?php if($tx->RECEIVER_BIC ?? $tx->receiver_bic): ?>
                                    <small class="text-muted font-monospace"><?php echo e($tx->RECEIVER_BIC ?? $tx->receiver_bic); ?></small>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php switch($status):
                                    case ('pending'): ?>
                                        <span class="badge bg-warning text-dark">En attente</span>
                                        <?php break; ?>
                                    <?php case ('processed'): ?>
                                        <span class="badge bg-info text-dark">
                                            <i class="fas fa-hourglass-half me-1"></i>À autoriser
                                        </span>
                                        <?php break; ?>
                                    <?php case ('authorized'): ?>
                                        <span class="badge bg-success">
                                            <i class="fas fa-shield-alt me-1"></i>Autorisé
                                        </span>
                                        <?php break; ?>
                                    <?php case ('suspended'): ?>
                                        <span class="badge bg-danger">
                                            <i class="fas fa-ban me-1"></i>Suspendu
                                        </span>
                                        <?php break; ?>
                                    <?php case ('rejected'): ?>
                                        <span class="badge bg-danger">Rejeté</span>
                                        <?php break; ?>
                                    <?php default: ?>
                                        <span class="badge bg-secondary"><?php echo e($status ?? '—'); ?></span>
                                <?php endswitch; ?>
                            </td>

                            <td>
                                <div class="d-flex gap-1 flex-wrap">
                                    <a href="<?php echo e(route('swift.show', $tx->id)); ?>"
                                       class="btn btn-outline-primary btn-sm" title="Voir détail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php $hasMt = !empty($tx->MT_CONTENT ?? $tx->mt_content); ?>
                                    <?php if($hasMt): ?>
                                        <button type="button" class="btn btn-outline-secondary btn-sm open-raw-file" data-url="<?php echo e(route('swift.view-mt', $tx->id)); ?>" data-title="MT">MT</button>
                                    <?php else: ?>
                                        <button class="btn btn-outline-secondary btn-sm" disabled>MT</button>
                                    <?php endif; ?>
                                    <?php $hasMx = !empty($tx->XML_BRUT ?? $tx->xml_brut); ?>
                                    <?php if($hasMx): ?>
                                        <a href="<?php echo e(route('swift.view-mx', $tx->id)); ?>" target="_blank" class="btn btn-outline-info btn-sm" title="MX">&lt;/&gt;</a>
                                    <?php else: ?>
                                        <button class="btn btn-outline-info btn-sm" disabled>&lt;/&gt;</button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-3x mb-3 d-block opacity-25"></i>
                                <p class="mb-1 fw-semibold">Aucune transaction internationale</p>
                                <small>
                                    <a href="<?php echo e(route('swift.import.form')); ?>">Importer des messages SWIFT</a>
                                </small>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if(isset($transactions) && $transactions->hasPages()): ?>
            <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                <small class="text-muted">
                    Affichage <?php echo e($transactions->firstItem()); ?>–<?php echo e($transactions->lastItem()); ?>

                    sur <?php echo e($transactions->total()); ?> résultat(s)
                </small>
                <?php echo e($transactions->links()); ?>

            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    setTimeout(function () {
        document.querySelectorAll('.alert').forEach(function (el) {
            bootstrap.Alert.getOrCreateInstance(el).close();
        });
    }, 5000);
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/swift-manager/dashboard.blade.php ENDPATH**/ ?>