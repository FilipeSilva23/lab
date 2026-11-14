<?php

use App\Models\Category;
use App\Models\Item;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

test('shows the name in the current locale and falls back to Portuguese', function () {
    $category = Category::query()->create(['name_pt' => 'Livros', 'name_en' => 'Books']);

    app()->setLocale('en');
    expect($category->name)->toBe('Books');

    app()->setLocale('pt');
    expect($category->name)->toBe('Livros');

    app()->setLocale('es');
    expect($category->name)->toBe('Livros');
});

test('requires both translations in the database', function () {
    expect(fn() => Category::query()->create(['name_pt' => 'Livros']))->toThrow(QueryException::class)
        ->and(fn() => Category::query()->create(['name_en' => 'Books']))->toThrow(QueryException::class);
});

test('does not accept duplicate names in either language', function () {
    Category::query()->create(['name_pt' => 'Livros', 'name_en' => 'Books']);

    expect(fn() => Category::query()->create(['name_pt' => 'Livros', 'name_en' => 'Other']))->toThrow(QueryException::class)
        ->and(fn() => Category::query()->create(['name_pt' => 'Outra', 'name_en' => 'Books']))->toThrow(QueryException::class);
});

test('deletes only the category and its links, not the items', function () {
    $category = Category::query()->create(['name_pt' => 'Livros', 'name_en' => 'Books']);
    $item = Item::factory()->create();
    $item->categories()->attach($category);

    $category->delete();

    expect(DB::table('category_item')->count())->toBe(0)
        ->and(Item::query()->find($item->id))->not->toBeNull();
});
