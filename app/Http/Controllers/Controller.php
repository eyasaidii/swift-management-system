<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Constantes pour les réponses JSON
     */
    protected const SUCCESS = 'success';

    protected const ERROR = 'error';

    protected const WARNING = 'warning';

    protected const INFO = 'info';

    /**
     * Retourne une réponse JSON standardisée
     */
    protected function jsonResponse($type, $message, $data = null, $code = 200)
    {
        return response()->json([
            'type' => $type,
            'message' => $message,
            'data' => $data,
            'timestamp' => now()->toDateTimeString(),
        ], $code);
    }

    /**
     * Réponse de succès
     */
    protected function success($message, $data = null, $code = 200)
    {
        return $this->jsonResponse(self::SUCCESS, $message, $data, $code);
    }

    /**
     * Réponse d'erreur
     */
    protected function error($message, $data = null, $code = 400)
    {
        return $this->jsonResponse(self::ERROR, $message, $data, $code);
    }

    /**
     * Réponse de succès avec redirection
     */
    protected function successRedirect($route, $message, $data = [])
    {
        return redirect()->route($route)
            ->with('success', $message)
            ->with('data', $data);
    }

    /**
     * Réponse d'erreur avec redirection
     */
    protected function errorRedirect($route, $message, $errors = [])
    {
        return redirect()->route($route)
            ->with('error', $message)
            ->withErrors($errors)
            ->withInput();
    }

    /**
     * Vérifie si l'utilisateur a un rôle spécifique
     */
    protected function userHasRole($role)
    {
        return auth()->check() && auth()->user()->hasRole($role);
    }

    /**
     * Vérifie si l'utilisateur a un des rôles spécifiés
     */
    protected function userHasAnyRole($roles)
    {
        return auth()->check() && auth()->user()->hasAnyRole($roles);
    }

    /**
     * Vérifie si l'utilisateur a toutes les permissions
     */
    protected function userHasAllPermissions($permissions)
    {
        return auth()->check() && auth()->user()->hasAllPermissions($permissions);
    }

    /**
     * Récupère le rôle principal de l'utilisateur connecté
     */
    protected function getUserPrimaryRole()
    {
        if (! auth()->check()) {
            return null;
        }

        return auth()->user()->getRoleNames()->first();
    }

    /**
     * Récupère les informations formatées du rôle de l'utilisateur
     */
    protected function getUserRoleInfo()
    {
        if (! auth()->check()) {
            return null;
        }

        return auth()->user()->getRoleInfo();
    }

    /**
     * Récupère l'agence de l'utilisateur connecté
     */
    protected function getUserAgency()
    {
        return auth()->check() ? auth()->user()->agence : null;
    }

    /**
     * Récupère le matricule de l'utilisateur connecté
     */
    protected function getUserMatricule()
    {
        return auth()->check() ? auth()->user()->matricule : null;
    }

    /**
     * Vérifie si l'utilisateur est administrateur
     */
    protected function isAdmin()
    {
        return $this->userHasRole('super-admin');
    }

    /**
     * Vérifie si l'utilisateur est dans le domaine international
     */
    protected function isInternationalUser()
    {
        return $this->userHasAnyRole(['swift-manager', 'swift-operator']);
    }

    /**
     * Vérifie si l'utilisateur est en backoffice
     */
    protected function isBackoffice()
    {
        return $this->userHasRole('backoffice');
    }

    /**
     * Vérifie si l'utilisateur est en monétique
     */
    protected function isMonetique()
    {
        return $this->userHasRole('monetique');
    }

    /**
     * Vérifie si l'utilisateur est chef d'agence
     */
    protected function isChefAgence()
    {
        return $this->userHasRole('chef-agence');
    }

    /**
     * Vérifie si l'utilisateur est chargé clientèle
     */
    protected function isChargee()
    {
        return $this->userHasRole('chargee');
    }

    /**
     * Vérifie si l'utilisateur est compliance
     */
    protected function isCompliance()
    {
        return $this->userHasRole('compliance-officer');
    }

    /**
     * Journalise une activité
     */
    protected function logActivity($description, $properties = [])
    {
        if (auth()->check()) {
            activity()
                ->causedBy(auth()->user())
                ->withProperties($properties)
                ->log($description);
        }
    }

    /**
     * Formate une date pour l'affichage
     */
    protected function formatDate($date, $format = 'd/m/Y H:i')
    {
        if (! $date) {
            return 'N/A';
        }

        if (is_string($date)) {
            $date = new \DateTime($date);
        }

        return $date->format($format);
    }

    /**
     * Formate un montant
     */
    protected function formatAmount($amount, $currency = 'DH')
    {
        if (! is_numeric($amount)) {
            return 'N/A';
        }

        return number_format($amount, 2, ',', ' ').' '.$currency;
    }

    /**
     * Génère une référence de transaction
     */
    protected function generateTransactionReference($prefix = 'TXN')
    {
        return $prefix.'-'.date('Ymd').'-'.strtoupper(uniqid());
    }

    /**
     * Valide les données avec des règles spécifiques aux rôles
     */
    protected function validateWithRoleRules($request, $rules, $messages = [])
    {
        // Règles supplémentaires basées sur le rôle
        $userRole = $this->getUserPrimaryRole();

        switch ($userRole) {
            case 'super-admin':
                // Super-admin peut tout faire
                break;

            case 'chef-agence':
                // Chef agence limité à son agence
                $rules['agence'] = 'required|in:'.$this->getUserAgency();
                break;

            case 'chargee':
                // Chargé(e) limité à certaines opérations
                if (isset($rules['type_operation'])) {
                    $rules['type_operation'] .= '|in:virement,retrait,depot';
                }
                break;
        }

        return $request->validate($rules, $messages);
    }

    /**
     * Récupère les statistiques de base pour le dashboard
     */
    protected function getDashboardStats($role)
    {
        $stats = [];

        switch ($role) {
            case 'super-admin':
                $stats = [
                    'total_users' => \App\Models\User::count(),
                    'active_users' => \App\Models\User::where('is_active', true)->count(),
                    'total_transactions' => 0, // À remplacer par votre modèle
                    'total_alerts' => 0, // À remplacer par votre modèle
                ];
                break;

            case 'swift-manager':
                $stats = [
                    'international_transactions' => 125,
                    'pending_authorizations' => 7,
                    'correspondent_banks' => 18,
                    'fx_operations' => 45,
                ];
                break;

            case 'swift-operator':
                $stats = [
                    'my_transactions' => 18,
                    'pending_transactions' => 5,
                    'swift_messages' => 47,
                    'total_amount' => 425000,
                ];
                break;

            case 'backoffice':
                $stats = [
                    'pending_operations' => 87,
                    'processed_today' => 245,
                    'reconciliation_pending' => 15,
                    'discrepancies' => 8,
                ];
                break;

            case 'monetique':
                $stats = [
                    'card_transactions' => 8542,
                    'fraud_alerts' => 17,
                    'pos_terminals' => 245,
                    'amount_processed' => 4200000,
                ];
                break;

            case 'chef-agence':
                $user = auth()->user();
                $stats = [
                    'agency_transactions' => 156,
                    'agency_clients' => 89,
                    'agency_staff' => 12,
                    'agency_volume' => 4500000,
                ];
                break;

            case 'chargee':
                $user = auth()->user();
                $stats = [
                    'client_operations' => 34,
                    'pending_requests' => 8,
                    'active_clients' => 24,
                    'total_volume' => 1250000,
                ];
                break;

            case 'compliance-officer':
                $stats = [
                    'aml_alerts' => 23,
                    'high_risk_alerts' => 7,
                    'rules_active' => 15,
                    'sanctions_matches' => 3,
                ];
                break;
        }

        return $stats;
    }

    /**
     * Récupère les activités récentes
     */
    protected function getRecentActivities($limit = 10)
    {
        if (class_exists('\Spatie\Activitylog\Models\Activity')) {
            return \Spatie\Activitylog\Models\Activity::latest()
                ->take($limit)
                ->get();
        }

        return collect();
    }

    /**
     * Vérifie les permissions avant une action
     */
    protected function checkPermission($permission, $resource = null)
    {
        if (! auth()->check()) {
            abort(403, 'Accès non autorisé.');
        }

        $user = auth()->user();

        if (! $user->hasPermissionTo($permission)) {
            abort(403, 'Vous n\'avez pas la permission nécessaire.');
        }

        // Vérifications supplémentaires selon le rôle
        if ($resource && method_exists($resource, 'getAgence')) {
            if ($user->hasRole('chef-agence') && $resource->getAgence() !== $user->agence) {
                abort(403, 'Accès limité à votre agence.');
            }
        }

        return true;
    }

    /**
     * Exporte des données au format CSV
     */
    protected function exportToCSV($data, $filename, $headers = null)
    {
        if (! $headers && count($data) > 0) {
            $headers = array_keys((array) $data[0]);
        }

        $callback = function () use ($data, $headers) {
            $file = fopen('php://output', 'w');

            // En-têtes
            fputcsv($file, $headers);

            // Données
            foreach ($data as $row) {
                fputcsv($file, (array) $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'.csv"',
        ]);
    }
}
