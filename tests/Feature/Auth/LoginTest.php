<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('login with an array password is a validation error, not a 500', function () {
    $user = User::factory()->create();

    $this->post(route('login'), ['email' => $user->email, 'password' => ['x']])
        ->assertSessionHasErrors('password');

    $this->assertGuest();
});

test('login still works with valid credentials', function () {
    $user = User::factory()->create();

    $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});
