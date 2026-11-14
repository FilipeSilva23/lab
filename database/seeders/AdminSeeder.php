<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = config('rentify.admin.email');
        $password = config('rentify.admin.password');

        if (! $email || ! $password)
            throw new RuntimeException('ADMIN_EMAIL and ADMIN_PASSWORD must be set in .env');

        $admin = User::query()->firstOrNew(['email' => $email]);
        $admin->name = 'Admin';
        $admin->password = $password;
        $admin->role = UserRole::Admin;
        $admin->save();
    }
}
