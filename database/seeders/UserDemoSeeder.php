<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\DeliverLocation;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class UserDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = config('rentify.demo_password');
        if (! $password)
            throw new RuntimeException('DEMO_PASSWORD must be set in .env');

        $locationIds = DeliverLocation::query()->pluck('id', 'name_pt');

        $users = [
            ['Student 1', 'student1@example.com', UserRole::Student, 'Átrio'],
            ['Student 2', 'student2@example.com', UserRole::Student, 'Átrio'],
            ['Student 3', 'student3@example.com', UserRole::Student, 'Átrio'],
            ['Biblioteca', 'service_biblioteca@example.com', UserRole::Service, 'Biblioteca'],
            ['Laboratorio', 'service_laboratorio@example.com', UserRole::Service, 'Laboratório'],
        ];

        foreach ($users as [$name, $email, $role, $location]) {
            $user = User::query()->firstOrNew(['email' => $email]);
            $user->name = $name;
            $user->password = $password;
            $user->role = $role;
            $user->deliver_location_id = $locationIds->get($location)
                ?? throw new RuntimeException("Deliver location {$location} not found, run DeliverLocationSeeder first");

            $user->save();
        }
    }
}
