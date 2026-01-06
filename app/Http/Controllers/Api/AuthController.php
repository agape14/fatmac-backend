<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
        return response()->json([
            'data' => [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
                'phone_number' => $request->user()->phone_number,
                'role' => $request->user()->role,
                'status' => $request->user()->status,
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
}

