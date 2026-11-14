<?php

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Item;
use App\Models\User;
use App\Services\ReservationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;


uses(RefreshDatabase::class);

function itemCrudPayload(array $overrides = []): array
{
    return array_merge([
        'title' => 'Arduino',
        'description' => 'Board',
        'quantity' => 2,
        'price_per_day' => '1.50',
        'categories' => [Category::query()->firstOrCreate(['name_pt' => 'Eletrónica'], ['name_en' => 'Electronics'])->id],
    ], $overrides);
}

//each case: [fields to override, field that must show the error]
dataset('invalid items payloads', [
    'empty title' => [['title' => ''], 'title'],
    'quantity zero' => [['quantity' => 0], 'quantity'],
    'quantity text' => [['quantity' => 'abc'], 'quantity'],
    'negative price' => [['price_per_day' => -1], 'price_per_day'],
    'price above 9999.99' => [['price_per_day' => 10000], 'price_per_day'],
    'no categories' => [['categories' => []], 'categories'],
    'category that does not exist' => [['categories' => [9999]], 'categories.0'],
    'deposit with price above 0' => [['price_per_day' => 2, 'deposit_amount' => 10], 'deposit_amount'],
    'title above 255 characters' => [['title' => str_repeat('a', 256)], 'title'],
    'description above 512 characters' => [['description' => str_repeat('a', 513)], 'description'],
    'deposit above 9999.99' => [['price_per_day' => 0, 'deposit_amount' => 10000], 'deposit_amount'],
    'negative deposit' => [['price_per_day' => 0, 'deposit_amount' => -1], 'deposit_amount'],
    'more than 10 categories' => [['categories' => range(1, 11)], 'categories'],
    'quantity above 1000' => [['quantity' => 1001], 'quantity'],
    'price with 3 decimals' => [['price_per_day' => '1.999'], 'price_per_day'],
    'deposit with 3 decimals' => [['price_per_day' => 0, 'deposit_amount' => '1.999'], 'deposit_amount'],
]);

test('rejects invalid data and saves nothing', function (array $override, string $field) {
    $this->actingAs(User::factory()->create())
        ->post(route('items.store'), itemCrudPayload($override))
        ->assertSessionHasErrors($field);

    expect(Item::query()->count())->toBe(0);
})->with('invalid items payloads');

test('rejects invalid data on update and keeps the item unchanged', function (array $override, string $field) {
    $owner = User::factory()->create();
    $item = Item::factory()->create(['user_id' => $owner->id, 'title' => 'Original']);

    $this->actingAs($owner)
        ->put(route('items.update', $item), itemCrudPayload($override))
        ->assertSessionHasErrors($field);

    //nothing was written
    expect($item->fresh()->title)->toBe('Original');
})->with('invalid items payloads');

//////////////////////////////////////////////////////////////////////////////////////////////////////

//values exactly on the limit must be accepted
dataset('valid edge payloads', [
    'title with exactly 255 characters' => [['title' => str_repeat('a', 255)]],
    'description with exactly 512 characters' => [['description' => str_repeat('a', 512)]],
    'quantity one' => [['quantity' => 1]],
    'highest price' => [['price_per_day' => 9999.99]],
    'free item' => [['price_per_day' => 0]],
    'highest deposit on a free item' => [['price_per_day' => 0, 'deposit_amount' => 9999.99]],
    'no description' => [['description' => null]],
    'quantity 1000' => [['quantity' => 1000]],
    'price with 1 decimal' => [['price_per_day' => '1.5']],
    'price with 2 decimals' => [['price_per_day' => '1.99']],
]);

test('accepts valid values on the limit', function (array $override) {
    $this->actingAs(User::factory()->create())
        ->post(route('items.store'), itemCrudPayload($override))
        ->assertSessionHasNoErrors();

    expect(Item::query()->count())->toBe(1);
})->with('valid edge payloads');

//////////////////////////////////////////////////////////////////////////////////////////////////////

test('creates the item for the logged-in user, ignores a forged user_id and links the categories', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    //user_id is sent on purpose, like someone editing the form in DevTools
    $this->actingAs($user)
        ->post(route('items.store'), itemCrudPayload(['user_id' => $other->id]))
        ->assertSessionHasNoErrors();

    $item = Item::query()->first();

    expect($item->user_id)->toEqual($user->id)
        ->and($item->categories)->toHaveCount(1);
});

//"0" and "0.00" must both count as zero: this is the text-vs-number trap
test('accepts a deposit when the price is zero', function (string|int $price) {
    $this->actingAs(User::factory()->create())
        ->post(route('items.store'), itemCrudPayload(['price_per_day' => $price, 'deposit_amount' => 20]))
        ->assertSessionHasNoErrors();

    expect(Item::query()->first()->deposit_amount)->toBe('20.00');
})->with([0, '0.00']);

