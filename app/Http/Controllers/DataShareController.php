<?php

namespace App\Http\Controllers;

use App\Models\DataShare;
use App\Models\ShareNotification;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DataShareController extends Controller
{
    /**
     * Estado de mi comparticion (como propietario)
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();

        $activeShare = DataShare::where('owner_id', $user->id)
            ->whereIn('status', [DataShare::STATUS_PENDING, DataShare::STATUS_ACCEPTED])
            ->with('guest:id,name,email')
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'has_active_share' => (bool) $activeShare,
                'share' => $activeShare
            ]
        ]);
    }

    /**
     * Invitar a alguien a ver mis datos
     */
    public function invite(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|max:255'
        ]);

        $user = $request->user();
        $guestEmail = strtolower($request->email);

        // Verificar que no tenga ya una comparticion activa o pendiente
        $existing = DataShare::where('owner_id', $user->id)
            ->whereIn('status', [DataShare::STATUS_PENDING, DataShare::STATUS_ACCEPTED])
            ->exists();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Ya tienes una comparticion activa. Revocala primero para invitar a otra persona.'
            ], 422);
        }

        // Verificar que no se invite a si mismo
        if ($guestEmail === strtolower($user->email)) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes invitarte a ti mismo'
            ], 422);
        }

        // Buscar si el invitado ya tiene cuenta (case insensitive)
        $guest = User::whereRaw('LOWER(email) = ?', [$guestEmail])->first();

        $share = DataShare::create([
            'owner_id' => $user->id,
            'guest_id' => $guest?->id,
            'guest_email' => $guestEmail,
            'status' => DataShare::STATUS_PENDING
        ]);

        // Crear notificacion para el invitado si tiene cuenta
        if ($guest) {
            ShareNotification::createShareInvitation($share, $user);
        }

        return response()->json([
            'success' => true,
            'data' => $share->load('guest:id,name,email'),
            'message' => 'Invitacion enviada correctamente'
        ], 201);
    }

    /**
     * Revocar acceso
     */
    public function revoke(Request $request): JsonResponse
    {
        $user = $request->user();

        $share = DataShare::where('owner_id', $user->id)
            ->whereIn('status', [DataShare::STATUS_PENDING, DataShare::STATUS_ACCEPTED])
            ->first();

        if (!$share) {
            return response()->json([
                'success' => false,
                'message' => 'No hay comparticion activa'
            ], 404);
        }

        $share->revoke();

        // Notificar al invitado si tiene cuenta
        if ($share->guest_id) {
            ShareNotification::createShareRevoked($share, $user);
        }

        // Rechazar solicitudes pendientes
        $share->pendingExpenses()->pending()->update([
            'status' => 'rejected',
            'rejection_reason' => 'Acceso revocado',
            'decided_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Acceso revocado correctamente'
        ]);
    }

    /**
     * Datos compartidos conmigo (como invitado)
     */
    public function sharedWithMe(Request $request): JsonResponse
    {
        $user = $request->user();

        // Buscar por guest_id o por email (case insensitive para cuando no tenia cuenta al ser invitado)
        $userEmailLower = strtolower($user->email);
        $shares = DataShare::where(function ($q) use ($user, $userEmailLower) {
                $q->where('guest_id', $user->id)
                  ->orWhereRaw('LOWER(guest_email) = ?', [$userEmailLower]);
            })
            ->with('owner:id,name,email')
            ->orderByDesc('created_at')
            ->get();

        // Agrupar por estado
        $active = $shares->where('status', DataShare::STATUS_ACCEPTED)->values();
        $pending = $shares->where('status', DataShare::STATUS_PENDING)->values();

        return response()->json([
            'success' => true,
            'data' => [
                'active' => $active,
                'pending' => $pending
            ]
        ]);
    }

    /**
     * Aceptar invitacion (como invitado)
     */
    public function accept(Request $request, DataShare $dataShare): JsonResponse
    {
        $user = $request->user();

        // Verificar que la invitacion sea para este usuario
        if (strtolower($dataShare->guest_email) !== strtolower($user->email)) {
            return response()->json([
                'success' => false,
                'message' => 'Esta invitacion no es para ti'
            ], 403);
        }

        if ($dataShare->status !== DataShare::STATUS_PENDING) {
            return response()->json([
                'success' => false,
                'message' => 'Esta invitacion ya no esta pendiente'
            ], 422);
        }

        // Actualizar con el guest_id si no estaba
        $dataShare->update([
            'guest_id' => $user->id,
            'status' => DataShare::STATUS_ACCEPTED,
            'accepted_at' => now()
        ]);

        // Marcar la notificacion de invitacion como leida
        ShareNotification::where('user_id', $user->id)
            ->where('notifiable_type', DataShare::class)
            ->where('notifiable_id', $dataShare->id)
            ->where('type', ShareNotification::TYPE_SHARE_INVITATION)
            ->update(['read' => true, 'read_at' => now()]);

        return response()->json([
            'success' => true,
            'data' => $dataShare->load('owner:id,name,email'),
            'message' => 'Invitacion aceptada'
        ]);
    }

    /**
     * Rechazar invitacion (como invitado)
     */
    public function reject(Request $request, DataShare $dataShare): JsonResponse
    {
        $user = $request->user();

        if (strtolower($dataShare->guest_email) !== strtolower($user->email)) {
            return response()->json([
                'success' => false,
                'message' => 'Esta invitacion no es para ti'
            ], 403);
        }

        if ($dataShare->status !== DataShare::STATUS_PENDING) {
            return response()->json([
                'success' => false,
                'message' => 'Esta invitacion ya no esta pendiente'
            ], 422);
        }

        $dataShare->reject();

        // Marcar la notificacion como leida
        ShareNotification::where('user_id', $user->id)
            ->where('notifiable_type', DataShare::class)
            ->where('notifiable_id', $dataShare->id)
            ->update(['read' => true, 'read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Invitacion rechazada'
        ]);
    }
}
