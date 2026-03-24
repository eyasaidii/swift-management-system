@extends('layouts.app')

@section('title', 'Détail du message SWIFT')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 fw-bold">Détail du message SWIFT</h1>
                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour
                </a>
            </div>

            {{-- Messages flash --}}
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

            {{-- Informations générales --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Informations générales</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p>
                                <strong>Type :</strong>
                                <span class="badge bg-secondary font-monospace ms-1">
                                    {{ $message->TYPE_MESSAGE ?? $message->type_message }}
                                </span>
                            </p>
                            <p>
                                <strong>Référence :</strong>
                                <span class="font-monospace">
                                    {{ $message->REFERENCE ?? $message->reference }}
                                </span>
                            </p>
                            <p>
                                <strong>Direction :</strong>
                                @php $dir = $message->DIRECTION ?? $message->direction; @endphp
                                @if($dir === 'IN')
                                    <span class="badge bg-primary">Reçu</span>
                                @else
                                    <span class="badge bg-dark">Émis</span>
                                @endif
                            </p>
                            <p>
                                <strong>Statut :</strong>
                                @php $status = $message->STATUS ?? $message->status; @endphp
                                @switch($status)
                                    @case('pending')
                                        <span class="badge bg-warning text-dark">En attente</span>
                                        @break
                                    @case('processed')
                                        <span class="badge bg-success">Traité</span>
                                        @break
                                    @case('rejected')
                                        <span class="badge bg-danger">Rejeté</span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary">{{ $status }}</span>
                                @endswitch
                            </p>
                            @if($message->PROCESSED_AT ?? $message->processed_at)
                                <p>
                                    <strong>Traité le :</strong>
                                    {{ \Carbon\Carbon::parse($message->PROCESSED_AT ?? $message->processed_at)->format('d/m/Y H:i') }}
                                </p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <p>
                                <strong>Montant :</strong>
                                <span class="fw-bold">
                                    {{ number_format((float)($message->AMOUNT ?? $message->amount ?? 0), 2) }}
                                    {{ $message->CURRENCY ?? $message->currency }}
                                </span>
                            </p>
                            <p>
                                <strong>Date valeur :</strong>
                                @php
                                    $vd = $message->VALUE_DATE ?? $message->value_date;
                                    echo $vd ? \Carbon\Carbon::parse($vd)->format('d/m/Y') : '—';
                                @endphp
                            </p>
                            <p>
                                <strong>Émetteur :</strong>
                                {{ $message->SENDER_NAME ?? $message->sender_name ?? '—' }}
                                @if($message->SENDER_BIC ?? $message->sender_bic)
                                    <small class="text-muted font-monospace">
                                        ({{ $message->SENDER_BIC ?? $message->sender_bic }})
                                    </small>
                                @endif
                            </p>
                            <p>
                                <strong>Bénéficiaire :</strong>
                                {{ $message->RECEIVER_NAME ?? $message->receiver_name ?? '—' }}
                                @if($message->RECEIVER_BIC ?? $message->receiver_bic)
                                    <small class="text-muted font-monospace">
                                        ({{ $message->RECEIVER_BIC ?? $message->receiver_bic }})
                                    </small>
                                @endif
                            </p>
                            @if($message->DESCRIPTION ?? $message->description)
                                <p>
                                    <strong>Description :</strong>
                                    {{ $message->DESCRIPTION ?? $message->description }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Détails spécifiques (tags MT) --}}
            @if($message->details && $message->details->count())
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            Détails spécifiques
                            ({{ $message->TYPE_MESSAGE ?? $message->type_message }})
                        </h5>
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
                                        <td class="font-monospace fw-bold">
                                            {{ $detail->tag_name }}
                                        </td>
                                        <td class="font-monospace">
                                            {{ $detail->tag_value }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- =========================================================
                 BOUTONS D'ACTION
                 Logique :
                   - Traiter / Rejeter : admin, international-admin pour tout
                                         chef-agence, chargee pour OUT uniquement
                   - Voir MT           : si MT_CONTENT existe (après traitement)
                   - Voir MX           : si XML_BRUT existe (généré à la création)
                   - Supprimer         : admin uniquement
                 ========================================================= --}}
            <div class="card shadow-sm">
                <div class="card-body d-flex gap-2 flex-wrap align-items-center">

                    @php
                        $status    = $message->STATUS    ?? $message->status    ?? null;
                        $direction = $message->DIRECTION ?? $message->direction ?? null;
                        $user      = auth()->user();

                        // Rôles qui peuvent traiter/rejeter selon la direction
                        $canAct =
                            $user->hasRole('admin')
                            || $user->hasRole('international-admin')
                            || ($user->hasRole(['chef-agence', 'chargee']) && $direction === 'OUT');

                        $hasMt = !empty($message->MT_CONTENT ?? $message->mt_content);
                        $hasMx = !empty($message->XML_BRUT   ?? $message->xml_brut);
                    @endphp

                    {{-- TRAITER — visible si pending ET rôle autorisé --}}
                    @if($status === 'pending' && $canAct)
                        <form method="POST"
                              action="{{ route('swift.process', $message->id) }}"
                              onsubmit="return confirm('Confirmer le traitement de ce message ?')">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check me-2"></i>Traiter
                            </button>
                        </form>

                        {{-- REJETER — visible si pending ET rôle autorisé --}}
                        <form method="POST"
                              action="{{ route('swift.reject', $message->id) }}"
                              onsubmit="return confirm('Confirmer le rejet de ce message ?')">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-times me-2"></i>Rejeter
                            </button>
                        </form>
                    @endif

                    {{-- VOIR MT --}}
                    @if($hasMt)
                        <a href="{{ route('swift.view-mt', $message->id) }}"
                           class="btn btn-outline-secondary" target="_blank">
                            <i class="fas fa-file-alt me-2"></i>Voir MT
                        </a>
                    @else
                        <button class="btn btn-outline-secondary disabled"
                                title="MT disponible après traitement">
                            <i class="fas fa-file-alt me-2"></i>Voir MT
                        </button>
                    @endif

                    {{-- VOIR MX --}}
                    @if($hasMx)
                        <a href="{{ route('swift.view-mx', $message->id) }}"
                           class="btn btn-outline-info" target="_blank">
                            <i class="fas fa-code me-2"></i>Voir MX
                        </a>
                    @else
                        <button class="btn btn-outline-secondary disabled"
                                title="MX disponible après traitement">
                            <i class="fas fa-code me-2"></i>Voir MX
                        </button>
                    @endif

                    {{-- SUPPRIMER — admin uniquement --}}
                    @if($user->hasRole('admin'))
                        <form method="POST"
                              action="{{ route('swift.destroy', $message->id) }}"
                              class="ms-auto"
                              onsubmit="return confirm('Confirmer la suppression définitive ?')">
                            @csrf
                            @method('DELETE')
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

{{-- Auto-dismiss alerts après 5 secondes --}}
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