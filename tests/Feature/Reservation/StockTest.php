<?php

use App\Models\Item;
use App\Models\Reservation;
use App\Models\User;
use App\Services\ReservationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

//every test starts with an item with stock 3 and 2 units already reserved from today+10 to today+12
beforeEach(function () {
    config(['rentify.max_days' => 5, 'rentify.gap_days' => 1]);

    $this->service = new ReservationService();
    $this->client = User::factory()->create();
    $this->item = Item::factory()->create(['quantity' => 3]);

    $this->service->create(
        User::factory()->create(),
        [$this->item->id => 2],
        Carbon::today()->addDays(10),
        Carbon::today()->addDays(12)
    );
});

test('accepts 1 unit when 1 is still free', function () {
    $this->service->create(
        $this->client,
        [$this->item->id => 1],
        Carbon::today()->addDays(10),
        Carbon::today()->addDays(12)
    );

    expect(Reservation::query()->count())->toBe(2);
});

test('rejects 2 units when only 1 is free', function () {
    expect(fn() => $this->service->create(
        $this->client,
        [$this->item->id => 2],
        Carbon::today()->addDays(10),
        Carbon::today()->addDays(12)
    ))->toThrow(ValidationException::class)
    ->and(Reservation::query()->count())->toBe(1);
});
