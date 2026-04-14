<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class BankRolesSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🎭 Début de la création/MAJ des rôles bancaires...');

        // DÉFINIR LES RÔLES AVEC LEURS PERMISSIONS
        $bankRoles = [
            'super-admin' => [
                'display_name' => 'Super-Admin Système',
                'color' => 'danger',
                'icon' => 'fas fa-crown',
                'permissions' => [] // Sera rempli automatiquement
            ],

            'swift-manager' => [
                'display_name' => 'Swift Manager',
                'color' => 'primary',
                'icon' => 'fas fa-globe-americas',
                'permissions' => [
                    'view-dashboard', 'view-statistics',
                    'view-international-transactions', 'create-international-transactions',
                    'edit-international-transactions', 'authorize-international-transactions',
                    'manage-correspondent-banks', 'view-correspondent-banks',
                    'view-fx-operations', 'authorize-fx-operations', 'view-fx-rates',
                    'view-swift-messages', 'create-swift-messages', 'edit-swift-messages',
                    'import-swift-messages', 'export-swift-messages', 'validate-swift-messages',
                    'view-international-reports', 'export-international-data',
                    'view-aml-alerts', 'process-aml-alerts',
                    'sanctions-screening', 'view-sanctions-list',
                ]
            ],
            'swift-operator' => [
                'display_name' => 'Swift Operator',
                'color' => 'info',
                'icon' => 'fas fa-user-tie',
                'permissions' => [
                    'view-dashboard',
                    'view-international-transactions', 'create-international-transactions',
                    'view-swift-messages', 'view-international-reports',
                    'view-correspondent-banks', 'view-fx-rates',
                    'export-international-data',
                ]
            ],

            'backoffice' => [
                'display_name' => 'Agent Backoffice',
                'color' => 'warning',
                'icon' => 'fas fa-desktop',
                'permissions' => [
                    'view-dashboard', 'view-statistics',
                    'view-transactions', 'process-transactions',
                    'reconciliation-operations', 'manage-nostro-accounts',
                    'view-pending-operations', 'view-backoffice-reports',
                    'process-payments', 'validate-transactions',
                    'view-swift-messages', 'import-swift-messages', 'export-swift-messages',
                    'view-client-transactions', 'view-card-transactions',
                    'view-client-info',
                ]
            ],

            'monetique' => [
                'display_name' => 'Agent Monétique',
                'color' => 'success',
                'icon' => 'fas fa-credit-card',
                'permissions' => [
                    'view-dashboard', 'view-statistics',
                    'view-card-transactions', 'process-card-transactions',
                    'authorize-card-transactions', 'manage-pos-terminals',
                    'fraud-monitoring', 'process-refunds',
                    'view-swift-messages', 'import-swift-messages',
                    'view-monetique-reports',
                    'view-client-info', 'view-client-portfolio',
                ]
            ],

            'chef-agence' => [
                'display_name' => 'Chef d\'Agence',
                'color' => 'dark',
                'icon' => 'fas fa-building',
                'permissions' => [
                    'view-dashboard', 'view-statistics',
                    'view-agency-transactions', 'authorize-agency-transactions',
                    'manage-agency-staff', 'view-agency-staff',
                    'view-agency-reports', 'generate-agency-reports',
                    'local-monitoring', 'view-agency-clients',
                    'view-swift-messages', 'create-swift-messages', 'export-swift-messages',
                    'view-client-transactions', 'create-client-transactions',
                    'view-client-info', 'process-client-requests',
                ]
            ],

            'chargee' => [
                'display_name' => 'Chargé(e) Clientèle',
                'color' => 'secondary',
                'icon' => 'fas fa-users',
                'permissions' => [
                    'view-dashboard',
                    'view-swift-messages', 'create-swift-messages',
                    'view-client-transactions', 'create-client-transactions',
                    'view-client-info', 'process-client-requests',
                    'send-client-messages', 'view-client-portfolio',
                ]
            ],

            'compliance-officer' => [
                'display_name' => 'Agent Compliance',
                'color' => 'purple',
                'icon' => 'fas fa-shield-alt',
                'permissions' => [
                    'view-dashboard', 'view-statistics',
                    'view-aml-alerts', 'process-aml-alerts', 'resolve-aml-alerts',
                    'create-aml-rules', 'edit-aml-rules', 'delete-aml-rules',
                    'sanctions-screening', 'view-sanctions-list',
                    'view-audit-trail', 'generate-compliance-reports',
                    'view-client-info', 'view-transactions',
                    'view-international-transactions',
                ]
            ]
        ];

        // Récupérer toutes les permissions pour l'admin
        $allPermissions = Permission::pluck('name')->toArray();
        $bankRoles['super-admin']['permissions'] = $allPermissions;

        // CRÉER LES RÔLES ET ASSIGNER LES PERMISSIONS
        $this->command->info('Création/Mise à jour des rôles...');
        
        $totalRoles = 0;
        $totalPermissionsAssigned = 0;
        
        foreach ($bankRoles as $roleKey => $roleData) {
            try {
                // Créer ou récupérer le rôle
                $role = Role::firstOrCreate([
                    'name' => $roleKey,
                    'guard_name' => 'web'
                ]);

                $permissionNames = $roleData['permissions'];
                
                // Vérifier l'existence des permissions
                $existingPermissions = Permission::whereIn('name', $permissionNames)->pluck('name')->toArray();
                
                // Synchroniser les permissions
                $role->syncPermissions($existingPermissions);
                
                $totalRoles++;
                $totalPermissionsAssigned += count($existingPermissions);
                
                $this->command->info("   ✅ {$roleData['display_name']} ({$roleKey}) - " . count($existingPermissions) . " permissions assignées");
                
            } catch (\Exception $e) {
                $this->command->error("   ❌ Erreur pour {$roleKey}: " . $e->getMessage());
                continue;
            }
        }

        // AFFICHER LE RÉSUMÉ
        $this->command->info('');
        $this->command->info('🎉 Création des rôles bancaires terminée !');
        $this->command->info('==========================================');
        $this->command->info("📊 RÉSUMÉ:");
        $this->command->info("   • Rôles créés/mis à jour: {$totalRoles}");
        
        // Afficher les permissions par rôle
        foreach ($bankRoles as $roleKey => $roleData) {
            $role = Role::where('name', $roleKey)->first();
            $count = $role->permissions()->count();
            $this->command->info("   • {$roleData['display_name']}: {$count} permissions");
        }
    }
}