@extends('layouts.app')

@section('title', 'Détail du message SWIFT')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 fw-bold mb-0">Détail du message SWIFT</h1>
                <div class="d-flex gap-2">
                    <a href="{{ route('swift.pdf', $message->id) }}" target="_blank" class="btn btn-danger">
                        <i class="fas fa-file-pdf me-2"></i>Télécharger PDF
                    </a>
                    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Retour
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- ============================================================ --}}
            {{-- NOTIFICATION SWIFT-OPERATOR : note d'autorisation reçue      --}}
            {{-- ============================================================ --}}
            @if($message->status === 'authorized' && $message->authorization_note && auth()->user()->hasRole('swift-operator'))
                <div class="alert alert-info alert-dismissible fade show border-0 shadow-sm mb-4"
                     style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
                    <div class="d-flex align-items-start gap-3">
                        <i class="fas fa-bell fs-4 text-white mt-1"></i>
                        <div class="flex-grow-1">
                            <h6 class="mb-1 text-white fw-bold">
                                ✅ Message autorisé — Note du Swift Manager
                            </h6>
                            <p class="mb-0 text-white-50 small">
                                Autorisé par
                                <strong class="text-white">
                                    {{ optional($message->authorizer)->name ?? 'Swift Manager' }}
                                </strong>
                                le {{ optional($message->authorized_at ?? $message->AUTHORIZED_AT)->format('d/m/Y à H:i') ?? '—' }}
                            </p>
                            <div class="mt-2 p-2 rounded" style="background: rgba(255,255,255,0.15);">
                                <i class="fas fa-quote-left text-white-50 me-1" style="font-size:10px"></i>
                                <span class="text-white fst-italic">{{ $message->authorization_note }}</span>
                                <i class="fas fa-quote-right text-white-50 ms-1" style="font-size:10px"></i>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            @endif
            {{-- ============================================================ --}}

            {{-- Informations générales --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Informations générales</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Type :</strong>
                                <span class="badge bg-secondary font-monospace ms-1">{{ $message->type_message }}</span>
                            </p>
                            <p><strong>Référence :</strong>
                                <span class="font-monospace">{{ $message->reference }}</span>
                            </p>
                            <p><strong>Direction :</strong>
                                @if($message->direction == 'IN')
                                    <span class="badge bg-primary">Reçu</span>
                                @else
                                    <span class="badge bg-dark">Émis</span>
                                @endif
                            </p>
                            <p><strong>Statut :</strong>
                                @switch($message->status)
                                    @case('pending')
                                        <span class="badge bg-warning text-dark">⏳ En attente</span>
                                        @break
                                    @case('processed')
                                        <span class="badge bg-info text-dark">🔵 À autoriser</span>
                                        @break
                                    @case('authorized')
                                        <span class="badge bg-success">✅ Autorisé</span>
                                        @break
                                    @case('suspended')
                                        <span class="badge bg-danger">⛔ Suspendu</span>
                                        @break
                                    @case('rejected')
                                        <span class="badge bg-danger">❌ Rejeté</span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary">{{ $message->status }}</span>
                                @endswitch
                            </p>
                            {{-- ================================ --}}

                        </div>
                    </div>
                </div>
            </div>

            {{-- Transaction associée --}}
            @if($message->transaction)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-exchange-alt me-2 text-success"></i>Transaction associée
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 border-end">
                            <small class="text-muted d-block mb-1">Montant</small>
                            <strong class="fs-5">
                                {{ number_format($message->transaction->montant, 2) }}
                                {{ $message->transaction->devise }}
                            </strong>
                        </div>
                        <div class="col-md-3 border-end">
                            <small class="text-muted d-block mb-1">Émetteur</small>
                            <strong>{{ $message->transaction->emetteur }}</strong>
                        </div>
                        <div class="col-md-3 border-end">
                            <small class="text-muted d-block mb-1">Récepteur</small>
                            <strong>{{ $message->transaction->recepteur }}</strong>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block mb-1">Date transaction</small>
                            <strong>
                                {{ \Carbon\Carbon::parse($message->transaction->date_transaction)->format('d/m/Y') }}
                            </strong>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- ============================================================ --}}
            {{-- BLOC IA — SCORE DE RISQUE & ANOMALIES                        --}}
            {{-- ============================================================ --}}
            @php $anomaly = $message->anomaly; @endphp

            @if($anomaly)
                @php
                    $score       = (int) $anomaly->score;
                    $niveau      = $anomaly->niveau_risque;
                    $barColor    = $score >= 60 ? '#dc3545' : ($score >= 20 ? '#fd7e14' : '#198754');
                    $borderColor = $score >= 60 ? '#dc3545' : ($score >= 20 ? '#fd7e14' : '#198754');
                    $bgAlert     = $score >= 60 ? '#fff5f5' : ($score >= 20 ? '#fffbf0' : '#f0fff4');
                    $niveauLabel = match($niveau) {
                        'HIGH'   => '🔴 Risque Critique',
                        'MEDIUM' => '🟡 Risque Moyen',
                        default  => '🟢 Risque Faible',
                    };
                    $raisons = is_array($anomaly->raisons)
                        ? $anomaly->raisons
                        : json_decode($anomaly->raisons ?? '[]', true);
                    $raisonLabels = [
                        'MONTANT_ZERO'        => ['label' => 'Montant nul',         'icon' => 'fas fa-ban',                'color' => 'danger'],
                        'MONTANT_ELEVE'       => ['label' => 'Montant élevé',       'icon' => 'fas fa-arrow-up',           'color' => 'warning'],
                        'STATUT_REJETE'       => ['label' => 'Statut rejeté',       'icon' => 'fas fa-times-circle',       'color' => 'danger'],
                        'TRANSLATION_ERROR'   => ['label' => 'Erreur XML',          'icon' => 'fas fa-code',               'color' => 'danger'],
                        'TYPE_ERROR'          => ['label' => 'Type invalide',       'icon' => 'fas fa-exclamation',        'color' => 'danger'],
                        'DOUBLON_REFERENCE'   => ['label' => 'Doublon référence',   'icon' => 'fas fa-copy',               'color' => 'warning'],
                        'BIC_MANQUANT'        => ['label' => 'BIC manquant',        'icon' => 'fas fa-university',         'color' => 'warning'],
                        'DEVISE_INHABITUELLE' => ['label' => 'Devise inhabituelle', 'icon' => 'fas fa-coins',              'color' => 'warning'],
                        'IMPORT_FAILED'       => ['label' => 'Import échoué',       'icon' => 'fas fa-file-excel',         'color' => 'danger'],
                        'PASSPORT_DETECTE'    => ['label' => 'Passeport détecté',   'icon' => 'fas fa-id-card',            'color' => 'danger'],
                    ];
                @endphp

                {{-- Bannière alerte critique --}}
                @if($niveau === 'HIGH' && !$anomaly->verifie_par && !$anomaly->rejetee_par)
                    @role('super-admin|swift-manager')
                    <a href="{{ route('swift.anomalies.index', ['niveau_risque' => 'HIGH', 'verifie' => 'non']) }}"
                       class="d-flex align-items-center gap-3 text-decoration-none mb-3 px-4 py-3 rounded-3"
                       style="background:#dc3545; color:white; transition:opacity .2s"
                       onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
                        <span style="font-size:1.5rem">🚨</span>
                        <div class="flex-grow-1">
                            <strong>Anomalie critique non vérifiée</strong>
                            <span class="ms-2 small opacity-75">
                                — Ce message présente un score de risque élevé. Cliquez pour accéder au tableau de bord.
                            </span>
                        </div>
                        <i class="fas fa-chevron-right"></i>
                    </a>
                    @endrole
                @endif

                <div class="card shadow-sm mb-4 border-0"
                     style="border-left: 5px solid {{ $borderColor }} !important; background: {{ $bgAlert }};">
                    <div class="card-header border-0 d-flex justify-content-between align-items-center py-3"
                         style="background:transparent;">
                        <h5 class="mb-0 d-flex align-items-center gap-2">
                            <i class="fas fa-brain" style="color:{{ $borderColor }}"></i>
                            Analyse IA — Détection d'Anomalies
                            @role('super-admin|swift-manager')
                            <a href="{{ route('swift.anomalies.index', ['niveau_risque' => $niveau]) }}"
                               class="badge text-decoration-none ms-1"
                               style="background:{{ $borderColor }}; color:white; font-size:12px; padding:5px 10px; border-radius:20px; transition:opacity .2s"
                               onmouseover="this.style.opacity='.75'" onmouseout="this.style.opacity='1'">
                                {{ $niveauLabel }}
                                <i class="fas fa-external-link-alt ms-1" style="font-size:10px"></i>
                            </a>
                            @else
                            <span class="badge ms-1"
                                  style="background:{{ $borderColor }}; color:white; font-size:12px; padding:5px 10px; border-radius:20px;">
                                {{ $niveauLabel }}
                            </span>
                            @endrole
                        </h5>
                        <div class="d-flex gap-2 align-items-center">
                            @role('super-admin|swift-manager')
                            <a href="{{ route('swift.anomalies.index') }}"
                               class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1">
                                <i class="fas fa-table"></i>
                                <span class="d-none d-md-inline">Tableau de bord</span>
                            </a>
                            <a href="{{ route('swift.anomalies.show', $anomaly->id) }}"
                               class="btn btn-sm d-flex align-items-center gap-1"
                               style="background:#1A5C38; color:white; border:none;">
                                <i class="fas fa-search-plus"></i>
                                <span class="d-none d-md-inline">Détail anomalie</span>
                            </a>
                            @endrole
                        </div>
                    </div>

                    <div class="card-body pt-0">
                        <div class="row g-3 align-items-center">

                            {{-- Jauge score --}}
                            <div class="col-md-3 text-center">
                                <div class="fw-bold mb-0"
                                     style="font-size:3rem; line-height:1; color:{{ $barColor }}">
                                    {{ $score }}
                                </div>
                                <div class="text-muted small mb-2">/100</div>
                                <div class="progress w-100" style="height:12px; border-radius:6px; min-width:140px">
                                    <div class="progress-bar" role="progressbar"
                                         style="width:{{ $score }}%; background:{{ $barColor }}; border-radius:6px; transition:width 1s ease"
                                         aria-valuenow="{{ $score }}" aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>
                                <small class="text-muted mt-1">Score de risque IA</small>
                            </div>

                            {{-- Raisons --}}
                            <div class="col-md-5">
                                <div class="text-muted small fw-bold mb-2 text-uppercase"
                                     style="letter-spacing:.05em">Anomalies détectées</div>
                                @if(count($raisons) > 0)
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($raisons as $raison)
                                            @php
                                                $info = $raisonLabels[$raison]
                                                     ?? ['label' => $raison, 'icon' => 'fas fa-exclamation-triangle', 'color' => 'secondary'];
                                            @endphp
                                            <span class="badge d-flex align-items-center gap-1 bg-{{ $info['color'] }}"
                                                  style="font-size:12px; padding:6px 10px; border-radius:20px">
                                                <i class="{{ $info['icon'] }}" style="font-size:10px"></i>
                                                {{ $info['label'] }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="d-flex align-items-center gap-2 text-success">
                                        <i class="fas fa-check-circle"></i>
                                        <span>Aucune anomalie spécifique détectée</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Vérification --}}
                            <div class="col-md-4 border-start ps-4">
                                <div class="text-muted small fw-bold mb-2 text-uppercase"
                                     style="letter-spacing:.05em">Vérification</div>
                                @if($anomaly->rejetee_par)
                                    <div class="d-flex align-items-center gap-2 text-danger mb-1">
                                        <i class="fas fa-times-circle fs-5"></i>
                                        <span class="fw-bold">Rejetée</span>
                                    </div>
                                    <div class="small text-muted">
                                        <i class="fas fa-user me-1"></i>
                                        {{ optional($anomaly->rejecteur)->name ?? '—' }}
                                    </div>
                                    <div class="small text-muted">
                                        <i class="fas fa-calendar me-1"></i>
                                        {{ optional($anomaly->rejetee_at)->format('d/m/Y à H:i') }}
                                    </div>
                                @elseif($anomaly->verifie_par)
                                    <div class="d-flex align-items-center gap-2 text-success mb-1">
                                        <i class="fas fa-check-circle fs-5"></i>
                                        <span class="fw-bold">Vérifiée</span>
                                    </div>
                                    <div class="small text-muted">
                                        <i class="fas fa-user me-1"></i>
                                        {{ optional($anomaly->verificateur)->name ?? '—' }}
                                    </div>
                                    <div class="small text-muted">
                                        <i class="fas fa-calendar me-1"></i>
                                        {{ optional($anomaly->verifie_at)->format('d/m/Y à H:i') }}
                                    </div>
                                @else
                                    <div class="d-flex align-items-center gap-2 text-warning mb-2">
                                        <i class="fas fa-clock fs-5"></i>
                                        <span class="fw-bold">En attente</span>
                                    </div>
                                    @role('super-admin|swift-manager')
                                    <form method="POST"
                                          action="{{ route('swift.anomalies.verify', $anomaly->id) }}"
                                          class="d-inline">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-success me-1">
                                            <i class="fas fa-check me-1"></i>Accepter
                                        </button>
                                    </form>
                                    <form method="POST"
                                          action="{{ route('swift.anomalies.reject', $anomaly->id) }}"
                                          class="d-inline"
                                          onsubmit="return confirm('Rejeter ce message SWIFT ?')">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-times me-1"></i>Rejeter
                                        </button>
                                    </form>
                                    @endrole
                                @endif

                                @role('super-admin|swift-manager')
                                <div class="mt-2">
                                    <form method="POST"
                                          action="{{ route('swift.anomalies.reanalyze', $anomaly->id) }}"
                                          class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-sync-alt me-1"></i>Re-analyser
                                        </button>
                                    </form>
                                </div>
                                @endrole
                            </div>

                        </div>
                    </div>
                </div>

            @else
                {{-- Message jamais analysé --}}
                @role('super-admin|swift-manager')
                <div class="card shadow-sm mb-4 border-0 border-start border-4 border-secondary">
                    <div class="card-body d-flex justify-content-between align-items-center py-3">
                        <div class="d-flex align-items-center gap-3">
                            <i class="fas fa-brain text-muted fs-4"></i>
                            <div>
                                <div class="fw-bold">Analyse IA non disponible</div>
                                <small class="text-muted">
                                    Ce message n'a pas encore été analysé par le moteur de détection.
                                </small>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <form method="POST"
                                  action="{{ route('swift.anomalies.analyze-single', $message->id) }}">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-play me-1"></i>Lancer l'analyse
                                </button>
                            </form>
                            <a href="{{ route('swift.anomalies.index') }}"
                               class="btn btn-sm btn-outline-dark d-flex align-items-center gap-1">
                                <i class="fas fa-table"></i>
                                <span>Tableau anomalies</span>
                            </a>
                        </div>
                    </div>
                </div>
                @endrole
            @endif
            {{-- ============================================================ --}}

            {{-- Détails spécifiques --}}
            @if($message->details && $message->details->count())
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Détails spécifiques ({{ $message->type_message }})</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:120px">Tag</th>
                                <th>Valeur</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($message->details as $detail)
                            <tr>
                                <td class="font-monospace fw-bold text-success">{{ $detail->tag_name }}</td>
                                <td class="font-monospace">{{ $detail->tag_value }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- Boutons d'action --}}
            <div class="card shadow-sm">
                <div class="card-body d-flex gap-2 flex-wrap align-items-center">
                    @php
                        $status       = $message->status;
                        $direction    = $message->direction;
                        $user         = auth()->user();
                        $canAct       = $user->hasRole('super-admin')
                                     || $user->hasRole('swift-manager')
                                     || ($user->hasRole(['chef-agence', 'chargee']) && $direction === 'OUT');
                        $canAuthorize = $user->hasRole(['super-admin', 'swift-manager']);
                    @endphp

                    @if($status === 'pending' && $canAct)
                        <form method="POST" action="{{ route('swift.process', $message->id) }}"
                              onsubmit="return confirm('Confirmer le traitement ?')">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check me-2"></i>Traiter
                            </button>
                        </form>
                        <form method="POST" action="{{ route('swift.reject', $message->id) }}"
                              onsubmit="return confirm('Confirmer le rejet ?')">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-times me-2"></i>Rejeter
                            </button>
                        </form>
                    @endif

                    @if($status === 'processed' && $canAuthorize)
                        <button type="button" class="btn btn-success"
                                data-bs-toggle="modal" data-bs-target="#modalAuthorize"
                                data-id="{{ $message->id }}" data-ref="{{ $message->reference }}">
                            <i class="fas fa-shield-alt me-2"></i>Autoriser
                        </button>
                    @endif

                    @if(in_array($status, ['processed', 'authorized']) && $canAuthorize)
                        <button type="button" class="btn btn-warning"
                                data-bs-toggle="modal" data-bs-target="#modalSuspend"
                                data-id="{{ $message->id }}" data-ref="{{ $message->reference }}">
                            <i class="fas fa-pause-circle me-2"></i>Suspendre
                        </button>
                    @endif

                    <button type="button"
                            class="btn btn-outline-secondary open-raw-file"
                            data-url="{{ route('swift.view-mt', $message->id) }}"
                            data-title="MT Content">
                        <i class="fas fa-file-alt me-2"></i>Voir MT
                    </button>

                    <a href="{{ route('swift.view-mx', $message->id) }}" target="_blank" class="btn btn-outline-info" title="MX (XML)">
                        <i class="fas fa-code me-2"></i>Voir MX
                    </a>

                    <a href="{{ route('swift.pdf', $message->id) }}" target="_blank"
                       class="btn btn-outline-danger">
                        <i class="fas fa-file-pdf me-2"></i>PDF
                    </a>

                    @if($user->hasRole('super-admin'))
                        <form method="POST" action="{{ route('swift.destroy', $message->id) }}"
                              class="ms-auto"
                              onsubmit="return confirm('Supprimer définitivement ce message ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="fas fa-trash me-2"></i>Supprimer
                            </button>
                        </form>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>

{{-- ============================================================ --}}
{{-- MODAL AUTORISER                                              --}}
{{-- ============================================================ --}}
<div class="modal fade" id="modalAuthorize" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formAuthorize" method="POST" action="">
                @csrf @method('PATCH')
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-shield-alt me-2"></i>Autoriser le virement
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Vous allez autoriser le message <strong id="authorizeRef"></strong>.</p>
                    <p class="text-muted small mb-3">
                        Le virement est conforme aux règles de contrôle des changes et peut être transmis via SWIFT.
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Note d'autorisation (optionnel)</label>
                        <textarea name="note"
                                  id="authorizeNote"
                                  class="form-control"
                                  rows="3"
                                  placeholder="ex: Vérifié — transaction conforme BCT"></textarea>
                        <div class="form-text text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Cette note sera visible par l'opérateur SWIFT.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Annuler
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-shield-alt me-2"></i>Confirmer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ============================================================ --}}
{{-- MODAL SUSPENDRE                                              --}}
{{-- ============================================================ --}}
<div class="modal fade" id="modalSuspend" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formSuspend" method="POST" action="">
                @csrf @method('PATCH')
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">
                        <i class="fas fa-pause-circle me-2"></i>Suspendre le message
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Vous allez suspendre le message <strong id="suspendRef"></strong>.</p>
                    <p class="text-muted small mb-3">
                        Le message sera bloqué et ne pourra plus être transmis.
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            Motif de suspension <span class="text-danger">*</span>
                        </label>
                        <textarea name="note"
                                  class="form-control"
                                  rows="3"
                                  required
                                  placeholder="ex: En attente de documentation complémentaire"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Annuler
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-pause-circle me-2"></i>Confirmer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // Modal Autoriser
    const modalAuthorize = document.getElementById('modalAuthorize');
    if (modalAuthorize) {
        modalAuthorize.addEventListener('show.bs.modal', function (e) {
            const btn = e.relatedTarget;
            document.getElementById('authorizeRef').textContent = btn.getAttribute('data-ref');
            document.getElementById('formAuthorize').action =
                '/swift/' + btn.getAttribute('data-id') + '/authorize';
            // Vider la note à chaque ouverture
            const noteField = document.getElementById('authorizeNote');
            if (noteField) noteField.value = '';
        });
    }

    // Modal Suspendre
    const modalSuspend = document.getElementById('modalSuspend');
    if (modalSuspend) {
        modalSuspend.addEventListener('show.bs.modal', function (e) {
            const btn = e.relatedTarget;
            document.getElementById('suspendRef').textContent = btn.getAttribute('data-ref');
            document.getElementById('formSuspend').action =
                '/swift/' + btn.getAttribute('data-id') + '/suspend';
        });
    }

        // Raw file viewer — use global modal from layout
        // (no duplicate modal creation needed here)

});
</script>

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