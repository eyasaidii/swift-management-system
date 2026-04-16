@extends('layouts.app')

@section('title', 'Détail de l\'utilisateur')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 fw-bold">
                    <i class="fas fa-user me-2"></i>Détail de l'utilisateur
                </h1>
                <div>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left me-2"></i>Retour à la liste
                    </a>
                    <a href="{{ route('admin.users.modifier', $user) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i>Modifier
                    </a>
                </div>
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

            {{-- Informations utilisateur --}}
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Informations générales</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Nom complet :</strong> {{ $user->name }}</p>
                            <p><strong>Email :</strong> {{ $user->email }}</p>
                        </div>
                        <div class="col-md-6">
                            <p>
                                <strong>Rôle :</strong>
                                @php
                                    $roleName = $user->getRoleNames()->first();
                                    $roles = App\Models\User::getBankRoles();
                                    $roleInfo = $roles[$roleName] ?? ['name' => $roleName ?? 'Aucun', 'color' => 'secondary', 'icon' => 'fa-user'];
                                @endphp
                                @if($roleName)
                                    <span class="badge bg-{{ $roleInfo['color'] }}">
                                        <i class="fas {{ $roleInfo['icon'] }} me-1"></i>
                                        {{ $roleInfo['name'] }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary">Aucun rôle</span>
                                @endif
                            </p>
                            <p><strong>Créé le :</strong> {{ $user->created_at->format('d/m/Y à H:i') }}</p>
                            <p><strong>Dernière modification :</strong> {{ $user->updated_at->format('d/m/Y à H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Actions supplémentaires (réinitialisation mot de passe, suppression) --}}
            @if(auth()->id() !== $user->id && $user->email !== 'admin@btl.ma')
                <div class="card shadow-sm mt-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex gap-3">
                            {{-- Réinitialiser mot de passe --}}
                            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#resetPasswordModal">
                                <i class="fas fa-key me-2"></i>Réinitialiser le mot de passe
                            </button>

                            {{-- Supprimer l'utilisateur --}}
                            <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                                <i class="fas fa-trash me-2"></i>Supprimer l'utilisateur
                            </button>
                            <form id="delete-form" action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-none">
                                @csrf
                                @method('DELETE')
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Modal Réinitialisation mot de passe --}}
<div class="modal fade" id="resetPasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.users.reset-password', $user) }}" method="POST">
                @csrf
                @method('POST')
                <div class="modal-header">
                    <h5 class="modal-title">Réinitialiser le mot de passe</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Utilisateur : <strong>{{ $user->name }}</strong></p>
                    <div class="mb-3">
                        <label for="password" class="form-label">Nouveau mot de passe</label>
                        <input type="password" class="form-control" name="password" required minlength="8">
                    </div>
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
                        <input type="password" class="form-control" name="password_confirmation" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-warning">Réinitialiser</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function confirmDelete() {
    if (confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible.')) {
        document.getElementById('delete-form').submit();
    }
}
</script>
@endsection