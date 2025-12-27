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
        $query = MedioPago::ordenados();

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
        $medioPago = MedioPago::create($request->validated());

        return response()->json([
            'success' => true,
            'data' => $medioPago,
            'message' => 'Medio de pago creado correctamente'
        ], 201);
    }

    public function show(MedioPago $medioPago): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $medioPago
        ]);
    }

    public function update(MedioPagoRequest $request, MedioPago $medioPago): JsonResponse
    {
        $medioPago->update($request->validated());

        return response()->json([
            'success' => true,
            'data' => $medioPago,
            'message' => 'Medio de pago actualizado correctamente'
        ]);
    }

    public function destroy(MedioPago $medioPago): JsonResponse
    {
        if (!$medioPago->puedeEliminarse()) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar el medio de pago porque tiene gastos asociados. Puedes desactivarlo en su lugar.'
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
        $request->validate([
            'orden' => 'required|array',
            'orden.*' => 'integer|exists:medios_pago,id'
        ]);

        foreach ($request->orden as $index => $id) {
            MedioPago::where('id', $id)->update(['orden' => $index + 1]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Orden actualizado correctamente'
        ]);
    }
}
