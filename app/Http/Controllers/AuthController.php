<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Categoria;
use App\Models\MedioPago;
use App\Models\VerificationCode;
use App\Mail\VerificationCodeMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Iniciar sesion y obtener token
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'nullable|string',
        ], [
            'email.required' => 'El correo es requerido',
            'email.email' => 'Ingresa un correo valido',
            'password.required' => 'La contrasena es requerida',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales son incorrectas'],
            ]);
        }

        $deviceName = $request->device_name ?? $request->userAgent() ?? 'unknown';
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
     * Paso 1: Enviar codigo de verificacion al email
     */
    public function sendVerificationCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'El correo es requerido',
            'email.email' => 'Ingresa un correo valido',
        ]);

        // Verificar si el email ya esta registrado
        if (User::where('email', $request->email)->exists()) {
            throw ValidationException::withMessages([
                'email' => ['Este correo ya esta registrado'],
            ]);
        }

        // Generar y enviar codigo
        $verification = VerificationCode::generateFor($request->email, 'register', 10);

        try {
            Mail::to($request->email)->send(new VerificationCodeMail($verification->code, 10));
        } catch (\Exception $e) {
            // Log del error para debugging
            \Log::error('Error enviando email de verificacion: ' . $e->getMessage());

            throw ValidationException::withMessages([
                'email' => ['Error al enviar el codigo. Intenta de nuevo.'],
            ]);
        }

        return response()->json([
            'message' => 'Codigo enviado correctamente',
            'expires_in' => 10, // minutos
        ]);
    }

    /**
     * Paso 2: Verificar el codigo
     */
    public function verifyCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6',
        ], [
            'email.required' => 'El correo es requerido',
            'code.required' => 'El codigo es requerido',
            'code.size' => 'El codigo debe tener 6 digitos',
        ]);

        $verification = VerificationCode::verify($request->email, $request->code, 'register');

        if (!$verification) {
            throw ValidationException::withMessages([
                'code' => ['Codigo invalido o expirado'],
            ]);
        }

        return response()->json([
            'message' => 'Codigo verificado correctamente',
            'verified' => true,
        ]);
    }

    /**
     * Paso 3: Completar registro (despues de verificar codigo)
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

        // Verificar que el email haya sido verificado
        if (!VerificationCode::isVerified($request->email, 'register')) {
            throw ValidationException::withMessages([
                'email' => ['Debes verificar tu correo primero'],
            ]);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Crear datos por defecto para el nuevo usuario
        $this->crearDatosPorDefecto($user);

        // Limpiar codigos de verificacion usados
        VerificationCode::where('email', $request->email)->delete();

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
     * Reenviar codigo de verificacion
     */
    public function resendVerificationCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        // Verificar si el email ya esta registrado
        if (User::where('email', $request->email)->exists()) {
            throw ValidationException::withMessages([
                'email' => ['Este correo ya esta registrado'],
            ]);
        }

        // Generar y enviar nuevo codigo
        $verification = VerificationCode::generateFor($request->email, 'register', 10);

        try {
            Mail::to($request->email)->send(new VerificationCodeMail($verification->code, 10));
        } catch (\Exception $e) {
            \Log::error('Error reenviando email de verificacion: ' . $e->getMessage());

            throw ValidationException::withMessages([
                'email' => ['Error al enviar el codigo. Intenta de nuevo.'],
            ]);
        }

        return response()->json([
            'message' => 'Codigo reenviado correctamente',
            'expires_in' => 10,
        ]);
    }

    /**
     * Solicitar recuperacion de contrasena
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'El correo es requerido',
            'email.email' => 'Ingresa un correo valido',
        ]);

        // Verificar si el email existe
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['No existe una cuenta con este correo'],
            ]);
        }

        // Generar y enviar codigo
        $verification = VerificationCode::generateFor($request->email, 'password_reset', 10);

        try {
            Mail::to($request->email)->send(new \App\Mail\PasswordResetMail($verification->code, 10));
        } catch (\Exception $e) {
            \Log::error('Error enviando email de recuperacion: ' . $e->getMessage());

            throw ValidationException::withMessages([
                'email' => ['Error al enviar el codigo. Intenta de nuevo.'],
            ]);
        }

        return response()->json([
            'message' => 'Codigo enviado correctamente',
            'expires_in' => 10,
        ]);
    }

    /**
     * Restablecer contrasena con codigo
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6',
            'password' => 'required|min:6|confirmed',
        ], [
            'email.required' => 'El correo es requerido',
            'email.email' => 'Ingresa un correo valido',
            'code.required' => 'El codigo es requerido',
            'code.size' => 'El codigo debe tener 6 digitos',
            'password.required' => 'La contrasena es requerida',
            'password.min' => 'La contrasena debe tener al menos 6 caracteres',
            'password.confirmed' => 'Las contrasenas no coinciden',
        ]);

        // Verificar el codigo
        $verification = VerificationCode::verify($request->email, $request->code, 'password_reset');

        if (!$verification) {
            throw ValidationException::withMessages([
                'code' => ['Codigo invalido o expirado'],
            ]);
        }

        // Buscar usuario y actualizar contrasena
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['No existe una cuenta con este correo'],
            ]);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        // Revocar todos los tokens existentes por seguridad
        $user->tokens()->delete();

        // Limpiar codigos de verificacion usados
        VerificationCode::where('email', $request->email)
            ->where('type', 'password_reset')
            ->delete();

        return response()->json([
            'message' => 'Contrasena actualizada correctamente',
        ]);
    }

    /**
     * Crear categorias, medios de pago y servicios por defecto para un nuevo usuario
     */
    private function crearDatosPorDefecto(User $user): void
    {
        \Database\Seeders\CategoriaSeeder::crearParaUsuario($user->id);
        \Database\Seeders\MedioPagoSeeder::crearParaUsuario($user->id);
        \Database\Seeders\ServicioSeeder::crearParaUsuario($user->id);
    }

    /**
     * Cerrar sesion (revocar token actual)
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesion cerrada correctamente',
        ]);
    }

    /**
     * Cerrar todas las sesiones (revocar todos los tokens)
     */
    public function logoutAll(Request $request)
    {
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

    /**
     * Restablecer todos los datos del usuario (borron y cuenta nueva)
     */
    public function resetUserData(Request $request)
    {
        $user = $request->user();

        // Eliminar todos los datos relacionados al usuario
        $user->gastos()->delete();
        $user->abonos()->delete();
        $user->plantillas()->delete();
        $user->gastosRecurrentes()->delete();
        $user->conceptosFrecuentes()->delete();
        $user->categorias()->delete();
        $user->mediosPago()->delete();

        // Recrear datos por defecto
        $this->crearDatosPorDefecto($user);

        return response()->json([
            'message' => 'Todos los datos han sido restablecidos correctamente',
        ]);
    }
}
