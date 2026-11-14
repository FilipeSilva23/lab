<?php

use App\Models\Item;
use App\Models\User;
use App\Services\ReservationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

test('rejects when there is no stock left', function () {
    $owner = User::factory()->create();
    $client = User::factory()->create();
    $item = Item::factory()->ownedBy($owner)->create();

    $service = new ReservationService();
    $start = Carbon::today()->addDays(1);
    $end = Carbon::today()->addDays(3);

    //takes the only unit
    $service->create($client, [$item->id => 1], $start, $end);

    //same dates, no unit left
    expect(fn () => $service->create($client, [$item->id => 1], $start, $end))
        ->toThrow(ValidationException::class);
});
