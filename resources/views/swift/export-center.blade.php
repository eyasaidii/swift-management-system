{{-- resources/views/swift/export-center.blade.php --}}
@extends('layouts.app')

@section('title', 'Export Center - BTL Bank')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-0 fw-bold">
                <i class="fas fa-download text-success me-2"></i>Export Center
            </h1>
            <p class="text-muted mb-0">Exportez vos messages SWIFT en Excel ou CSV</p>
        </div>
        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Retour
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">

        {{-- Formulaire d'export --}}
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-filter me-2 text-primary"></i>Paramètres d'export
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('swift.export') }}">

                        {{-- Choix du format --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold">Format d'export</label>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="card border-2 border-success h-100" id="card-xlsx"
                                         style="cursor:pointer" onclick="selectFormat('xlsx')">
                                        <div class="card-body text-center py-3">
                                            <i class="fas fa-file-excel fa-2x text-success mb-2"></i>
                                            <h6 class="fw-bold mb-1">Excel (.xlsx)</h6>
                                            <small class="text-muted">
                                                Mise en forme couleurs BTL,
                                                onglet résumé, filtres automatiques
                                            </small>
                                            <div class="mt-2">
                                                <span class="badge bg-success">Recommandé</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border h-100" id="card-csv"
                                         style="cursor:pointer" onclick="selectFormat('csv')">
                                        <div class="card-body text-center py-3">
                                            <i class="fas fa-file-csv fa-2x text-secondary mb-2"></i>
                                            <h6 class="fw-bold mb-1">CSV (.csv)</h6>
                                            <small class="text-muted">
                                                Format universel,
                                                compatible tous tableurs
                                            </small>
                                            <div class="mt-2">
                                                <span class="badge bg-secondary">Basique</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="format" id="format-input" value="xlsx">
                        </div>

                        <hr>

                        {{-- Filtres --}}
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Direction</label>
                                <select name="direction" class="form-select">
                                    <option value="">Tous</option>
                                    <option value="RECU">Reçus (IN)</option>
                                    <option value="EMIS">Émis (OUT)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Statut</label>
                                <select name="status" class="form-select">
                                    <option value="">Tous</option>
                                    <option value="authorized">Autorisé</option>
                                    <option value="processed">Traité</option>
                                    <option value="pending">En attente</option>
                                    <option value="suspended">Suspendu</option>
                                    <option value="rejected">Rejeté</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Devise</label>
                                <select name="currency" class="form-select">
                                    <option value="">Toutes</option>
                                    <option value="USD">USD</option>
                                    <option value="EUR">EUR</option>
                                    <option value="GBP">GBP</option>
                                    <option value="TND">TND</option>
                                    <option value="LYD">LYD</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date début</label>
                                <input type="date" name="date_from" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date fin</label>
                                <input type="date" name="date_to" class="form-control">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success btn-lg w-100">
                            <i class="fas fa-download me-2"></i>
                            Générer et télécharger l'export
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Historique des exports --}}
        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2 text-secondary"></i>Historique exports
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if(isset($exportJobs) && $exportJobs->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($exportJobs as $job)
                                <div class="list-group-item px-3 py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="badge {{ $job->format === 'xlsx' ? 'bg-success' : 'bg-secondary' }} me-2">
                                                {{ strtoupper($job->format) }}
                                            </span>
                                            <small class="text-muted">
                                                {{ \Carbon\Carbon::parse($job->date_demande)->format('d/m/Y H:i') }}
                                            </small>
                                        </div>
                                        <span class="badge {{ $job->statut === 'completed' ? 'bg-success' : 'bg-warning text-dark' }}">
                                            {{ $job->statut === 'completed' ? 'OK' : $job->statut }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-2x mb-3 d-block opacity-25"></i>
                            <p class="small">Aucun export réalisé</p>
                        </div>
                    @endif
                </div>
                @if(isset($exportJobs) && $exportJobs->count() > 0)
                    <div class="card-footer bg-white text-center">
                        <small class="text-muted">
                            {{ $exportJobs->count() }} export(s) réalisé(s)
                        </small>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>

<script>
function selectFormat(format) {
    document.getElementById('format-input').value = format;

    // Styles cartes
    const xlsx = document.getElementById('card-xlsx');
    const csv  = document.getElementById('card-csv');

    if (format === 'xlsx') {
        xlsx.classList.add('border-success', 'border-2');
        xlsx.classList.remove('border');
        csv.classList.remove('border-success', 'border-2');
        csv.classList.add('border');
    } else {
        csv.classList.add('border-success', 'border-2');
        csv.classList.remove('border');
        xlsx.classList.remove('border-success', 'border-2');
        xlsx.classList.add('border');
    }
}
</script>
@endsection