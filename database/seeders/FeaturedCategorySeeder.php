<?php

namespace Database\Seeders;

use App\Models\FeaturedCategory;
use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FeaturedCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::all()->keyBy('slug');

        $featuredCategories = [
            [
                'category_id' => $categories['blusas']->id ?? null,
                'name' => 'Blusas',
                'icon' => '👚',
                'image_url' => null,
                'background_color' => 'from-pink-pastel to-pink-300',
                'text_color' => 'text-gray-800',
                'order' => 1,
            ],
            [
                'category_id' => $categories['camisas']->id ?? null,
                'name' => 'Camisas',
                'icon' => '👔',
                'image_url' => null,
                'background_color' => 'from-blue-200 to-blue-300',
                'text_color' => 'text-gray-800',
                'order' => 2,
            ],
            [
                'category_id' => $categories['vestidos']->id ?? null,
                'name' => 'Vestidos',
                'icon' => '👗',
                'image_url' => null,
                'background_color' => 'from-orange-200 to-orange-300',
                'text_color' => 'text-gray-800',
                'order' => 3,
            ],
            [
                'category_id' => $categories['conjuntos']->id ?? null,
                'name' => 'Conjuntos',
                'icon' => '👕',
                'image_url' => null,
                'background_color' => 'from-blue-200 to-blue-300',
                'text_color' => 'text-gray-800',
                'order' => 4,
            ],
        ];

        foreach ($featuredCategories as $featured) {
            if ($featured['category_id']) {
                FeaturedCategory::create($featured);
            }
        }
    }
}

