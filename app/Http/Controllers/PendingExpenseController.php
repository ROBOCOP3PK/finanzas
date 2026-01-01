<?php

namespace App\Http\Controllers;

use App\Models\DataShare;
use App\Models\PendingExpense;
use App\Models\ShareNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PendingExpenseController extends Controller
{
    /**
     * Crear solicitud de gasto (como invitado)
     */
    public function store(Request $request, DataShare $dataShare): JsonResponse
    {
        $user = $request->user();

        // Verificar acceso
        if ($dataShare->guest_id !== $user->id || $dataShare->status !== DataShare::STATUS_ACCEPTED) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes acceso para crear gastos'
            ], 403);
        }

        $validated = $request->validate([
            'fecha' => 'nullable|date',
            'medio_pago_id' => 'nullable|integer|exists:medios_pago,id',
            'categoria_id' => 'nullable|integer|exists:categorias,id',
            'concepto' => 'required|string|max:255',
            'valor' => 'required|numeric|min:0.01',
            'tipo' => 'nullable|in:personal,pareja,compartido'
        ]);

        $pendingExpense = PendingExpense::create([
            'data_share_id' => $dataShare->id,
            'created_by' => $user->id,
            'owner_id' => $dataShare->owner_id,
            'fecha' => $validated['fecha'] ?? now()->toDateString(),
            'medio_pago_id' => $validated['medio_pago_id'] ?? null,
            'categoria_id' => $validated['categoria_id'] ?? null,
            'concepto' => $validated['concepto'],
            'valor' => $validated['valor'],
            'tipo' => $validated['tipo'] ?? 'personal',
            'status' => PendingExpense::STATUS_PENDING
        ]);

        // Notificar al propietario
        ShareNotification::createExpenseRequest($pendingExpense, $user);

        return response()->json([
            'success' => true,
            'data' => $pendingExpense->load(['medioPago', 'categoria']),
            'message' => 'Solicitud enviada. Esperando aprobacion del propietario.'
        ], 201);
    }

    /**
     * Listar solicitudes pendientes (como propietario)
     */
    public function pending(Request $request): JsonResponse
    {
        $user = $request->user();

        $pending = PendingExpense::where('owner_id', $user->id)
            ->where('status', PendingExpense::STATUS_PENDING)
            ->with(['createdBy:id,name,email', 'medioPago', 'categoria'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $pending,
            'count' => $pending->count()
        ]);
    }

    /**
     * Obtener una solicitud específica (como propietario)
     */
    public function show(Request $request, PendingExpense $pendingExpense): JsonResponse
    {
        $user = $request->user();

        if ($pendingExpense->owner_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $pendingExpense->load(['createdBy:id,name,email', 'medioPago', 'categoria'])
        ]);
    }

    /**
     * Aprobar solicitud (como propietario)
     * Acepta datos editados opcionales para modificar el gasto antes de aprobarlo
     */
    public function approve(Request $request, PendingExpense $pendingExpense): JsonResponse
    {
        $user = $request->user();

        if ($pendingExpense->owner_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        if ($pendingExpense->status !== PendingExpense::STATUS_PENDING) {
            return response()->json([
                'success' => false,
                'message' => 'Esta solicitud ya fue procesada'
            ], 422);
        }

        // Validar datos editados si se envían
        $editedData = null;
        if ($request->has('fecha') || $request->has('concepto') || $request->has('valor')) {
            $editedData = $request->validate([
                'fecha' => 'nullable|date',
                'medio_pago_id' => 'nullable|integer|exists:medios_pago,id',
                'categoria_id' => 'nullable|integer|exists:categorias,id',
                'concepto' => 'nullable|string|max:255',
                'valor' => 'nullable|numeric|min:0.01',
                'tipo' => 'nullable|in:personal,pareja,compartido'
            ]);
        }

        $gasto = $pendingExpense->approve($editedData);

        // Notificar al invitado
        ShareNotification::createExpenseApproved($pendingExpense);

        // Marcar notificacion original como leida
        ShareNotification::where('notifiable_type', PendingExpense::class)
            ->where('notifiable_id', $pendingExpense->id)
            ->where('user_id', $user->id)
            ->where('type', ShareNotification::TYPE_EXPENSE_REQUEST)
            ->update(['read' => true, 'read_at' => now()]);

        return response()->json([
            'success' => true,
            'data' => $gasto->load(['medioPago', 'categoria']),
            'message' => 'Gasto aprobado y registrado'
        ]);
    }

    /**
     * Rechazar solicitud (como propietario)
     */
    public function reject(Request $request, PendingExpense $pendingExpense): JsonResponse
    {
        $user = $request->user();

        if ($pendingExpense->owner_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        if ($pendingExpense->status !== PendingExpense::STATUS_PENDING) {
            return response()->json([
                'success' => false,
                'message' => 'Esta solicitud ya fue procesada'
            ], 422);
        }

        $request->validate([
            'reason' => 'nullable|string|max:500'
        ]);

        $pendingExpense->reject($request->reason);

        // Notificar al invitado
        ShareNotification::createExpenseRejected($pendingExpense);

        // Marcar notificacion original como leida
        ShareNotification::where('notifiable_type', PendingExpense::class)
            ->where('notifiable_id', $pendingExpense->id)
            ->where('user_id', $user->id)
            ->where('type', ShareNotification::TYPE_EXPENSE_REQUEST)
            ->update(['read' => true, 'read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Solicitud rechazada'
        ]);
    }

    /**
     * Historial de mis solicitudes (como invitado)
     */
    public function myRequests(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = PendingExpense::where('created_by', $user->id)
            ->with(['medioPago', 'categoria', 'dataShare.owner:id,name'])
            ->orderByDesc('created_at');

        // Filtrar por estado si se especifica
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $requests->items(),
            'meta' => [
                'current_page' => $requests->currentPage(),
                'last_page' => $requests->lastPage(),
                'total' => $requests->total()
            ]
        ]);
    }

    /**
     * Historial completo de solicitudes (como propietario) - aprobadas y rechazadas
     */
    public function history(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = PendingExpense::where('owner_id', $user->id)
            ->whereIn('status', [PendingExpense::STATUS_APPROVED, PendingExpense::STATUS_REJECTED])
            ->with(['createdBy:id,name', 'medioPago', 'categoria'])
            ->orderByDesc('decided_at');

        $history = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $history->items(),
            'meta' => [
                'current_page' => $history->currentPage(),
                'last_page' => $history->lastPage(),
                'total' => $history->total()
            ]
        ]);
    }
}
