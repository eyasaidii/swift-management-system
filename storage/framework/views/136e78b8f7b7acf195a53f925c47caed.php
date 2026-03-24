

<?php $__env->startSection('title', 'Détail du message SWIFT'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 fw-bold mb-0">Détail du message SWIFT</h1>
                <div class="d-flex gap-2">
                    
                    <a href="<?php echo e(route('swift.pdf', $message->id)); ?>"
                       target="_blank"
                       class="btn btn-danger">
                        <i class="fas fa-file-pdf me-2"></i>Télécharger PDF
                    </a>
                    <a href="<?php echo e(url()->previous()); ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Retour
                    </a>
                </div>
            </div>

            
            <?php if(session('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?php echo e(session('success')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if(session('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo e(session('error')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Informations générales</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p>
                                <strong>Type :</strong>
                                <span class="badge bg-secondary font-monospace ms-1">
                                    <?php echo e($message->type_message); ?>

                                </span>
                            </p>
                            <p>
                                <strong>Référence :</strong>
                                <span class="font-monospace"><?php echo e($message->reference); ?></span>
                            </p>
                            <p>
                                <strong>Direction :</strong>
                                <?php if($message->direction == 'IN'): ?>
                                    <span class="badge bg-primary">Reçu</span>
                                <?php else: ?>
                                    <span class="badge bg-dark">Émis</span>
                                <?php endif; ?>
                            </p>
                            <p>
                                <strong>Statut :</strong>
                                <?php switch($message->status):
                                    case ('pending'): ?>
                                        <span class="badge bg-warning text-dark">⏳ En attente</span>
                                        <?php break; ?>
                                    <?php case ('processed'): ?>
                                        <span class="badge bg-info text-dark">🔵 À autoriser</span>
                                        <?php break; ?>
                                    <?php case ('authorized'): ?>
                                        <span class="badge bg-success">✅ Autorisé</span>
                                        <?php break; ?>
                                    <?php case ('suspended'): ?>
                                        <span class="badge bg-danger">⛔ Suspendu</span>
                                        <?php break; ?>
                                    <?php case ('rejected'): ?>
                                        <span class="badge bg-danger">❌ Rejeté</span>
                                        <?php break; ?>
                                    <?php default: ?>
                                        <span class="badge bg-secondary"><?php echo e($message->status); ?></span>
                                <?php endswitch; ?>
                            </p>
                            <?php if($message->processed_at): ?>
                            <p>
                                <strong>Traité le :</strong>
                                <?php echo e(\Carbon\Carbon::parse($message->processed_at)->format('d/m/Y H:i')); ?>

                            </p>
                            <?php endif; ?>
                            <?php if($message->authorized_at): ?>
                            <p>
                                <strong>Autorisé le :</strong>
                                <?php echo e(\Carbon\Carbon::parse($message->authorized_at)->format('d/m/Y H:i')); ?>

                            </p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <p>
                                <strong>Montant :</strong>
                                <span class="fw-bold fs-5 text-success">
                                    <?php echo e(number_format($message->amount, 2)); ?> <?php echo e($message->currency); ?>

                                </span>
                            </p>
                            <p>
                                <strong>Date valeur :</strong>
                                <?php echo e($message->value_date?->format('d/m/Y') ?? '—'); ?>

                            </p>
                            <p>
                                <strong>Émetteur :</strong>
                                <?php echo e($message->sender_name ?? '—'); ?>

                                <?php if($message->sender_bic): ?>
                                    <small class="text-muted font-monospace">(<?php echo e($message->sender_bic); ?>)</small>
                                <?php endif; ?>
                            </p>
                            <p>
                                <strong>Bénéficiaire :</strong>
                                <?php echo e($message->receiver_name ?? '—'); ?>

                                <?php if($message->receiver_bic): ?>
                                    <small class="text-muted font-monospace">(<?php echo e($message->receiver_bic); ?>)</small>
                                <?php endif; ?>
                            </p>
                            <?php if($message->description): ?>
                            <p>
                                <strong>Description :</strong>
                                <?php echo e($message->description); ?>

                            </p>
                            <?php endif; ?>
                            <?php if($message->authorization_note): ?>
                            <p>
                                <strong>Note autorisation :</strong>
                                <em class="text-muted"><?php echo e($message->authorization_note); ?></em>
                            </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            
            <?php if($message->transaction): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-exchange-alt me-2 text-success"></i>Transaction associée
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 border-end">
                            <small class="text-muted d-block mb-1">Montant</small>
                            <strong class="fs-5">
                                <?php echo e(number_format($message->transaction->montant, 2)); ?>

                                <?php echo e($message->transaction->devise); ?>

                            </strong>
                        </div>
                        <div class="col-md-3 border-end">
                            <small class="text-muted d-block mb-1">Émetteur</small>
                            <strong><?php echo e($message->transaction->emetteur); ?></strong>
                        </div>
                        <div class="col-md-3 border-end">
                            <small class="text-muted d-block mb-1">Récepteur</small>
                            <strong><?php echo e($message->transaction->recepteur); ?></strong>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block mb-1">Date transaction</small>
                            <strong>
                                <?php echo e(\Carbon\Carbon::parse($message->transaction->date_transaction)->format('d/m/Y')); ?>

                            </strong>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            
            <?php if($message->details && $message->details->count()): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        Détails spécifiques (<?php echo e($message->type_message); ?>)
                    </h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:120px">Tag</th>
                                <th>Valeur</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $message->details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="font-monospace fw-bold text-success"><?php echo e($detail->tag_name); ?></td>
                                <td class="font-monospace"><?php echo e($detail->tag_value); ?></td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            
            <div class="card shadow-sm">
                <div class="card-body d-flex gap-2 flex-wrap align-items-center">

                    <?php
                        $status    = $message->status;
                        $direction = $message->direction;
                        $user      = auth()->user();

                        $canAct =
                            $user->hasRole('admin')
                            || $user->hasRole('international-admin')
                            || ($user->hasRole(['chef-agence', 'chargee']) && $direction === 'OUT');

                        $canAuthorize = $user->hasRole(['admin', 'international-admin']);
                    ?>

                    
                    <?php if($status === 'pending' && $canAct): ?>
                        <form method="POST"
                              action="<?php echo e(route('swift.process', $message->id)); ?>"
                              onsubmit="return confirm('Confirmer le traitement ?')">
                            <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check me-2"></i>Traiter
                            </button>
                        </form>
                        <form method="POST"
                              action="<?php echo e(route('swift.reject', $message->id)); ?>"
                              onsubmit="return confirm('Confirmer le rejet ?')">
                            <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-times me-2"></i>Rejeter
                            </button>
                        </form>
                    <?php endif; ?>

                    
                    <?php if($status === 'processed' && $canAuthorize): ?>
                        <button type="button"
                                class="btn btn-success"
                                data-bs-toggle="modal"
                                data-bs-target="#modalAuthorize"
                                data-id="<?php echo e($message->id); ?>"
                                data-ref="<?php echo e($message->reference); ?>">
                            <i class="fas fa-shield-alt me-2"></i>Autoriser
                        </button>
                    <?php endif; ?>

                    
                    <?php if(in_array($status, ['processed', 'authorized']) && $canAuthorize): ?>
                        <button type="button"
                                class="btn btn-warning"
                                data-bs-toggle="modal"
                                data-bs-target="#modalSuspend"
                                data-id="<?php echo e($message->id); ?>"
                                data-ref="<?php echo e($message->reference); ?>">
                            <i class="fas fa-pause-circle me-2"></i>Suspendre
                        </button>
                    <?php endif; ?>

                    
                    <?php if($message->mt_content): ?>
                        <a href="<?php echo e(route('swift.view-mt', $message->id)); ?>"
                           target="_blank" class="btn btn-outline-secondary">
                            <i class="fas fa-file-alt me-2"></i>Voir MT
                        </a>
                    <?php else: ?>
                        <button class="btn btn-outline-secondary disabled"
                                title="MT disponible après traitement">
                            <i class="fas fa-file-alt me-2"></i>Voir MT
                        </button>
                    <?php endif; ?>

                    
                    <?php if($message->xml_brut): ?>
                        <a href="<?php echo e(route('swift.view-mx', $message->id)); ?>"
                           target="_blank" class="btn btn-outline-info">
                            <i class="fas fa-code me-2"></i>Voir MX
                        </a>
                    <?php else: ?>
                        <button class="btn btn-outline-secondary disabled"
                                title="MX disponible après traitement">
                            <i class="fas fa-code me-2"></i>Voir MX
                        </button>
                    <?php endif; ?>

                    
                    <a href="<?php echo e(route('swift.pdf', $message->id)); ?>"
                       target="_blank"
                       class="btn btn-outline-danger">
                        <i class="fas fa-file-pdf me-2"></i>PDF
                    </a>

                    
                    <?php if($user->hasRole('admin')): ?>
                        <form method="POST"
                              action="<?php echo e(route('swift.destroy', $message->id)); ?>"
                              class="ms-auto"
                              onsubmit="return confirm('Supprimer définitivement ce message ?')">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="fas fa-trash me-2"></i>Supprimer
                            </button>
                        </form>
                    <?php endif; ?>

                </div>
            </div>

        </div>
    </div>
</div>


<div class="modal fade" id="modalAuthorize" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formAuthorize" method="POST" action="">
                <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-shield-alt me-2"></i>Autoriser le virement
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Vous allez autoriser le message <strong id="authorizeRef"></strong>.</p>
                    <p class="text-muted small mb-3">
                        Le virement est conforme aux règles de contrôle des changes et peut être transmis via SWIFT.
                    </p>
                    <div class="mb-3">
                        <label class="form-label">Note d'autorisation (optionnel)</label>
                        <textarea name="note" class="form-control" rows="2"
                                  placeholder="ex: Vérifié — transaction conforme BCT"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-shield-alt me-2"></i>Confirmer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal fade" id="modalSuspend" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formSuspend" method="POST" action="">
                <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">
                        <i class="fas fa-pause-circle me-2"></i>Suspendre le message
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Vous allez suspendre le message <strong id="suspendRef"></strong>.</p>
                    <p class="text-muted small mb-3">
                        Le message sera bloqué et ne pourra plus être transmis.
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            Motif de suspension <span class="text-danger">*</span>
                        </label>
                        <textarea name="note" class="form-control" rows="2" required
                                  placeholder="ex: En attente de documentation complémentaire"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-pause-circle me-2"></i>Confirmer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalAuthorize = document.getElementById('modalAuthorize');
    if (modalAuthorize) {
        modalAuthorize.addEventListener('show.bs.modal', function (e) {
            var btn = e.relatedTarget;
            document.getElementById('authorizeRef').textContent = btn.getAttribute('data-ref');
            document.getElementById('formAuthorize').action = '/swift/' + btn.getAttribute('data-id') + '/authorize';
        });
    }

    const modalSuspend = document.getElementById('modalSuspend');
    if (modalSuspend) {
        modalSuspend.addEventListener('show.bs.modal', function (e) {
            var btn = e.relatedTarget;
            document.getElementById('suspendRef').textContent = btn.getAttribute('data-ref');
            document.getElementById('formSuspend').action = '/swift/' + btn.getAttribute('data-id') + '/suspend';
        });
    }
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
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\eya saidi\Desktop\btl-swift-platform\btl-swift-platform-main\btl-swift-platform-main\resources\views/swift/show.blade.php ENDPATH**/ ?>