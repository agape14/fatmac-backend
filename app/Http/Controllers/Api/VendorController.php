<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
        $vendor->status = $request->status;
        $vendor->save();

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
}

