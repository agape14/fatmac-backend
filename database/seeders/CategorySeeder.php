<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Lo nuevo',
                'slug' => 'lo-nuevo',
                'icon' => '✨',
                'order' => 1,
            ],
            [
                'name' => 'Recién Nacido',
                'slug' => 'recien-nacido',
                'icon' => '👶',
                'order' => 2,
            ],
            [
                'name' => 'Bebé niña',
                'slug' => 'bebe-nina',
                'icon' => '👧',
                'order' => 3,
            ],
            [
                'name' => 'Bebé niño',
                'slug' => 'bebe-nino',
                'icon' => '👦',
                'order' => 4,
            ],
            [
                'name' => 'Niña',
                'slug' => 'nina',
                'icon' => '👗',
                'order' => 5,
            ],
            [
                'name' => 'Niño',
                'slug' => 'nino',
                'icon' => '👕',
                'order' => 6,
            ],
            [
                'name' => 'Ofertas',
                'slug' => 'ofertas',
                'icon' => '🏷️',
                'order' => 7,
            ],
            [
                'name' => 'Blusas',
                'slug' => 'blusas',
                'icon' => '👚',
                'order' => 8,
            ],
            [
                'name' => 'Camisas',
                'slug' => 'camisas',
                'icon' => '👔',
                'order' => 9,
            ],
            [
                'name' => 'Vestidos',
                'slug' => 'vestidos',
                'icon' => '👗',
                'order' => 10,
            ],
            [
                'name' => 'Conjuntos',
                'slug' => 'conjuntos',
                'icon' => '👕',
                'order' => 11,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}

