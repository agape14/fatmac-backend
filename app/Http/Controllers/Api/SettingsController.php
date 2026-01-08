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
            if ($oldLogo) {
                // Extraer el path relativo de la URL anterior
                $oldPath = str_replace(asset('storage/'), '', $oldLogo);
                $oldPath = str_replace(env('APP_URL') . '/storage/', '', $oldPath);
                if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            // Asegurar que el directorio logos existe con permisos correctos
            $logosDir = Storage::disk('public')->path('logos');
            if (!is_dir($logosDir)) {
                mkdir($logosDir, 0775, true);
            }
            // Asegurar permisos del directorio
            if (is_dir($logosDir) && is_writable($logosDir)) {
                @chmod($logosDir, 0775);
            }

            // Guardar el nuevo logo
            $logoPath = $request->file('logo')->store('logos', 'public');

            // Configurar permisos correctos del archivo guardado
            $fullPath = Storage::disk('public')->path($logoPath);
            if (file_exists($fullPath)) {
                // Establecer permisos: 0644 (rw-r--r--) es suficiente para lectura web
                // Pero 0664 (rw-rw-r--) permite que el grupo también pueda escribir
                @chmod($fullPath, 0664);

                // Intentar cambiar el propietario si tenemos permisos (solo funciona como root)
                // En producción, esto normalmente se hace manualmente o con scripts
                if (function_exists('posix_geteuid') && posix_geteuid() === 0) {
                    @chown($fullPath, 'www-data');
                    if (function_exists('posix_getgrnam')) {
                        $groupInfo = @posix_getgrnam('www-data');
                        if ($groupInfo) {
                            @chgrp($fullPath, 'www-data');
                        }
                    }
                }
            }

            // Usar Storage::url() para generar la URL absoluta correcta
            $logoUrl = Storage::disk('public')->url($logoPath);

            // Asegurar que la URL sea absoluta (con dominio completo)
            if (!filter_var($logoUrl, FILTER_VALIDATE_URL)) {
                // Si no es una URL absoluta, construirla usando APP_URL
                $appUrl = rtrim(env('APP_URL', 'http://localhost'), '/');
                $logoUrl = $appUrl . '/storage/' . $logoPath;
            }

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

