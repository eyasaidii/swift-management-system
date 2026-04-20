<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class BankPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🏗️  Création des permissions bancaires...');

        // Permissions de direction (globales)
        $directionPermissions = [
            'view-received-messages',
            'view-emitted-messages',
        ];

        // Permissions générales
        $generalPermissions = [
            'view-dashboard', 'view-statistics', 'view-reports',
            'view-transactions', 'create-transactions', 'edit-transactions',
            'delete-transactions', 'authorize-transactions', 'process-transactions',
            'view-international-transactions', 'create-international-transactions',
            'edit-international-transactions', 'authorize-international-transactions',
            'view-agency-transactions', 'authorize-agency-transactions',
            'view-client-transactions', 'create-client-transactions',
            'view-card-transactions', 'process-card-transactions',
            'authorize-card-transactions', 'process-payments',
            'view-swift-messages', 'create-swift-messages', 'edit-swift-messages',
            'delete-swift-messages', 'import-swift-messages', 'export-swift-messages',
            'validate-swift-messages', 'parse-swift-xml', 'view-swift-queue',
            'view-users', 'create-users', 'edit-users', 'delete-users',
            'manage-user-roles', 'reset-user-password', 'manage-agency-staff',
            'view-aml-alerts', 'create-aml-rules', 'edit-aml-rules',
            'delete-aml-rules', 'process-aml-alerts', 'resolve-aml-alerts',
            'sanctions-screening', 'view-sanctions-list', 'view-audit-trail',
            'view-audit-logs', 'generate-compliance-reports', 'view-client-info',
            'generate-reports', 'view-system-monitoring', 'export-reports',
            'view-international-reports', 'view-backoffice-reports',
            'view-monetique-reports', 'view-agency-reports', 'generate-agency-reports',
            'manage-correspondent-banks', 'view-correspondent-banks',
            'view-fx-operations', 'authorize-fx-operations', 'view-fx-rates',
            'reconciliation-operations', 'manage-nostro-accounts',
            'view-pending-operations', 'validate-transactions',
            'fraud-monitoring', 'process-refunds',
            'manage-pos-terminals', 'view-client-portfolio',
            'process-client-requests', 'send-client-messages',
            'system-configuration', 'manage-agencies', 'manage-parameters',
            'export-data', 'import-data', 'export-international-data',
            'local-monitoring', 'view-agency-clients', 'view-agency-staff',
        ];

        // Permissions pour les types de messages (IN et OUT) – conservées pour d'éventuelles utilisations fines
        $typePermissions = [
            'IN.MT103', 'IN.MT192', 'IN.MT202', 'IN.MT202COV', 'IN.MT940', 'IN.MT950',
            'IN.MT200', 'IN.MT910', 'IN.MT900',
            'OUT.MT103', 'OUT.MT192', 'OUT.MT202', 'OUT.MT202COV', 'OUT.MT940', 'OUT.MT950',
            'IN.pacs.008', 'IN.pacs.009', 'IN.camt.053', 'IN.camt.052',
            'OUT.pacs.008', 'OUT.pacs.009', 'OUT.camt.053', 'OUT.camt.052',
        ];

        // Permissions pour les catégories (sidebar)
        $categoriePermissions = [
            'RECU.PACS', 'RECU.CAMT', 'RECU.1', 'RECU.2', 'RECU.3', 'RECU.4', 'RECU.5', 'RECU.7', 'RECU.9',
            'EMIS.PACS', 'EMIS.CAMT', 'EMIS.1', 'EMIS.2', 'EMIS.3', 'EMIS.4', 'EMIS.5', 'EMIS.7', 'EMIS.9',
            'RECU.ALL', 'EMIS.ALL',
        ];

        $allPermissions = array_merge($directionPermissions, $generalPermissions, $typePermissions, $categoriePermissions);

        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $this->command->info('✅ '.count($allPermissions).' permissions créées');

        // Récupérer les rôles
        $admin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $internationalAdmin = Role::firstOrCreate(['name' => 'swift-manager', 'guard_name' => 'web']);
        $internationalUser = Role::firstOrCreate(['name' => 'swift-operator', 'guard_name' => 'web']);
        $chefAgence = Role::firstOrCreate(['name' => 'chef-agence', 'guard_name' => 'web']);
        $chargee = Role::firstOrCreate(['name' => 'chargee', 'guard_name' => 'web']);
        $backoffice = Role::firstOrCreate(['name' => 'backoffice', 'guard_name' => 'web']);
        $monetique = Role::firstOrCreate(['name' => 'monetique', 'guard_name' => 'web']);

        // Super-admin : toutes les permissions
        $admin->syncPermissions(Permission::all());

        // Swift manager : toutes les permissions
        $internationalAdmin->syncPermissions(Permission::all());

        // Swift operator : toutes les permissions
        $internationalUser->syncPermissions(Permission::all());

        // Backoffice : voit tous les messages reçus, peut importer et exporter
        $backoffice->syncPermissions([
            'view-swift-messages',
            'import-swift-messages',
            'export-swift-messages',
            'view-received-messages',   // global IN
        ]);

        // Monétique : voit tous les messages reçus, peut importer et exporter
        $monetique->syncPermissions([
            'view-swift-messages',
            'import-swift-messages',
            'export-swift-messages',
            'view-received-messages',   // global IN
        ]);

        // Chargée : voit tous les messages émis, peut importer, exporter et créer
        $chargee->syncPermissions([
            'view-swift-messages',
            'create-swift-messages',
            'import-swift-messages',
            'export-swift-messages',
            'view-emitted-messages',    // global OUT
        ]);

        // Chef d'agence : voit tous les messages émis, peut importer, exporter et créer
        $chefAgence->syncPermissions([
            'view-swift-messages',
            'create-swift-messages',
            'import-swift-messages',
            'export-swift-messages',
            'view-emitted-messages',    // global OUT
        ]);

        $this->command->info('✅ Permissions assignées aux rôles');
    }
}
