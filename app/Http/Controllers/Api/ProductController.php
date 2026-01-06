<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['user', 'categoryModel', 'images']);

        // Filtrar por categoría (slug)
        if ($request->has('category_slug') && $request->category_slug) {
            $query->whereHas('categoryModel', function ($q) use ($request) {
                $q->where('slug', $request->category_slug);
            });
        }

        // Filtrar por category_id (puede ser array o único)
        if ($request->has('category_id') && $request->category_id) {
            $categoryIds = is_array($request->category_id) ? $request->category_id : [$request->category_id];
            $query->whereIn('category_id', $categoryIds);
        }

        // Filtrar por categoría (string legacy)
        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        // Filtrar por ID del vendedor
        if ($request->has('vendor_id') && $request->vendor_id) {
            $query->where('user_id', $request->vendor_id);
        }

        // Filtrar productos nuevos
        if ($request->has('is_new') && $request->is_new) {
            $query->where('is_new', true);
        }

        // Filtrar productos con descuento
        if ($request->has('has_discount') && $request->has_discount) {
            $query->whereNotNull('discount_percentage')
                  ->where('discount_percentage', '>', 0);
        }

        // Filtrar productos destacados
        if ($request->has('is_featured') && $request->is_featured) {
            $query->where('is_featured', true);
        }

        // Filtrar por condición (puede ser array)
        if ($request->has('condition') && $request->condition) {
            $conditions = is_array($request->condition) ? $request->condition : [$request->condition];
            $query->whereIn('condition', $conditions);
        }

        // Filtrar por precio máximo
        if ($request->has('max_price') && $request->max_price) {
            $query->where('price', '<=', $request->max_price);
        }

        // Filtrar por precio mínimo
        if ($request->has('min_price') && $request->min_price) {
            $query->where('price', '>=', $request->min_price);
        }

        // Búsqueda por nombre
        if ($request->has('search') && $request->search) {
            $query->where('name', 'LIKE', '%' . $request->search . '%');
        }

        $products = $query->paginate(15);

        return response()->json([
            'data' => ProductResource::collection($products),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'stock' => 'required|integer|min:0',
            'condition' => 'required|in:nuevo,usado',
            'category_id' => 'nullable|exists:categories,id',
            'category' => 'nullable|string|max:255',
            'image_url' => 'nullable|url|max:255', // Mantener para compatibilidad
            'images' => 'nullable|array|max:10', // Máximo 10 imágenes
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB por imagen
            'is_new' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }

        $product = Product::create([
            'user_id' => $request->user()->id,
            'category_id' => $request->category_id,
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'discount_percentage' => $request->discount_percentage ?? 0,
            'stock' => $request->stock,
            'condition' => $request->condition,
            'category' => $request->category,
            'image_url' => $request->image_url, // Mantener para compatibilidad
            'is_new' => $request->is_new ?? false,
            'is_featured' => $request->is_featured ?? false,
        ]);

        // Guardar múltiples imágenes si se proporcionaron
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $imagePath = $image->store('products', 'public');
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $imagePath,
                    'order' => $index,
                ]);
            }
        }

        return response()->json([
            'message' => 'Producto creado exitosamente',
            'data' => new ProductResource($product->load(['user', 'categoryModel', 'images'])),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $product = Product::with(['user', 'categoryModel', 'images'])->findOrFail($id);

        return response()->json([
            'data' => new ProductResource($product),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $user = $request->user();

        // Verificar que el vendor solo pueda editar sus propios productos
        if ($user->role === 'vendor' && $product->user_id !== $user->id) {
            return response()->json([
                'message' => 'No autorizado. Solo puedes editar tus propios productos.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'stock' => 'sometimes|required|integer|min:0',
            'condition' => 'sometimes|required|in:nuevo,usado',
            'category_id' => 'nullable|exists:categories,id',
            'category' => 'nullable|string|max:255',
            'image_url' => 'nullable|url|max:255',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'delete_images' => 'nullable|array', // IDs de imágenes a eliminar
            'delete_images.*' => 'exists:product_images,id',
            'is_new' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }

        $product->update($request->only([
            'name',
            'description',
            'price',
            'discount_percentage',
            'stock',
            'condition',
            'category_id',
            'category',
            'image_url',
            'is_new',
            'is_featured',
        ]));

        // Eliminar imágenes si se solicita
        if ($request->has('delete_images') && is_array($request->delete_images)) {
            $imagesToDelete = ProductImage::whereIn('id', $request->delete_images)
                ->where('product_id', $product->id)
                ->get();

            foreach ($imagesToDelete as $image) {
                Storage::disk('public')->delete($image->image_path);
                $image->delete();
            }
        }

        // Agregar nuevas imágenes
        if ($request->hasFile('images')) {
            $existingImagesCount = $product->images()->count();
            foreach ($request->file('images') as $index => $image) {
                $imagePath = $image->store('products', 'public');
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $imagePath,
                    'order' => $existingImagesCount + $index,
                ]);
            }
        }

        return response()->json([
            'message' => 'Producto actualizado exitosamente',
            'data' => new ProductResource($product->fresh(['user', 'categoryModel', 'images'])),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $user = $request->user();

        // Verificar que el vendor solo pueda borrar sus propios productos
        if ($user->role === 'vendor' && $product->user_id !== $user->id) {
            return response()->json([
                'message' => 'No autorizado. Solo puedes eliminar tus propios productos.',
            ], 403);
        }

        $product->delete();

        return response()->json([
            'message' => 'Producto eliminado exitosamente',
        ], 200);
    }
}
