<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $roles = ['super-admin', 'swift-manager', 'swift-operator', 'backoffice', 'compliance-officer'];
    foreach ($roles as $r) {
        Role::firstOrCreate(['name' => $r, 'guard_name' => 'web']);
    }
});

test('admin dashboard view renders for super-admin', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $response = $this->actingAs($user)->get('/admin/dashboard');
    $response->assertStatus(200);
});

test('international-admin dashboard view renders for swift-manager', function () {
    $user = User::factory()->create();
    $user->assignRole('swift-manager');

    $response = $this->actingAs($user)->get('/international-admin/dashboard');
    $response->assertStatus(200);
});

test('international-user dashboard view renders for swift-operator', function () {
    $user = User::factory()->create();
    $user->assignRole('swift-operator');

    $response = $this->actingAs($user)->get('/international-user/dashboard');
    $response->assertStatus(200);
});
