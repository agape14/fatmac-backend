<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\VendorRegistrationNotificationMail;
use App\Mail\VendorStatusChangedMail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class VendorController extends Controller
{
    /**
     * Get all vendors (pending, approved, rejected).
     * Solo administradores pueden ver todos los vendedores.
     */
    public function index(Request $request): JsonResponse
    {
        $admin = $request->user();

        if ($admin->role !== 'admin') {
            return response()->json([
                'message' => 'No autorizado. Solo administradores pueden ver vendedores.',
            ], 403);
        }

        $query = User::where('role', 'vendor');

        // Filtrar por estado si se proporciona
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $vendors = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'data' => $vendors->map(function ($vendor) {
                return [
                    'id' => $vendor->id,
                    'name' => $vendor->name,
                    'email' => $vendor->email,
                    'phone_number' => $vendor->phone_number,
                    'whatsapp_number' => $vendor->whatsapp_number,
                    'business_description' => $vendor->business_description,
                    'business_address' => $vendor->business_address,
                    'status' => $vendor->status,
                    'created_at' => $vendor->created_at?->toISOString(),
                    'updated_at' => $vendor->updated_at?->toISOString(),
                ];
            }),
            'meta' => [
                'current_page' => $vendors->currentPage(),
                'last_page' => $vendors->lastPage(),
                'per_page' => $vendors->perPage(),
                'total' => $vendors->total(),
            ],
        ]);
    }

    /**
     * Update vendor status (approve or reject).
     * Solo administradores pueden aprobar/rechazar vendedores.
     */
    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $admin = $request->user();

        if ($admin->role !== 'admin') {
            return response()->json([
                'message' => 'No autorizado. Solo administradores pueden aprobar/rechazar vendedores.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:approved,rejected',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }

        $vendor = User::where('role', 'vendor')->findOrFail($id);
        $oldStatus = $vendor->status;
        $vendor->status = $request->status;
        $vendor->save();

        // Enviar email de notificación al vendedor
        try {
            Mail::to($vendor->email)->send(new VendorStatusChangedMail($vendor, $request->status));
        } catch (\Exception $e) {
            \Log::error('Error al enviar email de notificación al vendedor: ' . $e->getMessage());
        }

        $message = $request->status === 'approved'
            ? 'Vendedor aprobado exitosamente'
            : 'Vendedor rechazado exitosamente';

        return response()->json([
            'message' => $message,
            'data' => [
                'id' => $vendor->id,
                'name' => $vendor->name,
                'email' => $vendor->email,
                'status' => $vendor->status,
            ],
        ]);
    }

    /**
     * Get pending vendors count.
     * Para mostrar en el dashboard del admin.
     */
    public function pendingCount(Request $request): JsonResponse
    {
        $admin = $request->user();

        if ($admin->role !== 'admin') {
            return response()->json([
                'message' => 'No autorizado.',
            ], 403);
        }

        $count = User::where('role', 'vendor')
            ->where('status', 'pending')
            ->count();

        return response()->json([
            'data' => [
                'pending_count' => $count,
            ],
        ]);
    }

    /**
     * Update vendor profile.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $vendor = $request->user();

        if ($vendor->role !== 'vendor') {
            return response()->json([
                'message' => 'No autorizado. Solo vendedores pueden actualizar su perfil.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'phone_number' => 'sometimes|nullable|string|max:20',
            'whatsapp_number' => 'sometimes|nullable|string|max:20',
            'business_description' => 'sometimes|nullable|string',
            'business_address' => 'sometimes|nullable|string|max:255',
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'name.string' => 'El nombre debe ser texto.',
            'name.max' => 'El nombre no debe exceder 255 caracteres.',
            'phone_number.string' => 'El teléfono debe ser texto.',
            'phone_number.max' => 'El teléfono no debe exceder 20 caracteres.',
            'whatsapp_number.string' => 'El número de WhatsApp debe ser texto.',
            'whatsapp_number.max' => 'El número de WhatsApp no debe exceder 20 caracteres.',
            'business_description.string' => 'La descripción del negocio debe ser texto.',
            'business_address.string' => 'La dirección del negocio debe ser texto.',
            'business_address.max' => 'La dirección del negocio no debe exceder 255 caracteres.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }

        $vendor->update($request->only([
            'name',
            'phone_number',
            'whatsapp_number',
            'business_description',
            'business_address',
        ]));

        return response()->json([
            'message' => 'Perfil actualizado exitosamente',
            'data' => [
                'id' => $vendor->id,
                'name' => $vendor->name,
                'email' => $vendor->email,
                'phone_number' => $vendor->phone_number,
                'whatsapp_number' => $vendor->whatsapp_number,
                'business_description' => $vendor->business_description,
                'business_address' => $vendor->business_address,
                'yape_qr' => $vendor->yape_qr ? asset('storage/' . $vendor->yape_qr) : null,
                'plin_qr' => $vendor->plin_qr ? asset('storage/' . $vendor->plin_qr) : null,
            ],
        ]);
    }

    /**
     * Upload QR code for payment method.
     */
    public function uploadQr(Request $request): JsonResponse
    {
        $vendor = $request->user();

        if ($vendor->role !== 'vendor') {
            return response()->json([
                'message' => 'No autorizado. Solo vendedores pueden subir códigos QR.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'type' => 'required|in:yape,plin',
            'qr_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
        ], [
            'type.required' => 'El tipo de código QR es obligatorio.',
            'type.in' => 'El tipo debe ser Yape o Plin.',
            'qr_image.required' => 'La imagen del código QR es obligatoria.',
            'qr_image.image' => 'El archivo debe ser una imagen.',
            'qr_image.mimes' => 'La imagen debe ser en formato: jpeg, png, jpg o gif.',
            'qr_image.max' => 'La imagen no debe ser mayor a 5 MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Eliminar QR anterior si existe
        $qrField = $request->type === 'yape' ? 'yape_qr' : 'plin_qr';
        if ($vendor->$qrField) {
            Storage::disk('public')->delete($vendor->$qrField);
        }

        // Guardar nuevo QR
        $path = $request->file('qr_image')->store('vendor-qrs', 'public');
        $vendor->$qrField = $path;
        $vendor->save();

        return response()->json([
            'message' => 'Código QR subido exitosamente',
            'data' => [
                'type' => $request->type,
                'qr_url' => asset('storage/' . $path),
            ],
        ]);
    }

    /**
     * Update vendor basic info by admin.
     * Solo administradores pueden actualizar datos básicos de vendedores.
     * NO permite actualizar email ni password.
     */
    public function updateByAdmin(Request $request, string $id): JsonResponse
    {
        $admin = $request->user();

        if ($admin->role !== 'admin') {
            return response()->json([
                'message' => 'No autorizado. Solo administradores pueden actualizar vendedores.',
            ], 403);
        }

        $vendor = User::where('role', 'vendor')->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'phone_number' => 'sometimes|nullable|string|max:20',
            'whatsapp_number' => 'sometimes|nullable|string|max:20',
            'business_description' => 'sometimes|nullable|string',
            'business_address' => 'sometimes|nullable|string|max:255',
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'name.string' => 'El nombre debe ser texto.',
            'name.max' => 'El nombre no debe exceder 255 caracteres.',
            'phone_number.string' => 'El teléfono debe ser texto.',
            'phone_number.max' => 'El teléfono no debe exceder 20 caracteres.',
            'whatsapp_number.string' => 'El número de WhatsApp debe ser texto.',
            'whatsapp_number.max' => 'El número de WhatsApp no debe exceder 20 caracteres.',
            'business_description.string' => 'La descripción del negocio debe ser texto.',
            'business_address.string' => 'La dirección del negocio debe ser texto.',
            'business_address.max' => 'La dirección del negocio no debe exceder 255 caracteres.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }

        $vendor->update($request->only([
            'name',
            'phone_number',
            'whatsapp_number',
            'business_description',
            'business_address',
        ]));

        return response()->json([
            'message' => 'Vendedor actualizado exitosamente',
            'data' => [
                'id' => $vendor->id,
                'name' => $vendor->name,
                'email' => $vendor->email, // Solo lectura
                'phone_number' => $vendor->phone_number,
                'whatsapp_number' => $vendor->whatsapp_number,
                'business_description' => $vendor->business_description,
                'business_address' => $vendor->business_address,
                'status' => $vendor->status,
            ],
        ]);
    }
}

