<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // ensure roles exist in test DB
    $roles = ['super-admin', 'swift-manager', 'swift-operator', 'backoffice', 'compliance-officer'];
    foreach ($roles as $r) {
        Role::firstOrCreate(['name' => $r, 'guard_name' => 'web']);
    }
});

it('super-admin can login and access admin dashboard', function () {
    $user = User::factory()->create(['password' => bcrypt('password')]);
    $user->assignRole('super-admin');

    $this->post('/login', ['email' => $user->email, 'password' => 'password'])
        ->assertRedirect('/admin/dashboard');

    $this->get('/admin/dashboard')->assertStatus(200);

    $this->post('/logout');
});

it('swift-manager can login and access international-admin dashboard', function () {
    $user = User::factory()->create(['password' => bcrypt('password')]);
    $user->assignRole('swift-manager');

    $this->post('/login', ['email' => $user->email, 'password' => 'password'])
        ->assertRedirect('/international-admin/dashboard');

    $this->get('/international-admin/dashboard')->assertStatus(200);

    $this->post('/logout');
});
