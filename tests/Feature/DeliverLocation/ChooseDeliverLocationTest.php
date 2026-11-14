<?php

use App\Enums\UserRole;
use App\Models\DeliverLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lets a student change their delivery location', function () {
    $student = User::factory()->create(['role' => UserRole::Student]);
    $library = DeliverLocation::query()->create(['name_pt' => 'Biblioteca', 'name_en' => 'Library']);

    $this->actingAs($student)
        ->patch(route('dashboard.deliver-location.update'), ['deliver_location_id' => $library->id])
        ->assertRedirect();

    expect($student->fresh()->deliver_location_id)->toEqual($library->id);
});

it('forbids services from changing their delivery location', function () {
    $service = User::factory()->create(['role' => UserRole::Service]);
    $bar = DeliverLocation::query()->create(['name_pt' => 'Bar', 'name_en' => 'Café']);

    $this->actingAs($service)
        ->patch(route('dashboard.deliver-location.update'), ['deliver_location_id' => $bar->id])
        ->assertForbidden();

    expect($service->fresh()->deliver_location_id)->toBeNull();
});

it('rejects a location that does not exist', function () {
    $student = User::factory()->create(['role' => UserRole::Student]);

    $this->actingAs($student)
        ->patch(route('dashboard.deliver-location.update'), ['deliver_location_id' => 9999])
        ->assertSessionHasErrors('deliver_location_id');
});
