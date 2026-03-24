<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Vérification globale - Admin peut tout faire
     */
    public function before(User $user, $ability)
    {
        // L'administrateur a tous les droits
        if ($user->hasRole('admin')) {
            return true;
        }
    }

    /**
     * Déterminer si l'utilisateur peut voir la liste des utilisateurs
     */
    public function viewAny(User $user): bool
    {
        // Admin peut voir tous les utilisateurs
        if ($user->hasRole('admin')) {
            return true;
        }
        
        // Chef d'agence peut voir les utilisateurs de son agence
        if ($user->hasRole('chef-agence')) {
            return true;
        }
        
        return false;
    }

    /**
     * Déterminer si l'utilisateur peut voir un utilisateur spécifique
     */
    public function view(User $user, User $model): bool
    {
        // Admin peut voir tout le monde
        if ($user->hasRole('admin')) {
            return true;
        }
        
        // Chef d'agence peut voir les utilisateurs de son agence
        if ($user->hasRole('chef-agence') && $user->agence === $model->agence) {
            return true;
        }
        
        // Un utilisateur peut voir son propre profil
        return $user->id === $model->id;
    }

    /**
     * Déterminer si l'utilisateur peut créer des utilisateurs
     */
    public function create(User $user): bool
    {
        // Seul l'admin peut créer des utilisateurs
        return $user->hasRole('admin');
    }

    /**
     * Déterminer si l'utilisateur peut modifier un utilisateur
     */
    public function update(User $user, User $model): bool
    {
        // Admin peut modifier tout le monde
        if ($user->hasRole('admin')) {
            return true;
        }
        
        // Chef d'agence peut modifier les utilisateurs de son agence (sauf admin)
        if ($user->hasRole('chef-agence') && 
            $user->agence === $model->agence && 
            !$model->hasRole('admin')) {
            return true;
        }
        
        // Un utilisateur peut modifier son propre profil
        return $user->id === $model->id;
    }

    /**
     * Déterminer si l'utilisateur peut supprimer un utilisateur
     */
    public function delete(User $user, User $model): bool
    {
        // Empêcher de se supprimer soi-même
        if ($user->id === $model->id) {
            return false;
        }
        
        // Empêcher de supprimer l'admin principal
        if ($model->email === 'admin@btl.ma') {
            return false;
        }
        
        // Seul l'admin peut supprimer
        return $user->hasRole('admin');
    }

    /**
     * Déterminer si l'utilisateur peut restaurer un utilisateur supprimé
     */
    public function restore(User $user, User $model): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Déterminer si l'utilisateur peut supprimer définitivement
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Déterminer si l'utilisateur peut réinitialiser le mot de passe
     */
    public function resetPassword(User $user, User $model): bool
    {
        // Admin peut réinitialiser tous les mots de passe
        if ($user->hasRole('admin')) {
            return true;
        }
        
        // Chef d'agence peut réinitialiser les mots de passe de son agence
        if ($user->hasRole('chef-agence') && $user->agence === $model->agence) {
            return true;
        }
        
        return false;
    }

    /**
     * Déterminer si l'utilisateur peut activer/désactiver un compte
     */
    public function toggleStatus(User $user, User $model): bool
    {
        // Empêcher de se désactiver soi-même
        if ($user->id === $model->id) {
            return false;
        }
        
        // Empêcher de désactiver l'admin principal
        if ($model->email === 'admin@btl.ma') {
            return false;
        }
        
        // Seul l'admin peut activer/désactiver
        return $user->hasRole('admin');
    }

    /**
     * Déterminer si l'utilisateur peut exporter la liste des utilisateurs
     */
    public function export(User $user): bool
    {
        // Admin et chef d'agence peuvent exporter
        return $user->hasRole('admin') || $user->hasRole('chef-agence');
    }
}