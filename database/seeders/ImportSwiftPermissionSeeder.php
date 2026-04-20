<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ImportSwiftPermissionSeeder extends Seeder
{
    public function run()
    {
        // Create permission
        $perm = Permission::firstOrCreate(['name' => 'import-swift']);

        // Roles to assign by default
        $roles = [
            'super-admin',
            'backoffice',
            'monetique',
            'swift-manager',
            'swift-operator',
        ];

        foreach ($roles as $r) {
            $role = Role::where('name', $r)->first();
            if ($role) {
                $role->givePermissionTo($perm);
            }
        }
    }
}
