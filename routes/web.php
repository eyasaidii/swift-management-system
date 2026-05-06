<?php

use App\Http\Controllers\AnomalySwiftController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MessageSwiftController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SwiftExportController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

Route::aliasMiddleware('role', RoleMiddleware::class);

// Export pour réentraînement IA (appelé par le microservice Python)
Route::get('/api/swift-export', [SwiftExportController::class, 'export']);

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', function () {
        $user = auth()->user();
        $primaryRole = $user->getRoleNames()->first();

        return match ($primaryRole) {
            'super-admin' => redirect()->route('admin.dashboard'),
            'swift-manager' => redirect()->route('international-admin.dashboard'),
            'swift-operator' => redirect()->route('international-user.dashboard'),
            'admin' => redirect()->route('admin.dashboard'),
            'international-admin' => redirect()->route('international-admin.dashboard'),
            'international-user' => redirect()->route('international-user.dashboard'),
            'backoffice' => redirect()->route('backoffice.dashboard'),
            'monetique' => redirect()->route('monetique.dashboard'),
            'chef-agence' => redirect()->route('chef-agence.dashboard'),
            'chargee' => redirect()->route('chargee.dashboard'),
            'compliance-officer' => redirect()->route('compliance.dashboard'),
            default => redirect()->route('profile.edit')->with('error', 'Rôle non configuré'),
        };
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ============ ADMIN ============
    Route::prefix('admin')->middleware('role:super-admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');

        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
        Route::get('/users/{user}/modifier', [UserController::class, 'edit'])->name('users.modifier');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::get('/users/export', [UserController::class, 'export'])->name('users.export');

        Route::prefix('permissions')->name('permissions.')->group(function () {
            Route::get('/', [PermissionController::class, 'index'])->name('index');
            Route::get('/create', [PermissionController::class, 'create'])->name('create');
            Route::post('/', [PermissionController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [PermissionController::class, 'edit'])->name('edit');
            Route::put('/{id}', [PermissionController::class, 'update'])->name('update');
            Route::delete('/{id}', [PermissionController::class, 'destroy'])->name('destroy');
            Route::post('/assign-to-role', [PermissionController::class, 'assignToRole'])->name('assign-to-role');
            Route::get('/role-permissions', [PermissionController::class, 'getRolePermissions'])->name('role-permissions');
            Route::get('/search', [PermissionController::class, 'search'])->name('search');
            Route::get('/duplicate/{id}', [PermissionController::class, 'duplicate'])->name('duplicate');
            Route::get('/export', [PermissionController::class, 'export'])->name('export');
            Route::post('/sync-from-config', [PermissionController::class, 'syncFromConfig'])->name('sync-from-config');
        });

        Route::get('/configuration', [DashboardController::class, 'configuration'])->name('configuration');
        Route::get('/agencies', [DashboardController::class, 'agencies'])->name('agencies');
        Route::get('/system-monitoring', [DashboardController::class, 'systemMonitoring'])->name('system-monitoring');
        Route::get('/audit-logs', [DashboardController::class, 'auditLogs'])->name('audit-logs');
        Route::get('/stats/messages', [DashboardController::class, 'chartData']);
    });

    // ============ INTERNATIONAL ADMIN ============
    Route::prefix('international-admin')->middleware('role:swift-manager')->name('international-admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'internationalAdmin'])->name('dashboard');
        Route::get('/correspondent-banks', [DashboardController::class, 'correspondentBanks'])->name('correspondent-banks');
        Route::get('/foreign-exchange', [DashboardController::class, 'foreignExchange'])->name('foreign-exchange');
        Route::get('/fx-rates', [DashboardController::class, 'fxRates'])->name('fx-rates');
        Route::get('/pending-transactions', [DashboardController::class, 'pendingInternational'])->name('pending-transactions');
        Route::post('/transactions/{id}/authorize', [DashboardController::class, 'authorizeTransaction'])->name('transactions.authorize');
    });

    Route::get('/international-admin/ia-analytics', [DashboardController::class, 'iaAnalytics'])
        ->middleware('role:swift-manager|super-admin')
        ->name('international-admin.ia-analytics');

    // ============ INTERNATIONAL USER ============
    Route::prefix('international-user')->middleware('role:swift-operator')->name('international-user.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'internationalUser'])->name('dashboard');
        Route::get('/my-transactions', [DashboardController::class, 'myInternationalTransactions'])->name('my-transactions');
        Route::get('/transactions/create', [DashboardController::class, 'createInternationalTransaction'])->name('transactions.create');
        Route::post('/transactions', [DashboardController::class, 'storeInternationalTransaction'])->name('transactions.store');
        Route::get('/swift-messages', [DashboardController::class, 'swiftMessages'])->name('swift-messages');
    });

    // Aliases de route pour nouveaux slugs de rôle (compatibilité vues)
    Route::get('/swift-manager/dashboard', [DashboardController::class, 'internationalAdmin'])
        ->name('swift-manager.dashboard')->middleware(['auth', 'role:swift-manager']);

    Route::get('/swift-operator/dashboard', [DashboardController::class, 'internationalUser'])
        ->name('swift-operator.dashboard')->middleware(['auth', 'role:swift-operator']);

    // ============ BACKOFFICE ============
    Route::prefix('backoffice')->middleware('role:backoffice')->name('backoffice.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'backoffice'])->name('dashboard');
        Route::get('/pending-operations', [DashboardController::class, 'pendingOperations'])->name('pending-operations');
        Route::post('/operations/{id}/process', [DashboardController::class, 'processOperation'])->name('operations.process');
        Route::get('/reconciliation', [DashboardController::class, 'reconciliation'])->name('reconciliation');
        Route::get('/nostro-accounts', [DashboardController::class, 'nostroAccounts'])->name('nostro-accounts');
    });

    // ============ MONÉTIQUE ============
    Route::prefix('monetique')->middleware('role:monetique')->name('monetique.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'monetique'])->name('dashboard');
        Route::get('/card-transactions', [DashboardController::class, 'cardTransactions'])->name('card-transactions');
        Route::post('/card-transactions/{id}/authorize', [DashboardController::class, 'authorizeCardTransaction'])->name('card-transactions.authorize');
        Route::get('/pos-terminals', [DashboardController::class, 'posTerminals'])->name('pos-terminals');
        Route::get('/fraud-detection', [DashboardController::class, 'fraudDetection'])->name('fraud-detection');
    });

    // ============ CHEF D'AGENCE ============
    Route::prefix('chef-agence')->middleware('role:chef-agence')->name('chef-agence.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'chefAgence'])->name('dashboard');
        Route::get('/agency-stats', [DashboardController::class, 'agencyStats'])->name('agency-stats');
        Route::get('/staff', [DashboardController::class, 'agencyStaff'])->name('staff');
        Route::get('/local-transactions', [DashboardController::class, 'localTransactions'])->name('local-transactions');
        Route::post('/local-transactions/{id}/authorize', [DashboardController::class, 'authorizeLocal'])->name('local-transactions.authorize');
    });

    // ============ CHARGÉ(E) ============
    Route::prefix('chargee')->middleware('role:chargee')->name('chargee.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'chargee'])->name('dashboard');
        Route::get('/client-operations', [DashboardController::class, 'clientOperations'])->name('client-operations');
        Route::get('/operations/create', [DashboardController::class, 'createClientOperation'])->name('operations.create');
        Route::post('/operations', [DashboardController::class, 'storeClientOperation'])->name('operations.store');
        Route::get('/clients', [DashboardController::class, 'clients'])->name('clients');
    });

    // ============ COMPLIANCE ============
    Route::prefix('compliance')->middleware('role:compliance-officer')->name('compliance.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'compliance'])->name('dashboard');
        Route::get('/aml-alerts', [DashboardController::class, 'amlAlerts'])->name('aml-alerts');
        Route::post('/aml-alerts/{id}/process', [DashboardController::class, 'processAmlAlert'])->name('aml-alerts.process');
        Route::resource('aml-rules', DashboardController::class)->only(['index', 'create', 'store']);
        Route::get('/sanctions-screening', [DashboardController::class, 'sanctionsScreening'])->name('sanctions-screening');
    });

    // ============================================================
    // ROUTES SWIFT — ORDRE CRITIQUE
    // ============================================================
    Route::prefix('swift')->name('swift.')->group(function () {

        // Routes sans paramètre
        Route::get('/', [MessageSwiftController::class, 'index'])->name('index');
        Route::get('/creer', [MessageSwiftController::class, 'create'])->name('create');
        Route::post('/', [MessageSwiftController::class, 'store'])->name('store');

        // Import
        Route::middleware('can:import,App\\Models\\MessageSwift')->group(function () {
            Route::get('/importer', [MessageSwiftController::class, 'importForm'])->name('import.form');
            Route::post('/importer', [MessageSwiftController::class, 'import'])->name('import');
        });

        // Export + Export Center
        Route::middleware('role:super-admin,backoffice,monetique,chef-agence,swift-manager,swift-operator')
            ->group(function () {
                Route::get('/exporter', [MessageSwiftController::class, 'export'])->name('export');
                Route::get('/export-center', [DashboardController::class, 'exportCenter'])->name('export-center');
            });

        // Champs AJAX — AVANT /{id}
        Route::get('/fields/{type}', [MessageSwiftController::class, 'getFields'])->name('fields');

        // =========================================================
        // ROUTES IA — ANOMALIES (avant /{id} — ORDRE CRITIQUE)     ← AJOUT IA
        // =========================================================
        Route::middleware('role:super-admin,swift-manager')->group(function () {

            // Liste + filtres
            Route::get('/anomalies',
                [AnomalySwiftController::class, 'index'])
                ->name('anomalies.index');

            // Analyser tous les messages
            Route::post('/anomalies/analyze-all',
                [AnomalySwiftController::class, 'analyzeAll'])
                ->name('anomalies.analyze-all');

            // ← NOUVEAU : analyser un seul message (appelé depuis swift.show)
            Route::post('/anomalies/analyze/{id}',
                [AnomalySwiftController::class, 'analyzeSingle'])
                ->name('anomalies.analyze-single');

            // Détail d'une anomalie
            Route::get('/anomalies/{id}',
                [AnomalySwiftController::class, 'show'])
                ->name('anomalies.show');

            // Marquer vérifiée
            Route::patch('/anomalies/{id}/verify',
                [AnomalySwiftController::class, 'verify'])
                ->name('anomalies.verify');

            // Re-analyser une anomalie existante
            Route::post('/anomalies/{id}/reanalyze',
                [AnomalySwiftController::class, 'reanalyze'])
                ->name('anomalies.reanalyze');
        });
        // =========================================================

        // Actions sur message — AVANT /{id}
        Route::get('/{id}/view-mt', [MessageSwiftController::class, 'viewMt'])->name('view-mt');
        Route::get('/{id}/view-mx', [MessageSwiftController::class, 'viewMx'])->name('view-mx');
        Route::patch('/{id}/process', [MessageSwiftController::class, 'process'])->name('process');
        Route::patch('/{id}/reject', [MessageSwiftController::class, 'reject'])->name('reject');
        Route::patch('/{id}/authorize', [MessageSwiftController::class, 'approveMessage'])->name('authorize')->middleware('role:super-admin,swift-manager');
        Route::patch('/{id}/suspend', [MessageSwiftController::class, 'suspend'])->name('suspend')->middleware('role:super-admin,swift-manager');
        Route::delete('/{id}', [MessageSwiftController::class, 'destroy'])->name('destroy')->middleware('role:super-admin,swift-manager');

        // Détail et PDF — EN DERNIER
        Route::get('/{id}', [MessageSwiftController::class, 'show'])->name('show');
        Route::get('/{id}/pdf', [MessageSwiftController::class, 'downloadPdf'])->name('pdf');
    });

    // ───────────────────────────────────────────────
    // ROUTES PARTAGÉES
    // ───────────────────────────────────────────────
    Route::middleware('role:super-admin,swift-manager,swift-operator,compliance-officer')
        ->prefix('swift-legacy')->name('swift-legacy.')->group(function () {
            Route::get('/messages', [DashboardController::class, 'allSwiftMessages'])->name('messages');
            Route::post('/messages/import', [DashboardController::class, 'importSwift'])->name('messages.import');
        });

    Route::middleware('role:super-admin,backoffice,monetique,chef-agence,chargee')
        ->prefix('transactions')->name('transactions.')->group(function () {
            Route::get('/', [DashboardController::class, 'allTransactions'])->name('index');
            Route::get('/export', [DashboardController::class, 'exportTransactions'])->name('export');
        });

    Route::middleware('role:super-admin,chef-agence,compliance-officer')
        ->prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [DashboardController::class, 'allReports'])->name('index');
            Route::get('/generate', [DashboardController::class, 'generateReport'])->name('generate');
        });

    Route::get('/api/stats/messages', [DashboardController::class, 'chartData'])->name('api.stats.messages');
});

