
<?php $__env->startSection('title', 'Dashboard Administrateur - BTL Bank'); ?>
<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h1 class="h2 mb-0 fw-bold"><i class="fas fa-crown text-danger me-2"></i>Administration Système</h1>
            <p class="text-muted">Contrôle total - Tous les rôles et opérations</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?php echo e(route('admin.users.index')); ?>" class="btn btn-primary"><i class="fas fa-users me-2"></i>Gérer Utilisateurs</a>
            <a href="<?php echo e(route('swift.export-center')); ?>" class="btn btn-info"><i class="fas fa-download me-2"></i>Exporter Tout</a>
            <a href="<?php echo e(route('swift.index')); ?>" class="btn btn-success"><i class="fas fa-list me-2"></i>Messages SWIFT</a>
        </div>
    </div>

    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-white"><h5 class="mb-0"><i class="fas fa-sliders-h me-2 text-primary"></i>Filtres avancés</h5></div>
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('admin.dashboard')); ?>" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Type message</label>
                    <select name="type_message" class="form-select">
                        <option value="">Tous</option>
                        <?php $__currentLoopData = \App\Models\MessageSwift::TYPES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($code); ?>" <?php echo e(request('type_message') == $code ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Direction</label>
                    <select name="direction" class="form-select">
                        <option value="">Tous</option>
                        <option value="RECU" <?php echo e(request('direction') == 'RECU' ? 'selected' : ''); ?>>Reçus</option>
                        <option value="EMIS" <?php echo e(request('direction') == 'EMIS' ? 'selected' : ''); ?>>Émis</option>
                    </select>
                </div>
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
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-50"><i class="fas fa-filter me-2"></i>Filtrer</button>
                    <a href="<?php echo e(route('admin.dashboard')); ?>" class="btn btn-outline-danger w-50"><i class="fas fa-times me-2"></i>Réinitialiser</a>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white text-center shadow">
                <div class="card-body"><h6>Total Messages</h6><h3><?php echo e($totalCount ?? 0); ?></h3></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white text-center shadow">
                <div class="card-body"><h6>Reçus</h6><h3><?php echo e($receivedCount ?? 0); ?></h3></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white text-center shadow">
                <div class="card-body"><h6>Émis</h6><h3><?php echo e($emittedCount ?? 0); ?></h3></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white text-center shadow">
                <div class="card-body"><h6>En attente</h6><h3><?php echo e($pendingCount ?? 0); ?></h3></div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Tous les Messages SWIFT</h5>
            <small><?php echo e($messages->total() ?? 0); ?> messages trouvés</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th>DATE</th><th>TYPE</th><th>SENDER</th><th>RÉFÉRENCE</th>
                            <th>MONTANT</th><th>CUR</th><th>STATUT</th><th>ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $msg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $status = $msg->status ?? null;
                            $note   = $msg->authorization_note ?? null;
                        ?>
                        <tr>
                            <td class="small text-muted"><?php echo e($msg->created_at?->format('d/m/Y H:i') ?? '-'); ?></td>
                            <td><span class="badge bg-secondary font-monospace"><?php echo e($msg->type_message); ?></span></td>
                            <td>
                                <?php echo e($msg->sender_name ?? 'N/A'); ?>

                                <?php if($msg->sender_bic): ?><br><small class="text-muted font-monospace"><?php echo e($msg->sender_bic); ?></small><?php endif; ?>
                            </td>
                            <td class="font-monospace small"><?php echo e($msg->reference); ?></td>
                            <td class="fw-bold"><?php echo e(number_format($msg->amount ?? 0, 2)); ?> <?php echo e($msg->currency); ?></td>
                            <td><?php echo e($msg->currency); ?></td>
                            <td>
                                <?php switch($status):
                                    case ('pending'): ?>
                                        <span class="badge bg-warning text-dark">⏳ En attente</span><?php break; ?>
                                    <?php case ('processed'): ?>
                                        <span class="badge bg-info text-dark">🔵 À autoriser</span><?php break; ?>
                                    <?php case ('authorized'): ?>
                                        <span class="badge bg-success"
                                              <?php if($note): ?> data-bs-toggle="tooltip" title="<?php echo e($note); ?>" <?php endif; ?>>
                                            ✅ Autorisé
                                        </span>
                                        <?php if($note): ?><br><small class="text-muted fst-italic" style="font-size:10px"><?php echo e(Str::limit($note, 35)); ?></small><?php endif; ?>
                                        <?php break; ?>
                                    <?php case ('suspended'): ?>
                                        <span class="badge bg-danger"
                                              <?php if($note): ?> data-bs-toggle="tooltip" title="Motif: <?php echo e($note); ?>" <?php endif; ?>>
                                            ⛔ Suspendu
                                        </span>
                                        <?php if($note): ?><br><small class="text-danger fst-italic" style="font-size:10px"><?php echo e(Str::limit($note, 35)); ?></small><?php endif; ?>
                                        <?php break; ?>
                                    <?php case ('rejected'): ?>
                                        <span class="badge bg-danger">❌ Rejeté</span><?php break; ?>
                                    <?php default: ?>
                                        <span class="badge bg-secondary"><?php echo e($status); ?></span>
                                <?php endswitch; ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?php echo e(route('swift.show', $msg->id)); ?>" class="btn btn-outline-primary" title="Détail"><i class="fas fa-eye"></i></a>
                                    <a href="<?php echo e(route('swift.pdf', $msg->id)); ?>" class="btn btn-outline-danger" title="PDF" target="_blank"><i class="fas fa-file-pdf"></i></a>
                                    <?php if($msg->mt_content): ?>
                                        <a href="<?php echo e(route('swift.view-mt', $msg->id)); ?>" class="btn btn-outline-secondary" title="MT" target="_blank"><i class="fas fa-file-alt"></i></a>
                                    <?php else: ?>
                                        <button class="btn btn-outline-secondary disabled" title="Pas de MT"><i class="fas fa-file-alt"></i></button>
                                    <?php endif; ?>
                                    <?php if($msg->xml_brut): ?>
                                        <a href="<?php echo e(route('swift.view-mx', $msg->id)); ?>" class="btn btn-outline-dark" title="MX" target="_blank"><i class="fas fa-code"></i></a>
                                    <?php else: ?>
                                        <button class="btn btn-outline-secondary disabled" title="Pas de MX"><i class="fas fa-code"></i></button>
                                    <?php endif; ?>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $msg)): ?>
                                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo e($msg->id); ?>"><i class="fas fa-trash"></i></button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="8" class="text-center py-5 text-muted"><i class="fas fa-inbox fa-3x mb-3 d-block opacity-25"></i><p>Aucun message trouvé</p></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white"><?php echo e($messages->links()); ?></div>
    </div>
</div>

<?php $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $msg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $msg)): ?>
    <div class="modal fade" id="deleteModal<?php echo e($msg->id); ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Confirmer la suppression</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <i class="fas fa-trash-can fa-4x text-danger mb-3"></i>
                    <h5 class="mb-2"><?php echo e($msg->reference); ?></h5>
                    <p class="fw-bold mt-3">Voulez-vous vraiment supprimer ce message ?</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Annuler</button>
                    <form action="<?php echo e(route('swift.destroy', $msg->id)); ?>" method="POST" class="d-inline">
                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="btn btn-danger px-4">Supprimer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\eya saidi\Desktop\btl-swift-platform\btl-swift-platform-main\btl-swift-platform-main\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>