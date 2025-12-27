<?php

namespace App\Http\Controllers;

use App\Http\Requests\MedioPagoRequest;
use App\Models\MedioPago;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MedioPagoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Incluir medios de pago del usuario y globales (user_id = null)
        $query = MedioPago::where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereNull('user_id');
            })
            ->ordenados();

        if ($request->boolean('activos')) {
            $query->activos();
        }

        return response()->json([
            'success' => true,
            'data' => $query->get()
        ]);
    }

    public function store(MedioPagoRequest $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validated();
        $data['user_id'] = $user->id;

        $medioPago = MedioPago::create($data);

        return response()->json([
            'success' => true,
            'data' => $medioPago,
            'message' => 'Medio de pago creado correctamente'
        ], 201);
    }

    public function show(Request $request, MedioPago $medioPago): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $medioPago
        ]);
    }

    public function update(MedioPagoRequest $request, MedioPago $medioPago): JsonResponse
    {
        $user = $request->user();

        // Solo puede editar sus propios medios de pago
        if ($medioPago->user_id !== null && $medioPago->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        $medioPago->update($request->validated());

        return response()->json([
            'success' => true,
            'data' => $medioPago,
            'message' => 'Medio de pago actualizado correctamente'
        ]);
    }

    public function destroy(Request $request, MedioPago $medioPago): JsonResponse
    {
        $user = $request->user();

        // Solo puede eliminar sus propios medios de pago
        if ($medioPago->user_id !== null && $medioPago->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        if (!$medioPago->puedeEliminarse()) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar el medio de pago porque tiene gastos asociados.'
            ], 422);
        }

        $medioPago->delete();

        return response()->json([
            'success' => true,
            'message' => 'Medio de pago eliminado correctamente'
        ]);
    }

    public function reordenar(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'orden' => 'required|array',
            'orden.*' => 'integer|exists:medios_pago,id'
        ]);

        foreach ($request->orden as $index => $id) {
            MedioPago::where('id', $id)
                ->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id)->orWhereNull('user_id');
                })
                ->update(['orden' => $index + 1]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Orden actualizado correctamente'
        ]);
    }
}
