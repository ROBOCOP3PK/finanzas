<?php

use App\Http\Controllers\AbonoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ConceptoFrecuenteController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GastoController;
use App\Http\Controllers\GastoRecurrenteController;
use App\Http\Controllers\MedioPagoController;
use App\Http\Controllers\PlantillaController;
use Illuminate\Support\Facades\Route;

// Rutas públicas de autenticación
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/send-code', [AuthController::class, 'sendVerificationCode']);
Route::post('/auth/verify-code', [AuthController::class, 'verifyCode']);
Route::post('/auth/resend-code', [AuthController::class, 'resendVerificationCode']);
Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);

// Rutas protegidas
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/logout-all', [AuthController::class, 'logoutAll']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/reset-user-data', [AuthController::class, 'resetUserData']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/dashboard/saldo', [DashboardController::class, 'saldo']);
    Route::get('/dashboard/resumen-mes', [DashboardController::class, 'resumenMes']);

    // Configuración
    Route::get('/configuracion', [ConfiguracionController::class, 'index']);
    Route::put('/configuracion', [ConfiguracionController::class, 'update']);

    // Medios de Pago
    Route::put('/medios-pago/reordenar', [MedioPagoController::class, 'reordenar']);
    Route::apiResource('medios-pago', MedioPagoController::class)->parameters([
        'medios-pago' => 'medioPago'
    ]);

    // Categorías
    Route::put('/categorias/reordenar', [CategoriaController::class, 'reordenar']);
    Route::apiResource('categorias', CategoriaController::class);

    // Gastos
    Route::apiResource('gastos', GastoController::class);

    // Abonos
    Route::apiResource('abonos', AbonoController::class);

    // Conceptos Frecuentes
    Route::get('/conceptos-frecuentes', [ConceptoFrecuenteController::class, 'index']);
    Route::get('/conceptos-frecuentes/buscar', [ConceptoFrecuenteController::class, 'buscar']);
    Route::put('/conceptos-frecuentes/{conceptoFrecuente}/favorito', [ConceptoFrecuenteController::class, 'toggleFavorito']);
    Route::delete('/conceptos-frecuentes/{conceptoFrecuente}', [ConceptoFrecuenteController::class, 'destroy']);

    // Plantillas
    Route::get('/plantillas/rapidas', [PlantillaController::class, 'rapidas']);
    Route::put('/plantillas/reordenar', [PlantillaController::class, 'reordenar']);
    Route::post('/plantillas/{plantilla}/usar', [PlantillaController::class, 'usar']);
    Route::apiResource('plantillas', PlantillaController::class);

    // Gastos Recurrentes
    Route::get('/gastos-recurrentes/pendientes', [GastoRecurrenteController::class, 'pendientes']);
    Route::post('/gastos-recurrentes/registrar-pendientes', [GastoRecurrenteController::class, 'registrarPendientes']);
    Route::post('/gastos-recurrentes/{gastoRecurrente}/registrar', [GastoRecurrenteController::class, 'registrar']);
    Route::apiResource('gastos-recurrentes', GastoRecurrenteController::class)->parameters([
        'gastos-recurrentes' => 'gastoRecurrente'
    ]);
});
