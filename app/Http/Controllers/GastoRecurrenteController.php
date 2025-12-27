<?php

namespace App\Http\Controllers;

use App\Http\Requests\GastoRecurrenteRequest;
use App\Models\GastoRecurrente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GastoRecurrenteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = GastoRecurrente::with(['medioPago', 'categoria'])
            ->orderBy('dia_mes');

        if ($request->boolean('activos')) {
            $query->activos();
        }

        return response()->json([
            'success' => true,
            'data' => $query->get()
        ]);
    }

    public function pendientes(): JsonResponse
    {
        $pendientes = GastoRecurrente::with(['medioPago', 'categoria'])
            ->pendientes()
            ->orderBy('dia_mes')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $pendientes
        ]);
    }

    public function store(GastoRecurrenteRequest $request): JsonResponse
    {
        $gastoRecurrente = GastoRecurrente::create($request->validated());
        $gastoRecurrente->load(['medioPago', 'categoria']);

        return response()->json([
            'success' => true,
            'data' => $gastoRecurrente,
            'message' => 'Gasto recurrente creado correctamente'
        ], 201);
    }

    public function show(GastoRecurrente $gastoRecurrente): JsonResponse
    {
        $gastoRecurrente->load(['medioPago', 'categoria']);

        return response()->json([
            'success' => true,
            'data' => $gastoRecurrente
        ]);
    }

    public function update(GastoRecurrenteRequest $request, GastoRecurrente $gastoRecurrente): JsonResponse
    {
        $gastoRecurrente->update($request->validated());
        $gastoRecurrente->load(['medioPago', 'categoria']);

        return response()->json([
            'success' => true,
            'data' => $gastoRecurrente,
            'message' => 'Gasto recurrente actualizado correctamente'
        ]);
    }

    public function destroy(GastoRecurrente $gastoRecurrente): JsonResponse
    {
        $gastoRecurrente->delete();

        return response()->json([
            'success' => true,
            'message' => 'Gasto recurrente eliminado correctamente'
        ]);
    }

    public function registrar(GastoRecurrente $gastoRecurrente): JsonResponse
    {
        if (!$gastoRecurrente->estaPendiente()) {
            return response()->json([
                'success' => false,
                'message' => 'Este gasto recurrente ya fue registrado este mes'
            ], 422);
        }

        $gasto = $gastoRecurrente->registrar();
        $gasto->load(['medioPago', 'categoria']);

        return response()->json([
            'success' => true,
            'data' => $gasto,
            'message' => 'Gasto recurrente registrado correctamente'
        ], 201);
    }

    public function registrarPendientes(): JsonResponse
    {
        $pendientes = GastoRecurrente::pendientes()->get();
        $gastosCreados = [];

        foreach ($pendientes as $gastoRecurrente) {
            $gasto = $gastoRecurrente->registrar();
            $gasto->load(['medioPago', 'categoria']);
            $gastosCreados[] = $gasto;
        }

        return response()->json([
            'success' => true,
            'data' => $gastosCreados,
            'message' => count($gastosCreados) . ' gasto(s) recurrente(s) registrado(s)'
        ], 201);
    }
}
