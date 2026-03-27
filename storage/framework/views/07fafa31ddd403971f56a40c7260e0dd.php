

<?php $__env->startSection('title', 'Détail du message SWIFT'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 fw-bold">Détail du message SWIFT</h1>
                <a href="<?php echo e(url()->previous()); ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour
                </a>
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
                                    <?php echo e($message->TYPE_MESSAGE ?? $message->type_message); ?>

                                </span>
                            </p>
                            <p>
                                <strong>Référence :</strong>
                                <span class="font-monospace">
                                    <?php echo e($message->REFERENCE ?? $message->reference); ?>

                                </span>
                            </p>
                            <p>
                                <strong>Direction :</strong>
                                <?php $dir = $message->DIRECTION ?? $message->direction; ?>
                                <?php if($dir === 'IN'): ?>
                                    <span class="badge bg-primary">Reçu</span>
                                <?php else: ?>
                                    <span class="badge bg-dark">Émis</span>
                                <?php endif; ?>
                            </p>
                            <p>
                                <strong>Statut :</strong>
                                <?php $status = $message->STATUS ?? $message->status; ?>
                                <?php switch($status):
                                    case ('pending'): ?>
                                        <span class="badge bg-warning text-dark">En attente</span>
                                        <?php break; ?>
                                    <?php case ('processed'): ?>
                                        <span class="badge bg-success">Traité</span>
                                        <?php break; ?>
                                    <?php case ('rejected'): ?>
                                        <span class="badge bg-danger">Rejeté</span>
                                        <?php break; ?>
                                    <?php default: ?>
                                        <span class="badge bg-secondary"><?php echo e($status); ?></span>
                                <?php endswitch; ?>
                            </p>
                            <?php if($message->PROCESSED_AT ?? $message->processed_at): ?>
                                <p>
                                    <strong>Traité le :</strong>
                                    <?php echo e(\Carbon\Carbon::parse($message->PROCESSED_AT ?? $message->processed_at)->format('d/m/Y H:i')); ?>

                                </p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <p>
                                <strong>Montant :</strong>
                                <span class="fw-bold">
                                    <?php echo e(number_format((float)($message->AMOUNT ?? $message->amount ?? 0), 2)); ?>

                                    <?php echo e($message->CURRENCY ?? $message->currency); ?>

                                </span>
                            </p>
                            <p>
                                <strong>Date valeur :</strong>
                                <?php
                                    $vd = $message->VALUE_DATE ?? $message->value_date;
                                    echo $vd ? \Carbon\Carbon::parse($vd)->format('d/m/Y') : '—';
                                ?>
                            </p>
                            <p>
                                <strong>Émetteur :</strong>
                                <?php echo e($message->SENDER_NAME ?? $message->sender_name ?? '—'); ?>

                                <?php if($message->SENDER_BIC ?? $message->sender_bic): ?>
                                    <small class="text-muted font-monospace">
                                        (<?php echo e($message->SENDER_BIC ?? $message->sender_bic); ?>)
                                    </small>
                                <?php endif; ?>
                            </p>
                            <p>
                                <strong>Bénéficiaire :</strong>
                                <?php echo e($message->RECEIVER_NAME ?? $message->receiver_name ?? '—'); ?>

                                <?php if($message->RECEIVER_BIC ?? $message->receiver_bic): ?>
                                    <small class="text-muted font-monospace">
                                        (<?php echo e($message->RECEIVER_BIC ?? $message->receiver_bic); ?>)
                                    </small>
                                <?php endif; ?>
                            </p>
                            <?php if($message->DESCRIPTION ?? $message->description): ?>
                                <p>
                                    <strong>Description :</strong>
                                    <?php echo e($message->DESCRIPTION ?? $message->description); ?>

                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            
            <?php if($message->details && $message->details->count()): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            Détails spécifiques
                            (<?php echo e($message->TYPE_MESSAGE ?? $message->type_message); ?>)
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
                                        <td class="font-monospace fw-bold">
                                            <?php echo e($detail->tag_name); ?>

                                        </td>
                                        <td class="font-monospace">
                                            <?php echo e($detail->tag_value); ?>

                                        </td>
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
                        $status    = $message->STATUS    ?? $message->status    ?? null;
                        $direction = $message->DIRECTION ?? $message->direction ?? null;
                        $user      = auth()->user();

                        // Rôles qui peuvent traiter/rejeter selon la direction
                        $canAct =
                            $user->hasRole('admin')
                            || $user->hasRole('international-admin')
                            || ($user->hasRole(['chef-agence', 'chargee']) && $direction === 'OUT');

                        $hasMt = !empty($message->MT_CONTENT ?? $message->mt_content);
                        $hasMx = !empty($message->XML_BRUT   ?? $message->xml_brut);
                    ?>

                    
                    <?php if($status === 'pending' && $canAct): ?>
                        <form method="POST"
                              action="<?php echo e(route('swift.process', $message->id)); ?>"
                              onsubmit="return confirm('Confirmer le traitement de ce message ?')">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('PATCH'); ?>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check me-2"></i>Traiter
                            </button>
                        </form>

                        
                        <form method="POST"
                              action="<?php echo e(route('swift.reject', $message->id)); ?>"
                              onsubmit="return confirm('Confirmer le rejet de ce message ?')">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('PATCH'); ?>
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-times me-2"></i>Rejeter
                            </button>
                        </form>
                    <?php endif; ?>

                    
                    <?php if($hasMt): ?>
                        <a href="<?php echo e(route('swift.view-mt', $message->id)); ?>"
                           class="btn btn-outline-secondary" target="_blank">
                            <i class="fas fa-file-alt me-2"></i>Voir MT
                        </a>
                    <?php else: ?>
                        <button class="btn btn-outline-secondary disabled"
                                title="MT disponible après traitement">
                            <i class="fas fa-file-alt me-2"></i>Voir MT
                        </button>
                    <?php endif; ?>

                    
                    <?php if($hasMx): ?>
                        <a href="<?php echo e(route('swift.view-mx', $message->id)); ?>"
                           class="btn btn-outline-info" target="_blank">
                            <i class="fas fa-code me-2"></i>Voir MX
                        </a>
                    <?php else: ?>
                        <button class="btn btn-outline-secondary disabled"
                                title="MX disponible après traitement">
                            <i class="fas fa-code me-2"></i>Voir MX
                        </button>
                    <?php endif; ?>

                    
                    <?php if($user->hasRole('admin')): ?>
                        <form method="POST"
                              action="<?php echo e(route('swift.destroy', $message->id)); ?>"
                              class="ms-auto"
                              onsubmit="return confirm('Confirmer la suppression définitive ?')">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
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
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/admin/users/show.blade.php ENDPATH**/ ?>