@extends('layouts.app')

@section('title', 'Dashboard Swift Operator - BTL Bank')

@section('content')
<div class="container-fluid py-4">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h1 class="h2 mb-0 fw-bold">
                <i class="fas fa-globe-americas text-primary me-2"></i>Swift Operator
            </h1>
            <p class="text-muted">Opérations transfrontalières</p>
        </div>

        <div class="d-flex gap-2 flex-wrap">
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
        </div>
    </div>

    <!-- Filtres -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-sliders-h me-2 text-primary"></i>Filtres</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('swift-operator.dashboard') }}" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Direction</label>
                    <select name="direction" class="form-select">
                        <option value="">Toutes</option>
                        <option value="RECU" {{ request('direction') == 'RECU' ? 'selected' : '' }}>Reçus</option>
                        <option value="EMIS" {{ request('direction') == 'EMIS' ? 'selected' : '' }}>Émis</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Statut</label>
                    <select name="status" class="form-select">
                        <option value="">Tous</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                        <option value="processed" {{ request('status') == 'processed' ? 'selected' : '' }}>Traité</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejeté</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date début</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date fin</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Devise</label>
                    <select name="currency" class="form-select">
                        <option value="">Toutes</option>
                        <option value="USD" {{ request('currency') == 'USD' ? 'selected' : '' }}>USD</option>
                        <option value="EUR" {{ request('currency') == 'EUR' ? 'selected' : '' }}>EUR</option>
                        <option value="GBP" {{ request('currency') == 'GBP' ? 'selected' : '' }}>GBP</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-2"></i>Filtrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white text-center shadow">
                <div class="card-body">
                    <h6>Transactions 24h</h6>
                    <h3>{{ $transCount ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white text-center shadow">
                <div class="card-body">
                    <h6>Volume Traité</h6>
                    <h3>{{ $volumeFormatted ?? '0' }}</h3>
                    @if(isset($volumeByDevise) && count($volumeByDevise) > 1)
                        <div class="mt-1" style="font-size:11px;opacity:.85">
                            @foreach($volumeByDevise as $devise => $vol)
                                <span>{{ $devise }}: {{ number_format($vol, 0) }}</span><br>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-white text-center shadow">
                <div class="card-body">
                    <h6>En attente</h6>
                    <h3>{{ $pendingAuth ?? 0 }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau -->
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Messages SWIFT</h5>
            <small>{{ $messages->total() ?? 0 }} messages</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>DATE</th>
                            <th>DIR</th>
                            <th>TYPE</th>
                            <th>RÉFÉRENCE</th>
                            <th>ÉMETTEUR</th>
                            <th>BÉNÉFICIAIRE</th>
                            <th>MONTANT</th>
                            <th>STATUT</th>
                            <th>ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($messages as $msg)
                            <tr>
                                <td>{{ $msg->created_at ? $msg->created_at->format('d/m/Y H:i') : '-' }}</td>
                                <td>
                                    @if($msg->direction == 'IN')
                                        <span class="badge bg-info">Reçu</span>
                                    @else
                                        <span class="badge bg-secondary">Émis</span>
                                    @endif
                                </td>
                                <td><span class="badge bg-secondary">{{ $msg->type_message ?? 'N/A' }}</span></td>
                                <td>{{ $msg->reference ?? 'N/A' }}</td>
                                <td>{{ $msg->sender_name ?? $msg->sender_bic ?? 'N/A' }}</td>
                                <td>{{ $msg->receiver_name ?? $msg->receiver_bic ?? 'N/A' }}</td>
                                <td class="fw-bold">{{ number_format($msg->amount ?? 0, 2) }} {{ $msg->currency ?? 'EUR' }}</td>
                                <td>
                                    @if($msg->status == 'pending')
                                        <span class="badge bg-warning">En attente</span>
                                    @elseif($msg->status == 'processed')
                                        <span class="badge bg-success">Traité</span>
                                    @else
                                        <span class="badge bg-danger">{{ $msg->status }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('swift.show', $msg->id) }}" class="btn btn-outline-info" title="Détail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-success open-raw-file" data-url="{{ route('swift.view-mt', $msg->id) }}" data-title="MT"><i class="fas fa-file-alt"></i></button>
                                        <a href="{{ route('swift.view-mx', $msg->id) }}" target="_blank" class="btn btn-outline-dark" title="MX"><i class="fas fa-code"></i></a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                    <p>Aucun message trouvé</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            {{ $messages->links() }}
        </div>
    </div>
</div>
@endsection
