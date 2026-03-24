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
            'admin',
            'backoffice',
            'monetique',
            'international-admin',
            'international-user'
        ];

        foreach ($roles as $r) {
            $role = Role::where('name', $r)->first();
            if ($role) {
                $role->givePermissionTo($perm);
            }
        }
    }
}
