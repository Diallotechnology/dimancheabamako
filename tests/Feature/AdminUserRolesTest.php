<?php

declare(strict_types=1);

use App\Enum\RoleEnum;
use App\Models\User;

it('offers only staff roles when creating a user', function () {
    $this->actingAs(User::factory()->create(['role' => RoleEnum::ADMIN]))
        ->get('/admin/user')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/User/Index')
            ->where('roles', [
                ['label' => 'Administrateur (accès à tout)', 'value' => 'Administrateur'],
                ['label' => 'Secrétaire (accès limité)', 'value' => 'Secretaire'],
            ]));
});

it('offers every role, values matching the validated enum, when editing a user', function () {
    $admin = User::factory()->create(['role' => RoleEnum::ADMIN]);

    $this->actingAs($admin)
        ->get("/admin/user/{$admin->id}/edit")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/User/Update')
            ->has('roles', 3)
            ->where('roles.0.value', 'Administrateur')
            ->where('roles.1.value', 'Secretaire')
            ->where('roles.2.value', 'Client'));
});
