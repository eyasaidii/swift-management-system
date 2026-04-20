<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class RenameRoleSlugs extends Migration
{
    /**
     * Run the migrations.
     *
     * This will rename existing roles or, if a target role already exists,
     * reassign pivot entries from the old role to the existing one and delete the old role.
     */
    public function up()
    {
        $map = [
            'admin' => 'super-admin',
            'international-admin' => 'swift-manager',
            'international-user' => 'swift-operator',
        ];

        foreach ($map as $old => $new) {
            $oldRole = DB::table('roles')->where('name', $old)->first();
            $newRole = DB::table('roles')->where('name', $new)->first();

            if (! $oldRole) {
                continue;
            }

            if ($newRole) {
                // Move any model-role links to the existing new role, then remove the old role
                DB::table('model_has_roles')->where('role_id', $oldRole->id)->update(['role_id' => $newRole->id]);
                DB::table('roles')->where('id', $oldRole->id)->delete();
            } else {
                // Safe rename
                DB::table('roles')->where('id', $oldRole->id)->update(['name' => $new]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        $map = [
            'super-admin' => 'admin',
            'swift-manager' => 'international-admin',
            'swift-operator' => 'international-user',
        ];

        foreach ($map as $old => $new) {
            $oldRole = DB::table('roles')->where('name', $old)->first();
            $newRole = DB::table('roles')->where('name', $new)->first();

            if (! $oldRole) {
                continue;
            }

            if ($newRole) {
                DB::table('model_has_roles')->where('role_id', $oldRole->id)->update(['role_id' => $newRole->id]);
                DB::table('roles')->where('id', $oldRole->id)->delete();
            } else {
                DB::table('roles')->where('id', $oldRole->id)->update(['name' => $new]);
            }
        }
    }
}
