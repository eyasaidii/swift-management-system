@extends('layouts.app')

@section('title', 'Détail Anomalie — IA')

@push('styles')
<style>
    :root {
        --brand:       #1A5C38;
        --brand-light: #e8f5ee;
        --brand-mid:   #2a7a4f;
        --risk-high:   #c0392b;
        --risk-med:    #d68910;
        --risk-low:    #1e8449;
    }

    /* ── Page layout ── */
    .anomaly-page { background: #f4f6f9; min-height: 100vh; padding: 2rem 1.5rem; }

    /* ── Page header ── */
    .page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 2rem;
    }
    .page-header__title {
        font-size: 1.35rem;
        font-weight: 700;
        color: #1a202c;
        margin: 0;
        letter-spacing: -.3px;
    }
    .page-header__subtitle {
        font-size: .8rem;
        color: #718096;
        margin-top: 2px;
        letter-spacing: .03em;
        text-transform: uppercase;
    }
    .page-header__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px; height: 40px;
        background: var(--brand-light);
        color: var(--brand);
        border-radius: 10px;
        margin-right: .75rem;
        font-size: 1.1rem;
        flex-shrink: 0;
    }
    .btn-brand {
        background: var(--brand);
        color: #fff !important;
        border: none;
        font-weight: 500;
        padding: .42rem 1.1rem;
        border-radius: 8px;
        font-size: .85rem;
        transition: background .18s;
    }
    .btn-brand:hover { background: var(--brand-mid); color: #fff; }
    .btn-ghost {
        background: #fff;
        color: #374151;
        border: 1px solid #d1d5db;
        font-weight: 500;
        padding: .42rem 1.1rem;
        border-radius: 8px;
        font-size: .85rem;
        transition: border-color .18s, background .18s;
    }
    .btn-ghost:hover { background: #f9fafb; border-color: #9ca3af; }

    /* ── Cards ── */
    .pro-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        box-shadow: 0 1px 4px rgba(0,0,0,.06), 0 4px 12px rgba(0,0,0,.04);
        overflow: hidden;
    }
    .pro-card__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: .9rem 1.25rem;
        border-bottom: 1px solid #f0f0f0;
        background: #fafafa;
    }
    .pro-card__title {
        font-size: .82rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .07em;
        color: #374151;
        display: flex;
        align-items: center;
        gap: .5rem;
    }
    .pro-card__title i { color: var(--brand); font-size: .95rem; }
    .pro-card__body { padding: 1.25rem; }

    /* ── Score panel ── */
    .score-panel {
        border-radius: 14px;
        overflow: hidden;
        border: 1px solid #e5e7eb;
        box-shadow: 0 1px 4px rgba(0,0,0,.06), 0 4px 12px rgba(0,0,0,.04);
    }
    .score-panel__left {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 2rem 1.5rem;
        border-right: 1px solid rgba(255,255,255,.2);
    }
    .score-panel__number {
        font-size: 4.5rem;
        font-weight: 800;
        line-height: 1;
        letter-spacing: -2px;
        color: #fff;
    }
    .score-panel__denom {
        font-size: .8rem;
        color: rgba(255,255,255,.75);
        margin-top: 2px;
        font-weight: 600;
    }
    .score-panel__bar-wrap {
        width: 100%;
        background: rgba(255,255,255,.25);
        border-radius: 99px;
        height: 6px;
        margin: .9rem 0;
        overflow: hidden;
    }
    .score-panel__bar {
        height: 100%;
        background: #fff;
        border-radius: 99px;
        transition: width .4s ease;
    }
    .score-panel__badge {
        font-size: .75rem;
        font-weight: 700;
        letter-spacing: .05em;
        padding: .35rem .9rem;
        border-radius: 99px;
        background: rgba(255,255,255,.2);
        color: #fff;
        border: 1px solid rgba(255,255,255,.4);
    }
    .score-panel__right {
        padding: 1.5rem 1.75rem;
        background: #fff;
    }
    .score-panel__section-label {
        font-size: .72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #9ca3af;
        margin-bottom: 1rem;
    }

    /* ── Anomaly item ── */
    .anomaly-item {
        display: flex;
        align-items: flex-start;
        gap: .85rem;
        padding: .85rem 1rem;
        border-radius: 10px;
        border: 1px solid #f0f0f0;
        background: #fafafa;
        margin-bottom: .7rem;
        transition: box-shadow .15s;
    }
    .anomaly-item:hover { box-shadow: 0 2px 8px rgba(0,0,0,.08); background: #fff; }
    .anomaly-item:last-child { margin-bottom: 0; }
    .anomaly-item__icon {
        width: 36px; height: 36px;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: .85rem;
    }
    .anomaly-item__icon.danger  { background: #fee2e2; color: #b91c1c; }
    .anomaly-item__icon.warning { background: #fef3c7; color: #92400e; }
    .anomaly-item__icon.secondary { background: #f3f4f6; color: #4b5563; }
    .anomaly-item__label { font-size: .88rem; font-weight: 700; color: #1f2937; }
    .anomaly-item__desc  { font-size: .78rem; color: #6b7280; line-height: 1.45; margin-top: 2px; }

    /* ── Data grid ── */
    .data-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 1rem; }
    .data-cell__label { font-size: .72rem; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; color: #9ca3af; margin-bottom: .25rem; }
    .data-cell__value { font-size: .9rem; color: #1f2937; font-weight: 500; }
    .data-cell__value.mono { font-family: 'Courier New', monospace; color: var(--brand); font-weight: 700; }

    /* ── Badges ── */
    .pill {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .28rem .75rem;
        border-radius: 99px;
        font-size: .75rem;
        font-weight: 700;
        letter-spacing: .03em;
    }
    .pill-brand  { background: var(--brand-light); color: var(--brand); }
    .pill-in     { background: #dbeafe; color: #1e40af; }
    .pill-out    { background: #f3f4f6; color: #374151; }
    .pill-pending    { background: #fef3c7; color: #92400e; }
    .pill-processed  { background: #dbeafe; color: #1e40af; }
    .pill-authorized { background: #dcfce7; color: #166534; }
    .pill-suspended  { background: #fee2e2; color: #991b1b; }
    .pill-rejected   { background: #fee2e2; color: #991b1b; }

    /* ── SWIFT tags table ── */
    .tags-table { width: 100%; border-collapse: collapse; font-size: .85rem; }
    .tags-table thead th {
        padding: .6rem 1rem;
        font-size: .72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .07em;
        color: #6b7280;
        background: #f9fafb;
        border-bottom: 1px solid #e5e7eb;
    }
    .tags-table tbody td { padding: .6rem 1rem; border-bottom: 1px solid #f3f4f6; }
    .tags-table tbody tr:last-child td { border-bottom: none; }
    .tags-table tbody tr:hover td { background: #f9fafb; }
    .tag-name { font-family: 'Courier New', monospace; font-weight: 700; color: var(--brand); }
    .tag-value { font-family: 'Courier New', monospace; color: #374151; word-break: break-all; }

    /* ── Right sidebar cards ── */
    .verify-status {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 1.5rem 1rem;
        text-align: center;
    }
    .verify-status__icon {
        width: 60px; height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: .85rem;
    }
    .verify-status__icon.ok      { background: #dcfce7; color: #16a34a; }
    .verify-status__icon.pending { background: #fef3c7; color: #b45309; }
    .verify-status__text { font-size: 1rem; font-weight: 700; }
    .verify-status__text.ok      { color: #16a34a; }
    .verify-status__text.pending { color: #b45309; }
    .verify-meta { font-size: .78rem; color: #6b7280; margin-top: .25rem; display: flex; align-items: center; gap: .35rem; }

    .meta-row { display: flex; flex-direction: column; padding: .65rem 0; border-bottom: 1px solid #f3f4f6; }
    .meta-row:last-child { border-bottom: none; }
    .meta-row__label { font-size: .72rem; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; color: #9ca3af; margin-bottom: .2rem; }
    .meta-row__value { font-size: .875rem; color: #1f2937; font-weight: 500; }
    .meta-row__value.mono { font-family: 'Courier New', monospace; color: var(--brand); }

    .action-btn {
        display: flex;
        align-items: center;
        gap: .6rem;
        padding: .6rem 1rem;
        border-radius: 9px;
        font-size: .84rem;
        font-weight: 600;
        text-decoration: none;
        transition: background .15s, border-color .15s;
        cursor: pointer;
        width: 100%;
        border: 1px solid #e5e7eb;
        background: #fff;
        color: #374151;
    }
    .action-btn:hover { background: #f9fafb; border-color: #9ca3af; color: #1f2937; }
    .action-btn.primary { background: var(--brand); border-color: var(--brand); color: #fff; }
    .action-btn.primary:hover { background: var(--brand-mid); border-color: var(--brand-mid); color: #fff; }
    .action-btn i { width: 16px; text-align: center; }
</style>
@endpush

@section('content')

@php
    $score       = (int) $anomaly->score;
    $niveau      = $anomaly->niveau_risque;
    $barColor    = $score >= 60 ? '#c0392b' : ($score >= 20 ? '#d68910' : '#1e8449');
    $bgPanel     = $score >= 60 ? 'linear-gradient(135deg,#c0392b,#922b21)' : ($score >= 20 ? 'linear-gradient(135deg,#d68910,#b7770d)' : 'linear-gradient(135deg,#1e8449,#196f3d)');
    $niveauLabel = match($niveau) {
        'HIGH'   => 'Risque Critique',
        'MEDIUM' => 'Risque Moyen',
        default  => 'Risque Faible',
    };
    $niveauIcon  = match($niveau) {
        'HIGH'   => 'fa-radiation',
        'MEDIUM' => 'fa-exclamation-triangle',
        default  => 'fa-shield-alt',
    };
    $raisons = is_array($anomaly->raisons)
        ? $anomaly->raisons
        : json_decode($anomaly->raisons ?? '[]', true);
    $raisonDetails = [
        'MONTANT_ZERO'        => ['label' => 'Montant nul',         'icon' => 'fas fa-ban',          'color' => 'danger',   'desc' => 'Le montant de la transaction est égal à zéro, ce qui est anormal pour un message SWIFT de paiement.'],
        'MONTANT_ELEVE'       => ['label' => 'Montant très élevé',  'icon' => 'fas fa-arrow-up',     'color' => 'warning',  'desc' => 'Le montant dépasse le seuil de 100 000 unités monétaires. Une vérification manuelle est recommandée.'],
        'STATUT_REJETE'       => ['label' => 'Statut rejeté',       'icon' => 'fas fa-times-circle', 'color' => 'danger',   'desc' => 'Le message a été rejeté lors du traitement. Cela peut indiquer un problème de conformité ou de données.'],
        'TRANSLATION_ERROR'   => ['label' => 'Erreur XML',          'icon' => 'fas fa-code',         'color' => 'danger',   'desc' => 'Des erreurs ont été détectées lors de la traduction du format MT vers MX (ISO 20022).'],
        'TYPE_ERROR'          => ['label' => 'Type invalide',       'icon' => 'fas fa-exclamation',  'color' => 'danger',   'desc' => "Le type de message contient une valeur ERROR indiquant un échec d'import ou de parsing."],
        'DOUBLON_REFERENCE'   => ['label' => 'Doublon référence',   'icon' => 'fas fa-copy',         'color' => 'warning',  'desc' => 'Une autre transaction avec la même référence SWIFT existe dans le système.'],
        'BIC_MANQUANT'        => ['label' => 'BIC manquant',        'icon' => 'fas fa-university',   'color' => 'warning',  'desc' => "Le code BIC de l'émetteur ou du bénéficiaire est absent, ce qui est obligatoire pour les virements SWIFT."],
        'DEVISE_INHABITUELLE' => ['label' => 'Devise inhabituelle', 'icon' => 'fas fa-coins',        'color' => 'warning',  'desc' => 'La devise utilisée est inhabituelle (hors EUR, USD, TND, GBP, CHF). Un contrôle supplémentaire est conseillé.'],
        'IMPORT_FAILED'       => ['label' => 'Import échoué',       'icon' => 'fas fa-file-excel',   'color' => 'danger',   'desc' => 'La référence indique explicitement un échec d\'import (IMPORT-FAILED). Le message nécessite une correction.'],
        'PASSPORT_DETECTE'    => ['label' => 'Passeport détecté',   'icon' => 'fas fa-id-card',      'color' => 'danger',   'desc' => 'Un numéro de passeport a été détecté dans le champ bénéficiaire, ce qui peut constituer un risque de conformité.'],
    ];
@endphp

<div class="anomaly-page">

    {{-- ── PAGE HEADER ── --}}
    <div class="page-header">
        <div class="d-flex align-items-center">
            <span class="page-header__icon"><i class="fas fa-brain"></i></span>
            <div>
                <h1 class="page-header__title">Détail de l'Anomalie <span style="color:var(--brand)">#{{ $anomaly->id }}</span></h1>
                <div class="page-header__subtitle">Moteur de détection &mdash; Règles métier SWIFT</div>
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('swift.anomalies.index') }}" class="btn-ghost">
                <i class="fas fa-arrow-left me-1"></i> Retour anomalies
            </a>
            @if($anomaly->message)
                <a href="{{ route('swift.show', $anomaly->message->id) }}" class="btn-brand">
                    <i class="fas fa-eye me-1"></i> Voir message SWIFT
                </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 border-0 shadow-sm mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">

        {{-- ══ COLONNE GAUCHE ══ --}}
        <div class="col-lg-8">

            {{-- Score card --}}
            <div class="score-panel mb-4">
                <div class="row g-0">
                    <div class="col-md-4" style="background: {{ $bgPanel }};">
                        <div class="score-panel__left h-100">
                            <div class="score-panel__number">{{ $score }}</div>
                            <div class="score-panel__denom">Score / 100</div>
                            <div class="score-panel__bar-wrap">
                                <div class="score-panel__bar" style="width:{{ $score }}%"></div>
                            </div>
                            <div class="score-panel__badge">
                                <i class="fas {{ $niveauIcon }} me-1"></i>{{ $niveauLabel }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="score-panel__right h-100">
                            <div class="score-panel__section-label">
                                Anomalies détectées &nbsp;&mdash;&nbsp; {{ count($raisons) }} signal(s)
                            </div>
                            @forelse($raisons as $raison)
                                @php $info = $raisonDetails[$raison] ?? ['label' => $raison, 'icon' => 'fas fa-exclamation-triangle', 'color' => 'secondary', 'desc' => '']; @endphp
                                <div class="anomaly-item">
                                    <div class="anomaly-item__icon {{ $info['color'] }}">
                                        <i class="{{ $info['icon'] }}"></i>
                                    </div>
                                    <div>
                                        <div class="anomaly-item__label">{{ $info['label'] }}</div>
                                        <div class="anomaly-item__desc">{{ $info['desc'] }}</div>
                                    </div>
                                </div>
                            @empty
                                <div class="d-flex align-items-center gap-2 p-3 rounded-3" style="background:#f0fdf4; border:1px solid #bbf7d0">
                                    <i class="fas fa-check-circle text-success"></i>
                                    <span class="small text-success fw-semibold">Aucune anomalie spécifique détectée.</span>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            {{-- Message SWIFT associé --}}
            @if($anomaly->message)
            @php $msg = $anomaly->message; @endphp
            <div class="pro-card mb-4">
                <div class="pro-card__header">
                    <span class="pro-card__title"><i class="fas fa-envelope"></i> Message SWIFT associé</span>
                    <a href="{{ route('swift.show', $msg->id) }}" class="btn-ghost" style="font-size:.78rem; padding:.3rem .8rem">
                        <i class="fas fa-external-link-alt me-1"></i>Ouvrir
                    </a>
                </div>
                <div class="pro-card__body">
                    <div class="data-grid">
                        <div class="data-cell">
                            <div class="data-cell__label">Référence</div>
                            <div class="data-cell__value mono">{{ $msg->REFERENCE ?? $msg->reference ?? '—' }}</div>
                        </div>
                        <div class="data-cell">
                            <div class="data-cell__label">Type</div>
                            <span class="pill pill-brand">{{ $msg->TYPE_MESSAGE ?? $msg->type_message ?? '—' }}</span>
                        </div>
                        <div class="data-cell">
                            <div class="data-cell__label">Direction</div>
                            @php $dir = $msg->DIRECTION ?? $msg->direction; @endphp
                            <span class="pill {{ $dir === 'IN' ? 'pill-in' : 'pill-out' }}">
                                <i class="fas {{ $dir === 'IN' ? 'fa-arrow-down' : 'fa-arrow-up' }}"></i>
                                {{ $dir === 'IN' ? 'REÇU' : 'ÉMIS' }}
                            </span>
                        </div>
                        <div class="data-cell">
                            <div class="data-cell__label">Montant</div>
                            <div class="data-cell__value">
                                <span style="font-weight:700; font-size:.95rem">
                                    {{ number_format($msg->AMOUNT ?? $msg->amount ?? 0, 2, ',', ' ') }}
                                </span>
                                <span class="text-muted" style="font-size:.78rem; margin-left:3px">{{ $msg->CURRENCY ?? $msg->currency }}</span>
                            </div>
                        </div>
                        <div class="data-cell">
                            <div class="data-cell__label">Émetteur</div>
                            <div class="data-cell__value">{{ $msg->SENDER_NAME ?? $msg->sender_name ?? '—' }}</div>
                            @if($msg->SENDER_BIC ?? $msg->sender_bic)
                                <div style="font-size:.75rem; color:#9ca3af; font-family:monospace">{{ $msg->SENDER_BIC ?? $msg->sender_bic }}</div>
                            @endif
                        </div>
                        <div class="data-cell">
                            <div class="data-cell__label">Bénéficiaire</div>
                            <div class="data-cell__value">{{ $msg->RECEIVER_NAME ?? $msg->receiver_name ?? '—' }}</div>
                            @if($msg->RECEIVER_BIC ?? $msg->receiver_bic)
                                <div style="font-size:.75rem; color:#9ca3af; font-family:monospace">{{ $msg->RECEIVER_BIC ?? $msg->receiver_bic }}</div>
                            @endif
                        </div>
                        <div class="data-cell">
                            <div class="data-cell__label">Statut</div>
                            @php $st = $msg->STATUS ?? $msg->status ?? ''; @endphp
                            @switch($st)
                                @case('pending')    <span class="pill pill-pending">⏳ En attente</span>    @break
                                @case('processed')  <span class="pill pill-processed">À autoriser</span>   @break
                                @case('authorized') <span class="pill pill-authorized">✓ Autorisé</span>   @break
                                @case('suspended')  <span class="pill pill-suspended">⛔ Suspendu</span>   @break
                                @case('rejected')   <span class="pill pill-rejected">✗ Rejeté</span>       @break
                                @default            <span class="pill" style="background:#f3f4f6;color:#374151">{{ $st }}</span>
                            @endswitch
                        </div>
                        <div class="data-cell">
                            <div class="data-cell__label">Date création</div>
                            <div class="data-cell__value">{{ optional($msg->CREATED_AT ?? $msg->created_at)->format('d/m/Y H:i') ?? '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tags MT si disponibles --}}
            @if($msg->details && $msg->details->count())
            <div class="pro-card">
                <div class="pro-card__header">
                    <span class="pro-card__title">
                        <i class="fas fa-tags"></i>
                        Tags SWIFT &nbsp;<span style="color:#9ca3af; font-weight:400">({{ $msg->type_message ?? $msg->TYPE_MESSAGE }})</span>
                    </span>
                </div>
                <div style="overflow-x:auto">
                    <table class="tags-table">
                        <thead>
                            <tr>
                                <th style="width:110px">Tag</th>
                                <th>Valeur</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($msg->details as $detail)
                            <tr>
                                <td><span class="tag-name">{{ $detail->tag_name }}</span></td>
                                <td><span class="tag-value">{{ $detail->tag_value }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
            @endif

        </div>

        {{-- ══ COLONNE DROITE ══ --}}
        <div class="col-lg-4">

            {{-- Statut vérification --}}
            <div class="pro-card mb-4">
                <div class="pro-card__header">
                    <span class="pro-card__title"><i class="fas fa-user-check"></i> Vérification</span>
                </div>
                <div class="pro-card__body p-0">
                    @if($anomaly->verifie_par)
                        <div class="verify-status">
                            <div class="verify-status__icon ok"><i class="fas fa-check"></i></div>
                            <div class="verify-status__text ok">Vérifiée</div>
                            <div class="verify-meta mt-2">
                                <i class="fas fa-user"></i>
                                {{ optional($anomaly->verificateur)->name ?? '—' }}
                            </div>
                            <div class="verify-meta">
                                <i class="fas fa-clock"></i>
                                {{ optional($anomaly->verifie_at)->format('d/m/Y à H:i') }}
                            </div>
                        </div>
                    @else
                        <div class="verify-status">
                            <div class="verify-status__icon pending"><i class="fas fa-hourglass-half"></i></div>
                            <div class="verify-status__text pending">En attente de vérification</div>
                            <div class="verify-meta mt-1">Aucune vérification effectuée</div>
                        </div>
                        @role('super-admin|swift-manager')
                        <div style="padding: 0 1.25rem 1.25rem">
                            <form method="POST" action="{{ route('swift.anomalies.verify', $anomaly->id) }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="action-btn primary">
                                    <i class="fas fa-check"></i> Marquer comme vérifiée
                                </button>
                            </form>
                        </div>
                        @endrole
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            @role('super-admin|swift-manager')
            <div class="pro-card mb-4">
                <div class="pro-card__header">
                    <span class="pro-card__title"><i class="fas fa-bolt"></i> Actions</span>
                </div>
                <div class="pro-card__body d-flex flex-column gap-2">
                    <form method="POST" action="{{ route('swift.anomalies.reanalyze', $anomaly->id) }}">
                        @csrf
                        <button type="submit" class="action-btn primary">
                            <i class="fas fa-sync-alt"></i> Re-analyser ce message
                        </button>
                    </form>
                    <a href="{{ route('swift.anomalies.index') }}" class="action-btn">
                        <i class="fas fa-list"></i> Toutes les anomalies
                    </a>
                    <a href="{{ route('swift.anomalies.index', ['niveau_risque' => $anomaly->niveau_risque]) }}" class="action-btn">
                        <i class="fas fa-filter"></i> Anomalies — {{ $niveauLabel }}
                    </a>
                    @if($anomaly->message)
                    <a href="{{ route('swift.show', $anomaly->message->id) }}" class="action-btn">
                        <i class="fas fa-envelope"></i> Voir le message SWIFT
                    </a>
                    @endif
                </div>
            </div>
            @endrole

            {{-- Méta-données --}}
            <div class="pro-card">
                <div class="pro-card__header">
                    <span class="pro-card__title"><i class="fas fa-info-circle"></i> Informations</span>
                </div>
                <div class="pro-card__body">
                    <div class="meta-row">
                        <span class="meta-row__label">ID Anomalie</span>
                        <span class="meta-row__value mono">#{{ $anomaly->id }}</span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-row__label">Détectée le</span>
                        <span class="meta-row__value">{{ optional($anomaly->created_at)->format('d/m/Y à H:i') }}</span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-row__label">Dernière mise à jour</span>
                        <span class="meta-row__value">{{ optional($anomaly->updated_at)->format('d/m/Y à H:i') }}</span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-row__label">Message SWIFT #</span>
                        <span class="meta-row__value mono">{{ $anomaly->message_id }}</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection