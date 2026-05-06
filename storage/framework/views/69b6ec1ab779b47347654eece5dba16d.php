


<?php $__env->startSection('title', 'Export Center - BTL Bank'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-0 fw-bold">
                <i class="fas fa-download text-success me-2"></i>Export Center
            </h1>
            <p class="text-muted mb-0">Exportez vos messages SWIFT en Excel ou CSV</p>
        </div>
        <a href="<?php echo e(url()->previous()); ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Retour
        </a>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">

        
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-filter me-2 text-primary"></i>Paramètres d'export
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="<?php echo e(route('swift.export')); ?>">

                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">Format d'export</label>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="card border-2 border-success h-100" id="card-xlsx"
                                         style="cursor:pointer" onclick="selectFormat('xlsx')">
                                        <div class="card-body text-center py-3">
                                            <i class="fas fa-file-excel fa-2x text-success mb-2"></i>
                                            <h6 class="fw-bold mb-1">Excel (.xlsx)</h6>
                                            <small class="text-muted">
                                                Mise en forme couleurs BTL,
                                                onglet résumé, filtres automatiques
                                            </small>
                                            <div class="mt-2">
                                                <span class="badge bg-success">Recommandé</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border h-100" id="card-csv"
                                         style="cursor:pointer" onclick="selectFormat('csv')">
                                        <div class="card-body text-center py-3">
                                            <i class="fas fa-file-csv fa-2x text-secondary mb-2"></i>
                                            <h6 class="fw-bold mb-1">CSV (.csv)</h6>
                                            <small class="text-muted">
                                                Format universel,
                                                compatible tous tableurs
                                            </small>
                                            <div class="mt-2">
                                                <span class="badge bg-secondary">Basique</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="format" id="format-input" value="xlsx">
                        </div>

                        <hr>

                        <div class="mb-3">
                            <small class="text-muted">Filtres avancés masqués — l'export concernera l'ensemble des messages accessibles selon votre rôle.</small>
                        </div>

                        <button type="submit" class="btn btn-success btn-lg w-100">
                            <i class="fas fa-download me-2"></i>
                            Générer et télécharger l'export
                        </button>
                    </form>
                </div>
            </div>
        </div>

        
        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2 text-secondary"></i>Historique exports
                    </h5>
                </div>
                <div class="card-body p-0">
                    <?php if(isset($exportJobs) && $exportJobs->count() > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php $__currentLoopData = $exportJobs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $job): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="list-group-item px-3 py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="badge <?php echo e($job->format === 'xlsx' ? 'bg-success' : 'bg-secondary'); ?> me-2">
                                                <?php echo e(strtoupper($job->format)); ?>

                                            </span>
                                            <small class="text-muted">
                                                <?php echo e(\Carbon\Carbon::parse($job->date_demande)->format('d/m/Y H:i')); ?>

                                            </small>
                                        </div>
                                        <span class="badge <?php echo e($job->statut === 'completed' ? 'bg-success' : 'bg-warning text-dark'); ?>">
                                            <?php echo e($job->statut === 'completed' ? 'OK' : $job->statut); ?>

                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-2x mb-3 d-block opacity-25"></i>
                            <p class="small">Aucun export réalisé</p>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if(isset($exportJobs) && $exportJobs->count() > 0): ?>
                    <div class="card-footer bg-white text-center">
                        <small class="text-muted">
                            <?php echo e($exportJobs->count()); ?> export(s) réalisé(s)
                        </small>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<script>
function selectFormat(format) {
    document.getElementById('format-input').value = format;

    // Styles cartes
    const xlsx = document.getElementById('card-xlsx');
    const csv  = document.getElementById('card-csv');

    if (format === 'xlsx') {
        xlsx.classList.add('border-success', 'border-2');
        xlsx.classList.remove('border');
        csv.classList.remove('border-success', 'border-2');
        csv.classList.add('border');
    } else {
        csv.classList.add('border-success', 'border-2');
        csv.classList.remove('border');
        xlsx.classList.remove('border-success', 'border-2');
        xlsx.classList.add('border');
    }
}
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/swift/export-center.blade.php ENDPATH**/ ?>