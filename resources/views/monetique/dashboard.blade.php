@extends('layouts.app')
@section('title', 'Dashboard Monétique - BTL Bank')
@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h1 class="h2 mb-0 fw-bold"><i class="fas fa-credit-card text-success me-2"></i>Monétique</h1>
            <p class="text-muted">Transactions cartes & surveillance fraude</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            @can('export', App\Models\MessageSwift::class)
                <a href="{{ route('swift.export-center') }}" class="btn btn-info"><i class="fas fa-download me-2"></i>Export Center</a>
            @endcan
        </div>
    </div>

    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-white"><h5 class="mb-0"><i class="fas fa-sliders-h me-2 text-primary"></i>Filtres</h5></div>
        <div class="card-body">
            <form method="GET" action="{{ route('monetique.dashboard') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Statut</label>
                    <select name="status" class="form-select">
                        <option value="">Tous</option>
                        <option value="pending"    {{ request('status') == 'pending'    ? 'selected' : '' }}>En attente</option>
                        <option value="processed"  {{ request('status') == 'processed'  ? 'selected' : '' }}>Traité</option>
                        <option value="authorized" {{ request('status') == 'authorized' ? 'selected' : '' }}>Autorisé</option>
                        <option value="suspended"  {{ request('status') == 'suspended'  ? 'selected' : '' }}>Suspendu</option>
                        <option value="rejected"   {{ request('status') == 'rejected'   ? 'selected' : '' }}>Rejeté</option>
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
                    <button type="submit" class="btn btn-primary w-50"><i class="fas fa-filter me-2"></i>Filtrer</button>
                    <a href="{{ route('monetique.dashboard') }}" class="btn btn-outline-danger w-50"><i class="fas fa-times me-2"></i>Réinitialiser</a>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white text-center shadow">
                <div class="card-body"><h6>Total messages</h6><h3>{{ $totalCount ?? 0 }}</h3></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white text-center shadow">
                <div class="card-body"><h6>Reçus</h6><h3>{{ $inCount ?? 0 }}</h3></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-white text-center shadow">
                <div class="card-body"><h6>En attente</h6><h3>{{ $pendingCount ?? 0 }}</h3></div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-inbox me-2"></i>Messages SWIFT (Reçus)</h5>
            <small>{{ $messages->total() ?? 0 }} messages</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="bg-light">
                        <tr><th>DATE</th><th>TYPE</th><th>RÉFÉRENCE</th><th>EXPÉDITEUR</th><th>MONTANT</th><th>STATUT</th><th>ACTIONS</th></tr>
                    </thead>
                    <tbody>
                        @forelse($messages as $msg)
                        @php $note = $msg->authorization_note ?? null; @endphp
                        <tr>
                            <td class="small text-muted">{{ $msg->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                            <td><span class="badge bg-secondary font-monospace">{{ $msg->type_message ?? 'N/A' }}</span></td>
                            <td class="font-monospace small">{{ $msg->reference ?? 'N/A' }}</td>
                            <td>{{ $msg->sender_name ?? $msg->sender_bic ?? 'N/A' }}</td>
                            <td class="fw-bold">{{ number_format($msg->amount ?? 0, 2) }} {{ $msg->currency ?? 'EUR' }}</td>
                            <td>
                                @switch($msg->status)
                                    @case('pending')<span class="badge bg-warning text-dark">⏳ En attente</span>@break
                                    @case('processed')<span class="badge bg-info text-dark">🔵 À autoriser</span>@break
                                    @case('authorized')
                                        <span class="badge bg-success" @if($note) data-bs-toggle="tooltip" title="{{ $note }}" @endif>✅ Autorisé</span>
                                        @if($note)<br><small class="text-success fst-italic" style="font-size:10px">{{ Str::limit($note, 35) }}</small>@endif
                                        @break
                                    @case('suspended')
                                        <span class="badge bg-danger" @if($note) data-bs-toggle="tooltip" title="Motif: {{ $note }}" @endif>⛔ Suspendu</span>
                                        @if($note)<br><small class="text-danger fst-italic" style="font-size:10px">{{ Str::limit($note, 35) }}</small>@endif
                                        @break
                                    @case('rejected')<span class="badge bg-danger">❌ Rejeté</span>@break
                                    @default<span class="badge bg-secondary">{{ $msg->status }}</span>
                                @endswitch
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('swift.show', $msg->id) }}" class="btn btn-outline-primary" title="Détail"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('swift.pdf', $msg->id) }}" class="btn btn-outline-danger" title="PDF" target="_blank"><i class="fas fa-file-pdf"></i></a>
                                    @if($msg->mt_content)
                                        <a href="{{ route('swift.view-mt', $msg->id) }}" class="btn btn-outline-secondary" title="MT" target="_blank"><i class="fas fa-file-alt"></i></a>
                                    @else
                                        <button class="btn btn-outline-secondary disabled"><i class="fas fa-file-alt"></i></button>
                                    @endif
                                    @if($msg->xml_brut)
                                        <a href="{{ route('swift.view-mx', $msg->id) }}" class="btn btn-outline-dark" title="MX" target="_blank"><i class="fas fa-code"></i></a>
                                    @else
                                        <button class="btn btn-outline-secondary disabled"><i class="fas fa-code"></i></button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center py-5 text-muted"><i class="fas fa-inbox fa-3x mb-3 d-block opacity-25"></i><p>Aucun message trouvé</p></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">{{ $messages->links() }}</div>
    </div>
</div>
@endsection