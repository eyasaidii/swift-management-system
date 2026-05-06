

<?php $__env->startSection('title', 'Gestion des Permissions - Admin'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-0">
                <i class="fas fa-key text-warning me-2"></i>
                Gestion des Permissions
            </h1>
            <p class="text-muted">Administration des permissions système</p>
        </div>
        <div>
            <a href="<?php echo e(route('admin.permissions.create')); ?>" class="btn btn-success">
                <i class="fas fa-plus me-2"></i>Nouvelle permission
            </a>
            <a href="<?php echo e(route('admin.permissions.export')); ?>" class="btn btn-info">
                <i class="fas fa-download me-2"></i>Exporter
            </a>
            <a href="<?php echo e(route('admin.dashboard')); ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Retour
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
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-user-tag me-2 text-primary"></i>Assigner des permissions à un rôle</h5>
        </div>
        <div class="card-body">
            <form action="<?php echo e(route('admin.permissions.assign-to-role')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Sélectionner un rôle</label>
                        <select name="role_id" id="roleSelect" class="form-select" required>
                            <option value="">Choisir un rôle...</option>
                            <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($role->id); ?>"><?php echo e($role->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-8 mb-3">
                        <label class="form-label">Permissions à assigner</label>
                        <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                            <?php $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="form-check">
                                    <input class="form-check-input permission-checkbox" type="checkbox" 
                                           name="permissions[]" value="<?php echo e($permission->id); ?>" 
                                           id="perm_<?php echo e($permission->id); ?>">
                                    <label class="form-check-label" for="perm_<?php echo e($permission->id); ?>">
                                        <?php echo e($permission->name); ?>

                                    </label>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Assigner les permissions
                </button>
            </form>
        </div>
    </div>

    
    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-list me-2 text-primary"></i>Liste des permissions</h5>
            <span class="badge bg-info"><?php echo e($permissions->total()); ?> permissions</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>ID</th>
                            <th>Nom de la permission</th>
                            <th>Guard</th>
                            <th>Rôles associés</th>
                            <th>Date création</th>
                            <th width="200">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>#<?php echo e($permission->id); ?></td>
                            <td>
                                <span class="fw-bold"><?php echo e($permission->name); ?></span>
                            </td>
                            <td>
                                <span class="badge bg-secondary"><?php echo e($permission->guard_name); ?></span>
                            </td>
                            <td>
                                <?php $__currentLoopData = $permission->roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <span class="badge bg-info"><?php echo e($role->name); ?></span>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </td>
                            <td><?php echo e($permission->created_at->format('d/m/Y H:i')); ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?php echo e(route('admin.permissions.edit', $permission->id)); ?>" 
                                       class="btn btn-outline-primary" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?php echo e(route('admin.permissions.duplicate', $permission->id)); ?>" 
                                       class="btn btn-outline-info" title="Dupliquer">
                                        <i class="fas fa-copy"></i>
                                    </a>
                                    <?php if(!in_array($permission->name, ['view-dashboard', 'view-users', 'view-swift-messages'])): ?>
                                    <form action="<?php echo e(route('admin.permissions.destroy', $permission->id)); ?>" 
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('Supprimer cette permission ?')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-outline-danger" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <i class="fas fa-key fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Aucune permission trouvée</p>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if(method_exists($permissions, 'links')): ?>
        <div class="card-footer">
            <?php echo e($permissions->links()); ?>

        </div>
        <?php endif; ?>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
    // Charger les permissions du rôle sélectionné
    document.getElementById('roleSelect').addEventListener('change', function() {
        const roleId = this.value;
        if (!roleId) return;
        
        fetch(`/admin/permissions/role-permissions?role_id=${roleId}`)
            .then(response => response.json())
            .then(data => {
                document.querySelectorAll('.permission-checkbox').forEach(cb => {
                    cb.checked = data.includes(parseInt(cb.value));
                });
            });
    });
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/super-admin/permissions/index.blade.php ENDPATH**/ ?>