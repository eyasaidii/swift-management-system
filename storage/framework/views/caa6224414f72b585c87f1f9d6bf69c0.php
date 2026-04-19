

<?php $__env->startSection('title', 'Messages SWIFT - BTL Bank'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h1 class="h2 mb-0 fw-bold">Gestion des Messages SWIFT</h1>
            <p class="text-muted">Administration complète des messages SWIFT</p>
        </div>

        <div class="d-flex gap-2 flex-wrap">
            <a href="<?php echo e(route('dashboard')); ?>" class="btn btn-primary">
                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
            </a>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\MessageSwift::class)): ?>
                <a href="<?php echo e(route('swift.create')); ?>" class="btn btn-success">
                    <i class="fas fa-plus me-2"></i>Nouveau message
                </a>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('import', App\Models\MessageSwift::class)): ?>
                <a href="<?php echo e(route('swift.import.form')); ?>" class="btn btn-warning">
                    <i class="fas fa-file-import me-2"></i>Importer
                </a>
            <?php endif; ?>

            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('export', App\Models\MessageSwift::class)): ?>
                <a href="<?php echo e(route('swift.export')); ?>" class="btn btn-info">
                    <i class="fas fa-download me-2"></i>Exporter
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Alertes -->
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

    <div class="row">
  
        <!-- Tableau avec attributs en minuscules -->
        <div class="table-responsive">
            <table class="table table-hover table-bordered mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>DATE</th>
                        <th>TYPE</th>
                        <th>SENDER</th>
                        <th>TRANSACTION REFERENCE</th>
                        <th>AMOUNT</th>
                        <th>CUR</th>
                        <th>ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $msg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <?php echo e($msg->value_date ? $msg->value_date->format('Y-m-d H:i') : 
                                  ($msg->created_at ? $msg->created_at->format('Y-m-d H:i') : '-')); ?>

                            </td>

                            <td>
                                <span class="badge bg-info text-uppercase">
                                    <?php echo e($msg->type_message ?? 'UNKNOWN'); ?>

                                </span>
                            </td>

                            <td>
                                <?php echo e($msg->sender_name ?? $msg->sender_bic ?? 'N/A'); ?>

                            </td>

                            <td><?php echo e($msg->reference ?? 'N/A'); ?></td>

                            <td class="fw-bold text-end">
                                <?php echo e(number_format((float)($msg->amount ?? 0), 2, '.', ',')); ?>

                            </td>

                            <td class="text-uppercase"><?php echo e($msg->currency ?? 'N/A'); ?></td>

                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?php echo e(route('swift.show', $msg->id)); ?>" class="btn btn-outline-primary" title="Détail">
                                        <i class="fas fa-eye"></i> View
                                    </a>

                                    <button type="button" class="btn btn-outline-success open-raw-file" data-url="<?php echo e(route('swift.view-mt', $msg->id)); ?>" data-title="MT"><i class="fas fa-file-alt"></i> View MT</button>

                                    <a href="<?php echo e(route('swift.view-mx', $msg->id)); ?>" target="_blank" class="btn btn-outline-dark" title="MX"><i class="fas fa-code"></i> View MX</a>

                                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo e($msg->id); ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <p>Aucun message trouvé</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            <?php echo e($messages->appends(request()->query())->links()); ?>

        </div>
    </div>

    <!-- Modals suppression -->
    <?php $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $msg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
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
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="btn btn-danger px-4">Supprimer</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php $__env->stopSection(); ?>

<style>
    .list-group-item.active {
        background-color: #0d6efd !important;
        border-color: #0d6efd !important;
        color: white !important;
    }
    .btn-outline-success {
        color: #198754;
        border-color: #198754;
    }
    .btn-outline-success:hover:not(:disabled) {
        background-color: #198754;
        color: white;
    }
    .btn-outline-danger:hover {
        background-color: #dc3545;
        color: white;
    }
    .sticky-top {
        top: 20px;
        z-index: 100;
    }
</style>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/swift/index.blade.php ENDPATH**/ ?>