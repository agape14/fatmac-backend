<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MenuItemResource;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class MenuItemController extends Controller
{
    /**
     * Display a listing of active menu items.
     */
    public function index(): JsonResponse
    {
        $menuItems = MenuItem::where('is_active', true)
            ->orderBy('order')
            ->get();

        return response()->json([
            'data' => MenuItemResource::collection($menuItems),
        ]);
    }

    /**
     * Store a newly created menu item (Admin only).
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'label' => 'required|string|max:255',
            'path' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:menu_items',
            'icon' => 'nullable|string|max:10',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'type' => 'required|in:category,page,custom',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }

        $menuItem = MenuItem::create($request->all());

        return response()->json([
            'message' => 'Item de menú creado exitosamente',
            'data' => new MenuItemResource($menuItem),
        ], 201);
    }

    /**
     * Update the specified menu item (Admin only).
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $menuItem = MenuItem::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'label' => 'sometimes|required|string|max:255',
            'path' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|max:255|unique:menu_items,slug,' . $id,
            'icon' => 'nullable|string|max:10',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'type' => 'sometimes|required|in:category,page,custom',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }

        $menuItem->update($request->all());

        return response()->json([
            'message' => 'Item de menú actualizado exitosamente',
            'data' => new MenuItemResource($menuItem->fresh()),
        ]);
    }

    /**
     * Remove the specified menu item (Admin only).
     */
    public function destroy(string $id): JsonResponse
    {
        $menuItem = MenuItem::findOrFail($id);
        $menuItem->delete();

        return response()->json([
            'message' => 'Item de menú eliminado exitosamente',
        ], 200);
    }
}

