

<?php $__env->startSection('title', 'Émettre un message SWIFT'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Émettre un message SWIFT</h1>
                <a href="<?php echo e(route('swift.index')); ?>" class="btn btn-outline-secondary">Retour</a>
            </div>

            <!-- Affichage des erreurs de validation -->
            <?php if($errors->any()): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="<?php echo e(route('swift.store')); ?>" id="swiftForm">
                        <?php echo csrf_field(); ?>
                        <!-- Type de message (toujours visible) -->
                        <div class="mb-3">
                            <label class="form-label">Type de message <span class="text-danger">*</span></label>
                            <select name="type_message" id="type_message" class="form-select" required>
                                <option value="">Sélectionnez un type</option>
                                <?php $__currentLoopData = $types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($key); ?>"><?php echo e($key); ?> - <?php echo e($label); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <!-- Conteneur pour les champs spécifiques (caché initialement) -->
                        <div id="specificFields" style="display: none;"></div>

                        <!-- Bouton de soumission (caché initialement) -->
                        <button type="submit" id="submitBtn" class="btn btn-success mt-3" style="display: none;">Émettre</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('type_message').addEventListener('change', function() {
        const type = this.value;
        const container = document.getElementById('specificFields');
        const submitBtn = document.getElementById('submitBtn');

        if (!type) {
            container.style.display = 'none';
            submitBtn.style.display = 'none';
            container.innerHTML = '';
            return;
        }

        // Charger les champs spécifiques via AJAX
        fetch(`/swift/fields/${type}`)
            .then(response => response.json())
            .then(fields => {
                if (Object.keys(fields).length === 0) {
                    container.style.display = 'none';
                    submitBtn.style.display = 'none';
                    return;
                }

                let html = '<h5 class="mt-4">Champs spécifiques ' + type + '</h5><div class="row">';

                for (let [tag, config] of Object.entries(fields)) {
                    let fieldHtml = '';
                    const required = config.required ? 'required' : '';
                    const name = `details[${tag}]`;

                    switch (config.type) {
                        case 'textarea':
                            fieldHtml = `<textarea name="${name}" class="form-control" ${required}></textarea>`;
                            break;
                        case 'select':
                            let options = '<option value="">Sélectionner</option>';
                            for (let [val, label] of Object.entries(config.options)) {
                                options += `<option value="${val}">${label}</option>`;
                            }
                            fieldHtml = `<select name="${name}" class="form-control" ${required}>${options}</select>`;
                            break;
                        case 'date':
                            fieldHtml = `<input type="date" name="${name}" class="form-control" ${required}>`;
                            break;
                        default:
                            fieldHtml = `<input type="text" name="${name}" class="form-control" value="" ${required} maxlength="${config.maxlength || ''}" placeholder="${config.placeholder || ''}">`;
                    }

                    html += `
                        <div class="col-md-6 mb-3">
                            <label>${config.label}</label>
                            ${fieldHtml}
                            ${config.help ? `<small class="text-muted">${config.help}</small>` : ''}
                        </div>
                    `;
                }
                html += '</div>';
                container.innerHTML = html;
                container.style.display = 'block';
                submitBtn.style.display = 'inline-block';
            })
            .catch(error => console.error('Erreur:', error));
    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\eya saidi\Desktop\btl-swift-platform\btl-swift-platform-main\btl-swift-platform-main\resources\views/swift/create.blade.php ENDPATH**/ ?>