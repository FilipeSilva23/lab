<?php

use App\Enums\UserRole;
use App\Models\DeliverLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeDeliverLocation(string $pt = 'Bar', string $en = 'Café'): DeliverLocation
{
    return DeliverLocation::query()->create(['name_pt' => $pt, 'name_en' => $en]);
}
function createAdmin(): User
{
    return User::factory()->create(['role' => UserRole::Admin]);
}

test('lets an admin create, edit and delete a location', function () {
    $admin = createAdmin();

    $this->actingAs($admin)
        ->post(route('deliver-locations.store'), ['name_pt' => 'Bar', 'name_en' => 'Café'])
        ->assertSessionHasNoErrors();

    $location = DeliverLocation::query()->firstOrFail();

    $this->actingAs($admin)
        ->patch(route('deliver-locations.update', $location), ['name_pt' => 'Bar', 'name_en' => 'Pub'])
        ->assertSessionHasNoErrors();

    expect($location->fresh()->name_en)->toBe('Pub');

    $this->actingAs($admin)->delete(route('deliver-locations.destroy', $location));

    expect(DeliverLocation::query()->count())->toBe(0);
});

test('rejects invalid names and saves nothing', function (array $override, string $field) {
    makeDeliverLocation('Bar', 'Café');

    $this->actingAs(createAdmin())
        ->post(route('deliver-locations.store'), array_merge(['name_pt' => 'Cantina', 'name_en' => 'Canteen'], $override))
        ->assertSessionHasErrors($field);

    expect(DeliverLocation::query()->count())->toBe(1);
})->with([
    'empty name_pt' => [['name_pt' => ''], 'name_pt'],
    'empty name_en' => [['name_en' => ''], 'name_en'],
    'name_pt already taken' => [['name_pt' => 'Bar'], 'name_pt'],
    'name_en already taken' => [['name_en' => 'Café'], 'name_en'],
]);

test('does not delete a location that a user has selected', function () {
    $location = makeDeliverLocation();
    User::factory()->create(['deliver_location_id' => $location->id]);

    $this->actingAs(createAdmin())
        ->delete(route('deliver-locations.destroy', $location))
        ->assertSessionHasErrors('deliver_location');

    expect(DeliverLocation::query()->find($location->id))->not->toBeNull();
});

//destroy has no FormRequest, so the delete line also guards the Gate::authorize in the controller
test('forbids non-admins from creating, editing and deleting', function (UserRole $role) {
    $location = makeDeliverLocation();
    $user = User::factory()->create(['role' => $role]);
    $payload = ['name_pt' => 'Cantina', 'name_en' => 'Canteen'];

    $this->actingAs($user)->post(route('deliver-locations.store'), $payload)->assertForbidden();
    $this->actingAs($user)->patch(route('deliver-locations.update', $location), $payload)->assertForbidden();
    $this->actingAs($user)->delete(route('deliver-locations.destroy', $location))->assertForbidden();

    expect(DeliverLocation::query()->count())->toBe(1)
        ->and($location->fresh()->name_pt)->toBe('Bar');
})->with([
    'student' => [UserRole::Student],
    'service' => [UserRole::Service],
]);

test('shows the locations on the admin dashboard', function () {
    makeDeliverLocation('Biblioteca', 'Library');

    $this->actingAs(createAdmin())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Biblioteca');
});
