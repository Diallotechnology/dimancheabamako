<?php

declare(strict_types=1);

use App\Enum\RoleEnum;
use App\Models\Category;
use App\Models\User;

it('lists categories with the search filter for a secretary', function () {
    Category::create(['nom' => 'Bogolan', 'description' => 'Tissus']);
    Category::create(['nom' => 'Bijoux', 'description' => 'Parures']);

    $this->actingAs(User::factory()->create(['role' => RoleEnum::SECRTETAIRE]))
        ->get('/admin/category?search=Bogo')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Category/Index')
            ->has('rows.data', 1)
            ->where('rows.data.0.nom', 'Bogolan')
            ->missing('filter'));
});

it('deletes a category and answers with the JSON success payload read by ConfirmDelete', function () {
    $category = Category::create(['nom' => 'Cuir', 'description' => 'Sacs']);

    $this->actingAs(User::factory()->create(['role' => RoleEnum::SECRTETAIRE]))
        ->deleteJson("/admin/category/{$category->id}")
        ->assertOk()
        ->assertJson(['success' => true]);

    expect(Category::find($category->id))->toBeNull();
});
