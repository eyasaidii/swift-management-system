<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $roles = ['SUPER_ADMIN','ADMIN','MANAGER','ANALYST','AUDITOR','OPERATOR','USER'];
    foreach ($roles as $r) {
        Role::firstOrCreate(['name' => $r]);
    }
});

test('admin dashboard view renders for SUPER_ADMIN', function () {
    $user = User::factory()->create();
    $user->assignRole('SUPER_ADMIN');

    $response = $this->actingAs($user)->get('/admin/dashboard');
    $response->assertStatus(200);
});

test('manager dashboard view renders for MANAGER', function () {
    $user = User::factory()->create();
    $user->assignRole('MANAGER');

    $response = $this->actingAs($user)->get('/manager/dashboard');
    $response->assertStatus(200);
});

test('analyst dashboard view renders for ANALYST', function () {
    $user = User::factory()->create();
    $user->assignRole('ANALYST');

    $response = $this->actingAs($user)->get('/analyst/dashboard');
    $response->assertStatus(200);
});

test('auditor dashboard view renders for AUDITOR', function () {
    $user = User::factory()->create();
    $user->assignRole('AUDITOR');

    $response = $this->actingAs($user)->get('/auditor/dashboard');
    $response->assertStatus(200);
});

test('operator dashboard view renders for OPERATOR', function () {
    $user = User::factory()->create();
    $user->assignRole('OPERATOR');

    $response = $this->actingAs($user)->get('/operator/dashboard');
    $response->assertStatus(200);
});

test('user dashboard view renders for USER', function () {
    $user = User::factory()->create();
    $user->assignRole('USER');

    $response = $this->actingAs($user)->get('/user/dashboard');
    $response->assertStatus(200);
});
