<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PromotionalBannerResource;
use App\Models\PromotionalBanner;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class PromotionalBannerController extends Controller
{
    /**
     * Display a listing of active promotional banners.
     */
    public function index(): JsonResponse
    {
        $banners = PromotionalBanner::where('is_active', true)
            ->orderBy('order')
            ->get();

        return response()->json([
            'data' => PromotionalBannerResource::collection($banners),
        ]);
    }

    /**
     * Store a newly created promotional banner (Admin only).
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'button_text' => 'nullable|string|max:255',
            'button_link' => 'nullable|string|max:255',
            'image_left_url' => 'nullable|url|max:255',
            'image_right_url' => 'nullable|url|max:255',
            'background_color' => 'nullable|string|max:255',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }

        $banner = PromotionalBanner::create($request->all());

        return response()->json([
            'message' => 'Banner promocional creado exitosamente',
            'data' => new PromotionalBannerResource($banner),
        ], 201);
    }

    /**
     * Update the specified promotional banner (Admin only).
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $banner = PromotionalBanner::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'button_text' => 'nullable|string|max:255',
            'button_link' => 'nullable|string|max:255',
            'image_left_url' => 'nullable|url|max:255',
            'image_right_url' => 'nullable|url|max:255',
            'background_color' => 'nullable|string|max:255',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }

        $banner->update($request->all());

        return response()->json([
            'message' => 'Banner promocional actualizado exitosamente',
            'data' => new PromotionalBannerResource($banner->fresh()),
        ]);
    }

    /**
     * Remove the specified promotional banner (Admin only).
     */
    public function destroy(string $id): JsonResponse
    {
        $banner = PromotionalBanner::findOrFail($id);
        $banner->delete();

        return response()->json([
            'message' => 'Banner promocional eliminado exitosamente',
        ], 200);
    }
}