// ───────────────────────────────────────────────
// AUTHENTIFICATION COMPLÉMENTAIRE
// ───────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('forgot-password', [\App\Http\Controllers\Auth\PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [\App\Http\Controllers\Auth\PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('reset-password/{token}', [\App\Http\Controllers\Auth\NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [\App\Http\Controllers\Auth\NewPasswordController::class, 'store'])->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', \App\Http\Controllers\Auth\EmailVerificationPromptController::class)->name('verification.notice');
    Route::get('verify-email/{id}/{hash}', \App\Http\Controllers\Auth\VerifyEmailController::class)->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('email/verification-notification', [\App\Http\Controllers\Auth\EmailVerificationNotificationController::class, 'store'])->middleware('throttle:6,1')->name('verification.send');
    Route::get('confirm-password', [\App\Http\Controllers\Auth\ConfirmablePasswordController::class, 'show'])->name('password.confirm');
    Route::post('confirm-password', [\App\Http\Controllers\Auth\ConfirmablePasswordController::class, 'store']);
    Route::put('password', [\App\Http\Controllers\Auth\PasswordController::class, 'update'])->name('password.update');
});

// ───────────────────────────────────────────────
// DEBUG (supprimer en production)
// ───────────────────────────────────────────────
Route::get('/debug-user', function () {
    if (! auth()->check()) {
        return 'Non connecté';
    }
    $user = auth()->user();

    return response()->json([
        'user' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email],
        'roles' => $user->getRoleNames(),
        'permissions' => $user->getAllPermissions()->pluck('name'),
        'dashboard_route' => route('dashboard'),
        'is_verified' => ! is_null($user->email_verified_at),
    ]);
})->middleware('auth');

Route::get('/debug-swift-permissions', function () {
    if (! auth()->check()) {
        return 'Non connecté';
    }
    $user = auth()->user();

    return response()->json([
        'user' => $user->email,
        'role' => $user->getRoleNames()->first(),
        'can_create' => \App\Models\MessageSwift::canCreate($user, request('type', 'MT103')),
        'available_types_in' => array_keys(\App\Models\MessageSwift::getAvailableTypes($user, 'IN')),
        'available_types_out' => array_keys(\App\Models\MessageSwift::getAvailableTypes($user, 'OUT')),
        'permissions' => [
            'view_any' => $user->can('viewAny', \App\Models\MessageSwift::class),
            'create' => $user->can('create', \App\Models\MessageSwift::class),
            'import' => $user->can('import', \App\Models\MessageSwift::class),
            'export' => $user->can('export', \App\Models\MessageSwift::class),
        ],
    ]);
})->middleware('auth');

Route::fallback(function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
});
