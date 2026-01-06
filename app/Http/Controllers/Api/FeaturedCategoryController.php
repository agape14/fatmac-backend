<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FeaturedCategoryResource;
use App\Models\FeaturedCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class FeaturedCategoryController extends Controller
{
    /**
     * Display a listing of active featured categories.
     */
    public function index(): JsonResponse
    {
        $featuredCategories = FeaturedCategory::with('category')
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        return response()->json([
            'data' => FeaturedCategoryResource::collection($featuredCategories),
        ]);
    }

    /**
     * Store a newly created featured category (Admin only).
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:10',
            'image_url' => 'nullable|url|max:255',
            'background_color' => 'nullable|string|max:255',
            'text_color' => 'nullable|string|max:255',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }

        $featuredCategory = FeaturedCategory::create($request->all());

        return response()->json([
            'message' => 'Categoría destacada creada exitosamente',
            'data' => new FeaturedCategoryResource($featuredCategory->load('category')),
        ], 201);
    }

    /**
     * Update the specified featured category (Admin only).
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $featuredCategory = FeaturedCategory::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'category_id' => 'sometimes|required|exists:categories,id',
            'name' => 'sometimes|required|string|max:255',
            'icon' => 'nullable|string|max:10',
            'image_url' => 'nullable|url|max:255',
            'background_color' => 'nullable|string|max:255',
            'text_color' => 'nullable|string|max:255',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }

        $featuredCategory->update($request->all());

        return response()->json([
            'message' => 'Categoría destacada actualizada exitosamente',
            'data' => new FeaturedCategoryResource($featuredCategory->fresh('category')),
        ]);
    }

    /**
     * Remove the specified featured category (Admin only).
     */
    public function destroy(string $id): JsonResponse
    {
        $featuredCategory = FeaturedCategory::findOrFail($id);
        $featuredCategory->delete();

        return response()->json([
            'message' => 'Categoría destacada eliminada exitosamente',
        ], 200);
    }
}

