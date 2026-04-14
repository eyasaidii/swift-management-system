<?php
    $user = $user ?? null;
    $currentRole = $user ? $user->getRoleNames()->first() : old('role');
    $roles = App\Models\User::getBankRoles();
?>



<div class="row">
    <div class="col-md-6 mb-3">
        <label for="name" class="form-label">
            <i class="fas fa-user me-2"></i>Nom complet <span class="text-danger">*</span>
        </label>
        <input type="text" 
               class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
               id="name" 
               name="name" 
               value="<?php echo e(old('name', $user->name ?? '')); ?>" 
               placeholder="Jean Dupont"
               required>
        <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <div class="invalid-feedback"><?php echo e($message); ?></div>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div class="col-md-6 mb-3">
        <label for="email" class="form-label">
            <i class="fas fa-envelope me-2"></i>Email <span class="text-danger">*</span>
        </label>
        <input type="email" 
               class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
               id="email" 
               name="email" 
               value="<?php echo e(old('email', $user->email ?? '')); ?>" 
               placeholder="utilisateur@btl.ma"
               required>
        <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <div class="invalid-feedback"><?php echo e($message); ?></div>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="telephone" class="form-label">
            <i class="fas fa-phone me-2"></i>Téléphone
        </label>
        <input type="tel" 
               class="form-control <?php $__errorArgs = ['telephone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
               id="telephone" 
               name="telephone" 
               value="<?php echo e(old('telephone', $user->telephone ?? '')); ?>" 
               placeholder="+212 6 12 34 56 78">
        <?php $__errorArgs = ['telephone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <div class="invalid-feedback"><?php echo e($message); ?></div>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>
</div>


<div class="mb-4">
    <label class="form-label">
        <i class="fas fa-user-tag me-2"></i>Rôle <span class="text-danger">*</span>
    </label>
    <div class="border rounded p-3">
        <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="form-check mb-2">
            <input class="form-check-input" 
                   type="radio" 
                   name="role" 
                   id="role_<?php echo e($key); ?>"
                   value="<?php echo e($key); ?>"
                   <?php echo e(old('role', $currentRole ?? '') == $key ? 'checked' : ''); ?>

                   required>
            <label class="form-check-label" for="role_<?php echo e($key); ?>">
                <span class="badge bg-<?php echo e($role['color']); ?>">
                    <i class="fas <?php echo e($role['icon']); ?> me-1"></i>
                    <?php echo e($role['name']); ?>

                </span>
                <small class="text-muted d-block mt-1"><?php echo e($role['description']); ?></small>
            </label>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <?php $__errorArgs = ['role'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
        <div class="text-danger small mt-2"><?php echo e($message); ?></div>
    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
</div>


<?php if(!isset($user)): ?>
<div class="row">
    <div class="col-md-6 mb-3">
        <label for="password" class="form-label">
            <i class="fas fa-lock me-2"></i>Mot de passe <span class="text-danger">*</span>
        </label>
        <input type="password" 
               class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
               id="password" 
               name="password" 
               required>
        <div class="form-text">Minimum 8 caractères</div>
        <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <div class="invalid-feedback"><?php echo e($message); ?></div>
        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>

    <div class="col-md-6 mb-3">
        <label for="password_confirmation" class="form-label">
            <i class="fas fa-lock me-2"></i>Confirmer le mot de passe <span class="text-danger">*</span>
        </label>
        <input type="password" 
               class="form-control" 
               id="password_confirmation" 
               name="password_confirmation" 
               required>
    </div>
</div>
<?php endif; ?>




<div class="d-flex justify-content-end gap-2 mt-4">
    <a href="<?php echo e(route('admin.users.index')); ?>" class="btn btn-secondary">
        <i class="fas fa-times me-2"></i>Annuler
    </a>
    <button type="submit" class="btn btn-primary">
        <i class="fas <?php echo e($buttonIcon ?? 'fa-save'); ?> me-2"></i><?php echo e($submitButton ?? 'Enregistrer'); ?>

    </button>
</div>

<style>
.bg-purple {
    background-color: #6f42c1 !important;
}
.form-check-input:checked {
    background-color: var(--bs-primary);
    border-color: var(--bs-primary);
}
</style><?php /**PATH /var/www/resources/views/super-admin/users/_form.blade.php ENDPATH**/ ?>