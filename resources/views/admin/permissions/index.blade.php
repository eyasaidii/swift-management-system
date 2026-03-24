@extends('layouts.app')

@section('title', 'Gestion des Permissions - Admin')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-0">
                <i class="fas fa-key text-warning me-2"></i>
                Gestion des Permissions
            </h1>
            <p class="text-muted">Administration des permissions système</p>
        </div>
        <div>
            <a href="{{ route('admin.permissions.create') }}" class="btn btn-success">
                <i class="fas fa-plus me-2"></i>Nouvelle permission
            </a>
            <a href="{{ route('admin.permissions.export') }}" class="btn btn-info">
                <i class="fas fa-download me-2"></i>Exporter
            </a>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Retour
            </a>
        </div>
    </div>

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

    {{-- Section d'assignation aux rôles --}}
    <div class="card mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-user-tag me-2 text-primary"></i>Assigner des permissions à un rôle</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.permissions.assign-to-role') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Sélectionner un rôle</label>
                        <select name="role_id" id="roleSelect" class="form-select" required>
                            <option value="">Choisir un rôle...</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-8 mb-3">
                        <label class="form-label">Permissions à assigner</label>
                        <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                            @foreach($permissions as $permission)
                                <div class="form-check">
                                    <input class="form-check-input permission-checkbox" type="checkbox" 
                                           name="permissions[]" value="{{ $permission->id }}" 
                                           id="perm_{{ $permission->id }}">
                                    <label class="form-check-label" for="perm_{{ $permission->id }}">
                                        {{ $permission->name }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Assigner les permissions
                </button>
            </form>
        </div>
    </div>

    {{-- Liste des permissions --}}
    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-list me-2 text-primary"></i>Liste des permissions</h5>
            <span class="badge bg-info">{{ $permissions->total() }} permissions</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>ID</th>
                            <th>Nom de la permission</th>
                            <th>Guard</th>
                            <th>Rôles associés</th>
                            <th>Date création</th>
                            <th width="200">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($permissions as $permission)
                        <tr>
                            <td>#{{ $permission->id }}</td>
                            <td>
                                <span class="fw-bold">{{ $permission->name }}</span>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $permission->guard_name }}</span>
                            </td>
                            <td>
                                @foreach($permission->roles as $role)
                                    <span class="badge bg-info">{{ $role->name }}</span>
                                @endforeach
                            </td>
                            <td>{{ $permission->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.permissions.edit', $permission->id) }}" 
                                       class="btn btn-outline-primary" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="{{ route('admin.permissions.duplicate', $permission->id) }}" 
                                       class="btn btn-outline-info" title="Dupliquer">
                                        <i class="fas fa-copy"></i>
                                    </a>
                                    @if(!in_array($permission->name, ['view-dashboard', 'view-users', 'view-swift-messages']))
                                    <form action="{{ route('admin.permissions.destroy', $permission->id) }}" 
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('Supprimer cette permission ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <i class="fas fa-key fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Aucune permission trouvée</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if(method_exists($permissions, 'links'))
        <div class="card-footer">
            {{ $permissions->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    // Charger les permissions du rôle sélectionné
    document.getElementById('roleSelect').addEventListener('change', function() {
        const roleId = this.value;
        if (!roleId) return;
        
        fetch(`/admin/permissions/role-permissions?role_id=${roleId}`)
            .then(response => response.json())
            .then(data => {
                document.querySelectorAll('.permission-checkbox').forEach(cb => {
                    cb.checked = data.includes(parseInt(cb.value));
                });
            });
    });
</script>
@endpush
@endsection