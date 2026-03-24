<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
 
{
    $this->call([
        // D'ABORD créer toutes les permissions (un format unique)
        BankPermissionsSeeder::class,  // Renommer PermissionSeeder
        
        // ENSUITE créer les rôles avec leurs permissions
        BankRolesSeeder::class,
        
        // PUIS créer les utilisateurs
        BankUsersSeeder::class,
        
        // ENFIN les autres seeders si nécessaire
        // RoleSeeder::class, // À supprimer si conflit avec BankRolesSeeder
    ]);
}
}