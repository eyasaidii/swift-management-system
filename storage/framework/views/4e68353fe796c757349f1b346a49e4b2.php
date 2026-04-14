

<?php $__env->startSection('title', 'Importer des messages SWIFT - BTL Bank'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h1 class="h2 mb-0 fw-bold">
                <i class="fas fa-file-import text-warning me-2"></i>
                Importer des messages SWIFT
            </h1>
            <p class="text-muted">
                Chargez un fichier XML ou TXT.
            </p>
        </div>
        <a href="<?php echo e(route('swift.index')); ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Retour à la liste
        </a>
    </div>

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

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-upload me-2 text-warning"></i>Importer un message SWIFT</h5>
        </div>
        <div class="card-body">
            <form action="<?php echo e(route('swift.import')); ?>" method="POST" enctype="multipart/form-data" id="importForm">
                <?php echo csrf_field(); ?>

                <!-- Champ fichier unique -->
                <div class="mb-4">
                    <label for="file" class="form-label fw-bold">Fichier XML ou TXT</label>
                    <div class="border rounded p-5 text-center bg-light" id="dropzone">
                        <i class="fas fa-cloud-upload-alt fa-4x text-muted mb-3"></i>
                        <p class="mb-2" id="file-label">Glissez-déposez ou cliquez pour sélectionner</p>
                        <input type="file" name="file" id="file" class="form-control d-none" accept=".xml,.txt" required>
                        <button type="button" class="btn btn-outline-primary mt-2" id="fileButton">
                            <i class="fas fa-folder-open me-2"></i>Choisir un fichier
                        </button>
                        <div id="file-info" class="mt-3 small text-muted" style="display: none;"></div>
                    </div>
                    <?php $__errorArgs = ['file'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="text-danger mt-2"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    <small class="form-text text-muted mt-2">
                        Formats acceptés : .xml (ISO 20022), .txt (MT legacy) — Max 10 Mo
                    </small>
                </div>

                <div class="d-flex justify-content-end gap-3">
                    <a href="<?php echo e(route('swift.index')); ?>" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i>Annuler
                    </a>
                    <button type="submit" class="btn btn-warning" id="submitBtn">
                        <i class="fas fa-file-import me-2"></i>Importer et traiter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Infos -->
    <div class="card mt-4 shadow-sm">
        <div class="card-header bg-light">
            <h6 class="mb-0"><i class="fas fa-info-circle me-2 text-info"></i>Après l'import</h6>
        </div>
        <div class="card-body">
            <ul class="mb-0">
                <li>Fichier envoyé en traitement automatique</li>
                <li>Conversion MX → MT si applicable</li>
                <li>Message enregistré (pending / processed)</li>
                <li>Apparaît dans la liste RECU une fois traité</li>
            </ul>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.getElementById('file');
        const fileButton = document.getElementById('fileButton');
        const fileLabel = document.getElementById('file-label');
        const fileInfo = document.getElementById('file-info');
        const submitBtn = document.getElementById('submitBtn');
        const form = document.getElementById('importForm');

        // Simuler un clic sur le bouton pour ouvrir la boîte de dialogue
        fileButton.addEventListener('click', function() {
            fileInput.click();
        });

        // Afficher le nom du fichier sélectionné
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Mettre à jour le texte
                fileLabel.textContent = 'Fichier sélectionné :';
                fileInfo.style.display = 'block';
                fileInfo.innerHTML = `
                    <i class="fas fa-file me-2"></i>${file.name} (${(file.size / 1024).toFixed(2)} Ko)
                `;
                // Optionnel : changer le style du bouton
                fileButton.innerHTML = `<i class="fas fa-redo me-2"></i>Choisir un autre fichier`;
            } else {
                fileLabel.textContent = 'Glissez-déposez ou cliquez pour sélectionner';
                fileInfo.style.display = 'none';
                fileButton.innerHTML = `<i class="fas fa-folder-open me-2"></i>Choisir un fichier`;
            }
        });

        // Désactiver le bouton pendant la soumission (optionnel)
        form.addEventListener('submit', function() {
            submitBtn.disabled = true;
            submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Import en cours...`;
        });

        // Faire disparaître les alertes après 5 secondes
        setTimeout(function() {
            document.querySelectorAll('.alert').forEach(function(alert) {
                let bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                bsAlert.close();
            });
        }, 5000);
    });
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/swift/import.blade.php ENDPATH**/ ?>