@extends('layouts.app')

@section('title', 'Anomalies Détectées — IA')

@section('content')
<div class="container-fluid py-4">

    {{-- ===== EN-TÊTE ===== --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <span style="color:#1A5C38">&#9888;</span>
                Anomalies Détectées par l'IA
            </h4>
            <small class="text-muted">Moteur de détection — Règles métier SWIFT</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('swift.index') }}" class="btn btn-outline-secondary btn-sm">
                &#8592; Retour Messages
            </a>
            @role('super-admin|swift-manager')
            <form method="POST" action="{{ route('swift.anomalies.analyze-all') }}"
                  onsubmit="return confirm('Analyser tous les messages SWIFT existants ?')">
                @csrf
                <button type="submit" class="btn btn-sm text-white" style="background:#1A5C38">
                    &#9654; Analyser tout
                </button>
            </form>
            @endrole
        </div>
    </div>

    {{-- ===== ALERTES ===== --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ===== KPIs ===== --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="fs-2 fw-bold text-dark">{{ $stats['total'] }}</div>
                    <div class="text-muted small">Total anomalies</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #dc3545 !important;">
                <div class="card-body text-center">
                    <div class="fs-2 fw-bold text-danger">{{ $stats['high'] }}</div>
                    <div class="text-muted small">Risque critique</div>
                    @if($stats['non_verifiees'] > 0)
                        <span class="badge bg-danger">{{ $stats['non_verifiees'] }} non vérifiées</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #fd7e14 !important;">
                <div class="card-body text-center">
                    <div class="fs-2 fw-bold text-warning">{{ $stats['medium'] }}</div>
                    <div class="text-muted small">Risque moyen</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #198754 !important;">
                <div class="card-body text-center">
                    <div class="fs-2 fw-bold text-success">{{ $stats['low'] }}</div>
                    <div class="text-muted small">Risque faible</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== FILTRES ===== --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Niveau de risque</label>
                    <select name="niveau_risque" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        <option value="HIGH"   {{ request('niveau_risque') === 'HIGH'   ? 'selected' : '' }}>🔴 Critique</option>
                        <option value="MEDIUM" {{ request('niveau_risque') === 'MEDIUM' ? 'selected' : '' }}>🟡 Moyen</option>
                        <option value="LOW"    {{ request('niveau_risque') === 'LOW'    ? 'selected' : '' }}>🟢 Faible</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Vérification</label>
                    <select name="verifie" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        <option value="non" {{ request('verifie') === 'non' ? 'selected' : '' }}>Non vérifiées</option>
                        <option value="oui" {{ request('verifie') === 'oui' ? 'selected' : '' }}>Vérifiées</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted">Date début</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                           class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted">Date fin</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                           class="form-control form-control-sm">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-sm text-white w-100" style="background:#1A5C38">
                        Filtrer
                    </button>
                    <a href="{{ route('swift.anomalies.index') }}" class="btn btn-sm btn-outline-secondary w-100">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== TABLEAU ===== --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead style="background:#1A5C38; color:white;">
                        <tr>
                            <th class="px-3 py-3">Référence SWIFT</th>
                            <th class="py-3">Type</th>
                            <th class="py-3">Direction</th>
                            <th class="py-3">Montant</th>
                            <th class="py-3">Score IA</th>
                            <th class="py-3">Niveau</th>
                            <th class="py-3">Raisons</th>
                            <th class="py-3">Vérifié</th>
                            <th class="py-3">Détecté le</th>
                            <th class="py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($anomalies as $anomaly)
                        @php
                            $msg     = $anomaly->message;
                            $raisons = is_array($anomaly->raisons) ? $anomaly->raisons : json_decode($anomaly->raisons ?? '[]', true);
                            $rowBg   = match($anomaly->niveau_risque) {
                                'HIGH'   => '#fff5f5',
                                'MEDIUM' => '#fffbf0',
                                default  => 'white',
                            };
                        @endphp
                        <tr style="background: {{ $rowBg }}">
                            {{-- Référence --}}
                            <td class="px-3">
                                @if($msg)
                                    <a href="{{ route('swift.show', $msg->id) }}"
                                       class="fw-bold text-decoration-none" style="color:#1A5C38">
                                        {{ $msg->REFERENCE ?? $msg->reference ?? "#{$msg->id}" }}
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            {{-- Type --}}
                            <td>
                                @if($msg)
                                    <span class="badge rounded-pill"
                                          style="background:#1A5C38; color:white; font-size:11px">
                                        {{ $msg->TYPE_MESSAGE ?? $msg->type_message ?? '—' }}
                                    </span>
                                @endif
                            </td>

                            {{-- Direction --}}
                            <td>
                                @if($msg)
                                    @php $dir = $msg->DIRECTION ?? $msg->direction; @endphp
                                    <span class="badge {{ $dir === 'IN' ? 'bg-primary' : 'bg-dark' }}">
                                        {{ $dir === 'IN' ? 'REÇU' : 'ÉMIS' }}
                                    </span>
                                @endif
                            </td>

                            {{-- Montant --}}
                            <td class="fw-bold">
                                @if($msg)
                                    {{ number_format($msg->AMOUNT ?? $msg->amount ?? 0, 2, ',', ' ') }}
                                    <small class="text-muted">{{ $msg->CURRENCY ?? $msg->currency }}</small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            {{-- Score --}}
                            <td>
                                @php
                                    $score    = (int) $anomaly->score;
                                    $barColor = $score >= 60 ? '#dc3545' : ($score >= 30 ? '#fd7e14' : '#198754');
                                @endphp
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height:8px; width:80px">
                                        <div class="progress-bar" role="progressbar"
                                             style="width:{{ $score }}%; background:{{ $barColor }}"
                                             aria-valuenow="{{ $score }}" aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                    <span class="fw-bold" style="color:{{ $barColor }}; min-width:30px">
                                        {{ $score }}
                                    </span>
                                </div>
                            </td>

                            {{-- Niveau --}}
                            <td>
                                @php
                                    $badgeStyle = match($anomaly->niveau_risque) {
                                        'HIGH'   => 'background:#dc3545; color:white',
                                        'MEDIUM' => 'background:#fd7e14; color:white',
                                        default  => 'background:#198754; color:white',
                                    };
                                    $niveauLabel = match($anomaly->niveau_risque) {
                                        'HIGH'   => '🔴 Critique',
                                        'MEDIUM' => '🟡 Moyen',
                                        default  => '🟢 Faible',
                                    };
                                @endphp
                                <span class="badge" style="{{ $badgeStyle }}">
                                    {{ $niveauLabel }}
                                </span>
                            </td>

                            {{-- Raisons --}}
                            <td>
                                @foreach($raisons as $raison)
                                    <span class="badge bg-secondary me-1 mb-1" style="font-size:10px">
                                        {{ $raison }}
                                    </span>
                                @endforeach
                            </td>

                            {{-- Vérifié --}}
                            <td>
                                @if($anomaly->verifie_par)
                                    <span class="text-success fw-bold">✓</span>
                                    <small class="text-muted d-block">
                                        {{ optional($anomaly->verificateur)->name ?? '—' }}
                                    </small>
                                    <small class="text-muted">
                                        {{ optional($anomaly->verifie_at)->format('d/m/Y') }}
                                    </small>
                                @else
                                    <span class="text-danger fw-bold">✗ En attente</span>
                                @endif
                            </td>

                            {{-- Date --}}
                            <td>
                                <small>{{ optional($anomaly->created_at)->format('d/m/Y H:i') }}</small>
                            </td>

                            {{-- Actions --}}
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    @if($msg)
                                        <a href="{{ route('swift.show', $msg->id) }}"
                                           class="btn btn-sm btn-outline-secondary" title="Voir message">
                                            &#128065;
                                        </a>
                                    @endif

                                    @role('super-admin|swift-manager')
                                    @if(!$anomaly->verifie_par)
                                        <form method="POST"
                                              action="{{ route('swift.anomalies.verify', $anomaly->id) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="btn btn-sm btn-outline-success"
                                                    title="Marquer vérifié">✓</button>
                                        </form>
                                    @endif

                                    <form method="POST"
                                          action="{{ route('swift.anomalies.reanalyze', $anomaly->id) }}">
                                        @csrf
                                        <button type="submit"
                                                class="btn btn-sm btn-outline-primary"
                                                title="Re-analyser">&#8635;</button>
                                    </form>
                                    @endrole
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <div class="fs-4">&#9989;</div>
                                <div>Aucune anomalie détectée pour le moment.</div>
                                <small>Cliquez sur "Analyser tout" pour lancer la première analyse.</small>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($anomalies->hasPages())
        <div class="card-footer bg-white border-0 d-flex justify-content-between align-items-center px-3 py-2">
            <small class="text-muted">
                {{ $anomalies->firstItem() }}–{{ $anomalies->lastItem() }}
                sur {{ $anomalies->total() }} anomalie(s)
            </small>
            {{ $anomalies->links() }}
        </div>
        @endif
    </div>

</div>
@endsection