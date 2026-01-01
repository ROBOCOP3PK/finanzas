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
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\DataShareController;
use App\Http\Controllers\SharedDataController;
use App\Http\Controllers\PendingExpenseController;
use App\Http\Controllers\ShareNotificationController;
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
    Route::get('/dashboard/por-categoria', [DashboardController::class, 'porCategoria']);
    Route::get('/dashboard/categoria/{categoria}/gastos', [DashboardController::class, 'gastosPorCategoria']);

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
    Route::get('/gastos/exportar', [GastoController::class, 'exportar']);
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

    // Servicios
    Route::get('/servicios/pendientes', [ServicioController::class, 'pendientes']);
    Route::get('/servicios/alertas', [ServicioController::class, 'alertas']);
    Route::put('/servicios/reordenar', [ServicioController::class, 'reordenar']);
    Route::post('/servicios/{servicio}/pagar', [ServicioController::class, 'marcarPagado']);
    Route::delete('/servicios/{servicio}/pagar', [ServicioController::class, 'desmarcarPagado']);
    Route::apiResource('servicios', ServicioController::class);

    // ========================================
    // COMPARTICION DE DATOS
    // ========================================

    // Como propietario - gestionar mi comparticion
    Route::prefix('data-share')->group(function () {
        Route::get('/status', [DataShareController::class, 'status']);
        Route::post('/invite', [DataShareController::class, 'invite']);
        Route::post('/revoke', [DataShareController::class, 'revoke']);
    });

    // Como invitado - datos compartidos conmigo
    Route::prefix('shared-with-me')->group(function () {
        Route::get('/', [DataShareController::class, 'sharedWithMe']);
        Route::post('/{dataShare}/accept', [DataShareController::class, 'accept']);
        Route::post('/{dataShare}/reject', [DataShareController::class, 'reject']);

        // Acceso a datos del propietario
        Route::get('/{dataShare}/dashboard', [SharedDataController::class, 'dashboard']);
        Route::get('/{dataShare}/gastos', [SharedDataController::class, 'gastos']);
        Route::get('/{dataShare}/categorias', [SharedDataController::class, 'categorias']);
        Route::get('/{dataShare}/medios-pago', [SharedDataController::class, 'mediosPago']);
    });

    // Gastos pendientes de aprobacion
    Route::prefix('pending-expenses')->group(function () {
        // Como propietario
        Route::get('/pending', [PendingExpenseController::class, 'pending']);
        Route::get('/history', [PendingExpenseController::class, 'history']);
        Route::get('/{pendingExpense}', [PendingExpenseController::class, 'show']);
        Route::post('/{pendingExpense}/approve', [PendingExpenseController::class, 'approve']);
        Route::post('/{pendingExpense}/reject', [PendingExpenseController::class, 'reject']);

        // Como invitado
        Route::post('/share/{dataShare}', [PendingExpenseController::class, 'store']);
        Route::get('/my-requests', [PendingExpenseController::class, 'myRequests']);
    });

    // Notificaciones de comparticion
    Route::prefix('share-notifications')->group(function () {
        Route::get('/', [ShareNotificationController::class, 'index']);
        Route::get('/unread-count', [ShareNotificationController::class, 'unreadCount']);
        Route::post('/{shareNotification}/read', [ShareNotificationController::class, 'markAsRead']);
        Route::post('/read-all', [ShareNotificationController::class, 'markAllAsRead']);
        Route::delete('/{shareNotification}', [ShareNotificationController::class, 'destroy']);
    });
});
