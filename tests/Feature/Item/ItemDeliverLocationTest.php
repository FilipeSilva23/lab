<?php

use App\Models\DeliverLocation;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('follows the owner current delivery location', function () {
    $atrium = DeliverLocation::query()->create(['name_pt' => 'Átrio', 'name_en' => 'Atrium']);
    $library = DeliverLocation::query()->create(['name_pt' => 'Biblioteca', 'name_en' => 'Library']);

    $owner = User::factory()->create();
    $owner->deliverLocation()->associate($atrium)->save();
    $item = Item::factory()->ownedBy($owner)->create();

    expect($item->deliverLocation->is($atrium))->toBeTrue();

    // the owner changes location and the item follows without being touched
    $owner->deliverLocation()->associate($library)->save();
    expect($item->fresh()->deliverLocation->is($library))->toBeTrue();
});

it('has no delivery location when the owner has none', function () {
    $item = Item::factory()->ownedBy(User::factory()->create())->create();

    expect($item->deliverLocation)->toBeNull();
});

it('shows the delivery location of the owner on the item page', function () {
    $library = DeliverLocation::query()->create(['name_pt' => 'Biblioteca', 'name_en' => 'Library']);

    $owner = User::factory()->create();
    $owner->deliverLocation()->associate($library)->save();
    $item = Item::factory()->ownedBy($owner)->create();

    $this->actingAs($owner)
        ->withSession(['locale' => 'pt'])
        ->get(route('items.show', $item))
        ->assertOk()
        ->assertSee('Biblioteca');
});
