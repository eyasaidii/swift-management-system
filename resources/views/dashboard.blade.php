<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrateur - BTL Swift Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding-top: 20px;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            transition: transform 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .quick-action-btn {
            height: 180px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            border: 2px solid #e9ecef;
            transition: all 0.3s;
            text-decoration: none;
            color: #495057;
        }
        .quick-action-btn:hover {
            border-color: #0d6efd;
            background-color: rgba(13, 110, 253, 0.05);
        }
        .user-welcome {
            background: linear-gradient(135deg, #2c3e50 0%, #4a6491 100%);
            color: white;
            border-radius: 10px;
        }
        .btn-outline-custom {
            border-width: 2px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <!-- En-tête -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="user-welcome p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="h3 mb-2">
                                <i class="fas fa-user-shield me-2"></i>Dashboard Administrateur
                            </h1>
                            <p class="mb-0 opacity-75">
                                <i class="fas fa-user me-1"></i> Bienvenue, <strong>{{ auth()->user()->name }}</strong>
                            </p>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-light text-dark fs-6 p-2">
                                <i class="fas fa-crown text-warning me-1"></i> Rôle: Administrateur
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cartes d'action rapide -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">
                            <i class="fas fa-bolt text-warning me-2"></i>Actions Rapides
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Carte 1: Gestion Utilisateurs -->
                            <div class="col-md-3 mb-3">
                                @if(Route::has('admin.users.index'))
                                    <a href="{{ route('admin.users.index') }}" class="quick-action-btn btn btn-outline-danger p-4">
                                        <i class="fas fa-user-cog fa-3x mb-3"></i>
                                        <span class="fw-bold fs-5">Gérer Utilisateurs</span>
                                        <small class="text-muted mt-1">Ajouter/modifier/supprimer</small>
                                    </a>
                                @else
                                    <div class="quick-action-btn btn btn-outline-secondary p-4 disabled">
                                        <i class="fas fa-user-cog fa-3x mb-3"></i>
                                        <span class="fw-bold fs-5">Gérer Utilisateurs</span>
                                        <small class="text-muted mt-1">Route non configurée</small>
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Carte 2: Configuration -->
                            <div class="col-md-3 mb-3">
                                @if(Route::has('admin.configuration'))
                                    <a href="{{ route('admin.configuration') }}" class="quick-action-btn btn btn-outline-primary p-4">
                                        <i class="fas fa-cogs fa-3x mb-3"></i>
                                        <span class="fw-bold fs-5">Configuration</span>
                                        <small class="text-muted mt-1">Paramètres système</small>
                                    </a>
                                @else
                                    <div class="quick-action-btn btn btn-outline-secondary p-4 disabled">
                                        <i class="fas fa-cogs fa-3x mb-3"></i>
                                        <span class="fw-bold fs-5">Configuration</span>
                                        <small class="text-muted mt-1">À venir</small>
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Carte 3: Agences -->
                            <div class="col-md-3 mb-3">
                                @if(Route::has('admin.agencies'))
                                    <a href="{{ route('admin.agencies') }}" class="quick-action-btn btn btn-outline-warning p-4">
                                        <i class="fas fa-building fa-3x mb-3"></i>
                                        <span class="fw-bold fs-5">Agences</span>
                                        <small class="text-muted mt-1">Gestion agences</small>
                                    </a>
                                @else
                                    <div class="quick-action-btn btn btn-outline-secondary p-4 disabled">
                                        <i class="fas fa-building fa-3x mb-3"></i>
                                        <span class="fw-bold fs-5">Agences</span>
                                        <small class="text-muted mt-1">À venir</small>
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Carte 4: Monitoring -->
                            <div class="col-md-3 mb-3">
                                @if(Route::has('admin.system-monitoring'))
                                    <a href="{{ route('admin.system-monitoring') }}" class="quick-action-btn btn btn-outline-info p-4">
                                        <i class="fas fa-desktop fa-3x mb-3"></i>
                                        <span class="fw-bold fs-5">Monitoring</span>
                                        <small class="text-muted mt-1">Performances système</small>
                                    </a>
                                @else
                                    <div class="quick-action-btn btn btn-outline-secondary p-4 disabled">
                                        <i class="fas fa-desktop fa-3x mb-3"></i>
                                        <span class="fw-bold fs-5">Monitoring</span>
                                        <small class="text-muted mt-1">À venir</small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques rapides -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card border-start border-primary border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                                <i class="fas fa-users text-primary fa-2x"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Utilisateurs</h6>
                                <h3 class="mb-0">156</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card border-start border-success border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="bg-success bg-opacity-10 p-3 rounded-circle me-3">
                                <i class="fas fa-exchange-alt text-success fa-2x"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Transactions/jour</h6>
                                <h3 class="mb-0">2,847</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card border-start border-warning border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="bg-warning bg-opacity-10 p-3 rounded-circle me-3">
                                <i class="fas fa-clock text-warning fa-2x"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">En attente</h6>
                                <h3 class="mb-0">42</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card border-start border-danger border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="bg-danger bg-opacity-10 p-3 rounded-circle me-3">
                                <i class="fas fa-exclamation-triangle text-danger fa-2x"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Alertes</h6>
                                <h3 class="mb-0">7</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions supplémentaires -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">
                            <i class="fas fa-tasks text-success me-2"></i>Autres Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-3">
                            @if(Route::has('admin.users.index'))
                                <a href="{{ route('admin.users.index') }}" class="btn btn-primary btn-lg">
                                    <i class="fas fa-users me-2"></i>Voir tous les utilisateurs
                                </a>
                            @endif
                            
                            @if(Route::has('profile.edit'))
                                <a href="{{ route('profile.edit') }}" class="btn btn-secondary btn-lg">
                                    <i class="fas fa-user-edit me-2"></i>Modifier mon profil
                                </a>
                            @endif
                            
                            <a href="#" class="btn btn-success btn-lg">
                                <i class="fas fa-chart-bar me-2"></i>Statistiques
                            </a>
                            
                            <a href="#" class="btn btn-info btn-lg">
                                <i class="fas fa-file-export me-2"></i>Exporter rapports
                            </a>
                            
                            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger btn-lg">
                                    <i class="fas fa-sign-out-alt me-2"></i>Déconnexion
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pied de page -->
        <footer class="mt-5 pt-4 border-top">
            <div class="row">
                <div class="col-md-6">
                    <p class="text-muted">
                        <i class="fas fa-university me-1"></i> BTL Swift Platform v1.0
                    </p>
                </div>
                <div class="col-md-6 text-end">
                    <p class="text-muted">
                        <i class="fas fa-clock me-1"></i> {{ now()->format('d/m/Y H:i') }}
                    </p>
                </div>
            </div>
        </footer>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Animation des cartes
            $('.card').hover(
                function() {
                    $(this).css('transform', 'translateY(-5px)');
                },
                function() {
                    $(this).css('transform', 'translateY(0)');
                }
            );
            
            // Mise à jour de l'heure
            function updateTime() {
                const now = new Date();
                const timeString = now.toLocaleTimeString('fr-FR', { 
                    hour: '2-digit', 
                    minute: '2-digit' 
                });
                const dateString = now.toLocaleDateString('fr-FR', {
                    weekday: 'long',
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric'
                });
                
                $('.current-time').text(dateString + ' - ' + timeString);
            }
            
            // Mettre à jour toutes les minutes
            setInterval(updateTime, 60000);
            
            // Confirmation pour déconnexion
            $('form[action*="logout"] button').on('click', function(e) {
                if (!confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
                    e.preventDefault();
                }
            });
            
            // Désactiver les boutons non configurés
            $('.btn.disabled').on('click', function(e) {
                e.preventDefault();
                alert('Cette fonctionnalité n\'est pas encore disponible.');
            });
        });
    </script>
</body>
</html>