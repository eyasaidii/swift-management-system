

<?php $__env->startSection('title', 'Émettre un message SWIFT'); ?>

<?php $__env->startPush('styles'); ?>
<style>
/* ── Page header ── */
.swift-create-header {
    background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 60%, #1a4a3a 100%);
    border-radius: 14px;
    padding: 1.4rem 1.8rem;
    margin-bottom: 1.5rem;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
}
.swift-create-header h1 {
    font-size: 1.3rem;
    font-weight: 700;
    margin: 0;
    display: flex;
    align-items: center;
    gap: .6rem;
}
.swift-create-header p {
    margin: .2rem 0 0;
    font-size: .78rem;
    opacity: .65;
}
.btn-back {
    background: rgba(255,255,255,.12);
    border: 1px solid rgba(255,255,255,.22);
    color: #fff;
    border-radius: 8px;
    padding: .4rem 1rem;
    font-size: .8rem;
    text-decoration: none;
    transition: background .2s;
    white-space: nowrap;
}
.btn-back:hover { background: rgba(255,255,255,.22); color: #fff; }

/* ── Main card ── */
.swift-form-card {
    background: #fff;
    border: none;
    border-radius: 14px;
    box-shadow: 0 2px 16px rgba(0,0,0,.08);
    overflow: hidden;
}
.swift-form-card .card-header-section {
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    padding: 1rem 1.5rem;
    display: flex;
    align-items: center;
    gap: .6rem;
}
.swift-form-card .card-header-section .icon-wrap {
    width: 36px;
    height: 36px;
    background: linear-gradient(135deg, #134e4a, #0f766e);
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: .9rem;
    flex-shrink: 0;
}
.swift-form-card .card-header-section .title {
    font-size: .85rem;
    font-weight: 700;
    color: #1e293b;
}
.swift-form-card .card-header-section .subtitle {
    font-size: .7rem;
    color: #94a3b8;
}
.swift-form-card .card-body-section {
    padding: 1.6rem;
}

/* ── Champ type de message ── */
.type-select-wrap {
    background: #f8fafc;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    padding: 1.2rem 1.4rem;
    margin-bottom: .5rem;
    transition: border-color .2s;
}
.type-select-wrap:focus-within {
    border-color: #0f766e;
    background: #f0fdfa;
}
.type-select-wrap label {
    font-size: .78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: #475569;
    margin-bottom: .5rem;
    display: block;
}
.type-select-wrap select {
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: .88rem;
    color: #1e293b;
    background: #fff;
    padding: .55rem .8rem;
    width: 100%;
    transition: border-color .2s, box-shadow .2s;
}
.type-select-wrap select:focus {
    border-color: #0f766e;
    box-shadow: 0 0 0 3px rgba(15,118,110,.15);
    outline: none;
}

/* ── Divider avec badge ── */
.fields-divider {
    display: flex;
    align-items: center;
    gap: .8rem;
    margin: 1.4rem 0 1rem;
}
.fields-divider hr { flex: 1; border-color: #e2e8f0; margin: 0; }
.fields-divider .badge-type {
    background: linear-gradient(135deg, #134e4a, #0f766e);
    color: #fff;
    border-radius: 20px;
    padding: .25rem .9rem;
    font-size: .72rem;
    font-weight: 700;
    white-space: nowrap;
}

/* ── Champs dynamiques ── */
#specificFields .form-field-wrap {
    margin-bottom: 1rem;
}
#specificFields label {
    font-size: .77rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: .35rem;
    display: block;
}
#specificFields .field-required-dot::after {
    content: ' *';
    color: #ef4444;
}
#specificFields input,
#specificFields select,
#specificFields textarea {
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: .85rem;
    color: #1e293b;
    background: #fff;
    padding: .5rem .75rem;
    width: 100%;
    transition: border-color .2s, box-shadow .2s;
}
#specificFields input:focus,
#specificFields select:focus,
#specificFields textarea:focus {
    border-color: #0f766e;
    box-shadow: 0 0 0 3px rgba(15,118,110,.12);
    outline: none;
}
#specificFields textarea { resize: vertical; min-height: 80px; }
#specificFields small.help-text {
    font-size: .68rem;
    color: #6b7280;
    margin-top: .25rem;
    display: block;
}

/* ── Bouton submit ── */
.btn-emit {
    background: linear-gradient(135deg, #059669, #10b981);
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: .7rem 2rem;
    font-size: .88rem;
    font-weight: 700;
    letter-spacing: .02em;
    cursor: pointer;
    transition: opacity .2s, transform .1s;
    display: inline-flex;
    align-items: center;
    gap: .5rem;
}
.btn-emit:hover { opacity: .92; transform: translateY(-1px); }

/* ── Alert erreurs ── */
.alert-swift-error {
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-left: 4px solid #ef4444;
    border-radius: 10px;
    padding: .9rem 1.1rem;
    margin-bottom: 1.2rem;
    color: #b91c1c;
    font-size: .82rem;
}
.alert-swift-error ul { margin: .3rem 0 0 1rem; padding: 0; }

/* ── Placeholder état vide ── */
.empty-state {
    text-align: center;
    padding: 2.5rem 1rem;
    color: #94a3b8;
}
.empty-state i { font-size: 2.5rem; margin-bottom: .8rem; opacity: .4; }
.empty-state p { font-size: .82rem; margin: 0; }

/* ── Footer carte ── */
.form-footer {
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    padding: 1rem 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: .8rem;
}
.form-footer .info-text {
    font-size: .72rem;
    color: #94a3b8;
    display: flex;
    align-items: center;
    gap: .4rem;
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4" style="max-width:960px;">

    
    <div class="swift-create-header">
        <div>
            <h1>
                <i class="fas fa-paper-plane" style="color:#60a5fa;"></i>
                Émettre un message SWIFT
            </h1>
            <p>Tunisian Libyan Bank — Réseau SWIFT International</p>
        </div>
        <a href="<?php echo e(route('swift.index')); ?>" class="btn-back">
            <i class="fas fa-arrow-left me-1"></i>Retour
        </a>
    </div>

    
    <?php if($errors->any()): ?>
    <div class="alert-swift-error">
        <div style="font-weight:700;margin-bottom:.3rem;">
            <i class="fas fa-exclamation-circle me-1"></i>Erreurs de validation
        </div>
        <ul>
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($error); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
    <?php endif; ?>

    
    <div class="swift-form-card">
        
        <div class="card-header-section">
            <div class="icon-wrap"><i class="fas fa-file-alt"></i></div>
            <div>
                <div class="title">Nouveau message</div>
                <div class="subtitle">Sélectionnez le type puis renseignez les champs requis</div>
            </div>
        </div>

        <div class="card-body-section">
            <form method="POST" action="<?php echo e(route('swift.store')); ?>" id="swiftForm">
                <?php echo csrf_field(); ?>

                
                <div class="type-select-wrap">
                    <label for="type_message">
                        <i class="fas fa-tag me-1" style="color:#0f766e;"></i>
                        Type de message <span style="color:#ef4444;">*</span>
                    </label>
                    <select name="type_message" id="type_message" required>
                        <option value="">— Sélectionnez un type de message —</option>
                        <?php $__currentLoopData = $types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($key); ?>"><?php echo e($key); ?> — <?php echo e($label); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                
                <div id="specificFields" style="display:none;"></div>

                
                <div id="emptyState" class="empty-state">
                    <i class="fas fa-hand-point-up d-block"></i>
                    <p>Sélectionnez un type de message pour afficher les champs</p>
                </div>

            </form>
        </div>

        
        <div class="form-footer">
            <div class="info-text">
                <i class="fas fa-shield-alt" style="color:#10b981;"></i>
                Message sécurisé via réseau SWIFT · Analyse IA automatique à l'émission
            </div>
            <button type="submit" form="swiftForm" id="submitBtn" class="btn-emit" style="display:none;">
                <i class="fas fa-paper-plane"></i>
                Émettre le message
            </button>
        </div>
    </div>

</div>

<script>
document.getElementById('type_message').addEventListener('change', function () {
    const type      = this.value;
    const container = document.getElementById('specificFields');
    const submitBtn = document.getElementById('submitBtn');
    const emptyState = document.getElementById('emptyState');

    if (!type) {
        container.style.display = 'none';
        container.innerHTML = '';
        submitBtn.style.display = 'none';
        emptyState.style.display = 'block';
        return;
    }

    // Indicateur de chargement
    emptyState.style.display = 'none';
    container.style.display  = 'block';
    container.innerHTML = `
        <div style="text-align:center;padding:2rem;color:#94a3b8;">
            <i class="fas fa-spinner fa-spin fa-2x mb-2 d-block" style="opacity:.5;"></i>
            <span style="font-size:.8rem;">Chargement des champs…</span>
        </div>`;
    submitBtn.style.display = 'none';

    fetch(`/swift/fields/${encodeURIComponent(type)}`)
        .then(r => r.json())
        .then(fields => {
            if (!fields || Object.keys(fields).length === 0) {
                container.innerHTML = `
                    <div style="text-align:center;padding:2rem;color:#94a3b8;font-size:.82rem;">
                        <i class="fas fa-info-circle me-1"></i>Aucun champ spécifique pour ce type.
                    </div>`;
                submitBtn.style.display = 'inline-flex';
                return;
            }

            // Divider avec badge type
            let html = `
                <div class="fields-divider">
                    <hr>
                    <span class="badge-type"><i class="fas fa-layer-group me-1"></i>Champs ${type}</span>
                    <hr>
                </div>
                <div class="row">`;

            for (let [tag, cfg] of Object.entries(fields)) {
                const req  = cfg.required ? 'required' : '';
                const name = `details[${tag}]`;
                const reqClass = cfg.required ? 'field-required-dot' : '';
                let input = '';

                switch (cfg.type) {
                    case 'textarea':
                        input = `<textarea name="${name}" class="form-control" rows="3" ${req} placeholder="${cfg.placeholder || ''}"></textarea>`;
                        break;
                    case 'select':
                        let opts = '<option value="">— Sélectionner —</option>';
                        for (let [v, l] of Object.entries(cfg.options || {})) {
                            opts += `<option value="${v}">${l}</option>`;
                        }
                        input = `<select name="${name}" ${req}>${opts}</select>`;
                        break;
                    case 'date':
                        input = `<input type="date" name="${name}" ${req}>`;
                        break;
                    default:
                        input = `<input type="text" name="${name}" ${req}
                                    maxlength="${cfg.maxlength || 255}"
                                    placeholder="${cfg.placeholder || ''}">`;
                }

                html += `
                    <div class="col-md-6">
                        <div class="form-field-wrap">
                            <label class="${reqClass}">${cfg.label}</label>
                            ${input}
                            ${cfg.help ? `<small class="help-text"><i class="fas fa-info-circle me-1" style="color:#0f766e;"></i>${cfg.help}</small>` : ''}
                        </div>
                    </div>`;
            }

            html += '</div>';
            container.innerHTML = html;
            container.style.display = 'block';
            submitBtn.style.display  = 'inline-flex';
        })
        .catch(() => {
            container.innerHTML = `
                <div style="text-align:center;padding:1.5rem;color:#ef4444;font-size:.82rem;">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    Erreur lors du chargement des champs. Veuillez réessayer.
                </div>`;
        });
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/swift/create.blade.php ENDPATH**/ ?>