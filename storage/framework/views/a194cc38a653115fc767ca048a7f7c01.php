

<?php $__env->startSection('title', 'Détail de l\'utilisateur'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 fw-bold">
                    <i class="fas fa-user me-2"></i>Détail de l'utilisateur
                </h1>
                <div>
                    <a href="<?php echo e(route('admin.users.index')); ?>" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left me-2"></i>Retour à la liste
                    </a>
                    <a href="<?php echo e(route('admin.users.modifier', $user)); ?>" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i>Modifier
                    </a>
                </div>
            </div>

            
            <?php if(session('success')): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i><?php echo e(session('success')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if(session('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo e(session('error')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Informations générales</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Nom complet :</strong> <?php echo e($user->name); ?></p>
                            <p><strong>Email :</strong> <?php echo e($user->email); ?></p>
                            <p><strong>Téléphone :</strong> <?php echo e($user->telephone ?? 'Non renseigné'); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p>
                                <strong>Rôle :</strong>
                                <?php
                                    $roleName = $user->getRoleNames()->first();
                                    $roles = App\Models\User::getBankRoles();
                                    $roleInfo = $roles[$roleName] ?? ['name' => $roleName ?? 'Aucun', 'color' => 'secondary', 'icon' => 'fa-user'];
                                ?>
                                <?php if($roleName): ?>
                                    <span class="badge bg-<?php echo e($roleInfo['color']); ?>">
                                        <i class="fas <?php echo e($roleInfo['icon']); ?> me-1"></i>
                                        <?php echo e($roleInfo['name']); ?>

                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Aucun rôle</span>
                                <?php endif; ?>
                            </p>
                            <p><strong>Créé le :</strong> <?php echo e($user->created_at->format('d/m/Y à H:i')); ?></p>
                            <p><strong>Dernière modification :</strong> <?php echo e($user->updated_at->format('d/m/Y à H:i')); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            
            <?php if(auth()->id() !== $user->id && $user->email !== 'admin@btl.ma'): ?>
                <div class="card shadow-sm mt-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex gap-3">
                            
                            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#resetPasswordModal">
                                <i class="fas fa-key me-2"></i>Réinitialiser le mot de passe
                            </button>

                            
                            <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                                <i class="fas fa-trash me-2"></i>Supprimer l'utilisateur
                            </button>
                            <form id="delete-form" action="<?php echo e(route('admin.users.destroy', $user)); ?>" method="POST" class="d-none">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>


<div class="modal fade" id="resetPasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo e(route('admin.users.reset-password', $user)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <?php echo method_field('POST'); ?>
                <div class="modal-header">
                    <h5 class="modal-title">Réinitialiser le mot de passe</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Utilisateur : <strong><?php echo e($user->name); ?></strong></p>
                    <div class="mb-3">
                        <label for="password" class="form-label">Nouveau mot de passe</label>
                        <input type="password" class="form-control" name="password" required minlength="8">
                    </div>
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
                        <input type="password" class="form-control" name="password_confirmation" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-warning">Réinitialiser</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function confirmDelete() {
    if (confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible.')) {
        document.getElementById('delete-form').submit();
    }
}
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/super-admin/users/show.blade.php ENDPATH**/ ?>