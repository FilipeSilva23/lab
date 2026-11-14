<?php

use App\Models\Item;
use App\Models\Reservation;
use App\Models\User;
use App\Services\ReservationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function () {
    //the tests must not depend on what is written in config/rentify.php
    config(['rentify.max_days' => 5, 'rentify.gap_days' => 1]);
    $this->client = User::factory()->create();
    $this->item = Item::factory()->create(); //stock 1

    //books the item from today+$from to today+$to
    //it is a function stored in a property, so it is called like this: ($this->book)(1, 3)
    $this->book = fn (int $from, int $to) => (new ReservationService())->create(
        $this->client,
        [$this->item->id => 1],
        Carbon::today()->addDays($from),
        Carbon::today()->addDays($to)
    );
});

//returns the errors of the ValidationException thrown by the function (empty list if none is thrown)
function errorsOf(callable $callback): array
{
    try {
        $callback();
    } catch (ValidationException $e) {
        return $e->errors();
    }

    return [];
}

test('rejects more than 5 days', function () {
    expect(fn () => ($this->book)(1, 6))->toThrow(ValidationException::class);
});

test('accepts 1 day', function () {
    ($this->book)(1, 1);

    expect(Reservation::query()->count())->toBe(1);
});

test('accepts 5 days', function () {
    ($this->book)(1, 5);

    expect(Reservation::query()->count())->toBe(1);
});

test('rejects an end before the start', function () {
    expect(errorsOf(fn () => ($this->book)(3, 2)))->toHaveKey('dates');
});

test('rejects a start in the past', function () {
    expect(errorsOf(fn () => ($this->book)(-1, 1)))->toHaveKey('dates');
});

test('max days comes from the config', function () {
    config(['rentify.max_days' => 3]);

    expect(errorsOf(fn () => ($this->book)(1, 4)))->toHaveKey('dates'); //4 days
    ($this->book)(1, 3); //3 days

    expect(Reservation::query()->count())->toBe(1);
});
