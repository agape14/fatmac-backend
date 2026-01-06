<?php

namespace Database\Seeders;

use App\Models\PromotionalBanner;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PromotionalBannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PromotionalBanner::create([
            'title' => 'Colección',
            'subtitle' => 'VESTIDOS',
            'button_text' => 'VER AHORA',
            'button_link' => '/categoria/vestidos',
            'image_left_url' => null,
            'image_right_url' => null,
            'background_color' => 'from-pink-pastel via-purple-pastel to-yellow-pastel',
            'order' => 1,
            'is_active' => true,
        ]);
    }
}

