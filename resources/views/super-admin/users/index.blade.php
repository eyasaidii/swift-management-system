@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-0">
                <i class="fas fa-users me-2"></i>Gestion des utilisateurs
            </h1>
            <p class="text-muted">Administration des comptes utilisateurs BTL Bank</p>
        </div>
        <div>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-primary me-2">
                <i class="fas fa-tachometer-alt me-2"></i>Dashboard SWIFT
            </a>
            <a href="{{ route('admin.users.create') }}" class="btn btn-success">
                <i class="fas fa-plus me-2"></i>Nouvel utilisateur
            </a>
            <a href="{{ route('admin.users.export') }}" class="btn btn-outline-secondary">
                <i class="fas fa-download me-2"></i>Exporter
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

    {{-- Filtres --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.users.index') }}" class="row g-3">
                <div class="col-md-8">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Rechercher par nom ou email..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <select name="role" class="form-select">
                        <option value="all">Tous les rôles</option>
                        @foreach(App\Models\User::getBankRoles() as $key => $role)
                            <option value="{{ $key }}" {{ request('role') == $key ? 'selected' : '' }}>
                                {{ $role['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Statistiques --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Total utilisateurs</h6>
                            <h2 class="mt-2 mb-0">{{ $stats['total'] ?? 0 }}</h2>
                        </div>
                        <i class="fas fa-users fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-3">Répartition par rôle</h6>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($stats['by_role'] ?? [] as $role => $count)
                            @php
                                $roles = App\Models\User::getBankRoles();
                                $roleInfo = $roles[$role] ?? ['name' => $role, 'color' => 'secondary', 'icon' => 'fa-user'];
                            @endphp
                            <span class="badge bg-{{ $roleInfo['color'] }} p-2">
                                {{ $roleInfo['name'] }}: {{ $count }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tableau des utilisateurs --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        32
                            <th>Utilisateur</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Créé le</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle bg-primary text-white me-3">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold">{{ $user->name }}</div>
                                        <small class="text-muted">ID: #{{ $user->id }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
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
                            </td>
                            <td>{{ $user->created_at->format('d/m/Y') }}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-outline-info" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    {{-- ✅ CORRECTION : admin.users.modifier au lieu de admin.users.edit --}}
                                    <a href="{{ route('admin.users.modifier', $user) }}" class="btn btn-outline-primary" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    @if($user->email !== 'admin@btl.ma')
                                    <button type="button" class="btn btn-outline-warning" title="Réinitialiser mot de passe"
                                            data-bs-toggle="modal" data-bs-target="#resetPasswordModal{{ $user->id }}">
                                        <i class="fas fa-key"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" title="Supprimer"
                                            onclick="confirmDelete({{ $user->id }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <form id="delete-form-{{ $user->id }}" action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-none">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Aucun utilisateur trouvé</p>
                                <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Créer le premier utilisateur
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if(method_exists($users, 'links'))
        <div class="card-footer">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>

{{-- Modals pour réinitialisation de mot de passe --}}
@foreach($users as $user)
@if($user->email !== 'admin@btl.ma')
<div class="modal fade" id="resetPasswordModal{{ $user->id }}" tabindex="-1">
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
                    <p>Utilisateur: <strong>{{ $user->name }}</strong></p>
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
@endif
@endforeach

<style>
.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 18px;
}
.opacity-50 {
    opacity: 0.5;
}
</style>

<script>
function confirmDelete(userId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) {
        document.getElementById('delete-form-' + userId).submit();
    }
}
</script>
@endsection