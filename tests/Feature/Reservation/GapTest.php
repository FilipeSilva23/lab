<?php

use App\Models\Item;
use App\Models\Reservation;
use App\Models\User;
use App\Services\ReservationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

//every test starts with an item with stock 1 and a reservation already made from today+10 to today+12
beforeEach(function () {
    config(['rentify.max_days' => 5, 'rentify.gap_days' => 1]);

    $this->client = User::factory()->create();
    $this->item = Item::factory()->create(); //stock 1

    //books the item from today+$from to today+$to, call it like this: ($this->book)(14, 16)
    $this->book = fn (int $from, int $to) => (new ReservationService())->create(
        $this->client,
        [$this->item->id => 1],
        Carbon::today()->addDays($from),
        Carbon::today()->addDays($to)
    );

    //the reservation that already exists, made by another user
    $this->existing = (new ReservationService())->create(
        User::factory()->create(),
        [$this->item->id => 1],
        Carbon::today()->addDays(10),
        Carbon::today()->addDays(12)
    );
});

test('accepts starting 2 days after the existing reservation', function () {
    ($this->book)(14, 16);

    expect(Reservation::query()->count())->toBe(2);
});

test('rejects starting inside the gap after the existing reservation', function () {
    expect(fn () => ($this->book)(13, 15))->toThrow(ValidationException::class);
});

test('accepts ending 2 days before the existing reservation', function () {
    ($this->book)(6, 8);

    expect(Reservation::query()->count())->toBe(2);
});

test('rejects ending inside the gap before the existing reservation', function () {
    expect(fn () => ($this->book)(7, 9))->toThrow(ValidationException::class);
});

test('rejects overlapping dates', function () {
    expect(fn () => ($this->book)(11, 11))->toThrow(ValidationException::class);
});

test('a cancelled reservation does not block the dates', function () {
    //status is not fillable, so forceFill is needed (only for tests)
    $this->existing->forceFill(['status' => 'cancelled'])->save();

    ($this->book)(10, 12);

    expect(Reservation::query()->count())->toBe(2);
});
