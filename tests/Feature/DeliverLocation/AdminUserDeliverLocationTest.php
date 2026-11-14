<?php

use App\Enums\UserRole;
use App\Models\DeliverLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lets an admin change the delivery location of a student and of a service', function (UserRole $role) {
    $atrium = DeliverLocation::query()->create(['name_pt' => 'Átrio', 'name_en' => 'Atrium']);
    $library = DeliverLocation::query()->create(['name_pt' => 'Biblioteca', 'name_en' => 'Library']);
    $user = User::factory()->create(['role' => $role, 'deliver_location_id' => $atrium->id]);

    $this->actingAs(User::factory()->admin()->create())
        ->patch(route('users.deliver-location.update', $user), ['deliver_location_id' => $library->id])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($user->fresh()->deliver_location_id)->toEqual($library->id);
})->with([
    'student' => [UserRole::Student],
    'service' => [UserRole::Service],
]);

it('lets an admin set a location on a user that had none', function () {
    $library = DeliverLocation::query()->create(['name_pt' => 'Biblioteca', 'name_en' => 'Library']);
    $user = User::factory()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->patch(route('users.deliver-location.update', $user), ['deliver_location_id' => $library->id])
        ->assertSessionHas('success');

    expect($user->fresh()->deliver_location_id)->toEqual($library->id);
});

it('warns and changes nothing when the user already has that location', function () {
    $library = DeliverLocation::query()->create(['name_pt' => 'Biblioteca', 'name_en' => 'Library']);
    $user = User::factory()->create(['deliver_location_id' => $library->id]);

    $this->actingAs(User::factory()->admin()->create())
        ->patch(route('users.deliver-location.update', $user), ['deliver_location_id' => $library->id])
        ->assertSessionHas('warning')
        ->assertSessionMissing('success');

    expect($user->fresh()->deliver_location_id)->toEqual($library->id);
});

it('forbids non-admins from changing the location of another user', function (UserRole $role) {
    $atrium = DeliverLocation::query()->create(['name_pt' => 'Átrio', 'name_en' => 'Atrium']);
    $library = DeliverLocation::query()->create(['name_pt' => 'Biblioteca', 'name_en' => 'Library']);
    $target = User::factory()->create(['deliver_location_id' => $atrium->id]);

    $this->actingAs(User::factory()->create(['role' => $role]))
        ->patch(route('users.deliver-location.update', $target), ['deliver_location_id' => $library->id])
        ->assertForbidden();

    expect($target->fresh()->deliver_location_id)->toEqual($atrium->id);
})->with([
    'student' => [UserRole::Student],
    'service' => [UserRole::Service],
]);

it('redirects guests to the login page', function () {
    $library = DeliverLocation::query()->create(['name_pt' => 'Biblioteca', 'name_en' => 'Library']);
    $user = User::factory()->create();

    $this->patch(route('users.deliver-location.update', $user), ['deliver_location_id' => $library->id])
        ->assertRedirect(route('login'));

    expect($user->fresh()->deliver_location_id)->toBeNull();
});

it('forbids changing the location of an admin', function () {
    $library = DeliverLocation::query()->create(['name_pt' => 'Biblioteca', 'name_en' => 'Library']);
    $otherAdmin = User::factory()->admin()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->patch(route('users.deliver-location.update', $otherAdmin), ['deliver_location_id' => $library->id])
        ->assertForbidden();

    expect($otherAdmin->fresh()->deliver_location_id)->toBeNull();
});

it('rejects an invalid location and keeps the current one', function (mixed $value) {
    $atrium = DeliverLocation::query()->create(['name_pt' => 'Átrio', 'name_en' => 'Atrium']);
    $user = User::factory()->create(['deliver_location_id' => $atrium->id]);

    $this->actingAs(User::factory()->admin()->create())
        ->patch(route('users.deliver-location.update', $user), ['deliver_location_id' => $value])
        ->assertSessionHasErrors('deliver_location_id');

    expect($user->fresh()->deliver_location_id)->toEqual($atrium->id);
})->with([
    'does not exist' => [9999],
    'empty' => [''],
    'not a number' => ['abc'],
    'an array' => [[1, 2]],
]);

it('lists students and services on the admin dashboard but not admins', function () {
    $atrium = DeliverLocation::query()->create(['name_pt' => 'Átrio', 'name_en' => 'Atrium']);
    User::factory()->create(['name' => 'Ana Estudante', 'deliver_location_id' => $atrium->id]);
    User::factory()->service()->create(['name' => 'Servico Biblioteca']);
    User::factory()->admin()->create(['name' => 'Outro Admin Escondido']);

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Ana Estudante')
        ->assertSee('Servico Biblioteca')
        ->assertDontSee('Outro Admin Escondido');
});

it('paginates the users on the admin dashboard', function () {
    User::factory()->count(12)->create();

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertViewHas('users', fn ($users) => $users->count() === 10 && $users->total() === 12);
});
