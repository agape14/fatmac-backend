<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MenuItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::all()->keyBy('slug');

        $menuItems = [
            [
                'label' => 'Lo nuevo',
                'path' => '/novedades',
                'slug' => 'lo-nuevo',
                'icon' => '✨',
                'order' => 1,
                'type' => 'page',
                'category_id' => null,
            ],
            [
                'label' => 'Recién Nacido',
                'path' => '/categoria/recien-nacido',
                'slug' => 'recien-nacido',
                'icon' => '👶',
                'order' => 2,
                'type' => 'category',
                'category_id' => $categories['recien-nacido']->id ?? null,
            ],
            [
                'label' => 'Bebé niña',
                'path' => '/categoria/bebe-nina',
                'slug' => 'bebe-nina',
                'icon' => '👧',
                'order' => 3,
                'type' => 'category',
                'category_id' => $categories['bebe-nina']->id ?? null,
            ],
            [
                'label' => 'Bebé niño',
                'path' => '/categoria/bebe-nino',
                'slug' => 'bebe-nino',
                'icon' => '👦',
                'order' => 4,
                'type' => 'category',
                'category_id' => $categories['bebe-nino']->id ?? null,
            ],
            [
                'label' => 'Niña',
                'path' => '/categoria/nina',
                'slug' => 'nina',
                'icon' => '👗',
                'order' => 5,
                'type' => 'category',
                'category_id' => $categories['nina']->id ?? null,
            ],
            [
                'label' => 'Niño',
                'path' => '/categoria/nino',
                'slug' => 'nino',
                'icon' => '👕',
                'order' => 6,
                'type' => 'category',
                'category_id' => $categories['nino']->id ?? null,
            ],
            [
                'label' => 'Ofertas',
                'path' => '/ofertas',
                'slug' => 'ofertas',
                'icon' => '🏷️',
                'order' => 7,
                'type' => 'page',
                'category_id' => null,
            ],
        ];

        foreach ($menuItems as $item) {
            MenuItem::create($item);
        }
    }
}

