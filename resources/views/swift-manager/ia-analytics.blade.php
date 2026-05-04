@extends('layouts.app')

@section('title', 'Analyse IA — BTL Bank')

@section('content')
<div class="container-fluid py-4">

    {{-- En-tête --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h1 class="h2 mb-0 fw-bold">
                <i class="fas fa-brain text-danger me-2"></i>Analyse IA — Détection d'Anomalies
            </h1>
            <p class="text-muted mb-0">Tableaux de bord Intelligence Artificielle · 30 derniers jours</p>
        </div>
        <a href="{{ route('international-admin.dashboard') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Retour au Dashboard
        </a>
    </div>

    {{-- KPI Résumé --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #6c757d !important;">
                <div class="card-body text-center py-3">
                    <div class="fs-1 fw-bold text-secondary">{{ $totalAnomalies }}</div>
                    <div class="small text-muted">Total analysées</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #dc3545 !important;">
                <div class="card-body text-center py-3">
                    <div class="fs-1 fw-bold text-danger">{{ $highCount }}</div>
                    <div class="small text-muted">Risque HIGH</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #ffc107 !important;">
                <div class="card-body text-center py-3">
                    <div class="fs-1 fw-bold text-warning">{{ $mediumCount }}</div>
                    <div class="small text-muted">Risque MEDIUM</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #198754 !important;">
                <div class="card-body text-center py-3">
                    <div class="fs-1 fw-bold text-success">{{ $avgScore }}</div>
                    <div class="small text-muted">Score moyen IA</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Ligne 1 : Donut + Barres --}}
    <div class="row g-4 mb-4">

        {{-- Donut : Répartition niveaux de risque --}}
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white d-flex align-items-center gap-2">
                    <i class="fas fa-chart-pie text-danger"></i>
                    <h6 class="mb-0 fw-semibold">Répartition par Niveau de Risque</h6>
                </div>
                <div class="card-body d-flex flex-column align-items-center justify-content-center gap-3">
                    <canvas id="chartRiskLevel" style="max-height:220px;max-width:220px;"></canvas>
                    <div class="d-flex gap-3 flex-wrap justify-content-center">
                        <span class="badge bg-success px-3 py-2">
                            <i class="fas fa-check-circle me-1"></i>LOW : {{ $anomalyByLevel['LOW'] ?? $anomalyByLevel['low'] ?? 0 }}
                        </span>
                        <span class="badge bg-warning text-dark px-3 py-2">
                            <i class="fas fa-exclamation-triangle me-1"></i>MEDIUM : {{ $anomalyByLevel['MEDIUM'] ?? $anomalyByLevel['medium'] ?? 0 }}
                        </span>
                        <span class="badge bg-danger px-3 py-2">
                            <i class="fas fa-skull-crossbones me-1"></i>HIGH : {{ $anomalyByLevel['HIGH'] ?? $anomalyByLevel['high'] ?? 0 }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Barres : Anomalies MEDIUM+HIGH par type SWIFT --}}
        <div class="col-md-8">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white d-flex align-items-center gap-2">
                    <i class="fas fa-chart-bar text-primary"></i>
                    <h6 class="mb-0 fw-semibold">Anomalies (MEDIUM + HIGH) par Type SWIFT</h6>
                </div>
                <div class="card-body">
                    @if(empty($anomalyByType))
                        <div class="d-flex align-items-center justify-content-center h-100 text-muted">
                            <div class="text-center">
                                <i class="fas fa-check-circle fa-3x text-success mb-2"></i>
                                <p>Aucune anomalie MEDIUM ou HIGH détectée</p>
                            </div>
                        </div>
                    @else
                        <canvas id="chartByType" style="max-height:240px;"></canvas>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Ligne 2 : Courbe 30 jours (pleine largeur) --}}
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex align-items-center gap-2">
                    <i class="fas fa-chart-line text-warning"></i>
                    <h6 class="mb-0 fw-semibold">Score Moyen IA — 30 derniers jours</h6>
                </div>
                <div class="card-body">
                    @if(empty($scoreTimeline))
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-clock fa-2x mb-2"></i>
                            <p>Pas de données sur les 30 derniers jours</p>
                        </div>
                    @else
                        <canvas id="chartTimeline" style="max-height:260px;"></canvas>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Lien vers anomalies --}}
    <div class="row">
        <div class="col-12">
            <div class="card border-0 bg-light shadow-sm">
                <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-3 py-3">
                    <div class="d-flex align-items-center gap-3">
                        <i class="fas fa-search fa-2x text-secondary"></i>
                        <div>
                            <div class="fw-semibold">Inspecter les anomalies en détail</div>
                            <div class="small text-muted">Accédez à la liste complète avec filtres et actions</div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('swift.anomalies.index', ['niveau_risque' => 'HIGH']) }}"
                           class="btn btn-danger btn-sm">
                            <i class="fas fa-skull-crossbones me-1"></i>Voir HIGH
                        </a>
                        <a href="{{ route('swift.anomalies.index', ['niveau_risque' => 'MEDIUM']) }}"
                           class="btn btn-warning btn-sm text-dark">
                            <i class="fas fa-exclamation-triangle me-1"></i>Voir MEDIUM
                        </a>
                        <a href="{{ route('swift.anomalies.index') }}"
                           class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-list me-1"></i>Toutes
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
// ── Donut : Niveaux de risque ─────────────────────────────
new Chart(document.getElementById('chartRiskLevel'), {
    type: 'doughnut',
    data: {
        labels: ['LOW', 'MEDIUM', 'HIGH'],
        datasets: [{
            data: [
                {{ $anomalyByLevel['LOW'] ?? $anomalyByLevel['low'] ?? 0 }},
                {{ $anomalyByLevel['MEDIUM'] ?? $anomalyByLevel['medium'] ?? 0 }},
                {{ $anomalyByLevel['HIGH'] ?? $anomalyByLevel['high'] ?? 0 }}
            ],
            backgroundColor: ['#198754', '#ffc107', '#dc3545'],
            borderWidth: 2,
        }]
    },
    options: {
        plugins: {
            legend: { position: 'bottom' },
            tooltip: {
                callbacks: {
                    label: ctx => ` ${ctx.label} : ${ctx.parsed}`
                }
            }
        },
        cutout: '60%',
    }
});

