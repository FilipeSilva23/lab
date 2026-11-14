<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('ignores role in mass assignment', function () {
    $user = User::query()->create([
        'name' => 'Test',
        'email' => 'test@test.local',
        'password' => 'secret-pass-123',
        'role' => 'admin',
    ]);

    expect($user->fresh()->role)->toBe(UserRole::Student);
});

test('never exposes the password or the remember token in arrays', function () {
    $user = User::factory()->create();

    expect($user->toArray())->not->toHaveKeys(['password', 'remember_token']);
});
