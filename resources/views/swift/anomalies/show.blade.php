@extends('layouts.app')

@section('title', 'Détail Anomalie — IA')

@section('content')
<div class="container-fluid py-4">

    {{-- EN-TÊTE --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="fas fa-brain me-2" style="color:#1A5C38"></i>
                Détail de l'Anomalie #{{ $anomaly->id }}
            </h4>
            <small class="text-muted">Moteur de détection — Règles métier SWIFT</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('swift.anomalies.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Retour anomalies
            </a>
            @if($anomaly->message)
                <a href="{{ route('swift.show', $anomaly->message->id) }}"
                   class="btn btn-sm text-white" style="background:#1A5C38">
                    <i class="fas fa-eye me-1"></i> Voir message SWIFT
                </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @php
        $score       = (int) $anomaly->score;
        $niveau      = $anomaly->niveau_risque;
        $barColor    = $score >= 60 ? '#dc3545' : ($score >= 20 ? '#fd7e14' : '#198754');
        $bgCard      = $score >= 60 ? '#fff5f5' : ($score >= 20 ? '#fffbf0' : '#f0fff4');
        $niveauLabel = match($niveau) {
            'HIGH'   => '🔴 Risque Critique',
            'MEDIUM' => '🟡 Risque Moyen',
            default  => '🟢 Risque Faible',
        };
        $raisons = is_array($anomaly->raisons)
            ? $anomaly->raisons
            : json_decode($anomaly->raisons ?? '[]', true);
        $raisonDetails = [
            'MONTANT_ZERO'        => ['label' => 'Montant nul',         'icon' => 'fas fa-ban',          'color' => 'danger',   'desc' => 'Le montant de la transaction est égal à zéro, ce qui est anormal pour un message SWIFT de paiement.'],
            'MONTANT_ELEVE'       => ['label' => 'Montant très élevé',  'icon' => 'fas fa-arrow-up',     'color' => 'warning',  'desc' => 'Le montant dépasse le seuil de 100 000 unités monétaires. Une vérification manuelle est recommandée.'],
            'STATUT_REJETE'       => ['label' => 'Statut rejeté',       'icon' => 'fas fa-times-circle', 'color' => 'danger',   'desc' => 'Le message a été rejeté lors du traitement. Cela peut indiquer un problème de conformité ou de données.'],
            'TRANSLATION_ERROR'   => ['label' => 'Erreur XML',          'icon' => 'fas fa-code',         'color' => 'danger',   'desc' => 'Des erreurs ont été détectées lors de la traduction du format MT vers MX (ISO 20022).'],
            'TYPE_ERROR'          => ['label' => 'Type invalide',       'icon' => 'fas fa-exclamation',  'color' => 'danger',   'desc' => 'Le type de message contient une valeur ERROR indiquant un échec d\'import ou de parsing.'],
            'DOUBLON_REFERENCE'   => ['label' => 'Doublon référence',   'icon' => 'fas fa-copy',         'color' => 'warning',  'desc' => 'Une autre transaction avec la même référence SWIFT existe dans le système.'],
            'BIC_MANQUANT'        => ['label' => 'BIC manquant',        'icon' => 'fas fa-university',   'color' => 'warning',  'desc' => 'Le code BIC de l\'émetteur ou du bénéficiaire est absent, ce qui est obligatoire pour les virements SWIFT.'],
            'DEVISE_INHABITUELLE' => ['label' => 'Devise inhabituelle', 'icon' => 'fas fa-coins',        'color' => 'warning',  'desc' => 'La devise utilisée est inhabituelle (hors EUR, USD, TND, GBP, CHF). Un contrôle supplémentaire est conseillé.'],
            'IMPORT_FAILED'       => ['label' => 'Import échoué',       'icon' => 'fas fa-file-excel',   'color' => 'danger',   'desc' => 'La référence indique explicitement un échec d\'import (IMPORT-FAILED). Le message nécessite une correction.'],
            'PASSPORT_DETECTE'    => ['label' => 'Passeport détecté',   'icon' => 'fas fa-id-card',      'color' => 'danger',   'desc' => 'Un numéro de passeport a été détecté dans le champ bénéficiaire, ce qui peut constituer un risque de conformité.'],
        ];
    @endphp

    <div class="row g-4">

        {{-- COLONNE GAUCHE — Score + Raisons --}}
        <div class="col-lg-8">

            {{-- Score card --}}
            <div class="card border-0 shadow-sm mb-4" style="background: {{ $bgCard }}; border-left: 5px solid {{ $barColor }} !important;">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-3 text-center border-end">
                            <div class="fw-bold" style="font-size: 4rem; line-height:1; color: {{ $barColor }}">
                                {{ $score }}
                            </div>
                            <div class="text-muted small mb-2">/100</div>
                            <div class="progress mb-2" style="height:10px">
                                <div class="progress-bar" style="width:{{ $score }}%; background:{{ $barColor }}"></div>
                            </div>
                            <span class="badge" style="background:{{ $barColor }}; color:white; font-size:13px; padding:6px 14px; border-radius:20px">
                                {{ $niveauLabel }}
                            </span>
                        </div>
                        <div class="col-md-9 ps-4">
                            <div class="text-muted small fw-bold mb-3 text-uppercase" style="letter-spacing:.06em">
                                Anomalies détectées ({{ count($raisons) }})
                            </div>
                            @forelse($raisons as $raison)
                                @php $info = $raisonDetails[$raison] ?? ['label' => $raison, 'icon' => 'fas fa-exclamation-triangle', 'color' => 'secondary', 'desc' => '']; @endphp
                                <div class="d-flex align-items-start gap-3 mb-3 p-3 rounded-3 bg-white shadow-sm">
                                    <span class="badge bg-{{ $info['color'] }} d-flex align-items-center justify-content-center"
                                          style="width:36px; height:36px; border-radius:50%; flex-shrink:0">
                                        <i class="{{ $info['icon'] }}"></i>
                                    </span>
                                    <div>
                                        <div class="fw-bold text-{{ $info['color'] }}">{{ $info['label'] }}</div>
                                        <div class="small text-muted">{{ $info['desc'] }}</div>
                                    </div>
                                </div>
                            @empty
                                <div class="d-flex align-items-center gap-2 text-success">
                                    <i class="fas fa-check-circle fs-5"></i>
                                    <span>Aucune anomalie spécifique détectée.</span>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            {{-- Message SWIFT associé --}}
            @if($anomaly->message)
            @php $msg = $anomaly->message; @endphp
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-bold d-flex justify-content-between">
                    <span><i class="fas fa-envelope me-2" style="color:#1A5C38"></i>Message SWIFT associé</span>
                    <a href="{{ route('swift.show', $msg->id) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-external-link-alt me-1"></i>Ouvrir
                    </a>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-sm-6 col-md-4">
                            <div class="text-muted small mb-1">Référence</div>
                            <div class="fw-bold font-monospace" style="color:#1A5C38">
                                {{ $msg->REFERENCE ?? $msg->reference ?? '—' }}
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <div class="text-muted small mb-1">Type</div>
                            <span class="badge rounded-pill" style="background:#1A5C38; color:white">
                                {{ $msg->TYPE_MESSAGE ?? $msg->type_message ?? '—' }}
                            </span>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <div class="text-muted small mb-1">Direction</div>
                            @php $dir = $msg->DIRECTION ?? $msg->direction; @endphp
                            <span class="badge {{ $dir === 'IN' ? 'bg-primary' : 'bg-dark' }}">
                                {{ $dir === 'IN' ? 'REÇU' : 'ÉMIS' }}
                            </span>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <div class="text-muted small mb-1">Montant</div>
                            <div class="fw-bold">
                                {{ number_format($msg->AMOUNT ?? $msg->amount ?? 0, 2, ',', ' ') }}
                                <small class="text-muted">{{ $msg->CURRENCY ?? $msg->currency }}</small>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <div class="text-muted small mb-1">Émetteur</div>
                            <div>{{ $msg->SENDER_NAME ?? $msg->sender_name ?? '—' }}</div>
                            @if($msg->SENDER_BIC ?? $msg->sender_bic)
                                <small class="text-muted font-monospace">{{ $msg->SENDER_BIC ?? $msg->sender_bic }}</small>
                            @endif
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <div class="text-muted small mb-1">Bénéficiaire</div>
                            <div>{{ $msg->RECEIVER_NAME ?? $msg->receiver_name ?? '—' }}</div>
                            @if($msg->RECEIVER_BIC ?? $msg->receiver_bic)
                                <small class="text-muted font-monospace">{{ $msg->RECEIVER_BIC ?? $msg->receiver_bic }}</small>
                            @endif
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <div class="text-muted small mb-1">Statut</div>
                            @php $st = $msg->STATUS ?? $msg->status ?? ''; @endphp
                            @switch($st)
                                @case('pending')    <span class="badge bg-warning text-dark">⏳ En attente</span>  @break
                                @case('processed')  <span class="badge bg-info text-dark">🔵 À autoriser</span>   @break
                                @case('authorized') <span class="badge bg-success">✅ Autorisé</span>              @break
                                @case('suspended')  <span class="badge bg-danger">⛔ Suspendu</span>              @break
                                @case('rejected')   <span class="badge bg-danger">❌ Rejeté</span>                @break
                                @default            <span class="badge bg-secondary">{{ $st }}</span>
                            @endswitch
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <div class="text-muted small mb-1">Date création</div>
                            <div>{{ optional($msg->CREATED_AT ?? $msg->created_at)->format('d/m/Y H:i') ?? '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tags MT si disponibles --}}
            @if($msg->details && $msg->details->count())
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold">
                    <i class="fas fa-tags me-2" style="color:#1A5C38"></i>Tags SWIFT ({{ $msg->type_message ?? $msg->TYPE_MESSAGE }})
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
                            @foreach($msg->details as $detail)
                            <tr>
                                <td class="font-monospace fw-bold text-success">{{ $detail->tag_name }}</td>
                                <td class="font-monospace small">{{ $detail->tag_value }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
            @endif

        </div>

        {{-- COLONNE DROITE — Vérification + Actions --}}
        <div class="col-lg-4">

            {{-- Statut vérification --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-bold">
                    <i class="fas fa-user-check me-2" style="color:#1A5C38"></i>Vérification
                </div>
                <div class="card-body">
                    @if($anomaly->verifie_par)
                        <div class="text-center py-2">
                            <div class="mb-2" style="font-size:2.5rem">✅</div>
                            <div class="fw-bold text-success fs-5">Vérifiée</div>
                            <div class="text-muted small mt-2">
                                <i class="fas fa-user me-1"></i>
                                {{ optional($anomaly->verificateur)->name ?? '—' }}
                            </div>
                            <div class="text-muted small">
                                <i class="fas fa-calendar me-1"></i>
                                {{ optional($anomaly->verifie_at)->format('d/m/Y à H:i') }}
                            </div>
                        </div>
                    @else
                        <div class="text-center py-2 mb-3">
                            <div class="mb-2" style="font-size:2.5rem">⏳</div>
                            <div class="fw-bold text-danger fs-5">En attente</div>
                            <div class="text-muted small mt-1">Aucune vérification effectuée</div>
                        </div>
                        @role('super-admin|swift-manager')
                        <form method="POST"
                              action="{{ route('swift.anomalies.verify', $anomaly->id) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn w-100 text-white" style="background:#1A5C38">
                                <i class="fas fa-check me-2"></i>Marquer comme vérifiée
                            </button>
                        </form>
                        @endrole
                    @endif
                </div>
            </div>

            {{-- Actions IA --}}
            @role('super-admin|swift-manager')
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-bold">
                    <i class="fas fa-cogs me-2" style="color:#1A5C38"></i>Actions
                </div>
                <div class="card-body d-grid gap-2">
                    <form method="POST"
                          action="{{ route('swift.anomalies.reanalyze', $anomaly->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary w-100">
                            <i class="fas fa-sync-alt me-2"></i>Re-analyser ce message
                        </button>
                    </form>
                    <a href="{{ route('swift.anomalies.index') }}"
                       class="btn btn-outline-secondary w-100">
                        <i class="fas fa-list me-2"></i>Toutes les anomalies
                    </a>
                    <a href="{{ route('swift.anomalies.index', ['niveau_risque' => $anomaly->niveau_risque]) }}"
                       class="btn btn-outline-secondary w-100">
                        <i class="fas fa-filter me-2"></i>Anomalies {{ $niveauLabel }}
                    </a>
                    @if($anomaly->message)
                    <a href="{{ route('swift.show', $anomaly->message->id) }}"
                       class="btn btn-outline-dark w-100">
                        <i class="fas fa-envelope me-2"></i>Voir le message SWIFT
                    </a>
                    @endif
                </div>
            </div>
            @endrole

            {{-- Méta-données --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold">
                    <i class="fas fa-info-circle me-2" style="color:#1A5C38"></i>Informations
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="text-muted small">ID Anomalie</div>
                        <div class="fw-bold font-monospace">#{{ $anomaly->id }}</div>
                    </div>
                    <div class="mb-3">
                        <div class="text-muted small">Détectée le</div>
                        <div>{{ optional($anomaly->created_at)->format('d/m/Y à H:i') }}</div>
                    </div>
                    <div class="mb-3">
                        <div class="text-muted small">Dernière mise à jour</div>
                        <div>{{ optional($anomaly->updated_at)->format('d/m/Y à H:i') }}</div>
                    </div>
                    <div>
                        <div class="text-muted small">Message SWIFT #</div>
                        <div class="fw-bold">{{ $anomaly->message_id }}</div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection