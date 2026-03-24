@php
    $user = $user ?? null;
    $currentRole = $user ? $user->getRoleNames()->first() : old('role');
    $roles = App\Models\User::getBankRoles();
@endphp

{{-- Formulaire sans matricule, sans agence, sans is_active --}}

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="name" class="form-label">
            <i class="fas fa-user me-2"></i>Nom complet <span class="text-danger">*</span>
        </label>
        <input type="text" 
               class="form-control @error('name') is-invalid @enderror" 
               id="name" 
               name="name" 
               value="{{ old('name', $user->name ?? '') }}" 
               placeholder="Jean Dupont"
               required>
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label for="email" class="form-label">
            <i class="fas fa-envelope me-2"></i>Email <span class="text-danger">*</span>
        </label>
        <input type="email" 
               class="form-control @error('email') is-invalid @enderror" 
               id="email" 
               name="email" 
               value="{{ old('email', $user->email ?? '') }}" 
               placeholder="utilisateur@btl.ma"
               required>
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="telephone" class="form-label">
            <i class="fas fa-phone me-2"></i>Téléphone
        </label>
        <input type="tel" 
               class="form-control @error('telephone') is-invalid @enderror" 
               id="telephone" 
               name="telephone" 
               value="{{ old('telephone', $user->telephone ?? '') }}" 
               placeholder="+212 6 12 34 56 78">
        @error('telephone')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

{{-- SECTION RÔLE --}}
<div class="mb-4">
    <label class="form-label">
        <i class="fas fa-user-tag me-2"></i>Rôle <span class="text-danger">*</span>
    </label>
    <div class="border rounded p-3">
        @foreach($roles as $key => $role)
        <div class="form-check mb-2">
            <input class="form-check-input" 
                   type="radio" 
                   name="role" 
                   id="role_{{ $key }}"
                   value="{{ $key }}"
                   {{ old('role', $currentRole ?? '') == $key ? 'checked' : '' }}
                   required>
            <label class="form-check-label" for="role_{{ $key }}">
                <span class="badge bg-{{ $role['color'] }}">
                    <i class="fas {{ $role['icon'] }} me-1"></i>
                    {{ $role['name'] }}
                </span>
                <small class="text-muted d-block mt-1">{{ $role['description'] }}</small>
            </label>
        </div>
        @endforeach
    </div>
    @error('role')
        <div class="text-danger small mt-2">{{ $message }}</div>
    @enderror
</div>

{{-- MOT DE PASSE - UNIQUEMENT POUR CRÉATION --}}
@if(!isset($user))
<div class="row">
    <div class="col-md-6 mb-3">
        <label for="password" class="form-label">
            <i class="fas fa-lock me-2"></i>Mot de passe <span class="text-danger">*</span>
        </label>
        <input type="password" 
               class="form-control @error('password') is-invalid @enderror" 
               id="password" 
               name="password" 
               required>
        <div class="form-text">Minimum 8 caractères</div>
        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label for="password_confirmation" class="form-label">
            <i class="fas fa-lock me-2"></i>Confirmer le mot de passe <span class="text-danger">*</span>
        </label>
        <input type="password" 
               class="form-control" 
               id="password_confirmation" 
               name="password_confirmation" 
               required>
    </div>
</div>
@endif

{{-- PAS DE SECTION IS_ACTIVE --}}

{{-- BOUTONS --}}
<div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
        <i class="fas fa-times me-2"></i>Annuler
    </a>
    <button type="submit" class="btn btn-primary">
        <i class="fas {{ $buttonIcon ?? 'fa-save' }} me-2"></i>{{ $submitButton ?? 'Enregistrer' }}
    </button>
</div>

<style>
.bg-purple {
    background-color: #6f42c1 !important;
}
.form-check-input:checked {
    background-color: var(--bs-primary);
    border-color: var(--bs-primary);
}
</style>