<?php

declare(strict_types=1);

use App\Enum\RoleEnum;
use App\Models\User;

function userWithRole(RoleEnum $role): User
{
    return User::factory()->create(['role' => $role]);
}

it('redirects guests away from the admin area', function () {
    $this->get('/admin/product')->assertRedirect();
});

it('forbids customers from every admin page', function (string $uri) {
    $this->actingAs(userWithRole(RoleEnum::CUSTOMER))->get($uri)->assertForbidden();
})->with(['/admin/product', '/admin/order', '/admin/dashboard', '/admin/user', '/admin/devise']);

it('lets a secretary manage products, orders and the dashboard', function (string $uri) {
    $this->actingAs(userWithRole(RoleEnum::SECRTETAIRE))->get($uri)->assertOk();
})->with(['/admin/product', '/admin/order', '/admin/dashboard']);

it('forbids a secretary from admin-only pages', function (string $uri) {
    $this->actingAs(userWithRole(RoleEnum::SECRTETAIRE))->get($uri)->assertForbidden();
})->with(['/admin/user', '/admin/devise', '/admin/zone', '/admin/paylink']);

it('lets an administrator reach admin-only and secretary pages', function (string $uri) {
    $this->actingAs(userWithRole(RoleEnum::ADMIN))->get($uri)->assertOk();
})->with(['/admin/user', '/admin/devise', '/admin/product', '/admin/dashboard']);

it('forbids a secretary from deleting a user', function () {
    $target = userWithRole(RoleEnum::CUSTOMER);

    $this->actingAs(userWithRole(RoleEnum::SECRTETAIRE))
        ->delete("/admin/user/{$target->id}")
        ->assertForbidden();

    expect(User::find($target->id))->not->toBeNull();
});
