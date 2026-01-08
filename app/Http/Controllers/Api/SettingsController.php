<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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

    /**
     * Upload logo image (admin only).
     */
    public function uploadLogo(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user || $user->role !== 'admin') {
            return response()->json([
                'message' => 'No autorizado. Solo administradores pueden subir el logo.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'logo' => 'required|image|mimes:jpeg,png,jpg,svg|max:2048',
        ], [
            'logo.required' => 'La imagen del logo es obligatoria.',
            'logo.image' => 'El archivo debe ser una imagen.',
            'logo.mimes' => 'El logo debe ser un archivo de tipo: jpeg, png, jpg o svg.',
            'logo.max' => 'El logo no debe ser mayor a 2MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Eliminar el logo anterior si existe
            $oldLogo = Setting::getValue('logo_url');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }

            // Guardar el nuevo logo
            $logoPath = $request->file('logo')->store('logos', 'public');
            $logoUrl = asset('storage/' . $logoPath);

            // Guardar la URL en settings
            $setting = Setting::setValue('logo_url', $logoUrl, 'URL del logo del sitio');

            return response()->json([
                'message' => 'Logo actualizado exitosamente',
                'data' => [
                    'url' => $logoUrl,
                    'path' => $logoPath,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al subir el logo: ' . $e->getMessage(),
            ], 500);
        }
    }
}

