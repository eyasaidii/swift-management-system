{{-- resources/views/chargee/dashboard.blade.php --}}
@extends('layouts.app')

@section('title', 'Dashboard Chargé(e) Clientèle - BTL Bank')

@section('content')
<div class="container-fluid py-4">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h1 class="h2 mb-0 fw-bold">
                <i class="fas fa-users text-secondary me-2"></i>Chargé(e) Clientèle
            </h1>
            <p class="text-muted">Gestion des clients et opérations SWIFT</p>
        </div>

        <div class="d-flex gap-2 flex-wrap">
            {{-- ✅ AJOUT : bouton Nouveau SWIFT --}}
            @can('create', App\Models\MessageSwift::class)
                <a href="{{ route('swift.create') }}" class="btn btn-success">
                    <i class="fas fa-plus me-2"></i>Nouveau SWIFT
                </a>
            @endcan
            @can('import', App\Models\MessageSwift::class)
                <a href="{{ route('swift.import.form') }}" class="btn btn-warning">
                    <i class="fas fa-file-import me-2"></i>Importer
                </a>
            @endcan
            @can('export', App\Models\MessageSwift::class)
                <a href="{{ route('swift.export') }}" class="btn btn-info">
                    <i class="fas fa-download me-2"></i>Exporter
                </a>
            @endcan
        </div>
    </div>

    {{-- ✅ AJOUT : Messages flash --}}
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

    <!-- Filtres -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-sliders-h me-2 text-primary"></i>Filtres</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('chargee.dashboard') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Statut</label>
                    <select name="status" class="form-select">
                        <option value="">Tous</option>
                        <option value="pending"   {{ request('status') == 'pending'   ? 'selected' : '' }}>En attente</option>
                        <option value="processed" {{ request('status') == 'processed' ? 'selected' : '' }}>Traité</option>
                        <option value="rejected"  {{ request('status') == 'rejected'  ? 'selected' : '' }}>Rejeté</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date début</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date fin</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-50">
                        <i class="fas fa-filter me-2"></i>Filtrer
                    </button>
                    <a href="{{ route('chargee.dashboard') }}" class="btn btn-outline-danger w-50">
                        <i class="fas fa-times me-2"></i>Réinitialiser
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white text-center shadow">
                <div class="card-body">
                    <h6><i class="fas fa-envelope me-1"></i>Total messages</h6>
                    <h3 class="mb-0">{{ $totalCount ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white text-center shadow">
                <div class="card-body">
                    <h6><i class="fas fa-paper-plane me-1"></i>Émis</h6>
                    <h3 class="mb-0">{{ $outCount ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-white text-center shadow">
                <div class="card-body">
                    <h6><i class="fas fa-clock me-1"></i>En attente</h6>
                    <h3 class="mb-0">{{ $pendingCount ?? 0 }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau -->
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-paper-plane me-2"></i>Messages SWIFT (Émis)</h5>
            <small class="text-muted">{{ $messages->total() ?? 0 }} messages</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>DATE</th>
                            <th>TYPE</th>
                            <th>RÉFÉRENCE</th>
                            <th>BÉNÉFICIAIRE</th>
                            <th>MONTANT</th>
                            <th>STATUT</th>
                            <th>ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($messages as $msg)
                            <tr class="{{ $msg->status == 'rejected' ? 'table-danger' : ($msg->status == 'pending' ? 'table-warning' : '') }}">
                                <td class="text-nowrap small text-muted">
                                    {{ $msg->created_at ? $msg->created_at->format('d/m/Y H:i') : '-' }}
                                </td>
                                <td>
                                    <span class="badge bg-secondary font-monospace">
                                        {{ $msg->type_message ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="font-monospace small">{{ $msg->reference ?? 'N/A' }}</td>
                                <td>{{ $msg->receiver_name ?? $msg->receiver_bic ?? 'N/A' }}</td>
                                <td class="fw-bold text-nowrap">
                                    {{ number_format($msg->amount ?? 0, 2) }} {{ $msg->currency ?? 'EUR' }}
                                </td>
                                <td>
                                    @switch($msg->status)
                                        @case('pending')
                                            <span class="badge bg-warning text-dark">⏳ En attente</span>
                                            @break
                                        @case('processed')
                                            <span class="badge bg-success">✅ Traité</span>
                                            @break
                                        @case('rejected')
                                            {{-- ✅ AJOUT : affichage clair du rejet pour le chargee --}}
                                            <span class="badge bg-danger">❌ Rejeté</span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary">{{ $msg->status }}</span>
                                    @endswitch
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('swift.show', $msg->id) }}"
                                           class="btn btn-outline-info" title="Détail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($msg->mt_content)
                                            <a href="{{ route('swift.view-mt', $msg->id) }}"
                                               class="btn btn-outline-success"
                                               title="Voir MT" target="_blank">MT</a>
                                        @else
                                            <button class="btn btn-outline-secondary disabled"
                                                    title="MT non disponible (en attente de traitement)">MT</button>
                                        @endif
                                        @if($msg->xml_brut)
                                            <a href="{{ route('swift.view-mx', $msg->id) }}"
                                               class="btn btn-outline-dark"
                                               title="Voir XML MX" target="_blank">&lt;/&gt;</a>
                                        @else
                                            <button class="btn btn-outline-secondary disabled"
                                                    title="MX non disponible">&lt;/&gt;</button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3 d-block opacity-25"></i>
                                    <p class="mb-1 fw-semibold">Aucun message SWIFT émis</p>
                                    <small>
                                        <a href="{{ route('swift.create') }}">Créer votre premier message SWIFT</a>
                                    </small>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white d-flex justify-content-between align-items-center">
            <small class="text-muted">
                @if(($messages->total() ?? 0) > 0)
                    Affichage {{ $messages->firstItem() }}–{{ $messages->lastItem() }}
                    sur {{ $messages->total() }} résultat(s)
                @endif
            </small>
            {{ $messages->links() }}
        </div>
    </div>
</div>

@if(session('success') || session('error'))
<script>
    setTimeout(function () {
        document.querySelectorAll('.alert').forEach(function (el) {
            bootstrap.Alert.getOrCreateInstance(el).close();
        });
    }, 5000);
</script>
@endif
@endsection