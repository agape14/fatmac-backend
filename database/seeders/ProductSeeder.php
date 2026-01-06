<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vendor = User::where('role', 'vendor')->first();

        if (!$vendor) {
            $this->command->warn('No se encontró un vendedor. Asegúrate de ejecutar UserSeeder primero.');
            return;
        }

        $categories = Category::all()->keyBy('slug');

        $products = [
            // Productos nuevos sin descuento
            [
                'name' => 'Vestido Urban Kawaii',
                'description' => 'Hermoso vestido con diseño kawaii, perfecto para ocasiones especiales. Tela suave y cómoda.',
                'price' => 89.90,
                'discount_percentage' => null,
                'stock' => 15,
                'condition' => 'nuevo',
                'category_id' => $categories['vestidos']->id ?? null,
                'category' => 'Vestidos',
                'image_url' => 'https://images.unsplash.com/photo-1595777457583-95e059d581b8?w=400',
                'is_new' => true,
                'is_featured' => true,
            ],
            [
                'name' => 'Conjunto Sport Chic',
                'description' => 'Conjunto moderno y cómodo para el día a día. Incluye top y falda.',
                'price' => 99.90,
                'discount_percentage' => null,
                'stock' => 10,
                'condition' => 'nuevo',
                'category_id' => $categories['conjuntos']->id ?? null,
                'category' => 'Conjuntos',
                'image_url' => 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=400',
                'is_new' => true,
                'is_featured' => false,
            ],
            [
                'name' => 'Sudadera Color Block',
                'description' => 'Sudadera cómoda con diseño color block. Perfecta para el invierno.',
                'price' => 79.90,
                'discount_percentage' => null,
                'stock' => 20,
                'condition' => 'nuevo',
                'category_id' => $categories['nino']->id ?? null,
                'category' => 'Niño',
                'image_url' => 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?w=400',
                'is_new' => true,
                'is_featured' => false,
            ],
            [
                'name' => 'Conjunto French Terry Fernando Para Niño',
                'description' => 'Conjunto cómodo en tela French Terry, ideal para actividades diarias.',
                'price' => 89.00,
                'discount_percentage' => null,
                'stock' => 12,
                'condition' => 'nuevo',
                'category_id' => $categories['conjuntos']->id ?? null,
                'category' => 'Conjuntos',
                'image_url' => 'https://images.unsplash.com/photo-1503341504253-dff4815485f1?w=400',
                'is_new' => true,
                'is_featured' => true,
            ],
            [
                'name' => 'Conjunto Drill Tito Para Niño',
                'description' => 'Conjunto en tela drill, resistente y cómodo para el día a día.',
                'price' => 109.00,
                'discount_percentage' => null,
                'stock' => 8,
                'condition' => 'nuevo',
                'category_id' => $categories['conjuntos']->id ?? null,
                'category' => 'Conjuntos',
                'image_url' => 'https://images.unsplash.com/photo-1503341338985-b0217f85d424?w=400',
                'is_new' => true,
                'is_featured' => false,
            ],
            [
                'name' => 'Conjunto Popelina Andy Para Niño',
                'description' => 'Conjunto en popelina suave, perfecto para el verano.',
                'price' => 89.00,
                'discount_percentage' => null,
                'stock' => 15,
                'condition' => 'nuevo',
                'category_id' => $categories['conjuntos']->id ?? null,
                'category' => 'Conjuntos',
                'image_url' => 'https://images.unsplash.com/photo-1503341504253-dff4815485f1?w=400',
                'is_new' => true,
                'is_featured' => false,
            ],
            [
                'name' => 'Blusa Lino Lucero Para Niña',
                'description' => 'Blusa elegante en lino, fresca y cómoda para cualquier ocasión.',
                'price' => 59.00,
                'discount_percentage' => null,
                'stock' => 18,
                'condition' => 'nuevo',
                'category_id' => $categories['blusas']->id ?? null,
                'category' => 'Blusas',
                'image_url' => 'https://images.unsplash.com/photo-1594633313593-bab3825d0caf?w=400',
                'is_new' => true,
                'is_featured' => false,
            ],
            [
                'name' => 'Casaca Franela Kiko Para Niño',
                'description' => 'Casaca cálida en franela, ideal para el invierno.',
                'price' => 99.00,
                'discount_percentage' => null,
                'stock' => 10,
                'condition' => 'nuevo',
                'category_id' => $categories['nino']->id ?? null,
                'category' => 'Niño',
                'image_url' => 'https://images.unsplash.com/photo-1556821840-3a63f95609a7?w=400',
                'is_new' => true,
                'is_featured' => false,
            ],

            // Productos con descuento
            [
                'name' => 'Conjunto Lino Hanna Para Niña',
                'description' => 'Conjunto elegante en lino con diseño floral. Perfecto para ocasiones especiales.',
                'price' => 89.00,
                'discount_percentage' => 20,
                'stock' => 5,
                'condition' => 'nuevo',
                'category_id' => $categories['conjuntos']->id ?? null,
                'category' => 'Conjuntos',
                'image_url' => 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=400',
                'is_new' => false,
                'is_featured' => true,
            ],
            [
                'name' => 'Vestido Popelina Carmela Para Niña',
                'description' => 'Vestido hermoso en popelina con diseño kawaii. Muy cómodo y suave.',
                'price' => 129.00,
                'discount_percentage' => 20,
                'stock' => 7,
                'condition' => 'nuevo',
                'category_id' => $categories['vestidos']->id ?? null,
                'category' => 'Vestidos',
                'image_url' => 'https://images.unsplash.com/photo-1595777457583-95e059d581b8?w=400',
                'is_new' => false,
                'is_featured' => true,
            ],
            [
                'name' => 'Blusa Nansú Mary para Niña',
                'description' => 'Blusa cómoda en tela nansú, perfecta para el día a día.',
                'price' => 59.00,
                'discount_percentage' => 20,
                'stock' => 12,
                'condition' => 'nuevo',
                'category_id' => $categories['blusas']->id ?? null,
                'category' => 'Blusas',
                'image_url' => 'https://images.unsplash.com/photo-1594633313593-bab3825d0caf?w=400',
                'is_new' => false,
                'is_featured' => false,
            ],
            [
                'name' => 'Blusa Nansú Liria Para Niña',
                'description' => 'Blusa elegante en tela nansú con detalles delicados.',
                'price' => 59.00,
                'discount_percentage' => 20,
                'stock' => 9,
                'condition' => 'nuevo',
                'category_id' => $categories['blusas']->id ?? null,
                'category' => 'Blusas',
                'image_url' => 'https://images.unsplash.com/photo-1594633313593-bab3825d0caf?w=400',
                'is_new' => false,
                'is_featured' => false,
            ],
            [
                'name' => 'Camisa Lino Carlos Para Niño',
                'description' => 'Camisa elegante en lino, perfecta para ocasiones formales.',
                'price' => 69.00,
                'discount_percentage' => 15,
                'stock' => 11,
                'condition' => 'nuevo',
                'category_id' => $categories['camisas']->id ?? null,
                'category' => 'Camisas',
                'image_url' => 'https://images.unsplash.com/photo-1503341504253-dff4815485f1?w=400',
                'is_new' => false,
                'is_featured' => false,
            ],

            // Más productos variados
            [
                'name' => 'Vestido Floral Primavera',
                'description' => 'Vestido con estampado floral, ideal para la primavera. Diseño kawaii y cómodo.',
                'price' => 95.00,
                'discount_percentage' => null,
                'stock' => 14,
                'condition' => 'nuevo',
                'category_id' => $categories['vestidos']->id ?? null,
                'category' => 'Vestidos',
                'image_url' => 'https://images.unsplash.com/photo-1595777457583-95e059d581b8?w=400',
                'is_new' => true,
                'is_featured' => true,
            ],
            [
                'name' => 'Blusa Estampada Kawaii',
                'description' => 'Blusa con estampados kawaii, perfecta para combinar con cualquier outfit.',
                'price' => 65.00,
                'discount_percentage' => null,
                'stock' => 16,
                'condition' => 'nuevo',
                'category_id' => $categories['blusas']->id ?? null,
                'category' => 'Blusas',
                'image_url' => 'https://images.unsplash.com/photo-1594633313593-bab3825d0caf?w=400',
                'is_new' => true,
                'is_featured' => false,
            ],
            [
                'name' => 'Camisa Formal Elegante',
                'description' => 'Camisa formal para ocasiones especiales. Tela de alta calidad.',
                'price' => 75.00,
                'discount_percentage' => 10,
                'stock' => 13,
                'condition' => 'nuevo',
                'category_id' => $categories['camisas']->id ?? null,
                'category' => 'Camisas',
                'image_url' => 'https://images.unsplash.com/photo-1503341504253-dff4815485f1?w=400',
                'is_new' => false,
                'is_featured' => false,
            ],
            [
                'name' => 'Conjunto Deportivo Moderno',
                'description' => 'Conjunto deportivo cómodo y moderno, perfecto para actividades físicas.',
                'price' => 85.00,
                'discount_percentage' => null,
                'stock' => 17,
                'condition' => 'nuevo',
                'category_id' => $categories['conjuntos']->id ?? null,
                'category' => 'Conjuntos',
                'image_url' => 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=400',
                'is_new' => true,
                'is_featured' => false,
            ],
            [
                'name' => 'Vestido de Gala Kawaii',
                'description' => 'Vestido elegante para ocasiones especiales con toque kawaii.',
                'price' => 120.00,
                'discount_percentage' => 25,
                'stock' => 6,
                'condition' => 'nuevo',
                'category_id' => $categories['vestidos']->id ?? null,
                'category' => 'Vestidos',
                'image_url' => 'https://images.unsplash.com/photo-1595777457583-95e059d581b8?w=400',
                'is_new' => false,
                'is_featured' => true,
            ],

            // Productos para Recién Nacido
            [
                'name' => 'Body Recién Nacido Kawaii',
                'description' => 'Body suave y cómodo para recién nacidos con diseño kawaii.',
                'price' => 45.00,
                'discount_percentage' => null,
                'stock' => 20,
                'condition' => 'nuevo',
                'category_id' => $categories['recien-nacido']->id ?? null,
                'category' => 'Recién Nacido',
                'image_url' => 'https://images.unsplash.com/photo-1515488042361-ee00e0ddd4e4?w=400',
                'is_new' => true,
                'is_featured' => false,
            ],
            [
                'name' => 'Conjunto Bebé Recién Nacido',
                'description' => 'Conjunto completo para recién nacidos, suave y cómodo.',
                'price' => 75.00,
                'discount_percentage' => 15,
                'stock' => 15,
                'condition' => 'nuevo',
                'category_id' => $categories['recien-nacido']->id ?? null,
                'category' => 'Recién Nacido',
                'image_url' => 'https://images.unsplash.com/photo-1515488042361-ee00e0ddd4e4?w=400',
                'is_new' => false,
                'is_featured' => true,
            ],

            // Productos para Bebé Niña
            [
                'name' => 'Vestido Bebé Niña Floral',
                'description' => 'Vestido hermoso con estampado floral para bebé niña.',
                'price' => 65.00,
                'discount_percentage' => null,
                'stock' => 12,
                'condition' => 'nuevo',
                'category_id' => $categories['bebe-nina']->id ?? null,
                'category' => 'Bebé niña',
                'image_url' => 'https://images.unsplash.com/photo-1595777457583-95e059d581b8?w=400',
                'is_new' => true,
                'is_featured' => false,
            ],
            [
                'name' => 'Blusa Bebé Niña Rosa',
                'description' => 'Blusa suave en color rosa pastel para bebé niña.',
                'price' => 55.00,
                'discount_percentage' => 10,
                'stock' => 18,
                'condition' => 'nuevo',
                'category_id' => $categories['bebe-nina']->id ?? null,
                'category' => 'Bebé niña',
                'image_url' => 'https://images.unsplash.com/photo-1594633313593-bab3825d0caf?w=400',
                'is_new' => false,
                'is_featured' => false,
            ],

            // Productos para Bebé Niño
            [
                'name' => 'Conjunto Bebé Niño Azul',
                'description' => 'Conjunto cómodo en color azul para bebé niño.',
                'price' => 70.00,
                'discount_percentage' => null,
                'stock' => 14,
                'condition' => 'nuevo',
                'category_id' => $categories['bebe-nino']->id ?? null,
                'category' => 'Bebé niño',
                'image_url' => 'https://images.unsplash.com/photo-1503341504253-dff4815485f1?w=400',
                'is_new' => true,
                'is_featured' => false,
            ],
            [
                'name' => 'Camisa Bebé Niño Formal',
                'description' => 'Camisa elegante para ocasiones especiales de bebé niño.',
                'price' => 60.00,
                'discount_percentage' => 20,
                'stock' => 10,
                'condition' => 'nuevo',
                'category_id' => $categories['bebe-nino']->id ?? null,
                'category' => 'Bebé niño',
                'image_url' => 'https://images.unsplash.com/photo-1503341504253-dff4815485f1?w=400',
                'is_new' => false,
                'is_featured' => true,
            ],
        ];

        foreach ($products as $productData) {
            Product::create([
                'user_id' => $vendor->id,
                ...$productData,
            ]);
        }

        $this->command->info('Productos creados exitosamente: ' . count($products));
    }
}

