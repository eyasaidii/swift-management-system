<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Rôles bancaires disponibles
     */
    public static function getBankRoles(): array
    {
        return [
            'super-admin' => [
                'name' => 'Super-Admin',
                'description' => 'Accès complet au système et gestion technique',
                'color' => 'danger',
                'icon' => 'fas fa-crown',
                'level' => 100, // Niveau de permission
            ],
            'swift-manager' => [
                'name' => 'Swift Manager',
                'description' => 'Supervision des opérations SWIFT',
                'color' => 'primary',
                'icon' => 'fas fa-globe-americas',
                'level' => 90,
            ],
            'swift-operator' => [
                'name' => 'Swift Operator',
                'description' => 'Opérateur SWIFT - gestion et consultation',
                'color' => 'info',
                'icon' => 'fas fa-user-tie',
                'level' => 80,
            ],
            'backoffice' => [
                'name' => 'Backoffice',
                'description' => 'Traitement des opérations backoffice',
                'color' => 'warning',
                'icon' => 'fas fa-desktop',
                'level' => 70,
            ],
            'monetique' => [
                'name' => 'Monétique',
                'description' => 'Gestion des transactions monétiques',
                'color' => 'success',
                'icon' => 'fas fa-credit-card',
                'level' => 60,
            ],
            'chef-agence' => [
                'name' => 'Chef Agence',
                'description' => 'Gestion d\'agence locale',
                'color' => 'dark',
                'icon' => 'fas fa-building',
                'level' => 50,
            ],
            'chargee' => [
                'name' => 'Chargé(e) Clientèle',
                'description' => 'Opérations clientèle',
                'color' => 'secondary',
                'icon' => 'fas fa-users',
                'level' => 40,
            ],
            'compliance-officer' => [
                'name' => 'Compliance Officer',
                'description' => 'Surveillance AML/CFT',
                'color' => 'purple',
                'icon' => 'fas fa-shield-alt',
                'level' => 85,
            ],
        ];
    }

    /**
     * Récupérer le rôle principal formaté
     */
    public function getPrimaryRoleFormatted(): array
    {
        $roles = self::getBankRoles();
        $userRole = $this->getRoleNames()->first();

        return $roles[$userRole] ?? [
            'name' => $userRole ?? 'Non défini',
            'description' => 'Rôle utilisateur',
            'color' => 'light',
            'icon' => 'fas fa-user',
            'level' => 0,
        ];
    }

    /**
     * Vérifier si l'utilisateur a un rôle spécifique
     * (Alternative à la méthode Spatie)
     */
    public function hasRoleName(string $role): bool
    {
        return $this->hasRole($role);
    }

    /**
     * Vérifier si l'utilisateur a au moins un des rôles
     */
    public function hasAnyRoleName(array $roles): bool
    {
        return $this->hasAnyRole($roles);
    }

    /**
     * Récupérer le niveau du rôle principal
     */
    public function getRoleLevel(): int
    {
        $roleInfo = $this->getPrimaryRoleFormatted();

        return $roleInfo['level'] ?? 0;
    }

    /**
     * Vérifier si l'utilisateur a un niveau de rôle supérieur ou égal
     */
    public function hasRoleLevel(int $requiredLevel): bool
    {
        return $this->getRoleLevel() >= $requiredLevel;
    }

    /**
     * Formater les informations de l'utilisateur pour l'affichage
     */
    public function getDisplayInfo(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->getPrimaryRoleFormatted(),
            'created_at' => $this->created_at->format('d/m/Y'),
            'has_verified_email' => ! is_null($this->email_verified_at),
            'role_names' => $this->getRoleNames()->toArray(),
            'permissions' => $this->getAllPermissions()->pluck('name')->toArray(),
        ];
    }

    /**
     * Scopes pour filtrer par rôle
     */
    public function scopeByRole($query, $role)
    {
        return $query->whereHas('roles', function ($q) use ($role) {
            $q->where('name', $role);
        });
    }

    public function scopeAdmins($query)
    {
        return $this->scopeByRole($query, 'super-admin');
    }

    public function scopeInternational($query)
    {
        return $query->whereHas('roles', function ($q) {
            $q->whereIn('name', ['swift-manager', 'swift-operator']);
        });
    }

    public function scopeBackoffice($query)
    {
        return $this->scopeByRole($query, 'backoffice');
    }

    public function scopeMonetique($query)
    {
        return $this->scopeByRole($query, 'monetique');
    }

    /**
     * Attribuer un rôle à l'utilisateur avec validation
     */
    public function assignValidRole(string $role): bool
    {
        $validRoles = array_keys(self::getBankRoles());

        if (! in_array($role, $validRoles)) {
            return false;
        }

        $this->syncRoles([$role]);

        return true;
    }

    /**
     * Méthode pour vérifier les accès dans les vues
     */
    public function canAccessSection(string $section): bool
    {
        $accessRules = [
            'super-admin' => ['all'],
            'swift-manager' => ['international', 'reports', 'dashboard'],
            'swift-operator' => ['international', 'dashboard'],
            'backoffice' => ['backoffice', 'transactions', 'dashboard'],
            'monetique' => ['monetique', 'transactions', 'dashboard'],
            'chef-agence' => ['agency', 'clients', 'dashboard'],
            'chargee' => ['clients', 'dashboard'],
            'compliance-officer' => ['compliance', 'reports', 'dashboard'],
        ];

        $userRole = $this->getRoleNames()->first();

        if (! isset($accessRules[$userRole])) {
            return false;
        }

        return in_array('all', $accessRules[$userRole]) ||
               in_array($section, $accessRules[$userRole]);
    }
}
