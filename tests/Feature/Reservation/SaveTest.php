<?php

use App\Models\Item;
use App\Models\Reservation;
use App\Models\User;
use App\Services\ReservationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

test('creates a reservation', function () {
    $owner = User::factory()->create();
    $client = User::factory()->create();
    $item = Item::factory()->ownedBy($owner)->create(['quantity' => 5, 'price_per_day' => 1.50]);

    $service = new ReservationService();
    $reservation = $service->create(
        $client,
        [$item->id => 2],
        Carbon::today()->addDays(1),
        Carbon::today()->addDays(3)
    );

    //2 items x 1.50 x 3 days = 9.00
    expect($reservation->fresh()->total_price)->toBe('9.00')
        ->and(Reservation::query()->count())->toBe(1);
});

test('calculates the total and copies the price of each item', function () {
    $owner = User::factory()->create();
    $client = User::factory()->create();
    $a = Item::factory()->ownedBy($owner)->create(['quantity' => 5, 'price_per_day' => 1.50]);
    $b = Item::factory()->ownedBy($owner)->create(['quantity' => 5, 'price_per_day' => 3.00]);

    //2 x 1.50 + 1 x 3.00 = 6.00 per day, during 3 days = 18.00
    $reservation = (new ReservationService())->create(
        $client,
        [$a->id => 2, $b->id => 1],
        Carbon::today()->addDays(1),
        Carbon::today()->addDays(3)
    )->fresh();

    $pivotA = $reservation->items->firstWhere('id', $a->id)->pivot;
    $pivotB = $reservation->items->firstWhere('id', $b->id)->pivot;

    expect($reservation->total_price)->toBe('18.00')
        ->and($reservation->user_id)->toBe($client->id)
        ->and(DB::table('reservations')->value('status'))->toBe('confirmed')
        ->and((int) $pivotA->quantity)->toBe(2)
        ->and((float) $pivotA->price_per_day)->toBe(1.5)
        ->and((int) $pivotB->quantity)->toBe(1)
        ->and((float) $pivotB->price_per_day)->toBe(3.0);
});

test('user, status and total price cannot be mass assigned', function () {
    $reservation = new Reservation();

    expect($reservation->isFillable('user_id'))->toBeFalse()
        ->and($reservation->isFillable('status'))->toBeFalse()
        ->and($reservation->isFillable('total_price'))->toBeFalse();
});

test('nothing is saved if it fails halfway', function () {
    $client = User::factory()->create();
    $item = Item::factory()->create();

    //runs right after the reservation INSERT, before the items are linked
    Reservation::created(fn () => throw new RuntimeException('failure halfway'));

    expect(fn() => (new ReservationService())->create(
        $client,
        [$item->id => 1],
        Carbon::today()->addDays(1),
        Carbon::today()->addDays(3)
    ))->toThrow(RuntimeException::class)
        ->and(Reservation::query()->count())->toBe(0)
        ->and(DB::table('item_reservation')->count())->toBe(0);

});
