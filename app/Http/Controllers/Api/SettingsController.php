<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    /**
     * Get a setting value by key.
     */
    public function get(Request $request, string $key): JsonResponse
    {
        $value = Setting::getValue($key);

        return response()->json([
            'data' => [
                'key' => $key,
                'value' => $value,
            ],
        ]);
    }

    /**
     * Get all settings (admin only).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user || $user->role !== 'admin') {
            return response()->json([
                'message' => 'No autorizado. Solo administradores pueden ver todas las configuraciones.',
            ], 403);
        }

        $settings = Setting::all();

        return response()->json([
            'data' => $settings->map(function ($setting) {
                return [
                    'id' => $setting->id,
                    'key' => $setting->key,
                    'value' => $setting->value,
                    'description' => $setting->description,
                ];
            }),
        ]);
    }

    /**
     * Update a setting (admin only).
     */
    public function update(Request $request, string $key): JsonResponse
    {
        $user = $request->user();

        if (!$user || $user->role !== 'admin') {
            return response()->json([
                'message' => 'No autorizado. Solo administradores pueden actualizar configuraciones.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'value' => 'required|string',
        ], [
            'value.required' => 'El valor es obligatorio.',
            'value.string' => 'El valor debe ser texto.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }

        $setting = Setting::setValue($key, $request->value);

        return response()->json([
            'message' => 'Configuración actualizada exitosamente',
            'data' => [
                'id' => $setting->id,
                'key' => $setting->key,
                'value' => $setting->value,
                'description' => $setting->description,
            ],
        ]);
    }
}

