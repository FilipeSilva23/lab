<?php

use App\Models\Item;
use App\Models\Reservation;
use App\Models\User;
use App\Services\ReservationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

test('rejects items from different owners', function () {
    $client = User::factory()->create();

    //without ownedBy, each item creates its own owner
    $itemA = Item::factory()->create();
    $itemB = Item::factory()->create();

    expect(fn() => (new ReservationService())->create(
        $client,
        [$itemA->id => 1, $itemB->id => 1],
        Carbon::today()->addDays(1),
        Carbon::today()->addDays(3)
    ))->toThrow(ValidationException::class)
    ->and(Reservation::query()->count())->toBe(0);

});

test('rejects an item that does not exist', function () {
    $client = User::factory()->create();

    expect(fn() => (new ReservationService())->create(
        $client,
        [999999 => 1],
        Carbon::today()->addDays(1),
        Carbon::today()->addDays(3)
    ))->toThrow(ValidationException::class)
    ->and(Reservation::query()->count())->toBe(0);

});

test('rejects an empty list of items', function () {
    $client = User::factory()->create();

    expect(fn() => (new ReservationService())->create(
        $client,
        [],
        Carbon::today()->addDays(1),
        Carbon::today()->addDays(3)
    ))->toThrow(ValidationException::class)
    ->and(Reservation::query()->count())->toBe(0);

});

test('rejects a quantity below 1', function () {
    $client = User::factory()->create();
    $item = Item::factory()->create();
    $service = new ReservationService();

    //0 and negative quantities
    expect(fn() => $service->create(
        $client,
        [$item->id => 0],
        Carbon::today()->addDays(1),
        Carbon::today()->addDays(3)
    ))->toThrow(ValidationException::class)
    ->and(fn() => $service->create(
        $client,
        [$item->id => -1],
        Carbon::today()->addDays(1),
        Carbon::today()->addDays(3)
    ))->toThrow(ValidationException::class)
    ->and(Reservation::query()->count())->toBe(0);

});
