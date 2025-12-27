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
        $user = $request->user();

        $query = $user->gastos()
            ->with(['medioPago', 'categoria'])
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
        $user = $request->user();

        $data = $request->validated();
        $data['user_id'] = $user->id;
        $data['registrado_por'] = $user->id;

        $gasto = Gasto::create($data);

        // Registrar concepto frecuente
        ConceptoFrecuente::registrarUso(
            $request->concepto,
            $request->medio_pago_id,
            $request->tipo,
            $user->id
        );

        $gasto->load(['medioPago', 'categoria']);

        return response()->json([
            'success' => true,
            'data' => $gasto,
            'message' => 'Gasto registrado correctamente'
        ], 201);
    }

    public function show(Request $request, Gasto $gasto): JsonResponse
    {
        $user = $request->user();

        // Verificar que el gasto pertenece al usuario
        if ($gasto->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        $gasto->load(['medioPago', 'categoria']);

        return response()->json([
            'success' => true,
            'data' => $gasto
        ]);
    }

    public function update(GastoRequest $request, Gasto $gasto): JsonResponse
    {
        $user = $request->user();

        // Verificar que el gasto pertenece al usuario
        if ($gasto->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        $gasto->update($request->validated());
        $gasto->load(['medioPago', 'categoria']);

        return response()->json([
            'success' => true,
            'data' => $gasto,
            'message' => 'Gasto actualizado correctamente'
        ]);
    }

    public function destroy(Request $request, Gasto $gasto): JsonResponse
    {
        $user = $request->user();

        // Verificar que el gasto pertenece al usuario
        if ($gasto->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        $gasto->delete();

        return response()->json([
            'success' => true,
            'message' => 'Gasto eliminado correctamente'
        ]);
    }
}
