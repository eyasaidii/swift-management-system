
<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead class="bg-light">
            <tr>
                <th>Référence</th>
                <th>Type</th>
                <th>Catégorie</th>
                <th>Émetteur</th>
                <th>Récepteur</th>
                <th>Montant</th>
                <th>Date</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $msg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php if($msg->DIRECTION == 'OUT'): ?>
                <tr>
                    <td><strong><?php echo e($msg->REFERENCE); ?></strong></td>
                    <td><span class="badge bg-secondary"><?php echo e($msg->TYPE_MESSAGE); ?></span></td>
                    <td>
                        <?php
                            $cat = $msg->CATEGORIE ?? $msg->determineCategorie();
                        ?>
                        <span class="badge bg-success"><?php echo e($cat); ?></span>
                    </td>
                    <td>
                        <div>
                            <strong><?php echo e($msg->SENDER_NAME ?? 'N/A'); ?></strong>
                            <?php if($msg->SENDER_BIC): ?>
                                <br><small class="text-muted"><?php echo e($msg->SENDER_BIC); ?></small>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <div>
                            <strong><?php echo e($msg->RECEIVER_NAME ?? 'N/A'); ?></strong>
                            <?php if($msg->RECEIVER_BIC): ?>
                                <br><small class="text-muted"><?php echo e($msg->RECEIVER_BIC); ?></small>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="fw-bold"><?php echo e(number_format($msg->AMOUNT, 2)); ?> <?php echo e($msg->CURRENCY); ?></td>
                    <td>
                        <?php if($msg->CREATED_AT): ?>
                            <?php echo e($msg->CREATED_AT->format('d/m/Y H:i')); ?>

                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($msg->STATUS == 'pending'): ?>
                            <span class="badge bg-warning">En attente</span>
                        <?php elseif($msg->STATUS == 'processed'): ?>
                            <span class="badge bg-success">Traité</span>
                        <?php else: ?>
                            <span class="badge bg-secondary"><?php echo e($msg->STATUS); ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <a href="<?php echo e(route('swift.show', $msg->id)); ?>" class="btn btn-outline-info" title="Voir">
                                <i class="fas fa-eye"></i>
                            </a>
                            <button type="button" class="btn btn-outline-primary" onclick="openModal('mx', <?php echo e($msg->id); ?>)" title="View MX">
                                <i class="fas fa-code"></i>
                            </button>
                            <button type="button" class="btn btn-outline-success" onclick="openModal('mt', <?php echo e($msg->id); ?>)" title="View MT">
                                <i class="fas fa-file-alt"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="9" class="text-center py-4">
                        <i class="fas fa-paper-plane fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Aucun message émis trouvé</p>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div><?php /**PATH /var/www/resources/views/swift/partials/emitted-table.blade.php ENDPATH**/ ?>