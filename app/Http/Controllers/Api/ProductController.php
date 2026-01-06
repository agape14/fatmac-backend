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
        $query = Product::with(['user', 'categoryModel', 'images'])
            ->whereHas('user', function ($q) {
                // Solo mostrar productos de vendedores aprobados (o admins)
                $q->where(function ($query) {
                    $query->where('role', 'admin')
                          ->orWhere(function ($q) {
                              $q->where('role', 'vendor')
                                ->where('status', 'approved');
                          });
                });
            });

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

        // Filtrar por ID del vendedor (puede ser array o único)
        if ($request->has('vendor_id') && $request->vendor_id) {
            $vendorIds = is_array($request->vendor_id) ? $request->vendor_id : [$request->vendor_id];
            $query->whereIn('user_id', $vendorIds);
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
        // Convertir strings 'true'/'false'/'1'/'0' a booleanos
        $requestData = $request->all();
        if (isset($requestData['is_new']) && !is_bool($requestData['is_new'])) {
            $isNewValue = $requestData['is_new'];
            if (is_string($isNewValue)) {
                $isNewValue = strtolower(trim($isNewValue));
                $requestData['is_new'] = in_array($isNewValue, ['true', '1', 'yes', 'on'], true);
            } elseif (is_numeric($isNewValue)) {
                $requestData['is_new'] = (bool)(int)$isNewValue;
            } else {
                $requestData['is_new'] = filter_var($isNewValue, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
            }
        }
        if (isset($requestData['is_featured']) && !is_bool($requestData['is_featured'])) {
            $isFeaturedValue = $requestData['is_featured'];
            if (is_string($isFeaturedValue)) {
                $isFeaturedValue = strtolower(trim($isFeaturedValue));
                $requestData['is_featured'] = in_array($isFeaturedValue, ['true', '1', 'yes', 'on'], true);
            } elseif (is_numeric($isFeaturedValue)) {
                $requestData['is_featured'] = (bool)(int)$isFeaturedValue;
            } else {
                $requestData['is_featured'] = filter_var($isFeaturedValue, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
            }
        }

        $validator = Validator::make($requestData, [
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
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'name.string' => 'El nombre debe ser texto.',
            'name.max' => 'El nombre no debe exceder 255 caracteres.',
            'description.string' => 'La descripción debe ser texto.',
            'price.required' => 'El precio es obligatorio.',
            'price.numeric' => 'El precio debe ser un número.',
            'price.min' => 'El precio debe ser mayor o igual a 0.',
            'discount_percentage.numeric' => 'El porcentaje de descuento debe ser un número.',
            'discount_percentage.min' => 'El porcentaje de descuento debe ser mayor o igual a 0.',
            'discount_percentage.max' => 'El porcentaje de descuento no puede ser mayor a 100.',
            'stock.required' => 'El stock es obligatorio.',
            'stock.integer' => 'El stock debe ser un número entero.',
            'stock.min' => 'El stock debe ser mayor o igual a 0.',
            'condition.required' => 'La condición es obligatoria.',
            'condition.in' => 'La condición debe ser "nuevo" o "usado".',
            'category_id.exists' => 'La categoría seleccionada no existe.',
            'category.string' => 'La categoría debe ser texto.',
            'category.max' => 'La categoría no debe exceder 255 caracteres.',
            'image_url.url' => 'La URL de la imagen debe ser válida.',
            'image_url.max' => 'La URL de la imagen no debe exceder 255 caracteres.',
            'images.array' => 'Las imágenes deben ser un array.',
            'images.max' => 'No se pueden subir más de 10 imágenes.',
            'images.*.image' => 'Todos los archivos deben ser imágenes.',
            'images.*.mimes' => 'Las imágenes deben ser en formato: jpeg, png, jpg, gif o webp.',
            'images.*.max' => 'Cada imagen no debe ser mayor a 5 MB.',
            'is_new.boolean' => 'El campo "nuevo" debe ser verdadero o falso.',
            'is_featured.boolean' => 'El campo "destacado" debe ser verdadero o falso.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }

        $product = Product::create([
            'user_id' => $request->user()->id,
            'category_id' => $requestData['category_id'] ?? null,
            'name' => $requestData['name'],
            'description' => $requestData['description'] ?? null,
            'price' => $requestData['price'],
            'discount_percentage' => $requestData['discount_percentage'] ?? 0,
            'stock' => $requestData['stock'],
            'condition' => $requestData['condition'],
            'category' => $requestData['category'] ?? null,
            'image_url' => $requestData['image_url'] ?? null, // Mantener para compatibilidad
            'is_new' => $requestData['is_new'] ?? false,
            'is_featured' => $requestData['is_featured'] ?? false,
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

        // Convertir strings 'true'/'false'/'1'/'0' a booleanos
        $requestData = $request->all();
        if (isset($requestData['is_new']) && !is_bool($requestData['is_new'])) {
            $isNewValue = $requestData['is_new'];
            if (is_string($isNewValue)) {
                $isNewValue = strtolower(trim($isNewValue));
                $requestData['is_new'] = in_array($isNewValue, ['true', '1', 'yes', 'on'], true);
            } elseif (is_numeric($isNewValue)) {
                $requestData['is_new'] = (bool)(int)$isNewValue;
            } else {
                $requestData['is_new'] = filter_var($isNewValue, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
            }
        }
        if (isset($requestData['is_featured']) && !is_bool($requestData['is_featured'])) {
            $isFeaturedValue = $requestData['is_featured'];
            if (is_string($isFeaturedValue)) {
                $isFeaturedValue = strtolower(trim($isFeaturedValue));
                $requestData['is_featured'] = in_array($isFeaturedValue, ['true', '1', 'yes', 'on'], true);
            } elseif (is_numeric($isFeaturedValue)) {
                $requestData['is_featured'] = (bool)(int)$isFeaturedValue;
            } else {
                $requestData['is_featured'] = filter_var($isFeaturedValue, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
            }
        }

        $validator = Validator::make($requestData, [
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
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'name.string' => 'El nombre debe ser texto.',
            'name.max' => 'El nombre no debe exceder 255 caracteres.',
            'description.string' => 'La descripción debe ser texto.',
            'price.required' => 'El precio es obligatorio.',
            'price.numeric' => 'El precio debe ser un número.',
            'price.min' => 'El precio debe ser mayor o igual a 0.',
            'discount_percentage.numeric' => 'El porcentaje de descuento debe ser un número.',
            'discount_percentage.min' => 'El porcentaje de descuento debe ser mayor o igual a 0.',
            'discount_percentage.max' => 'El porcentaje de descuento no puede ser mayor a 100.',
            'stock.required' => 'El stock es obligatorio.',
            'stock.integer' => 'El stock debe ser un número entero.',
            'stock.min' => 'El stock debe ser mayor o igual a 0.',
            'condition.required' => 'La condición es obligatoria.',
            'condition.in' => 'La condición debe ser "nuevo" o "usado".',
            'category_id.exists' => 'La categoría seleccionada no existe.',
            'category.string' => 'La categoría debe ser texto.',
            'category.max' => 'La categoría no debe exceder 255 caracteres.',
            'image_url.url' => 'La URL de la imagen debe ser válida.',
            'image_url.max' => 'La URL de la imagen no debe exceder 255 caracteres.',
            'images.array' => 'Las imágenes deben ser un array.',
            'images.max' => 'No se pueden subir más de 10 imágenes.',
            'images.*.image' => 'Todos los archivos deben ser imágenes.',
            'images.*.mimes' => 'Las imágenes deben ser en formato: jpeg, png, jpg, gif o webp.',
            'images.*.max' => 'Cada imagen no debe ser mayor a 5 MB.',
            'delete_images.array' => 'Los IDs de imágenes a eliminar deben ser un array.',
            'delete_images.*.exists' => 'Uno o más IDs de imágenes no existen.',
            'is_new.boolean' => 'El campo "nuevo" debe ser verdadero o falso.',
            'is_featured.boolean' => 'El campo "destacado" debe ser verdadero o falso.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }

        $product->update(array_intersect_key($requestData, array_flip([
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
        ])));

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