// ── Barres : Anomalies par type SWIFT ────────────────────
@if(!empty($anomalyByType))
new Chart(document.getElementById('chartByType'), {
    type: 'bar',
    data: {
        labels: {!! json_encode(array_keys($anomalyByType)) !!},
        datasets: [{
            label: 'Anomalies (MEDIUM + HIGH)',
            data: {!! json_encode(array_values($anomalyByType)) !!},
            backgroundColor: ['#0d6efd','#6f42c1','#d63384','#fd7e14','#20c997','#0dcaf0','#ffc107'],
            borderRadius: 6,
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 } }
        }
    }
});
@endif

// ── Courbe : Score moyen 30 jours ────────────────────────
@if(!empty($scoreTimeline))
new Chart(document.getElementById('chartTimeline'), {
    type: 'line',
    data: {
        labels: {!! json_encode(array_column($scoreTimeline, 'jour')) !!},
        datasets: [{
            label: 'Score moyen IA',
            data: {!! json_encode(array_column($scoreTimeline, 'avg_score')) !!},
            borderColor: '#f59e0b',
            backgroundColor: 'rgba(245,158,11,0.12)',
            borderWidth: 2.5,
            pointRadius: 4,
            pointBackgroundColor: '#f59e0b',
            fill: true,
            tension: 0.35,
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: {
            y: {
                beginAtZero: true,
                suggestedMax: 100,
                ticks: { callback: v => v + '' }
            }
        }
    }
});
@endif
</script>
@endpush
