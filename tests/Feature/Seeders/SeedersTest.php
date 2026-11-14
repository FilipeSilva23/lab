<?php

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\DeliverLocation;
use App\Models\Item;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\DeliverLocationSeeder;
use Database\Seeders\ItemDemoSeeder;
use Database\Seeders\UserDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    config()->set('rentify.admin', ['email' => 'admin@test.local', 'password' => 'admin-pass-123']);
    config()->set('rentify.demo_password', 'demo-pass-456');
});


test('creates one admin with a hashed password', function () {
    $this->seed(AdminSeeder::class);

    $admin = User::query()->where('email', 'admin@test.local')->firstOrFail();

    expect(User::query()->count())->toBe(1)
        ->and($admin->role)->toBe(UserRole::Admin)
        ->and($admin->password)->not->toBe('admin-pass-123')
        ->and(Hash::check('admin-pass-123', $admin->password))->toBeTrue();
});

test('fails without admin credentials and creates nothing', function (array $admin) {
    config()->set('rentify.admin', $admin);

    expect(fn() => $this->seed(AdminSeeder::class))->toThrow(RuntimeException::class)
        ->and(User::query()->count())->toBe(0);
})->with([
    'missing email' => [['email' => null, 'password' => 'admin-pass-123']],
    'missing password' => [['email' => 'admin@test.local', 'password' => null]],
]);

test('seeds categories with both translations', function () {
    $this->seed(CategorySeeder::class);

    expect(Category::query()->count())->toBeGreaterThan(0)
        ->and(Category::query()->whereNull('name_pt')->orWhereNull('name_en')->count())->toBe(0)
        ->and(Category::query()->where('name_pt', '')->orWhere('name_en', '')->count())->toBe(0);
});

test('seeds the demo users with the right roles and a hashed password', function () {
    $this->seed(DeliverLocationSeeder::class);
    $this->seed(UserDemoSeeder::class);

    expect(User::query()->where('role', UserRole::Student)->count())->toBe(3)
        ->and(User::query()->where('role', UserRole::Service)->count())->toBe(2);

    $user = User::where('role', UserRole::Student)->first();
    expect($user->password)->not->toBe('demo-pass-456')
        ->and(Hash::check('demo-pass-456', $user->password))->toBeTrue();
});

test('fails without the demo password and creates nothing', function () {
    config()->set('rentify.demo_password', null);

    expect(fn() => $this->seed(UserDemoSeeder::class))->toThrow(RuntimeException::class)
        ->and(User::query()->count())->toBe(0);
});

test('gives students the Átrio and each service its own location', function () {
    $this->seed(DeliverLocationSeeder::class);
    $this->seed(UserDemoSeeder::class);

    $locationId = fn(string $name) => DeliverLocation::query()->where('name_pt', $name)->value('id');
    $userLocationId = fn(string $email) => User::query()->where('email', $email)->value('deliver_location_id');

    expect($userLocationId('student1@example.com'))->toEqual($locationId('Átrio'))
        ->and($userLocationId('student2@example.com'))->toEqual($locationId('Átrio'))
        ->and($userLocationId('student3@example.com'))->toEqual($locationId('Átrio'))
        ->and($userLocationId('service_biblioteca@example.com'))->toEqual($locationId('Biblioteca'))
        ->and($userLocationId('service_laboratorio@example.com'))->toEqual($locationId('Laboratório'));
});

test('fails when the delivery locations were not seeded and saves no demo user', function () {
    expect(fn() => $this->seed(UserDemoSeeder::class))->toThrow(RuntimeException::class)
        ->and(User::query()->count())->toBe(0);
});

test('seeds items owned by services, each with a category', function () {
    $this->seed();

    expect(Item::query()->count())->toBeGreaterThan(0)
        ->and(Item::query()->doesntHave('categories')->count())->toBe(0)
        ->and(Item::query()->whereHas('owner', fn($q) => $q->where('role', UserRole::Service))->count())->toBe(Item::count());
});

test('fails when the owners were not seeded', function () {
    $this->seed(CategorySeeder::class);

    expect(fn() => $this->seed(ItemDemoSeeder::class))->toThrow(RuntimeException::class)
        ->and(Item::query()->count())->toBe(0);
});

test('fails when the categories were not seeded and saves no item', function () {
    $this->seed(DeliverLocationSeeder::class);
    $this->seed(UserDemoSeeder::class);

    expect(fn() => $this->seed(ItemDemoSeeder::class))->toThrow(RuntimeException::class)
        ->and(Item::query()->count())->toBe(0);
});

test('does not duplicate anything when seeded twice', function () {
    $count = fn () => [
        User::query()->count(),
        Category::query()->count(),
        Item::query()->count(),
        DB::table('category_item')->count(),
    ];

    $this->seed();
    $first = $count();

    $this->seed();

    expect($count())->toBe($first);
});

test('does not create the default Laravel example user', function () {
    $this->seed();

    expect(User::query()->where('email', 'test@example.com')->exists())->toBeFalse();
});
