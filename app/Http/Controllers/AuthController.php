<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Categoria;
use App\Models\MedioPago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Iniciar sesión y obtener token
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'nullable|string',
        ], [
            'email.required' => 'El correo es requerido',
            'email.email' => 'Ingresa un correo válido',
            'password.required' => 'La contraseña es requerida',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales son incorrectas'],
            ]);
        }

        // Nombre del dispositivo para identificar el token
        $deviceName = $request->device_name ?? $request->userAgent() ?? 'unknown';

        // Crear token que no expira (para persistencia en dispositivo)
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'token' => $token,
        ]);
    }

    /**
     * Registrar nuevo usuario
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
        ], [
            'name.required' => 'El nombre es requerido',
            'email.required' => 'El correo es requerido',
            'email.email' => 'Ingresa un correo valido',
            'email.unique' => 'Este correo ya esta registrado',
            'password.required' => 'La contrasena es requerida',
            'password.min' => 'La contrasena debe tener al menos 6 caracteres',
            'password.confirmed' => 'Las contrasenas no coinciden',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Crear datos por defecto para el nuevo usuario
        $this->crearDatosPorDefecto($user);

        $deviceName = $request->device_name ?? $request->userAgent() ?? 'unknown';
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'token' => $token,
        ], 201);
    }

    /**
     * Crear categorias y medios de pago por defecto para un nuevo usuario
     */
    private function crearDatosPorDefecto(User $user): void
    {
        // Categorias por defecto
        $categorias = [
            ['nombre' => 'Comida', 'icono' => 'utensils', 'color' => '#EF4444', 'orden' => 1],
            ['nombre' => 'Hogar', 'icono' => 'home', 'color' => '#8B5CF6', 'orden' => 2],
            ['nombre' => 'Transporte', 'icono' => 'car', 'color' => '#3B82F6', 'orden' => 3],
            ['nombre' => 'Personal', 'icono' => 'user', 'color' => '#EC4899', 'orden' => 4],
            ['nombre' => 'Prestamo', 'icono' => 'banknotes', 'color' => '#F59E0B', 'orden' => 5],
            ['nombre' => 'Regalos', 'icono' => 'gift', 'color' => '#10B981', 'orden' => 6],
            ['nombre' => 'Servicios', 'icono' => 'zap', 'color' => '#6366F1', 'orden' => 7],
            ['nombre' => 'Viajes', 'icono' => 'plane', 'color' => '#14B8A6', 'orden' => 8],
        ];

        foreach ($categorias as $categoria) {
            Categoria::create([
                ...$categoria,
                'user_id' => $user->id,
                'activo' => true,
            ]);
        }

        // Medios de pago por defecto
        $mediosPago = [
            ['nombre' => 'Efectivo', 'icono' => 'banknote', 'orden' => 1],
            ['nombre' => 'Tarjeta Debito', 'icono' => 'credit-card', 'orden' => 2],
            ['nombre' => 'Tarjeta Credito', 'icono' => 'credit-card', 'orden' => 3],
            ['nombre' => 'Transferencia', 'icono' => 'smartphone', 'orden' => 4],
        ];

        foreach ($mediosPago as $medio) {
            MedioPago::create([
                ...$medio,
                'user_id' => $user->id,
                'activo' => true,
            ]);
        }
    }

    /**
     * Cerrar sesión (revocar token actual)
     */
    public function logout(Request $request)
    {
        // Revocar el token actual
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada correctamente',
        ]);
    }

    /**
     * Cerrar todas las sesiones (revocar todos los tokens)
     */
    public function logoutAll(Request $request)
    {
        // Revocar todos los tokens del usuario
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Todas las sesiones han sido cerradas',
        ]);
    }

    /**
     * Obtener usuario autenticado
     */
    public function me(Request $request)
    {
        return response()->json([
            'user' => [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
            ],
        ]);
    }
}
