@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            {{-- Header --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h2 mb-0">
                        <i class="fas fa-user-plus me-2"></i>Nouvel utilisateur
                    </h1>
                    <p class="text-muted">Créer un nouveau compte utilisateur</p>
                </div>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour
                </a>
            </div>

            {{-- Formulaire --}}
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.users.store') }}" method="POST">
                        @csrf
                        
                        @include('admin.users._form', [
                            'submitButton' => 'Créer l\'utilisateur',
                            'buttonIcon' => 'fa-save'
                        ])
                    </form>
                </div>
            </div>

            {{-- Info --}}
            <div class="alert alert-info mt-4">
                <div class="d-flex">
                    <div class="me-3">
                        <i class="fas fa-info-circle fa-2x"></i>
                    </div>
                    <div>
                        <h5 class="alert-heading">Informations</h5>
                        <p class="mb-0">L'utilisateur pourra se connecter immédiatement avec ses identifiants.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection