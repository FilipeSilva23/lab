<?php

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lists category names in the current locale', function () {
    Category::query()->create(['name_pt' => 'Livros', 'name_en' => 'Books']);
    $user = User::factory()->create();

    // 'locale' is the session key SetLocale reads (assumed, confirm in the middleware)
    $this->actingAs($user)->withSession(['locale' => 'pt'])
        ->get(route('items.create'))->assertSee('Livros')->assertDontSee('Books');

    $this->actingAs($user)->withSession(['locale' => 'en'])
        ->get(route('items.create'))->assertSee('Books')->assertDontSee('Livros');
});

it('lists the categories in alphabetical order, ignoring accents', function () {
    Category::query()->create(['name_pt' => 'Eletrónica', 'name_en' => 'Electronics']);
    Category::query()->create(['name_pt' => 'Álbuns', 'name_en' => 'Albums']);
    Category::query()->create(['name_pt' => 'Desporto', 'name_en' => 'Sports']);

    $this->actingAs(User::factory()->create())
        ->get(route('items.create'))
        ->assertSeeInOrder(['Álbuns', 'Desporto', 'Eletrónica']);
});
