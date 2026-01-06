<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics for the authenticated vendor or admin.
     */
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();

        // Verificar que el usuario sea un vendor o admin
        if (!in_array($user->role, ['vendor', 'admin'])) {
            return response()->json([
                'message' => 'No autorizado. Solo vendedores y administradores pueden ver estadísticas.',
            ], 403);
        }

        // Si es admin, ver todas las estadísticas; si es vendor, solo las suyas
        $productQuery = Product::query();
        $orderQuery = Order::query();

        if ($user->role === 'vendor') {
            $productQuery->where('user_id', $user->id);
            $orderQuery->where('vendor_id', $user->id);
        }
        // Si es admin, no aplicamos filtros (ver todo)

        // Estadísticas de productos
        $totalProducts = $productQuery->count();
        $productsInStock = (clone $productQuery)->where('stock', '>', 0)->count();
        $productsOutOfStock = (clone $productQuery)->where(function($query) {
            $query->where('stock', '<=', 0)
                  ->orWhereNull('stock');
        })->count();

        // Estadísticas de pedidos
        $totalOrders = $orderQuery->count();
        $pendingOrders = (clone $orderQuery)->where('status', 'pending')->count();
        $paidOrders = (clone $orderQuery)->where('status', 'paid')->count();
        $rejectedOrders = (clone $orderQuery)->where('status', 'rejected')->count();

        // Total de ventas (solo pedidos pagados)
        $totalSales = (clone $orderQuery)
            ->where('status', 'paid')
            ->sum('total_price');

        // Ventas del último mes
        $salesLastMonth = (clone $orderQuery)
            ->where('status', 'paid')
            ->where('created_at', '>=', now()->subMonth())
            ->sum('total_price');

        return response()->json([
            'data' => [
                'products' => [
                    'total' => $totalProducts,
                    'in_stock' => $productsInStock,
                    'out_of_stock' => $productsOutOfStock,
                ],
                'orders' => [
                    'total' => $totalOrders,
                    'pending' => $pendingOrders,
                    'paid' => $paidOrders,
                    'rejected' => $rejectedOrders,
                ],
                'sales' => [
                    'total' => (float) $totalSales,
                    'last_month' => (float) $salesLastMonth,
                ],
            ],
        ]);
    }
}

