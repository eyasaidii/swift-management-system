@extends('layouts.app')

@section('title', 'Dashboard Administrateur - BTL Bank')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h1 class="h2 mb-0 fw-bold"><i class="fas fa-crown text-danger me-2"></i>Administration Système</h1>
            <p class="text-muted">Contrôle total - Tous les rôles et opérations</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.users.index') }}" class="btn btn-primary">
                <i class="fas fa-users me-2"></i>Gérer Utilisateurs
            </a>
            
            {{-- NOUVEAU : Bouton Anomalies IA --}}
            @php
                $criticalCount = \App\Models\AnomalySwift::where('niveau_risque', 'HIGH')
                                  ->whereNull('verifie_par')
                                  ->count();
                $totalAnomalies = \App\Models\AnomalySwift::count();
            @endphp
            <a href="{{ route('swift.anomalies.index') }}"
               class="btn {{ $criticalCount > 0 ? 'btn-danger' : 'btn-warning' }} position-relative">
                <i class="fas fa-brain me-2"></i>Anomalies IA
                @if($totalAnomalies > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill {{ $criticalCount > 0 ? 'bg-light text-danger' : 'bg-dark' }}">
                        {{ $totalAnomalies }}
                    </span>
                @endif
            </a>
            
            <a href="{{ route('swift.export-center') }}" class="btn btn-info">
                <i class="fas fa-download me-2"></i>Exporter Tout
            </a>
            <a href="{{ route('swift.index') }}" class="btn btn-success">
                <i class="fas fa-list me-2"></i>Messages SWIFT
            </a>
        </div>
    </div>

    {{-- NOUVEAU : Bannière d'alerte IA si anomalies critiques --}}
    @if($criticalCount > 0)
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%);">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <i class="fas fa-skull-crossbow fs-1 text-white"></i>
                <div>
                    <h5 class="mb-0 text-white fw-bold">🚨 ALERTE SÉCURITÉ IA</h5>
                    <p class="mb-0 text-white-50">
                        {{ $criticalCount }} anomalie(s) critique(s) non vérifiée(s) détectée(s) par l'IA.
                        Une attention immédiate est requise.
                    </p>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('swift.anomalies.index', ['niveau_risque' => 'HIGH', 'verifie' => 'non']) }}"
                   class="btn btn-light btn-sm">
                    <i class="fas fa-eye me-1"></i>Voir les anomalies
                </a>
                <form method="POST" action="{{ route('swift.anomalies.analyze-all') }}"
                      class="d-inline" onsubmit="return confirm('Analyser tous les messages SWIFT ?')">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm">
                        <i class="fas fa-sync-alt me-1"></i>Analyser tout
                    </button>
                </form>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
            </div>
        </div>
    </div>
    @endif

    {{-- Filtres --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-sliders-h me-2 text-primary"></i>Filtres avancés</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.dashboard') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Type message</label>
                    <select name="type_message" class="form-select">
                        <option value="">Tous</option>
                        @foreach(\App\Models\MessageSwift::TYPES as $code => $label)
                            <option value="{{ $code }}" {{ request('type_message') == $code ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Direction</label>
                    <select name="direction" class="form-select">
                        <option value="">Tous</option>
                        <option value="RECU" {{ request('direction') == 'RECU' ? 'selected' : '' }}>Reçus</option>
                        <option value="EMIS" {{ request('direction') == 'EMIS' ? 'selected' : '' }}>Émis</option>
                    </select>
                </div>
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
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-50">
                        <i class="fas fa-filter me-2"></i>Filtrer
                    </button>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-danger w-50">
                        <i class="fas fa-times me-2"></i>Réinitialiser
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Stats existantes --}}
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white text-center shadow">
                <div class="card-body">
                    <h6>Total Messages</h6>
                    <h3>{{ $totalCount ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white text-center shadow">
                <div class="card-body">
                    <h6>Reçus</h6>
                    <h3>{{ $receivedCount ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white text-center shadow">
                <div class="card-body">
                    <h6>Émis</h6>
                    <h3>{{ $emittedCount ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white text-center shadow">
                <div class="card-body">
                    <h6>En attente</h6>
                    <h3>{{ $pendingCount ?? 0 }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Tableau des messages --}}
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Tous les Messages SWIFT</h5>
            <small>{{ $messages->total() ?? 0 }} messages trouvés</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th>DATE</th>
                            <th>TYPE</th>
                            <th>SENDER</th>
                            <th>RÉFÉRENCE</th>
                            <th>MONTANT</th>
                            <th>CUR</th>
                            <th>STATUT</th>
                            <th>ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($messages as $msg)
                        @php
                            $status = $msg->status ?? null;
                            $note   = $msg->authorization_note ?? null;
                        @endphp
                        <tr>
                            <td class="small text-muted">{{ $msg->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                            <td><span class="badge bg-secondary font-monospace">{{ $msg->type_message }}</span></td>
                            <td>
                                {{ $msg->sender_name ?? 'N/A' }}
                                @if($msg->sender_bic)<br><small class="text-muted font-monospace">{{ $msg->sender_bic }}</small>@endif
                            </td>
                            <td class="font-monospace small">{{ $msg->reference }}</td>
                            <td class="fw-bold">{{ number_format($msg->amount ?? 0, 2) }} {{ $msg->currency }}</td>
                            <td>{{ $msg->currency }}</td>
                            <td>
                                @switch($status)
                                    @case('pending')
                                        <span class="badge bg-warning text-dark">⏳ En attente</span>@break
                                    @case('processed')
                                        <span class="badge bg-info text-dark">🔵 À autoriser</span>@break
                                    @case('authorized')
                                        <span class="badge bg-success" @if($note) data-bs-toggle="tooltip" title="{{ $note }}" @endif>✅ Autorisé</span>
                                        @if($note)<br><small class="text-muted fst-italic" style="font-size:10px">{{ Str::limit($note, 35) }}</small>@endif
                                        @break
                                    @case('suspended')
                                        <span class="badge bg-danger" @if($note) data-bs-toggle="tooltip" title="Motif: {{ $note }}" @endif>⛔ Suspendu</span>
                                        @if($note)<br><small class="text-danger fst-italic" style="font-size:10px">{{ Str::limit($note, 35) }}</small>@endif
                                        @break
                                    @case('rejected')
                                        <span class="badge bg-danger">❌ Rejeté</span>@break
                                    @default
                                        <span class="badge bg-secondary">{{ $status }}</span>
                                @endswitch
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('swift.show', $msg->id) }}" class="btn btn-outline-primary" title="Détail"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('swift.pdf', $msg->id) }}" class="btn btn-outline-danger" title="PDF" target="_blank"><i class="fas fa-file-pdf"></i></a>
                                    @if($msg->mt_content)
                                        <button type="button" class="btn btn-outline-secondary open-raw-file" data-url="{{ route('swift.view-mt', $msg->id) }}" data-title="MT"><i class="fas fa-file-alt"></i></button>
                                    @else
                                        <button class="btn btn-outline-secondary disabled" title="Pas de MT"><i class="fas fa-file-alt"></i></button>
                                    @endif
                                    @if($msg->xml_brut)
                                        <a href="{{ route('swift.view-mx', $msg->id) }}" target="_blank" class="btn btn-outline-dark" title="MX"><i class="fas fa-code"></i></a>
                                    @else
                                        <button class="btn btn-outline-secondary disabled" title="Pas de MX"><i class="fas fa-code"></i></button>
                                    @endif
                                    @can('delete', $msg)
                                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $msg->id }}"><i class="fas fa-trash"></i></button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-3x mb-3 d-block opacity-25"></i>
                                <p>Aucun message trouvé</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">{{ $messages->links() }}</div>
    </div>
</div>

@foreach($messages as $msg)
    @can('delete', $msg)
    <div class="modal fade" id="deleteModal{{ $msg->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Confirmer la suppression</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <i class="fas fa-trash-can fa-4x text-danger mb-3"></i>
                    <h5 class="mb-2">{{ $msg->reference }}</h5>
                    <p class="fw-bold mt-3">Voulez-vous vraiment supprimer ce message ?</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Annuler</button>
                    <form action="{{ route('swift.destroy', $msg->id) }}" method="POST" class="d-inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger px-4">Supprimer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endcan
@endforeach

<script>
    setTimeout(function () {
        document.querySelectorAll('.alert').forEach(function (el) {
            bootstrap.Alert.getOrCreateInstance(el).close();
        });
    }, 5000);
</script>
@endsection