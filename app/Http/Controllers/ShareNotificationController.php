<?php

namespace App\Http\Controllers;

use App\Models\ShareNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShareNotificationController extends Controller
{
    /**
     * Listar notificaciones
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $notifications = $user->shareNotifications()
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $notifications,
            'unread_count' => $notifications->where('read', false)->count()
        ]);
    }

    /**
     * Contador de notificaciones sin leer
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $user = $request->user();

        $count = $user->unreadShareNotificationsCount();
        $pendingExpenses = $user->pendingExpensesCount();

        return response()->json([
            'success' => true,
            'data' => [
                'notifications' => $count,
                'pending_expenses' => $pendingExpenses,
                'total' => $count + $pendingExpenses
            ]
        ]);
    }

    /**
     * Marcar notificacion como leida
     */
    public function markAsRead(Request $request, ShareNotification $shareNotification): JsonResponse
    {
        $user = $request->user();

        if ($shareNotification->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        $shareNotification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notificacion marcada como leida'
        ]);
    }

    /**
     * Marcar todas las notificaciones como leidas
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = $request->user();

        $user->shareNotifications()
            ->where('read', false)
            ->update([
                'read' => true,
                'read_at' => now()
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Todas las notificaciones marcadas como leidas'
        ]);
    }
}
