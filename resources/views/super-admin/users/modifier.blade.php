@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            {{-- Header --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h2 mb-0">
                        <i class="fas fa-user-edit me-2"></i>Modifier utilisateur
                    </h1>
                    <p class="text-muted">{{ $user->name }} - {{ $user->email }}</p>
                </div>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour
                </a>
            </div>

            {{-- Messages flash --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Formulaire --}}
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.users.update', $user) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        @include('super-admin.users._form', [
                            'submitButton' => 'Mettre à jour',
                            'buttonIcon' => 'fa-save',
                            'user' => $user
                        ])
                    </form>
                </div>
            </div>

            {{-- Réinitialisation mot de passe --}}
            <div class="card mt-4">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0"><i class="fas fa-key me-2"></i>Réinitialiser le mot de passe</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.users.reset-password', $user) }}" method="POST">
                        @csrf
                        @method('POST')
                        <div class="row">
                            <div class="col-md-5 mb-3">
                                <label for="reset_password" class="form-label">Nouveau mot de passe</label>
                                <input type="password" class="form-control" name="password" id="reset_password" required minlength="8">
                            </div>
                            <div class="col-md-5 mb-3">
                                <label for="reset_password_confirmation" class="form-label">Confirmer le mot de passe</label>
                                <input type="password" class="form-control" name="password_confirmation" id="reset_password_confirmation" required>
                            </div>
                            <div class="col-md-2 mb-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-warning w-100">
                                    <i class="fas fa-sync-alt me-2"></i>Réinitialiser
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Zone de danger - SUPPRESSION --}}
            @if($user->email !== 'admin@btl.ma')
            <div class="card mt-4 border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Zone de danger</h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <h6>Supprimer cet utilisateur</h6>
                            <p class="text-muted mb-0">Cette action est irréversible. Toutes les données de l'utilisateur seront supprimées.</p>
                        </div>
                        <div class="col-auto">
                            <button type="button" class="btn btn-danger" onclick="confirmDelete({{ $user->id }})">
                                <i class="fas fa-trash me-2"></i>Supprimer définitivement
                            </button>
                            <form id="delete-form-{{ $user->id }}" action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-none">
                                @csrf
                                @method('DELETE')
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
function confirmDelete(userId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible.')) {
        document.getElementById('delete-form-' + userId).submit();
    }
}
</script>
@endsection