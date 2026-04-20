<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // ensure roles exist in test DB
    $roles = ['SUPER_ADMIN', 'ADMIN', 'MANAGER', 'ANALYST', 'AUDITOR', 'OPERATOR', 'USER'];
    foreach ($roles as $r) {
        Role::firstOrCreate(['name' => $r]);
    }
});

it('each seeded role can login and access its dashboard', function () {
    $map = [
        'SUPER_ADMIN' => '/admin/dashboard',
        'ADMIN' => '/admin/dashboard',
        'MANAGER' => '/manager/dashboard',
        'ANALYST' => '/analyst/dashboard',
        'AUDITOR' => '/auditor/dashboard',
        'OPERATOR' => '/operator/dashboard',
        'USER' => '/user/dashboard',
    ];

    foreach ($map as $role => $path) {
        $email = strtolower($role).'@example.test';

        // ensure user exists (created by seeder, but keep idempotent)
        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => $role.' Test', 'password' => bcrypt('password'), 'email_verified_at' => now()]
        );
        if (! $user->hasRole($role)) {
            $user->assignRole($role);
        }

        // submit login form
        $this->post('/login', ['email' => $email, 'password' => 'password'])
            ->assertRedirect($path);

        // access dashboard
        $this->get($path)->assertStatus(200);

        // logout to reset session for next iteration
        $this->post('/logout');
    }
});
