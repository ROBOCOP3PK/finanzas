<?php

namespace App\Http\Controllers;

use App\Http\Requests\GastoRequest;
use App\Models\ConceptoFrecuente;
use App\Models\Gasto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GastoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Gasto::with(['medioPago', 'categoria'])
            ->orderByDesc('fecha')
            ->orderByDesc('created_at');

        // Filtro por rango de fechas
        if ($request->filled('desde') && $request->filled('hasta')) {
            $query->fecha($request->desde, $request->hasta);
        }

        // Filtro por tipo
        if ($request->filled('tipo')) {
            $query->tipo($request->tipo);
        }

        // Filtro por medio de pago
        if ($request->filled('medio_pago_id')) {
            $query->medioPago($request->medio_pago_id);
        }

        // Filtro por categoría
        if ($request->filled('categoria_id')) {
            $query->categoria($request->categoria_id);
        }

        $perPage = $request->input('per_page', 20);
        $gastos = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $gastos->items(),
            'meta' => [
                'current_page' => $gastos->currentPage(),
                'last_page' => $gastos->lastPage(),
                'per_page' => $gastos->perPage(),
                'total' => $gastos->total()
            ]
        ]);
    }

    public function store(GastoRequest $request): JsonResponse
    {
        $gasto = Gasto::create($request->validated());

        // Registrar concepto frecuente
        ConceptoFrecuente::registrarUso(
            $request->concepto,
            $request->medio_pago_id,
            $request->tipo
        );

        $gasto->load(['medioPago', 'categoria']);

        return response()->json([
            'success' => true,
            'data' => $gasto,
            'message' => 'Gasto registrado correctamente'
        ], 201);
    }

    public function show(Gasto $gasto): JsonResponse
    {
        $gasto->load(['medioPago', 'categoria']);

        return response()->json([
            'success' => true,
            'data' => $gasto
        ]);
    }

    public function update(GastoRequest $request, Gasto $gasto): JsonResponse
    {
        $gasto->update($request->validated());
        $gasto->load(['medioPago', 'categoria']);

        return response()->json([
            'success' => true,
            'data' => $gasto,
            'message' => 'Gasto actualizado correctamente'
        ]);
    }

    public function destroy(Gasto $gasto): JsonResponse
    {
        $gasto->delete();

        return response()->json([
            'success' => true,
            'message' => 'Gasto eliminado correctamente'
        ]);
    }
}
