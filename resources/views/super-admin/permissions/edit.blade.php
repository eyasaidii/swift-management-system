@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h2 mb-0">
                    <i class="fas fa-edit text-primary me-2"></i>
                    Modifier permission
                </h1>
                <a href="{{ route('admin.permissions.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.permissions.update', $permission->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Nom de la permission</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name', $permission->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Guard</label>
                            <input type="text" class="form-control" value="{{ $permission->guard_name }}" readonly disabled>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Rôles associés</label>
                            <div class="border rounded p-3">
                                @foreach($roles as $role)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                               disabled {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}>
                                        <label class="form-check-label">
                                            {{ $role->name }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            <small class="text-muted">Utilisez la page d'assignation pour modifier les rôles</small>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary">Annuler</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Mettre à jour
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection