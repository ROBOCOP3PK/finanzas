<?php

namespace App\Http\Controllers;

use App\Http\Requests\AbonoRequest;
use App\Models\Abono;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AbonoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Abono::orderByDesc('fecha')->orderByDesc('created_at');

        // Filtro por rango de fechas
        if ($request->filled('desde') && $request->filled('hasta')) {
            $query->fecha($request->desde, $request->hasta);
        }

        $perPage = $request->input('per_page', 20);
        $abonos = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $abonos->items(),
            'meta' => [
                'current_page' => $abonos->currentPage(),
                'last_page' => $abonos->lastPage(),
                'per_page' => $abonos->perPage(),
                'total' => $abonos->total()
            ]
        ]);
    }

    public function store(AbonoRequest $request): JsonResponse
    {
        $abono = Abono::create($request->validated());

        return response()->json([
            'success' => true,
            'data' => $abono,
            'message' => 'Abono registrado correctamente'
        ], 201);
    }

    public function show(Abono $abono): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $abono
        ]);
    }

    public function update(AbonoRequest $request, Abono $abono): JsonResponse
    {
        $abono->update($request->validated());

        return response()->json([
            'success' => true,
            'data' => $abono,
            'message' => 'Abono actualizado correctamente'
        ]);
    }

    public function destroy(Abono $abono): JsonResponse
    {
        $abono->delete();

        return response()->json([
            'success' => true,
            'message' => 'Abono eliminado correctamente'
        ]);
    }
}
