

<?php $__env->startSection('title', 'Dashboard Swift Operator - BTL Bank'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h1 class="h2 mb-0 fw-bold">
                <i class="fas fa-globe-americas text-primary me-2"></i>Swift Operator
            </h1>
            <p class="text-muted">Opérations transfrontalières</p>
        </div>

        <div class="d-flex gap-2 flex-wrap">
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\MessageSwift::class)): ?>
                <a href="<?php echo e(route('swift.create')); ?>" class="btn btn-success">
                    <i class="fas fa-plus me-2"></i>Nouveau SWIFT
                </a>
            <?php endif; ?>
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('import', App\Models\MessageSwift::class)): ?>
                <a href="<?php echo e(route('swift.import.form')); ?>" class="btn btn-warning">
                    <i class="fas fa-file-import me-2"></i>Importer
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-sliders-h me-2 text-primary"></i>Filtres</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('swift-operator.dashboard')); ?>" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Direction</label>
                    <select name="direction" class="form-select">
                        <option value="">Toutes</option>
                        <option value="RECU" <?php echo e(request('direction') == 'RECU' ? 'selected' : ''); ?>>Reçus</option>
                        <option value="EMIS" <?php echo e(request('direction') == 'EMIS' ? 'selected' : ''); ?>>Émis</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Statut</label>
                    <select name="status" class="form-select">
                        <option value="">Tous</option>
                        <option value="pending" <?php echo e(request('status') == 'pending' ? 'selected' : ''); ?>>En attente</option>
                        <option value="processed" <?php echo e(request('status') == 'processed' ? 'selected' : ''); ?>>Traité</option>
                        <option value="rejected" <?php echo e(request('status') == 'rejected' ? 'selected' : ''); ?>>Rejeté</option>
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
                <div class="col-md-2">
                    <label class="form-label">Devise</label>
                    <select name="currency" class="form-select">
                        <option value="">Toutes</option>
                        <option value="USD" <?php echo e(request('currency') == 'USD' ? 'selected' : ''); ?>>USD</option>
                        <option value="EUR" <?php echo e(request('currency') == 'EUR' ? 'selected' : ''); ?>>EUR</option>
                        <option value="GBP" <?php echo e(request('currency') == 'GBP' ? 'selected' : ''); ?>>GBP</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-2"></i>Filtrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white text-center shadow">
                <div class="card-body">
                    <h6>Transactions 24h</h6>
                    <h3><?php echo e($transCount ?? 0); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white text-center shadow">
                <div class="card-body">
                    <h6>Volume Traité</h6>
                    <h3><?php echo e($volumeFormatted ?? '0'); ?></h3>
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
        <div class="col-md-4">
            <div class="card bg-warning text-white text-center shadow">
                <div class="card-body">
                    <h6>En attente</h6>
                    <h3><?php echo e($pendingAuth ?? 0); ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau -->
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Messages SWIFT</h5>
            <small><?php echo e($messages->total() ?? 0); ?> messages</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>DATE</th>
                            <th>DIR</th>
                            <th>TYPE</th>
                            <th>RÉFÉRENCE</th>
                            <th>ÉMETTEUR</th>
                            <th>BÉNÉFICIAIRE</th>
                            <th>MONTANT</th>
                            <th>STATUT</th>
                            <th>ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $msg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($msg->created_at ? $msg->created_at->format('d/m/Y H:i') : '-'); ?></td>
                                <td>
                                    <?php if($msg->direction == 'IN'): ?>
                                        <span class="badge bg-info">Reçu</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Émis</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-secondary"><?php echo e($msg->type_message ?? 'N/A'); ?></span></td>
                                <td><?php echo e($msg->reference ?? 'N/A'); ?></td>
                                <td><?php echo e($msg->sender_name ?? $msg->sender_bic ?? 'N/A'); ?></td>
                                <td><?php echo e($msg->receiver_name ?? $msg->receiver_bic ?? 'N/A'); ?></td>
                                <td class="fw-bold"><?php echo e(number_format($msg->amount ?? 0, 2)); ?> <?php echo e($msg->currency ?? 'EUR'); ?></td>
                                <td>
                                    <?php if($msg->status == 'pending'): ?>
                                        <span class="badge bg-warning">En attente</span>
                                    <?php elseif($msg->status == 'processed'): ?>
                                        <span class="badge bg-success">Traité</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger"><?php echo e($msg->status); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?php echo e(route('swift.show', $msg->id)); ?>" class="btn btn-outline-info" title="Détail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-success open-raw-file" data-url="<?php echo e(route('swift.view-mt', $msg->id)); ?>" data-title="MT"><i class="fas fa-file-alt"></i></button>
                                        <a href="<?php echo e(route('swift.view-mx', $msg->id)); ?>" target="_blank" class="btn btn-outline-dark" title="MX"><i class="fas fa-code"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                    <p>Aucun message trouvé</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            <?php echo e($messages->links()); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/swift-operator/dashboard.blade.php ENDPATH**/ ?>