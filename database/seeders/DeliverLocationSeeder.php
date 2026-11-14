<?php

namespace Database\Seeders;

use App\Models\DeliverLocation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DeliverLocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = [
            ['Átrio', 'Atrium'],
            ['Biblioteca', 'Library'],
            ['Bar', 'Café'],
            ['Laboratório', 'Laboratory'],
        ];

        foreach ($locations as [$pt, $en]) {
            DeliverLocation::query()->updateOrCreate(['name_pt' => $pt], ['name_en' => $en]);
        }
    }
}
