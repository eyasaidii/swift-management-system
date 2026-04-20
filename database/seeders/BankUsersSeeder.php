<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class BankUsersSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('👤 Début de la création des utilisateurs bancaires...');

        // Utilisateurs de test pour chaque rôle
        $users = [
            [
                'name' => 'Admin System',
                'email' => 'admin@btl.ma',
                'password' => Hash::make('Admin123!'),
                'role' => 'super-admin',
                'email_verified_at' => now(), // Ajout de la vérification d'email
            ],
            [
                'name' => 'Swift Manager',
                'email' => 'int.admin@btl.ma',
                'password' => Hash::make('IntAdmin123!'),
                'role' => 'swift-manager',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Swift Operator',
                'email' => 'int.user@btl.ma',
                'password' => Hash::make('IntUser123!'),
                'role' => 'swift-operator',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Backoffice Manager',
                'email' => 'backoffice@btl.ma',
                'password' => Hash::make('Backoffice123!'),
                'role' => 'backoffice',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Monetique Operator',
                'email' => 'monetique@btl.ma',
                'password' => Hash::make('Monetique123!'),
                'role' => 'monetique',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Chef Agence',
                'email' => 'chef.agence@btl.ma',
                'password' => Hash::make('ChefAgence123!'),
                'role' => 'chef-agence',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Chargée Clientèle',
                'email' => 'chargee@btl.ma',
                'password' => Hash::make('Chargee123!'),
                'role' => 'chargee',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Compliance Officer',
                'email' => 'compliance@btl.ma',
                'password' => Hash::make('Compliance123!'),
                'role' => 'compliance-officer',
                'email_verified_at' => now(),
            ],
        ];

        $createdCount = 0;
        $updatedCount = 0;

        foreach ($users as $userData) {
            $roleName = $userData['role'];
            $email = $userData['email'];

            // Préparer les données utilisateur
            $userAttributes = [
                'name' => $userData['name'],
                'email' => $email,
                'password' => $userData['password'],
                'email_verified_at' => $userData['email_verified_at'] ?? null,
            ];

            // Vérifier si l'utilisateur existe déjà
            $existingUser = User::where('email', $email)->first();

            if ($existingUser) {
                // Mettre à jour l'utilisateur existant
                $existingUser->update($userAttributes);
                $user = $existingUser;
                $updatedCount++;
                $action = 'mis à jour';
            } else {
                // Créer un nouvel utilisateur
                $user = User::create($userAttributes);
                $createdCount++;
                $action = 'créé';
            }

            // Assigner le rôle
            $role = Role::where('name', $roleName)->first();

            if ($role) {
                // Synchroniser les rôles (remplace tous les rôles existants)
                $user->syncRoles([$roleName]);

                $this->command->info("   ✅ {$userData['name']} ({$email}) - {$action} avec rôle: {$roleName}");
            } else {
                $this->command->error("   ❌ Rôle '{$roleName}' non trouvé pour {$email}");
            }
        }

        $this->command->info('');
        $this->command->info('🎉 Utilisateurs bancaires traités avec succès !');
        $this->command->info('📊 RÉSUMÉ:');
        $this->command->info("   • Utilisateurs créés: {$createdCount}");
        $this->command->info("   • Utilisateurs mis à jour: {$updatedCount}");
        $this->command->info('   • Total: '.($createdCount + $updatedCount));
        $this->command->info('');

        // Afficher un tableau des identifiants
        $this->command->info('🔑 IDENTIFIANTS DE TEST:');
        $this->command->info('┌──────────────────────────────┬──────────────────────┐');
        $this->command->info('│ Rôle                        │ Email               │');
        $this->command->info('├──────────────────────────────┼──────────────────────┤');

        foreach ($users as $user) {
            $roleName = str_pad($user['role'], 28, ' ');
            $email = str_pad($user['email'], 20, ' ');
            $this->command->info("│ {$roleName} │ {$email} │");
        }

        $this->command->info('└──────────────────────────────┴──────────────────────┘');
        $this->command->info('');
        $this->command->info('🔐 Mot de passe pour tous: NomDuRôle123!');
        $this->command->info('   Exemple: Admin123!, IntAdmin123!, etc.');
    }
}
