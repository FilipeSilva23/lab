<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name_pt' => 'Livros', 'name_en' => 'Books'],
            ['name_pt' => 'Eletrónica', 'name_en' => 'Electronics'],
            ['name_pt' => 'Comunicação', 'name_en' => 'Comunication'],
            ['name_pt' => 'Desporto', 'name_en' => 'Sports'],
            ['name_pt' => 'Informática', 'name_en' => 'Informatics'],
        ];

        foreach ($categories as $category) {
            Category::query()->updateOrCreate(['name_pt' => $category['name_pt']], $category);
        }
    }
}
