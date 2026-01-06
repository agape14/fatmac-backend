<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    /**
     * Display a listing of active categories.
     */
    public function index(): JsonResponse
    {
        $categories = Category::where('is_active', true)
            ->orderBy('order')
            ->get();

        return response()->json([
            'data' => CategoryResource::collection($categories),
        ]);
    }

    /**
     * Display the specified category.
     */
    public function show(string $id): JsonResponse
    {
        $category = Category::findOrFail($id);

        return response()->json([
            'data' => new CategoryResource($category),
        ]);
    }
}

