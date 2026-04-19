
<?php $__env->startSection('title', 'Dashboard Monétique - BTL Bank'); ?>
<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h1 class="h2 mb-0 fw-bold"><i class="fas fa-credit-card text-success me-2"></i>Monétique</h1>
            <p class="text-muted">Transactions cartes & surveillance fraude</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('export', App\Models\MessageSwift::class)): ?>
                <a href="<?php echo e(route('swift.export-center')); ?>" class="btn btn-info"><i class="fas fa-download me-2"></i>Export Center</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-white"><h5 class="mb-0"><i class="fas fa-sliders-h me-2 text-primary"></i>Filtres</h5></div>
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('monetique.dashboard')); ?>" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Statut</label>
                    <select name="status" class="form-select">
                        <option value="">Tous</option>
                        <option value="pending"    <?php echo e(request('status') == 'pending'    ? 'selected' : ''); ?>>En attente</option>
                        <option value="processed"  <?php echo e(request('status') == 'processed'  ? 'selected' : ''); ?>>Traité</option>
                        <option value="authorized" <?php echo e(request('status') == 'authorized' ? 'selected' : ''); ?>>Autorisé</option>
                        <option value="suspended"  <?php echo e(request('status') == 'suspended'  ? 'selected' : ''); ?>>Suspendu</option>
                        <option value="rejected"   <?php echo e(request('status') == 'rejected'   ? 'selected' : ''); ?>>Rejeté</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date début</label>
                    <input type="date" name="date_from" class="form-control" value="<?php echo e(request('date_from')); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date fin</label>
                    <input type="date" name="date_to" class="form-control" value="<?php echo e(request('date_to')); ?>">
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-50"><i class="fas fa-filter me-2"></i>Filtrer</button>
                    <a href="<?php echo e(route('monetique.dashboard')); ?>" class="btn btn-outline-danger w-50"><i class="fas fa-times me-2"></i>Réinitialiser</a>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white text-center shadow">
                <div class="card-body"><h6>Total messages</h6><h3><?php echo e($totalCount ?? 0); ?></h3></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white text-center shadow">
                <div class="card-body"><h6>Reçus</h6><h3><?php echo e($inCount ?? 0); ?></h3></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-white text-center shadow">
                <div class="card-body"><h6>En attente</h6><h3><?php echo e($pendingCount ?? 0); ?></h3></div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-inbox me-2"></i>Messages SWIFT (Reçus)</h5>
            <small><?php echo e($messages->total() ?? 0); ?> messages</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="bg-light">
                        <tr><th>DATE</th><th>TYPE</th><th>RÉFÉRENCE</th><th>EXPÉDITEUR</th><th>MONTANT</th><th>STATUT</th><th>ACTIONS</th></tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $msg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php $note = $msg->authorization_note ?? null; ?>
                        <tr>
                            <td class="small text-muted"><?php echo e($msg->created_at?->format('d/m/Y H:i') ?? '-'); ?></td>
                            <td><span class="badge bg-secondary font-monospace"><?php echo e($msg->type_message ?? 'N/A'); ?></span></td>
                            <td class="font-monospace small"><?php echo e($msg->reference ?? 'N/A'); ?></td>
                            <td><?php echo e($msg->sender_name ?? $msg->sender_bic ?? 'N/A'); ?></td>
                            <td class="fw-bold"><?php echo e(number_format($msg->amount ?? 0, 2)); ?> <?php echo e($msg->currency ?? 'EUR'); ?></td>
                            <td>
                                <?php switch($msg->status):
                                    case ('pending'): ?><span class="badge bg-warning text-dark">⏳ En attente</span><?php break; ?>
                                    <?php case ('processed'): ?><span class="badge bg-info text-dark">🔵 À autoriser</span><?php break; ?>
                                    <?php case ('authorized'): ?>
                                        <span class="badge bg-success" <?php if($note): ?> data-bs-toggle="tooltip" title="<?php echo e($note); ?>" <?php endif; ?>>✅ Autorisé</span>
                                        <?php if($note): ?><br><small class="text-success fst-italic" style="font-size:10px"><?php echo e(Str::limit($note, 35)); ?></small><?php endif; ?>
                                        <?php break; ?>
                                    <?php case ('suspended'): ?>
                                        <span class="badge bg-danger" <?php if($note): ?> data-bs-toggle="tooltip" title="Motif: <?php echo e($note); ?>" <?php endif; ?>>⛔ Suspendu</span>
                                        <?php if($note): ?><br><small class="text-danger fst-italic" style="font-size:10px"><?php echo e(Str::limit($note, 35)); ?></small><?php endif; ?>
                                        <?php break; ?>
                                    <?php case ('rejected'): ?><span class="badge bg-danger">❌ Rejeté</span><?php break; ?>
                                    <?php default: ?><span class="badge bg-secondary"><?php echo e($msg->status); ?></span>
                                <?php endswitch; ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?php echo e(route('swift.show', $msg->id)); ?>" class="btn btn-outline-primary" title="Détail"><i class="fas fa-eye"></i></a>
                                    <a href="<?php echo e(route('swift.pdf', $msg->id)); ?>" class="btn btn-outline-danger" title="PDF" target="_blank"><i class="fas fa-file-pdf"></i></a>
                                    <button type="button" class="btn btn-outline-secondary open-raw-file" data-url="<?php echo e(route('swift.view-mt', $msg->id)); ?>" data-title="MT"><i class="fas fa-file-alt"></i></button>
                                    <a href="<?php echo e(route('swift.view-mx', $msg->id)); ?>" target="_blank" class="btn btn-outline-dark" title="MX"><i class="fas fa-code"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="7" class="text-center py-5 text-muted"><i class="fas fa-inbox fa-3x mb-3 d-block opacity-25"></i><p>Aucun message trouvé</p></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white"><?php echo e($messages->links()); ?></div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/monetique/dashboard.blade.php ENDPATH**/ ?>