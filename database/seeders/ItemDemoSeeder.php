<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use RuntimeException;

class ItemDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            [
                'owner' => 'service_biblioteca@example.com',
                'title' => 'Livro',
                'description' => 'Um Livro',
                'quantity' => 5,
                'price_per_day' => 0,
                'deposit_amount' => 10,
                'categories' => ['Livros'],
            ],
            [
                'owner' => 'service_laboratorio@example.com',
                'title' => 'Kit Arduino',
                'description' => 'Placa Arduino com cabos e componentes básicos',
                'quantity' => 10,
                'price_per_day' => 2,
                'categories' => ['Eletrónica'],
            ],
        ];

        $categoryIds = Category::query()->pluck('id', 'name_pt');
        $owners = User::query()
            ->whereIn('email', array_column($items, 'owner'))
            ->get()
            ->keyBy('email');

        foreach ($items as $data) {
            /** @var string $email */
            $email = $data['owner'];
            $owner = $owners->get($email)
                ?? throw new RuntimeException("Owner {$email} not found, run UserDemoSeeder first");

            // Take the extra keys out of $data, so only real columns are left
            $names = $data['categories'];
            unset($data['owner'], $data['categories']);

            $ids = $categoryIds->only($names);
            if ($ids->count() !== count($names))
                throw new RuntimeException('Categories not found: ' . implode(', ', array_diff($names, $ids->keys()->all())));

            $item = $owner->items()->updateOrCreate(['title' => $data['title']], $data);

            // Leave in category_item exactly these categories for this item, never duplicates
            $item->categories()->sync($ids->values());
        }
    }
}
