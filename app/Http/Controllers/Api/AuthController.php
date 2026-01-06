<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\VendorRegistrationConfirmationMail;
use App\Mail\VendorRegistrationNotificationMail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user.
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone_number' => 'nullable|string|max:20',
            'role' => 'nullable|in:customer,vendor,admin',
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'name.string' => 'El nombre debe ser texto.',
            'name.max' => 'El nombre no debe exceder 255 caracteres.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Debe ser un correo electrónico válido.',
            'email.max' => 'El correo electrónico no debe exceder 255 caracteres.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'phone_number.string' => 'El teléfono debe ser texto.',
            'phone_number.max' => 'El teléfono no debe exceder 20 caracteres.',
            'role.in' => 'El rol debe ser cliente, vendedor o administrador.',
        ]);

        // Si el rol es vendor, el status será 'pending' (requiere aprobación)
        // Si el rol es customer o admin, el status será 'approved' por defecto
        $role = $request->role ?? 'customer';
        $status = ($role === 'vendor') ? 'pending' : 'approved';

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone_number' => $request->phone_number,
            'role' => $role,
            'status' => $status,
        ]);

        // Si es un vendedor, enviar notificaciones
        if ($role === 'vendor' && $status === 'pending') {
            // Enviar email de confirmación al vendedor
            try {
                Mail::to($user->email)->send(new VendorRegistrationConfirmationMail($user));
            } catch (\Exception $e) {
                \Log::error('Error al enviar email de confirmación al vendedor: ' . $e->getMessage());
            }

            // Enviar notificación a los administradores
            try {
                $admins = User::where('role', 'admin')->get();
                foreach ($admins as $admin) {
                    Mail::to($admin->email)->send(new VendorRegistrationNotificationMail($user));
                }
            } catch (\Exception $e) {
                \Log::error('Error al enviar notificación a administradores: ' . $e->getMessage());
            }
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        $message = ($role === 'vendor' && $status === 'pending')
            ? 'Usuario registrado exitosamente. Tu cuenta está pendiente de aprobación.'
            : 'Usuario registrado exitosamente';

        return response()->json([
            'message' => $message,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                    'role' => $user->role,
                    'status' => $user->status,
                ],
                'token' => $token,
            ],
        ], 201);
    }

    /**
     * Login user and create token.
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Debe ser un correo electrónico válido.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Credenciales incorrectas',
            ], 401);
        }

        // Verificar que el usuario esté aprobado (si es vendor)
        if ($user->role === 'vendor' && $user->status !== 'approved') {
            return response()->json([
                'message' => 'Tu cuenta está pendiente de aprobación. Por favor, espera la aprobación del administrador.',
            ], 403);
        }

        // Revocar todos los tokens anteriores del usuario (opcional, para seguridad)
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login exitoso',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                    'role' => $user->role,
                    'status' => $user->status,
                ],
                'token' => $token,
            ],
        ]);
    }

    /**
     * Logout user (Revoke the token).
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada exitosamente',
        ]);
    }

    /**
     * Get authenticated user.
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user();
        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'whatsapp_number' => $user->whatsapp_number,
                'role' => $user->role,
                'status' => $user->status,
                'yape_qr' => $user->yape_qr ? asset('storage/' . $user->yape_qr) : null,
                'plin_qr' => $user->plin_qr ? asset('storage/' . $user->plin_qr) : null,
                'business_description' => $user->business_description,
                'business_address' => $user->business_address,
            ],
        ]);
    }

    /**
     * Update user profile.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone_number' => 'nullable|string|max:20',
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'name.string' => 'El nombre debe ser texto.',
            'name.max' => 'El nombre no debe exceder 255 caracteres.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Debe ser un correo electrónico válido.',
            'email.max' => 'El correo electrónico no debe exceder 255 caracteres.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'phone_number.string' => 'El teléfono debe ser texto.',
            'phone_number.max' => 'El teléfono no debe exceder 20 caracteres.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
        ]);

        return response()->json([
            'message' => 'Perfil actualizado exitosamente',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'role' => $user->role,
                'status' => $user->status,
            ],
        ]);
    }

    /**
     * Change user password.
     */
    public function changePassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'current_password.required' => 'La contraseña actual es requerida',
            'password.required' => 'La nueva contraseña es requerida',
            'password.min' => 'La nueva contraseña debe tener al menos 8 caracteres',
            'password.confirmed' => 'Las contraseñas no coinciden',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();

        // Verificar contraseña actual
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'La contraseña actual es incorrecta',
                'errors' => [
                    'current_password' => ['La contraseña actual es incorrecta'],
                ],
            ], 422);
        }

        // Actualizar contraseña
        $user->update([
            'password' => Hash::make($request->password),
            'must_change_password' => false, // Ya no necesita cambiar la contraseña
        ]);

        return response()->json([
            'message' => 'Contraseña actualizada exitosamente',
        ]);
    }

    /**
     * Register a new vendor (public endpoint).
     */
    public function registerVendor(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone_number' => 'required|string|max:20',
            'whatsapp_number' => 'nullable|string|max:20',
            'business_description' => 'nullable|string',
            'business_address' => 'nullable|string|max:255',
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'name.string' => 'El nombre debe ser texto.',
            'name.max' => 'El nombre no debe exceder 255 caracteres.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Debe ser un correo electrónico válido.',
            'email.max' => 'El correo electrónico no debe exceder 255 caracteres.',
            'email.unique' => 'Este correo electrónico ya está registrado. Por favor, inicia sesión o utiliza otro correo.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'phone_number.required' => 'El teléfono es obligatorio.',
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

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone_number' => $request->phone_number,
            'whatsapp_number' => $request->whatsapp_number,
            'business_description' => $request->business_description,
            'business_address' => $request->business_address,
            'role' => 'vendor',
            'status' => 'pending', // Requiere aprobación
        ]);

        // Enviar email de confirmación al vendedor
        try {
            Mail::to($user->email)->send(new VendorRegistrationConfirmationMail($user));
        } catch (\Exception $e) {
            \Log::error('Error al enviar email de confirmación al vendedor: ' . $e->getMessage());
        }

        // Enviar notificación a los administradores
        try {
            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                Mail::to($admin->email)->send(new VendorRegistrationNotificationMail($user));
            }
        } catch (\Exception $e) {
            \Log::error('Error al enviar notificación a administradores: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Registro exitoso. Tu solicitud está en proceso de evaluación. Te notificaremos por correo cuando sea aprobada.',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                    'role' => $user->role,
                    'status' => $user->status,
                ],
            ],
        ], 201);
    }
}

