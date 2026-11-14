<?php

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function admin(): User
{
    return User::factory()->create(['role' => UserRole::Admin]);
}

test('lets an admin create a category with both translations', function () {
    $this->actingAs(admin())
        ->post(route('categories.store'), ['name_pt' => 'Livros', 'name_en' => 'Books'])
        ->assertSessionHasNoErrors();

    expect(Category::query()->count())->toBe(1);
});

test('rejects a category without both translations', function (array $payload) {
    $this->actingAs(admin())
        ->post(route('categories.store'), $payload)
        ->assertSessionHasErrors();

    expect(Category::query()->count())->toBe(0);
})->with([
    'missing english' => [['name_pt' => 'Livros']],
    'missing portuguese' => [['name_en' => 'Books']],
    'empty english' => [['name_pt' => 'Livros', 'name_en' => '']],
    'name above 255 characters' => [['name_pt' => str_repeat('a', 256), 'name_en' => 'Books']],
]);

test('rejects duplicate names', function () {
    Category::query()->create(['name_pt' => 'Livros', 'name_en' => 'Books']);

    $this->actingAs(admin())
        ->post(route('categories.store'), ['name_pt' => 'Livros', 'name_en' => 'Other'])
        ->assertSessionHasErrors('name_pt');
    $this->actingAs(admin())
        ->post(route('categories.store'), ['name_pt' => 'Outro', 'name_en' => 'Books'])
        ->assertSessionHasErrors('name_en');

    expect(Category::query()->count())->toBe(1);
});

test('lets a category keep its own names on update', function () {
    $category = Category::query()->create(['name_pt' => 'Livros', 'name_en' => 'Books']);

    $this->actingAs(admin())
        ->put(route('categories.update', $category), ['name_pt' => 'Livros', 'name_en' => 'Novels'])
        ->assertSessionHasNoErrors();

    expect($category->fresh()->name_en)->toBe('Novels');
});

test('forbids non-admins from managing categories', function () {
    $student = User::factory()->create();

    $this->actingAs($student)
        ->post(route('categories.store'), ['name_pt' => 'Livros', 'name_en' => 'Books'])
        ->assertForbidden();
    $this->actingAs($student)->get(route('categories.create'))->assertForbidden();

    expect(Category::query()->count())->toBe(0);
});

test('redirects guests to login', function () {
    $this->get(route('categories.create'))->assertRedirect(route('login'));
});
