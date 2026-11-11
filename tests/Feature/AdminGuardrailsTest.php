<?php

declare(strict_types=1);

use App\Enum\RoleEnum;
use App\Models\Devise;
use App\Models\User;

function guardAdmin(): User
{
    return User::factory()->create(['role' => RoleEnum::ADMIN]);
}

it('does not expose a delete route for currencies', function () {
    $devise = Devise::query()->create(['type' => 'EUR', 'taux' => 656]);

    $this->actingAs(guardAdmin())
        ->deleteJson("/admin/devise/{$devise->id}")
        ->assertStatus(405);

    expect(Devise::query()->whereKey($devise->id)->exists())->toBeTrue();
});

it('rejects a null role when updating a user', function () {
    $admin = guardAdmin();
    $target = User::factory()->create(['role' => RoleEnum::SECRTETAIRE]);

    $this->actingAs($admin)
        ->patchJson("/admin/user/{$target->id}", [
            'name' => $target->name,
            'email' => $target->email,
            'role' => null,
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('role');

    expect($target->fresh()->role)->toBe(RoleEnum::SECRTETAIRE);
});

it('forbids an administrator from deleting their own account', function () {
    $admin = guardAdmin();
    guardAdmin(); // un second admin : seule la règle « soi-même » doit bloquer

    $this->actingAs($admin)
        ->deleteJson("/admin/user/{$admin->id}")
        ->assertForbidden();

    expect(User::query()->whereKey($admin->id)->exists())->toBeTrue();
});

it('forbids demoting the last administrator', function () {
    $admin = guardAdmin();

    $this->actingAs($admin)
        ->patchJson("/admin/user/{$admin->id}", [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => RoleEnum::SECRTETAIRE->value,
        ])
        ->assertForbidden();

    expect($admin->fresh()->role)->toBe(RoleEnum::ADMIN);
});

it('still lets an administrator demote or delete another administrator when not the last', function () {
    $admin = guardAdmin();
    $second = guardAdmin();
    $third = guardAdmin();

    $this->actingAs($admin)
        ->patchJson("/admin/user/{$second->id}", [
            'name' => $second->name,
            'email' => $second->email,
            'role' => RoleEnum::SECRTETAIRE->value,
        ])
        ->assertRedirect();

    expect($second->fresh()->role)->toBe(RoleEnum::SECRTETAIRE);

    $this->actingAs($admin)
        ->deleteJson("/admin/user/{$third->id}")
        ->assertOk();

    expect(User::query()->whereKey($third->id)->exists())->toBeFalse();
});
