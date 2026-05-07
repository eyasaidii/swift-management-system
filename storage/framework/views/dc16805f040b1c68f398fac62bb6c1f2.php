

<?php $__env->startSection('title', 'Analyse IA — BTL Bank'); ?>

<?php $__env->startPush('styles'); ?>
<style>
.bi-header{background:linear-gradient(135deg,#0f172a 0%,#1e3a5f 60%,#1a4a3a 100%);border-radius:14px;padding:1.5rem 2rem;margin-bottom:1.5rem;color:#fff;}
.bi-header h1{font-size:1.45rem;font-weight:700;margin:0;}
.bi-header p{margin:0;opacity:.7;font-size:.82rem;}
.bi-header .back-btn{background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.2);color:#fff;border-radius:8px;padding:.4rem 1rem;font-size:.8rem;text-decoration:none;transition:.2s;}
.bi-header .back-btn:hover{background:rgba(255,255,255,.22);color:#fff;}
.bi-header .ts{font-size:.7rem;opacity:.55;}
.kpi-card{border:none;border-radius:14px;padding:1.25rem 1.4rem;position:relative;overflow:hidden;transition:transform .15s,box-shadow .15s;}
.kpi-card:hover{transform:translateY(-2px);box-shadow:0 8px 28px rgba(0,0,0,.12)!important;}
.kpi-card .kpi-icon{width:46px;height:46px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;flex-shrink:0;}
.kpi-card .kpi-val{font-size:2rem;font-weight:700;line-height:1;}
.kpi-card .kpi-label{font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;opacity:.65;}
.kpi-card .kpi-trend{font-size:.7rem;margin-top:.3rem;}
.kpi-card .kpi-bg-icon{position:absolute;right:-10px;bottom:-10px;font-size:4rem;opacity:.06;}
.kpi-total{background:linear-gradient(135deg,#1e293b,#334155);color:#fff;}
.kpi-total .kpi-icon{background:rgba(255,255,255,.12);color:#93c5fd;}
.kpi-high{background:linear-gradient(135deg,#7f1d1d,#991b1b);color:#fff;}
.kpi-high .kpi-icon{background:rgba(255,255,255,.12);color:#fca5a5;}
.kpi-medium{background:linear-gradient(135deg,#713f12,#92400e);color:#fff;}
.kpi-medium .kpi-icon{background:rgba(255,255,255,.12);color:#fcd34d;}
.kpi-low{background:linear-gradient(135deg,#14532d,#166534);color:#fff;}
.kpi-low .kpi-icon{background:rgba(255,255,255,.12);color:#86efac;}
.kpi-avg{background:linear-gradient(135deg,#1e3a5f,#1d4ed8);color:#fff;}
.kpi-avg .kpi-icon{background:rgba(255,255,255,.12);color:#93c5fd;}
.kpi-rate{background:linear-gradient(135deg,#134e4a,#0f766e);color:#fff;}
.kpi-rate .kpi-icon{background:rgba(255,255,255,.12);color:#5eead4;}
.chart-card{border:none;border-radius:14px;box-shadow:0 2px 12px rgba(0,0,0,.07);overflow:hidden;}
.chart-card .chart-header{padding:.9rem 1.2rem;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;gap:.6rem;background:#fff;}
.chart-card .chart-title{font-size:.82rem;font-weight:600;color:#1e293b;margin:0;}
.chart-card .chart-subtitle{font-size:.68rem;color:#94a3b8;margin:0;}
.chart-card .chart-body{padding:1.2rem;background:#fff;}
.risk-bar-wrap{margin-bottom:.6rem;}
.risk-bar-label{display:flex;justify-content:space-between;font-size:.72rem;margin-bottom:.2rem;}
.risk-bar{height:8px;border-radius:20px;overflow:hidden;background:#f1f5f9;}
.risk-bar-fill{height:100%;border-radius:20px;}
.top-table{width:100%;border-collapse:separate;border-spacing:0;}
.top-table th{font-size:.67rem;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;padding:.5rem .8rem;border-bottom:2px solid #f1f5f9;background:#fafafa;font-weight:600;}
.top-table td{font-size:.75rem;padding:.55rem .8rem;border-bottom:1px solid #f8fafc;vertical-align:middle;}
.top-table tr:last-child td{border-bottom:none;}
.top-table tr:hover td{background:#f8fafc;}
.score-pill{display:inline-block;padding:.18rem .55rem;border-radius:20px;font-size:.68rem;font-weight:700;}
.score-high{background:#fee2e2;color:#dc2626;}
.gauge-wrap{position:relative;display:inline-block;}
.gauge-center{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center;pointer-events:none;}
.gauge-val{font-size:1.5rem;font-weight:700;line-height:1;}
.gauge-sub{font-size:.62rem;color:#94a3b8;}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div style="max-width:1400px;margin:0 auto;">


<div class="bi-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <div class="ts mb-1"><i class="fas fa-circle me-1" style="color:#22c55e;font-size:.5rem;"></i>INTELLIGENCE ARTIFICIELLE — BTL BANK</div>
        <h1><i class="fas fa-brain me-2" style="color:#60a5fa;"></i>Tableau de Bord IA — Détection d'Anomalies SWIFT</h1>
        <p class="mt-1">Analyse statistique sur <?php echo e($totalAnomalies); ?> transactions · Modèle v2.0 · 30 derniers jours</p>
    </div>
    <div class="d-flex gap-2 align-items-center flex-wrap">
        <a href="<?php echo e(route('swift.anomalies.index', ['niveau_risque'=>'HIGH'])); ?>" class="back-btn">
            <i class="fas fa-exclamation-triangle me-1" style="color:#fca5a5;"></i><?php echo e($highCount); ?> HIGH critiques
        </a>
        <a href="<?php echo e(route('international-admin.dashboard')); ?>" class="back-btn">
            <i class="fas fa-arrow-left me-1"></i>Dashboard
        </a>
    </div>
</div>


<div class="row g-3 mb-4">
    <div class="col-6 col-md-2">
        <div class="kpi-card kpi-total shadow-sm h-100">
            <div class="d-flex align-items-start gap-3">
                <div class="kpi-icon"><i class="fas fa-database"></i></div>
                <div>
                    <div class="kpi-val"><?php echo e($totalAnomalies); ?></div>
                    <div class="kpi-label">Total analysées</div>
                </div>
            </div>
            <i class="fas fa-layer-group kpi-bg-icon"></i>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="kpi-card kpi-high shadow-sm h-100">
            <div class="d-flex align-items-start gap-3">
                <div class="kpi-icon"><i class="fas fa-skull-crossbones"></i></div>
                <div>
                    <div class="kpi-val"><?php echo e($highCount); ?></div>
                    <div class="kpi-label">Risque HIGH</div>
                    <div class="kpi-trend opacity-75">≥ 60/100</div>
                </div>
            </div>
            <i class="fas fa-radiation kpi-bg-icon"></i>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="kpi-card kpi-medium shadow-sm h-100">
            <div class="d-flex align-items-start gap-3">
                <div class="kpi-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <div>
                    <div class="kpi-val"><?php echo e($mediumCount); ?></div>
                    <div class="kpi-label">Risque MEDIUM</div>
                    <div class="kpi-trend opacity-75">20–59/100</div>
                </div>
            </div>
            <i class="fas fa-exclamation kpi-bg-icon"></i>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="kpi-card kpi-low shadow-sm h-100">
            <div class="d-flex align-items-start gap-3">
                <div class="kpi-icon"><i class="fas fa-check-circle"></i></div>
                <div>
                    <div class="kpi-val"><?php echo e($lowCount); ?></div>
                    <div class="kpi-label">Risque LOW</div>
                    <div class="kpi-trend opacity-75">&lt; 20/100</div>
                </div>
            </div>
            <i class="fas fa-shield-alt kpi-bg-icon"></i>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="kpi-card kpi-avg shadow-sm h-100">
            <div class="d-flex align-items-start gap-3">
                <div class="kpi-icon"><i class="fas fa-chart-line"></i></div>
                <div>
                    <div class="kpi-val"><?php echo e($avgScore); ?></div>
                    <div class="kpi-label">Score moyen IA</div>
                    <div class="kpi-trend opacity-75">sur 100</div>
                </div>
            </div>
            <i class="fas fa-tachometer-alt kpi-bg-icon"></i>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="kpi-card kpi-rate shadow-sm h-100">
            <div class="d-flex align-items-start gap-3">
                <div class="kpi-icon"><i class="fas fa-tasks"></i></div>
                <div>
                    <div class="kpi-val"><?php echo e($resolutionRate); ?>%</div>
                    <div class="kpi-label">Taux résolution</div>
                    <div class="kpi-trend opacity-75"><?php echo e($verifiedCount + $rejectedCount); ?> / <?php echo e($totalAnomalies); ?></div>
                </div>
            </div>
            <i class="fas fa-bullseye kpi-bg-icon"></i>
        </div>
    </div>
</div>


<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="chart-card shadow-sm h-100">
            <div class="chart-header">
                <i class="fas fa-chart-pie" style="color:#6366f1;"></i>
                <div>
                    <div class="chart-title">Répartition par Niveau de Risque</div>
                    <div class="chart-subtitle">Distribution des <?php echo e($totalAnomalies); ?> anomalies détectées</div>
                </div>
            </div>
            <div class="chart-body d-flex flex-column align-items-center gap-3">
                <?php
                    $low    = $anomalyByLevel['LOW']    ?? $anomalyByLevel['low']    ?? 0;
                    $medium = $anomalyByLevel['MEDIUM'] ?? $anomalyByLevel['medium'] ?? 0;
                    $high   = $anomalyByLevel['HIGH']   ?? $anomalyByLevel['high']   ?? 0;
                    $total  = max(1, $totalAnomalies);
                ?>
                <div class="gauge-wrap">
                    <canvas id="chartRiskLevel" width="200" height="200"></canvas>
                    <div class="gauge-center">
                        <div class="gauge-val" style="color:#1e293b;"><?php echo e($totalAnomalies); ?></div>
                        <div class="gauge-sub">total</div>
                    </div>
                </div>
                <div class="w-100 px-2">
                    <div class="risk-bar-wrap">
                        <div class="risk-bar-label">
                            <span style="color:#16a34a;font-weight:600;"><i class="fas fa-circle me-1" style="font-size:.5rem;"></i>LOW</span>
                            <span style="color:#16a34a;font-weight:600;"><?php echo e($low); ?> <span style="color:#94a3b8;font-weight:400;">(<?php echo e(round($low/$total*100)); ?>%)</span></span>
                        </div>
                        <div class="risk-bar"><div class="risk-bar-fill" style="width:<?php echo e(round($low/$total*100)); ?>%;background:#22c55e;"></div></div>
                    </div>
                    <div class="risk-bar-wrap">
                        <div class="risk-bar-label">
                            <span style="color:#d97706;font-weight:600;"><i class="fas fa-circle me-1" style="font-size:.5rem;"></i>MEDIUM</span>
                            <span style="color:#d97706;font-weight:600;"><?php echo e($medium); ?> <span style="color:#94a3b8;font-weight:400;">(<?php echo e(round($medium/$total*100)); ?>%)</span></span>
                        </div>
                        <div class="risk-bar"><div class="risk-bar-fill" style="width:<?php echo e(round($medium/$total*100)); ?>%;background:#f59e0b;"></div></div>
                    </div>
                    <div class="risk-bar-wrap">
                        <div class="risk-bar-label">
                            <span style="color:#dc2626;font-weight:600;"><i class="fas fa-circle me-1" style="font-size:.5rem;"></i>HIGH</span>
                            <span style="color:#dc2626;font-weight:600;"><?php echo e($high); ?> <span style="color:#94a3b8;font-weight:400;">(<?php echo e(round($high/$total*100)); ?>%)</span></span>
                        </div>
                        <div class="risk-bar"><div class="risk-bar-fill" style="width:<?php echo e(round($high/$total*100)); ?>%;background:#ef4444;"></div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="chart-card shadow-sm h-100">
            <div class="chart-header justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-chart-bar" style="color:#3b82f6;"></i>
                    <div>
                        <div class="chart-title">Anomalies à Risque par Type de Message SWIFT</div>
                        <div class="chart-subtitle">Barres empilées MEDIUM (jaune) + HIGH (rouge)</div>
                    </div>
                </div>
                <div class="d-flex gap-1">
                    <span class="badge" style="background:#fef3c7;color:#92400e;font-size:.63rem;">MEDIUM</span>
                    <span class="badge" style="background:#fee2e2;color:#991b1b;font-size:.63rem;">HIGH</span>
                </div>
            </div>
            <div class="chart-body">
                <?php if(empty($allTypes)): ?>
                    <div class="d-flex align-items-center justify-content-center py-5 text-muted">
                        <div class="text-center"><i class="fas fa-check-circle fa-3x text-success mb-2"></i><p>Aucune anomalie MEDIUM ou HIGH</p></div>
                    </div>
                <?php else: ?>
                    <canvas id="chartByType" style="max-height:280px;"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


<div class="row g-4 mb-4">
    <div class="col-md-7">
        <div class="chart-card shadow-sm h-100">
            <div class="chart-header justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-chart-area" style="color:#f59e0b;"></i>
                    <div>
                        <div class="chart-title">Score Moyen IA — Évolution 30 jours</div>
                        <div class="chart-subtitle">Avec lignes de seuil LOW / MEDIUM / HIGH</div>
                    </div>
                </div>
                <div class="d-flex gap-1 flex-wrap">
                    <span class="badge" style="background:#dcfce7;color:#166534;font-size:.6rem;">LOW &lt;20</span>
                    <span class="badge" style="background:#fef3c7;color:#92400e;font-size:.6rem;">MED 20-59</span>
                    <span class="badge" style="background:#fee2e2;color:#991b1b;font-size:.6rem;">HIGH ≥60</span>
                </div>
            </div>
            <div class="chart-body">
                <?php if(empty($scoreTimeline)): ?>
                    <div class="text-center py-5 text-muted"><i class="fas fa-clock fa-2x mb-2"></i><p>Pas de données sur les 30 derniers jours</p></div>
                <?php else: ?>
                    <canvas id="chartTimeline" style="max-height:250px;"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-5 d-flex flex-column gap-4">
        <div class="chart-card shadow-sm" style="flex:1;">
            <div class="chart-header">
                <i class="fas fa-clipboard-check" style="color:#10b981;"></i>
                <div>
                    <div class="chart-title">Statut des Vérifications</div>
                    <div class="chart-subtitle">Taux de résolution global</div>
                </div>
            </div>
            <div class="chart-body d-flex align-items-center gap-4">
                <div class="gauge-wrap flex-shrink-0">
                    <canvas id="chartStatus" width="130" height="130"></canvas>
                    <div class="gauge-center">
                        <div class="gauge-val" style="font-size:1.1rem;color:#1e293b;"><?php echo e($resolutionRate); ?>%</div>
                        <div class="gauge-sub">résolu</div>
                    </div>
                </div>
                <div class="flex-grow-1">
                    <div class="risk-bar-wrap">
                        <div class="risk-bar-label">
                            <span style="color:#16a34a;font-weight:600;font-size:.72rem;"><i class="fas fa-check me-1"></i>Autorisées</span>
                            <span style="color:#16a34a;font-weight:600;font-size:.72rem;"><?php echo e($verifiedCount); ?></span>
                        </div>
                        <div class="risk-bar"><div class="risk-bar-fill" style="width:<?php echo e($total>0?round($verifiedCount/$total*100):0); ?>%;background:#22c55e;"></div></div>
                    </div>
                    <div class="risk-bar-wrap">
                        <div class="risk-bar-label">
                            <span style="color:#dc2626;font-weight:600;font-size:.72rem;"><i class="fas fa-times me-1"></i>Rejetées</span>
                            <span style="color:#dc2626;font-weight:600;font-size:.72rem;"><?php echo e($rejectedCount); ?></span>
                        </div>
                        <div class="risk-bar"><div class="risk-bar-fill" style="width:<?php echo e($total>0?round($rejectedCount/$total*100):0); ?>%;background:#ef4444;"></div></div>
                    </div>
                    <div class="risk-bar-wrap">
                        <div class="risk-bar-label">
                            <span style="color:#f59e0b;font-weight:600;font-size:.72rem;"><i class="fas fa-clock me-1"></i>En attente</span>
                            <span style="color:#f59e0b;font-weight:600;font-size:.72rem;"><?php echo e($pendingCount); ?></span>
                        </div>
                        <div class="risk-bar"><div class="risk-bar-fill" style="width:<?php echo e($total>0?round($pendingCount/$total*100):0); ?>%;background:#f59e0b;"></div></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="chart-card shadow-sm" style="flex:1;">
            <div class="chart-header">
                <i class="fas fa-chart-bar" style="color:#8b5cf6;"></i>
                <div>
                    <div class="chart-title">Distribution des Scores IA</div>
                    <div class="chart-subtitle">Répartition par tranche (0-100)</div>
                </div>
            </div>
            <div class="chart-body">
                <canvas id="chartScoreDist" style="max-height:140px;"></canvas>
            </div>
        </div>
    </div>
</div>


<?php if($topHighAnomalies->count() > 0): ?>
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="chart-card shadow-sm">
            <div class="chart-header justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-radiation" style="color:#dc2626;"></i>
                    <div>
                        <div class="chart-title">Top Anomalies HIGH — Action Requise</div>
                        <div class="chart-subtitle">Transactions critiques non vérifiées, triées par score décroissant</div>
                    </div>
                </div>
                <a href="<?php echo e(route('swift.anomalies.index', ['niveau_risque'=>'HIGH','verifie'=>'non'])); ?>"
                   class="btn btn-danger btn-sm" style="font-size:.72rem;">
                    <i class="fas fa-arrow-right me-1"></i>Voir toutes
                </a>
            </div>
            <div class="chart-body p-0">
                <table class="top-table">
                    <thead>
                        <tr>
                            <th>Référence</th>
                            <th>Type</th>
                            <th>Émetteur</th>
                            <th>Score IA</th>
                            <th>Raisons</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $topHighAnomalies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><span class="fw-semibold" style="font-size:.72rem;color:#1e293b;"><?php echo e($a->message->REFERENCE ?? '—'); ?></span></td>
                            <td><span class="badge" style="background:#eff6ff;color:#1d4ed8;font-size:.65rem;"><?php echo e($a->message->TYPE_MESSAGE ?? '—'); ?></span></td>
                            <td style="max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                <?php echo e(Str::limit($a->message->SENDER_NAME ?? $a->message->EMETTEUR ?? '—', 22)); ?>

                            </td>
                            <td><span class="score-pill score-high"><?php echo e(number_format($a->score, 1)); ?></span></td>
                            <td style="max-width:200px;">
                                <?php if($a->raisons && count($a->raisons) > 0): ?>
                                    <span style="font-size:.68rem;color:#64748b;"><?php echo e(Str::limit(implode(', ', array_slice($a->raisons, 0, 2)), 48)); ?></span>
                                <?php else: ?>
                                    <span style="color:#94a3b8;font-size:.68rem;">—</span>
                                <?php endif; ?>
                            </td>
                            <td style="color:#94a3b8;font-size:.68rem;"><?php echo e(optional($a->created_at)->format('d/m H:i')); ?></td>
                            <td>
                                <a href="<?php echo e(route('swift.show', $a->message_id)); ?>"
                                   style="background:#fee2e2;color:#dc2626;border:none;font-size:.65rem;padding:.25rem .6rem;border-radius:6px;text-decoration:none;display:inline-block;">
                                    <i class="fas fa-eye me-1"></i>Inspecter
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>


<div class="chart-card shadow-sm mb-4">
    <div class="chart-body d-flex align-items-center justify-content-between flex-wrap gap-3 py-2">
        <div class="d-flex align-items-center gap-3">
            <div style="width:38px;height:38px;background:#f1f5f9;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                <i class="fas fa-search" style="color:#64748b;"></i>
            </div>
            <div>
                <div style="font-size:.82rem;font-weight:600;color:#1e293b;">Accès direct aux anomalies</div>
                <div style="font-size:.7rem;color:#94a3b8;">Filtres par niveau de risque</div>
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?php echo e(route('swift.anomalies.index', ['niveau_risque'=>'HIGH','verifie'=>'non'])); ?>"
               style="background:#dc2626;color:#fff;border-radius:8px;padding:.4rem 1rem;font-size:.75rem;text-decoration:none;">
                <i class="fas fa-radiation me-1"></i>HIGH non vérifiés
            </a>
            <a href="<?php echo e(route('swift.anomalies.index', ['niveau_risque'=>'MEDIUM'])); ?>"
               style="background:#f59e0b;color:#fff;border-radius:8px;padding:.4rem 1rem;font-size:.75rem;text-decoration:none;">
                <i class="fas fa-exclamation-triangle me-1"></i>MEDIUM (<?php echo e($mediumCount); ?>)
            </a>
            <a href="<?php echo e(route('swift.anomalies.index')); ?>"
               style="background:#f1f5f9;color:#475569;border-radius:8px;padding:.4rem 1rem;font-size:.75rem;text-decoration:none;">
                <i class="fas fa-list me-1"></i>Toutes (<?php echo e($totalAnomalies); ?>)
            </a>
        </div>
    </div>
</div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.font.family = "'Inter','Segoe UI',sans-serif";
Chart.defaults.color = '#64748b';

// Donut risque
new Chart(document.getElementById('chartRiskLevel'), {
    type: 'doughnut',
    data: {
        labels: ['LOW', 'MEDIUM', 'HIGH'],
        datasets: [{
            data: [
                <?php echo e($anomalyByLevel['LOW'] ?? $anomalyByLevel['low'] ?? 0); ?>,
                <?php echo e($anomalyByLevel['MEDIUM'] ?? $anomalyByLevel['medium'] ?? 0); ?>,
                <?php echo e($anomalyByLevel['HIGH'] ?? $anomalyByLevel['high'] ?? 0); ?>

            ],
            backgroundColor: ['#22c55e','#f59e0b','#ef4444'],
            borderColor: '#fff',
            borderWidth: 3,
            hoverOffset: 6,
        }]
    },
    options: {
        cutout: '68%',
        plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: ctx => ` ${ctx.label} : ${ctx.parsed} (${Math.round(ctx.parsed/<?php echo e(max(1,$totalAnomalies)); ?>*100)}%)` } }
        }
    }
});

// Barres empilées
<?php if(!empty($allTypes)): ?>
const types = <?php echo json_encode($allTypes); ?>;
const mediumData = <?php echo json_encode($anomalyByTypeMedium); ?>;
const highData   = <?php echo json_encode($anomalyByTypeHigh); ?>;
new Chart(document.getElementById('chartByType'), {
    type: 'bar',
    data: {
        labels: types,
        datasets: [
            {
                label: 'MEDIUM',
                data: types.map(t => mediumData[t] ?? 0),
                backgroundColor: 'rgba(245,158,11,0.85)',
                borderWidth: 0,
                borderRadius: 4,
                borderSkipped: false,
            },
            {
                label: 'HIGH',
                data: types.map(t => highData[t] ?? 0),
                backgroundColor: 'rgba(239,68,68,0.85)',
                borderWidth: 0,
                borderRadius: 4,
                borderSkipped: false,
            }
        ]
    },
    options: {
        plugins: {
            legend: { position: 'top', labels: { boxWidth: 12, padding: 16, font: { size: 11 } } },
            tooltip: { mode: 'index', intersect: false }
        },
        scales: {
            x: { stacked: true, grid: { display: false }, ticks: { font: { size: 11 } } },
            y: { stacked: true, beginAtZero: true, ticks: { stepSize: 1, font: { size: 11 } }, grid: { color: 'rgba(0,0,0,0.05)' } }
        }
    }
});
<?php endif; ?>

// Timeline score + seuils
<?php if(!empty($scoreTimeline)): ?>
const tlL = <?php echo json_encode(array_column($scoreTimeline, 'jour')); ?>;
const tlS = <?php echo json_encode(array_column($scoreTimeline, 'avg_score')); ?>;
new Chart(document.getElementById('chartTimeline'), {
    type: 'line',
    data: {
        labels: tlL,
        datasets: [
            {
                label: 'Score moyen IA',
                data: tlS,
                borderColor: '#f59e0b',
                backgroundColor: 'rgba(245,158,11,0.08)',
                borderWidth: 2.5,
                pointRadius: 5,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#f59e0b',
                pointBorderWidth: 2,
                fill: true,
                tension: 0.4,
            },
            {
                label: 'Seuil HIGH (60)',
                data: tlL.map(() => 60),
                borderColor: 'rgba(239,68,68,0.5)',
                borderWidth: 1.5,
                borderDash: [5,4],
                pointRadius: 0,
                fill: false,
            },
            {
                label: 'Seuil MEDIUM (20)',
                data: tlL.map(() => 20),
                borderColor: 'rgba(245,158,11,0.5)',
                borderWidth: 1.5,
                borderDash: [5,4],
                pointRadius: 0,
                fill: false,
            }
        ]
    },
    options: {
        interaction: { mode: 'index', intersect: false },
        plugins: { legend: { position: 'top', labels: { boxWidth: 12, padding: 16, font: { size: 11 } } } },
        scales: {
            y: { beginAtZero: true, suggestedMax: 100, grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { font: { size: 11 } } },
            x: { grid: { display: false }, ticks: { font: { size: 11 } } }
        }
    }
});
<?php endif; ?>

// Donut statut
new Chart(document.getElementById('chartStatus'), {
    type: 'doughnut',
    data: {
        labels: ['Autorisées','Rejetées','En attente'],
        datasets: [{
            data: [<?php echo e($verifiedCount); ?>, <?php echo e($rejectedCount); ?>, <?php echo e($pendingCount); ?>],
            backgroundColor: ['#22c55e','#ef4444','#f59e0b'],
            borderColor: '#fff',
            borderWidth: 3,
            hoverOffset: 4,
        }]
    },
    options: {
        cutout: '65%',
        plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: ctx => ` ${ctx.label} : ${ctx.parsed}` } }
        }
    }
});

// Distribution scores horizontale
new Chart(document.getElementById('chartScoreDist'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_keys($scoreRanges)); ?>,
        datasets: [{
            label: 'Anomalies',
            data: <?php echo json_encode(array_values($scoreRanges)); ?>,
            backgroundColor: ['#22c55e','#84cc16','#f59e0b','#f97316','#ef4444'],
            borderRadius: 6,
            borderWidth: 0,
        }]
    },
    options: {
        indexAxis: 'y',
        plugins: { legend: { display: false } },
        scales: {
            x: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { font: { size: 10 } } },
            y: { grid: { display: false }, ticks: { font: { size: 10 } } }
        }
    }
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/resources/views/swift-manager/ia-analytics.blade.php ENDPATH**/ ?>