test('lets the owner edit without changing the owner', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $item = Item::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($owner)
        ->put(route('items.update', $item), itemCrudPayload(['title' => 'New title', 'user_id' => $other->id]))
        ->assertSessionHasNoErrors();

    expect($item->fresh()->title)->toBe('New title')
        ->and($item->fresh()->user_id)->toEqual($owner->id);
});

test('forbids a user who is not the owner from editing or deleting', function () {
    $item = Item::factory()->create(['user_id' => User::factory()->create()->id]);
    $intruder = User::factory()->create();

    $this->actingAs($intruder)->get(route('items.edit', $item))->assertForbidden();
    $this->actingAs($intruder)->put(route('items.update', $item), itemCrudPayload())->assertForbidden();
    $this->actingAs($intruder)->delete(route('items.destroy', $item))->assertForbidden();

    expect(Item::query()->find($item->id))->not->toBeNull();
});

test('lets an admin edit and delete any item', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $item = Item::factory()->create(['user_id' => User::factory()->create()->id]);

    $this->actingAs($admin)
        ->put(route('items.update', $item), itemCrudPayload(['title' => 'Edited by admin']))
        ->assertSessionHasNoErrors();

    expect($item->fresh()->title)->toBe('Edited by admin');

    $this->actingAs($admin)->delete(route('items.destroy', $item));

    expect(Item::query()->find($item->id))->toBeNull();
});

test('deletes an item that has categories', function () {
    $owner = User::factory()->create();
    $item = Item::factory()->create(['user_id' => $owner->id]);
    $item->categories()->sync([Category::query()->firstOrCreate(['name_pt' => 'Eletrónica'], ['name_en' => 'Electronics'])->id]);

    $this->actingAs($owner)
        ->delete(route('items.destroy', $item))
        ->assertRedirect(route('items.index'));

    expect(Item::query()->find($item->id))->toBeNull();
});

test('update replaces the categories of the item', function () {
    $owner = User::factory()->create();
    $item = Item::factory()->create(['user_id' => $owner->id]);
    $old = Category::query()->create(['name_pt' => 'Antiga', 'name_en' => 'Old']);
    $new = Category::query()->create(['name_pt' => 'Nova', 'name_en' => 'New']);
    $item->categories()->sync([$old->id]);

    $this->actingAs($owner)
        ->put(route('items.update', $item), itemCrudPayload(['categories' => [$new->id]]))
        ->assertSessionHasNoErrors();

    expect($item->fresh()->categories->pluck('id')->all())->toEqual([$new->id]);
});

//the limit itself must be accepted (needs 10 real categories, so it can't be a dataset row)
test('accepts exactly 10 categories', function () {
    $ids = [];
    foreach (range(1, 10) as $i) {
        $ids[] = Category::query()->create(['name_pt' => "Cat $i", 'name_en' => "Cat $i"])->id;
    }

    $this->actingAs(User::factory()->create())->post(
                                                    route('items.store'),
                                                    itemCrudPayload(['categories' => $ids])
                                                )->assertSessionHasNoErrors();

    expect(Item::query()->first()->categories)->toHaveCount(10);
});

test('blocks deleting an item that has reservations and keeps the item and its files', function () {
    Storage::fake('public');
    config(['rentify.max_days' => 5, 'rentify.gap_days' => 1]);

    $owner = User::factory()->create();
    $item = Item::factory()->create(['user_id' => $owner->id]);

    Storage::disk('public')->put('items/a.jpg', 'x');
    $item->images()->forceCreate(['path' => 'items/a.jpg']);

    new ReservationService()->create(
        User::factory()->create(),
        [$item->id => 1],
        Carbon::today()->addDays(10),
        Carbon::today()->addDays(11)
    );

    $this->actingAs($owner)
        ->delete(route('items.destroy', $item))
        ->assertRedirect(route('items.show', $item))
        ->assertSessionHas('error', __('items.has_reservations'));

    expect(Item::query()->find($item->id))->not->toBeNull();
    Storage::disk('public')->assertExists('items/a.jpg');
    expect($item->images()->count())->toBe(1);
});

test('deleting an item without reservations removes the row and all its image files', function () {
    Storage::fake('public');

    $owner = User::factory()->create();
    $item = Item::factory()->create(['user_id' => $owner->id]);

    foreach (['items/a.jpg', 'items/b.jpg'] as $path) {
        Storage::disk('public')->put($path, 'x');
        $item->images()->forceCreate(['path' => $path]);
    }

    $this->actingAs($owner)
        ->delete(route('items.destroy', $item))
        ->assertRedirect(route('items.index'));

    expect(Item::query()->find($item->id))->toBeNull();
    Storage::disk('public')->assertMissing('items/a.jpg');
    Storage::disk('public')->assertMissing('items/b.jpg');
});
