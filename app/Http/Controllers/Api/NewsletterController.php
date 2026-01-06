<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscription;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class NewsletterController extends Controller
{
    /**
     * Suscribirse al newsletter (público)
     */
    public function subscribe(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }
        
        // Verificar si ya existe
        $subscription = NewsletterSubscription::where('email', $request->email)->first();
        
        if ($subscription) {
            if ($subscription->is_active) {
                return response()->json([
                    'message' => 'Ya estás suscrito al newsletter',
                ], 200);
            } else {
                // Reactivar suscripción
                $subscription->update([
                    'is_active' => true,
                    'subscribed_at' => now(),
                ]);
                
                return response()->json([
                    'message' => 'Suscripción reactivada exitosamente',
                ], 200);
            }
        }
        
        // Crear nueva suscripción
        $subscription = NewsletterSubscription::create([
            'email' => $request->email,
            'is_active' => true,
            'subscribed_at' => now(),
        ]);
        
        return response()->json([
            'message' => 'Suscripción exitosa',
            'data' => $subscription,
        ], 201);
    }
    
    /**
     * Obtener todas las suscripciones (admin)
     */
    public function index(): JsonResponse
    {
        $subscriptions = NewsletterSubscription::where('is_active', true)
            ->orderBy('subscribed_at', 'desc')
            ->get();
        
        return response()->json([
            'data' => $subscriptions,
        ]);
    }
    
    /**
     * Cancelar suscripción
     */
    public function unsubscribe(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }
        
        $subscription = NewsletterSubscription::where('email', $request->email)->first();
        
        if ($subscription) {
            $subscription->update(['is_active' => false]);
            
            return response()->json([
                'message' => 'Suscripción cancelada',
            ]);
        }
        
        return response()->json([
            'message' => 'No se encontró la suscripción',
        ], 404);
    }
}
