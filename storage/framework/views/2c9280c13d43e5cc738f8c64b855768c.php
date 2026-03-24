

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-0">
                <i class="fas fa-users me-2"></i>Gestion des utilisateurs
            </h1>
            <p class="text-muted">Administration des comptes utilisateurs BTL Bank</p>
        </div>
        <div>
            <a href="<?php echo e(route('admin.users.create')); ?>" class="btn btn-success">
                <i class="fas fa-plus me-2"></i>Nouvel utilisateur
            </a>
            <a href="<?php echo e(route('admin.users.export')); ?>" class="btn btn-outline-secondary">
                <i class="fas fa-download me-2"></i>Exporter
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

    
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('admin.users.index')); ?>" class="row g-3">
                <div class="col-md-8">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Rechercher par nom ou email..." value="<?php echo e(request('search')); ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <select name="role" class="form-select">
                        <option value="all">Tous les rôles</option>
                        <?php $__currentLoopData = App\Models\User::getBankRoles(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($key); ?>" <?php echo e(request('role') == $key ? 'selected' : ''); ?>>
                                <?php echo e($role['name']); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                </div>
            </form>
        </div>
    </div>

    
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Total utilisateurs</h6>
                            <h2 class="mt-2 mb-0"><?php echo e($stats['total'] ?? 0); ?></h2>
                        </div>
                        <i class="fas fa-users fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-3">Répartition par rôle</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <?php $__currentLoopData = $stats['by_role'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $roles = App\Models\User::getBankRoles();
                                $roleInfo = $roles[$role] ?? ['name' => $role, 'color' => 'secondary'];
                            ?>
                            <span class="badge bg-<?php echo e($roleInfo['color']); ?> p-2">
                                <?php echo e($roleInfo['name']); ?>: <?php echo e($count); ?>

                            </span>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Utilisateur</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Créé le</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle bg-primary text-white me-3">
                                        <?php echo e(strtoupper(substr($user->name, 0, 1))); ?>

                                    </div>
                                    <div>
                                        <div class="fw-bold"><?php echo e($user->name); ?></div>
                                        <small class="text-muted">ID: #<?php echo e($user->id); ?></small>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo e($user->email); ?></td>
                            <td>
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
                            </td>
                            <td><?php echo e($user->created_at->format('d/m/Y')); ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?php echo e(route('admin.users.show', $user)); ?>" class="btn btn-outline-info" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    
                                    <a href="<?php echo e(route('admin.users.modifier', $user)); ?>" class="btn btn-outline-primary" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    <?php if($user->email !== 'admin@btl.ma'): ?>
                                    <button type="button" class="btn btn-outline-warning" title="Réinitialiser mot de passe"
                                            data-bs-toggle="modal" data-bs-target="#resetPasswordModal<?php echo e($user->id); ?>">
                                        <i class="fas fa-key"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" title="Supprimer"
                                            onclick="confirmDelete(<?php echo e($user->id); ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <form id="delete-form-<?php echo e($user->id); ?>" action="<?php echo e(route('admin.users.destroy', $user)); ?>" method="POST" class="d-none">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Aucun utilisateur trouvé</p>
                                <a href="<?php echo e(route('admin.users.create')); ?>" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Créer le premier utilisateur
                                </a>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if(method_exists($users, 'links')): ?>
        <div class="card-footer">
            <?php echo e($users->links()); ?>

        </div>
        <?php endif; ?>
    </div>
</div>


<?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<?php if($user->email !== 'admin@btl.ma'): ?>
<div class="modal fade" id="resetPasswordModal<?php echo e($user->id); ?>" tabindex="-1">
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
                    <p>Utilisateur: <strong><?php echo e($user->name); ?></strong></p>
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
<?php endif; ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<style>
.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 18px;
}
.opacity-50 {
    opacity: 0.5;
}
</style>

<script>
function confirmDelete(userId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) {
        document.getElementById('delete-form-' + userId).submit();
    }
}
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\eya saidi\Desktop\btl-swift-platform\btl-swift-platform-main\btl-swift-platform-main\resources\views/admin/users/index.blade.php ENDPATH**/ ?>