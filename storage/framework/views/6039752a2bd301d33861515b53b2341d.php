
<?php $__env->startSection('title', 'Dashboard Administrateur - BTL Bank'); ?>
<?php $__env->startSection('page-title', 'Administration'); ?>
<?php $__env->startPush('styles'); ?>
<style>
.dash-header{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem;margin-bottom:1.5rem;}
.dash-header-left h1{font-size:1.3rem;font-weight:700;color:#111827;margin:0;line-height:1.3;display:flex;align-items:center;gap:.5rem;}
.dash-header-left p{font-size:.78rem;color:#6b7280;margin:.2rem 0 0;}
.dash-btns{display:flex;gap:.4rem;flex-wrap:wrap;align-items:center;}
.dbtn{font-size:.78rem;font-weight:600;padding:.38rem .9rem;border-radius:8px;display:inline-flex;align-items:center;gap:.38rem;white-space:nowrap;text-decoration:none;border:none;transition:filter .15s;cursor:pointer;}
.dbtn:hover{filter:brightness(.9);text-decoration:none;}
.dbtn-blue{background:#3b82f6;color:#fff;}
.dbtn-green{background:#10b981;color:#fff;}
.dbtn-amber{background:#f59e0b;color:#fff;}
.dbtn-red{background:#ef4444;color:#fff;}
.dbtn-dark{background:#1f2937;color:#fff;}
.dbtn-gray{background:#f3f4f6;color:#374151;border:1px solid #e5e7eb!important;}
.kpi-card{border-radius:12px;padding:1.25rem 1.5rem;position:relative;overflow:hidden;box-shadow:0 4px 15px rgba(0,0,0,.13);height:100%;}
.kpi-val{font-size:2.1rem;font-weight:800;color:#fff;line-height:1.05;position:relative;z-index:1;}
.kpi-lbl{font-size:.72rem;font-weight:600;color:rgba(255,255,255,.82);margin-top:.3rem;text-transform:uppercase;letter-spacing:.05em;position:relative;z-index:1;}
.kpi-sub{font-size:.65rem;color:rgba(255,255,255,.65);margin-top:.2rem;position:relative;z-index:1;}
.kpi-ico{position:absolute;right:.8rem;top:50%;transform:translateY(-50%);font-size:3.2rem;color:rgba(255,255,255,.18);pointer-events:none;}
.kpi-blue{background:linear-gradient(135deg,#3b82f6,#1d4ed8);}
.kpi-cyan{background:linear-gradient(135deg,#06b6d4,#0369a1);}
.kpi-green{background:linear-gradient(135deg,#10b981,#047857);}
.kpi-amber{background:linear-gradient(135deg,#f59e0b,#b45309);}
.kpi-red{background:linear-gradient(135deg,#ef4444,#b91c1c);}
.kpi-purple{background:linear-gradient(135deg,#8b5cf6,#6d28d9);}
.filt-card{background:#fff;border-radius:12px;padding:1.1rem 1.25rem;box-shadow:0 1px 4px rgba(0,0,0,.06);margin-bottom:1.5rem;border:1px solid #f3f4f6;}
.filt-title{font-size:.82rem;font-weight:700;color:#111827;margin-bottom:.8rem;display:flex;align-items:center;gap:.5rem;}
.tbl-card{background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.06);border:1px solid #f3f4f6;}
.tbl-card-hd{display:flex;align-items:center;justify-content:space-between;padding:.9rem 1.1rem;border-bottom:1px solid #f3f4f6;}
.tbl-card-title{font-size:.9rem;font-weight:700;color:#111827;display:flex;align-items:center;gap:.5rem;}
.swift-tbl{width:100%;border-collapse:collapse;}
.swift-tbl thead th{font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;background:#f9fafb;padding:.55rem .8rem;border-bottom:1px solid #e5e7eb;white-space:nowrap;}
.swift-tbl tbody td{padding:.55rem .8rem;font-size:.82rem;color:#111827!important;border-bottom:1px solid #f3f4f6;vertical-align:middle;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:0;}
.swift-tbl tbody td div{color:#111827!important;}
.swift-tbl tbody td span:not(.sbadge):not(.dir-in):not(.dir-out){color:#111827!important;}
.swift-tbl tbody td a.ta-btn{color:revert!important;}
.swift-tbl tbody tr:last-child td{border-bottom:none;}
.swift-tbl tbody tr:hover td{background:#f9fafb;}
.sbadge{display:inline-flex;align-items:center;gap:.25rem;font-size:.68rem;font-weight:600;padding:.22rem .55rem;border-radius:20px;}
.sb-pending{background:#fffbeb;color:#92400e;}
.sb-processed{background:#eff6ff;color:#1e40af;}
.sb-authorized{background:#ecfdf5;color:#065f46;}
.sb-suspended{background:#fef2f2;color:#991b1b;}
.sb-rejected{background:#f3f4f6;color:#6b7280;}
.dir-in{background:#dbeafe;color:#1d4ed8;font-size:.65rem;font-weight:700;padding:.18rem .45rem;border-radius:4px;}
.dir-out{background:#f0fdf4;color:#166534;font-size:.65rem;font-weight:700;padding:.18rem .45rem;border-radius:4px;}
.tbl-actions{display:flex;gap:.25rem;}
.ta-btn{font-size:.7rem;font-weight:600;padding:.25rem .55rem;border-radius:5px;border:1px solid;text-decoration:none;display:inline-flex;align-items:center;gap:.22rem;transition:background .12s;background:transparent;cursor:pointer;white-space:nowrap;}
.ta-view{border-color:#d1d5db;color:#374151;}.ta-view:hover{background:#f3f4f6;}
.ta-pdf{border-color:#fecaca;color:#dc2626;}.ta-pdf:hover{background:#fef2f2;}
.ta-mt{border-color:#bbf7d0;color:#065f46;}.ta-mt:hover{background:#ecfdf5;}
.ta-mx{border-color:#e0e7ff;color:#4338ca;}.ta-mx:hover{background:#eef2ff;}
.ta-ok{border-color:#bbf7d0;color:#065f46;}.ta-ok:hover{background:#ecfdf5;}
.ta-del{border-color:#fecaca;color:#dc2626;}.ta-del:hover{background:#fef2f2;}
.empty-state{text-align:center;padding:2.5rem 1rem;color:#9ca3af;}
.empty-state i{font-size:2rem;margin-bottom:.6rem;display:block;opacity:.25;}
.tbl-footer{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem;padding:.65rem 1rem;border-top:1px solid #f3f4f6;}
.tbl-footer-info{font-size:.72rem;color:#9ca3af;}
.alert-strip{display:flex;align-items:center;gap:.75rem;padding:.55rem 1rem;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;margin-bottom:1rem;}
.alert-strip p{margin:0;font-size:.8rem;color:#991b1b;flex:1;}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div style="max-width:1400px;">

    
    <div class="dash-header">
        <div class="dash-header-left">
            <h1><i class="fas fa-crown" style="color:#ef4444;font-size:1rem;"></i> Administration Système</h1>
            <p>Contrôle total &mdash; Tous les rôles et opérations</p>
        </div>
        <div class="dash-btns">
            <?php
                $criticalCount  = \App\Models\AnomalySwift::where('niveau_risque','HIGH')->whereNull('verifie_par')->count();
                $totalAnomalies = \App\Models\AnomalySwift::count();
            ?>
            <a href="<?php echo e(route('admin.users.index')); ?>" class="dbtn dbtn-blue"><i class="fas fa-users"></i> Utilisateurs</a>
            <a href="<?php echo e(route('swift.anomalies.index')); ?>" class="dbtn dbtn-red position-relative">
                <i class="fas fa-brain"></i> Anomalies IA
                <?php if($totalAnomalies > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill"
                          style="background:#f59e0b;color:#fff;font-size:.58rem;padding:.15rem .38rem;"><?php echo e($totalAnomalies); ?></span>
                <?php endif; ?>
            </a>
            <a href="<?php echo e(route('swift.export-center')); ?>" class="dbtn dbtn-gray"><i class="fas fa-download"></i> Exporter</a>
            <a href="<?php echo e(route('swift.index')); ?>" class="dbtn dbtn-dark"><i class="fas fa-list"></i> Messages SWIFT</a>
        </div>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show py-2 px-3 mb-3" style="font-size:.8rem;border-radius:8px;">
            <i class="fas fa-check-circle me-1"></i><?php echo e(session('success')); ?>

            <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if(session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show py-2 px-3 mb-3" style="font-size:.8rem;border-radius:8px;">
            <i class="fas fa-exclamation-circle me-1"></i><?php echo e(session('error')); ?>

            <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    
    <?php if($criticalCount > 0): ?>
    <div class="alert-strip mb-3" id="critAlert">
        <i class="fas fa-exclamation-triangle" style="color:#dc2626;flex-shrink:0;"></i>
        <p><strong><?php echo e($criticalCount); ?> anomalie(s) HIGH</strong> non v&eacute;rifi&eacute;e(s) &mdash; action requise.</p>
        <a href="<?php echo e(route('swift.anomalies.index', ['niveau_risque'=>'HIGH','verifie'=>'non'])); ?>"
           style="font-size:.75rem;font-weight:600;color:#dc2626;text-decoration:none;white-space:nowrap;">
            Inspecter <i class="fas fa-arrow-right ms-1"></i>
        </a>
        <button onclick="document.getElementById('critAlert').remove()"
                style="background:none;border:none;cursor:pointer;color:#9ca3af;padding:.1rem .3rem;margin-left:.25rem;">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <?php endif; ?>

    
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="kpi-card kpi-blue">
                <div class="kpi-val"><?php echo e($totalCount ?? 0); ?></div>
                <div class="kpi-lbl">Total Messages</div>
                <i class="fas fa-envelope kpi-ico"></i>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="kpi-card kpi-cyan">
                <div class="kpi-val"><?php echo e($receivedCount ?? 0); ?></div>
                <div class="kpi-lbl">Re&ccedil;us</div>
                <i class="fas fa-inbox kpi-ico"></i>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="kpi-card kpi-green">
                <div class="kpi-val"><?php echo e($emittedCount ?? 0); ?></div>
                <div class="kpi-lbl">&Eacute;mis</div>
                <i class="fas fa-paper-plane kpi-ico"></i>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="kpi-card kpi-amber">
                <div class="kpi-val"><?php echo e($pendingCount ?? 0); ?></div>
                <div class="kpi-lbl">En attente</div>
                <i class="fas fa-hourglass-half kpi-ico"></i>
            </div>
        </div>
    </div>

    
    <div class="filt-card">
        <div class="filt-title"><i class="fas fa-sliders-h text-muted"></i> Filtres avancés</div>
        <form method="GET" action="<?php echo e(route('admin.dashboard')); ?>" class="row g-2 align-items-end">
            <div class="col-6 col-md-3">
                <label class="form-label" style="font-size:.72rem;font-weight:600;color:#374151;margin-bottom:.25rem;">Type message</label>
                <select name="type_message" class="form-select form-select-sm">
                    <option value="">Tous</option>
                    <?php $__currentLoopData = \App\Models\MessageSwift::TYPES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($code); ?>" <?php if(request('type_message') == $code): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label" style="font-size:.72rem;font-weight:600;color:#374151;margin-bottom:.25rem;">Direction</label>
                <select name="direction" class="form-select form-select-sm">
                    <option value="">Tous</option>
                    <option value="RECU" <?php if(request('direction')=='RECU'): echo 'selected'; endif; ?>>Re&ccedil;us</option>
                    <option value="EMIS" <?php if(request('direction')=='EMIS'): echo 'selected'; endif; ?>>&Eacute;mis</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label" style="font-size:.72rem;font-weight:600;color:#374151;margin-bottom:.25rem;">Statut</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Tous</option>
                    <option value="pending"    <?php if(request('status')=='pending'): echo 'selected'; endif; ?>>En attente</option>
                    <option value="processed"  <?php if(request('status')=='processed'): echo 'selected'; endif; ?>>&Agrave; autoriser</option>
                    <option value="authorized" <?php if(request('status')=='authorized'): echo 'selected'; endif; ?>>Autoris&eacute;</option>
                    <option value="suspended"  <?php if(request('status')=='suspended'): echo 'selected'; endif; ?>>Suspendu</option>
                    <option value="rejected"   <?php if(request('status')=='rejected'): echo 'selected'; endif; ?>>Rejet&eacute;</option>
                </select>
            </div>
            <div class="col-6 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary flex-fill" style="font-size:.73rem;"><i class="fas fa-filter me-1"></i>Filtrer</button>
                <a href="<?php echo e(route('admin.dashboard')); ?>" class="btn btn-sm btn-light flex-fill" style="font-size:.73rem;border:1px solid #e5e7eb;"><i class="fas fa-times"></i></a>
            </div>
        </form>
    </div>

    
    <div class="tbl-card">
        <div class="tbl-card-hd">
            <div class="tbl-card-title"><i class="fas fa-table text-muted"></i> Tous les Messages SWIFT</div>
            <span style="font-size:.72rem;color:#9ca3af;"><?php echo e($messages->total() ?? 0); ?> r&eacute;sultat(s)</span>
        </div>
        <div style="overflow-x:auto;">
            <table class="swift-tbl">
                <thead>
                    <tr>
                        <th style="width:10%;">Date</th>
                        <th style="width:7%;">Type</th>
                        <th style="width:19%;">&Eacute;metteur</th>
                        <th style="width:18%;">R&eacute;f&eacute;rence</th>
                        <th style="width:12%;text-align:right;">Montant</th>
                        <th style="width:4%;">Dev.</th>
                        <th style="width:12%;">Statut</th>
                        <th style="width:18%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $msg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $type = $msg->type_message ?? null;
                        $typeBg = match(true){
                            str_starts_with($type??'','PACS')=>['#dbeafe','#1d4ed8'],
                            str_starts_with($type??'','CAMT')=>['#ede9fe','#7c3aed'],
                            str_starts_with($type??'','MT')  =>['#e0f2fe','#0369a1'],
                            default=>['#f3f4f6','#374151'],
                        };
                    ?>
                    <tr>
                        <td style="width:10%;">
                            <span style="font-size:.75rem;"><?php echo e($msg->created_at?->format('d/m/Y') ?? '&mdash;'); ?></span><br>
                            <span style="font-size:.65rem;color:#9ca3af;"><?php echo e($msg->created_at?->format('H:i') ?? ''); ?></span>
                        </td>
                        <td style="width:7%;">
                            <span style="background:<?php echo e($typeBg[0]); ?>;color:<?php echo e($typeBg[1]); ?>;font-size:.65rem;font-weight:700;padding:.18rem .45rem;border-radius:4px;font-family:monospace;"><?php echo e($type ?? '&mdash;'); ?></span>
                        </td>
                        <td style="width:19%;">
                            <div style="font-weight:600;font-size:.78rem;"><?php echo e(Str::limit($msg->sender_name ?? 'N/A', 22)); ?></div>
                            <?php if($msg->sender_bic): ?><div style="font-size:.65rem;color:#9ca3af;font-family:monospace;"><?php echo e($msg->sender_bic); ?></div><?php endif; ?>
                        </td>
                        <td style="width:18%;font-family:monospace;font-size:.73rem;"><?php echo e(Str::limit($msg->reference ?? '&mdash;', 24)); ?></td>
                        <td style="width:12%;text-align:right;font-weight:600;font-size:.8rem;"><?php echo e(number_format((float)($msg->amount ?? 0),2,',',' ')); ?></td>
                        <td style="width:4%;font-family:monospace;font-size:.73rem;color:#6b7280;"><?php echo e($msg->currency ?? '&mdash;'); ?></td>
                        <td style="width:12%;">
                            <?php switch($msg->status):
                                case ('pending'): ?>    <span class="sbadge sb-pending"><i class="fas fa-clock" style="font-size:.6rem;"></i> En attente</span> <?php break; ?>
                                <?php case ('processed'): ?>  <span class="sbadge sb-processed"><i class="fas fa-hourglass-half" style="font-size:.6rem;"></i> &Agrave; autor.</span> <?php break; ?>
                                <?php case ('authorized'): ?> <span class="sbadge sb-authorized"><i class="fas fa-check" style="font-size:.6rem;"></i> Autoris&eacute;</span> <?php break; ?>
                                <?php case ('suspended'): ?>  <span class="sbadge sb-suspended"><i class="fas fa-ban" style="font-size:.6rem;"></i> Suspendu</span> <?php break; ?>
                                <?php case ('rejected'): ?>   <span class="sbadge sb-rejected">Rejet&eacute;</span> <?php break; ?>
                                <?php default: ?>            <span class="sbadge sb-rejected"><?php echo e($msg->status ?? '&mdash;'); ?></span>
                            <?php endswitch; ?>
                        </td>
                        <td style="width:18%;">
                            <div class="tbl-actions">
                                <a href="<?php echo e(route('swift.show',$msg->id)); ?>" class="ta-btn ta-view"><i class="fas fa-eye"></i></a>
                                <a href="<?php echo e(route('swift.pdf',$msg->id)); ?>" target="_blank" class="ta-btn ta-pdf"><i class="fas fa-file-pdf"></i> PDF</a>
                                <button type="button" class="ta-btn ta-mt open-raw-file" data-url="<?php echo e(route('swift.view-mt',$msg->id)); ?>" data-title="MT"><i class="fas fa-file-alt"></i> MT</button>
                                <a href="<?php echo e(route('swift.view-mx',$msg->id)); ?>" target="_blank" class="ta-btn ta-mx"><i class="fas fa-code"></i></a>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete',$msg)): ?>
                                    <button type="button" class="ta-btn ta-del" data-bs-toggle="modal" data-bs-target="#del<?php echo e($msg->id); ?>"><i class="fas fa-trash"></i></button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="8"><div class="empty-state"><i class="fas fa-inbox"></i><p class="fw-semibold mb-0">Aucun message trouv&eacute;</p></div></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($messages->hasPages()): ?>
        <div class="tbl-footer">
            <div class="tbl-footer-info">Affichage <?php echo e($messages->firstItem()); ?>&ndash;<?php echo e($messages->lastItem()); ?> sur <?php echo e($messages->total()); ?></div>
            <?php echo e($messages->links()); ?>

        </div>
        <?php endif; ?>
    </div>
</div>

<?php $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $msg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete',$msg)): ?>
    <div class="modal fade" id="del<?php echo e($msg->id); ?>" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered" style="max-width:380px;">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-body text-center p-4">
                    <div style="width:52px;height:52px;background:#fef2f2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
                        <i class="fas fa-trash" style="color:#dc2626;font-size:1.1rem;"></i>
                    </div>
                    <h6 class="fw-bold mb-1">Supprimer ce message ?</h6>
                    <p style="font-size:.78rem;color:#6b7280;"><?php echo e($msg->reference); ?></p>
                    <div class="d-flex gap-2 justify-content-center mt-3">
                        <button type="button" class="btn btn-sm btn-light px-4" data-bs-dismiss="modal">Annuler</button>
                        <form action="<?php echo e(route('swift.destroy',$msg->id)); ?>" method="POST" class="d-inline">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="btn btn-sm btn-danger px-4">Supprimer</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<script>
setTimeout(function(){document.querySelectorAll('.alert').forEach(function(el){bootstrap.Alert.getOrCreateInstance(el).close();});},5000);
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/super-admin/dashboard.blade.php ENDPATH**/ ?>