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
        $user = $request->user();

        $query = $user->abonos()
            ->orderByDesc('fecha')
            ->orderByDesc('created_at');

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
        $user = $request->user();

        $data = $request->validated();
        $data['user_id'] = $user->id;

        $abono = Abono::create($data);

        return response()->json([
            'success' => true,
            'data' => $abono,
            'message' => 'Abono registrado correctamente'
        ], 201);
    }

    public function show(Request $request, Abono $abono): JsonResponse
    {
        $user = $request->user();

        if ($abono->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $abono
        ]);
    }

    public function update(AbonoRequest $request, Abono $abono): JsonResponse
    {
        $user = $request->user();

        if ($abono->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        $abono->update($request->validated());

        return response()->json([
            'success' => true,
            'data' => $abono,
            'message' => 'Abono actualizado correctamente'
        ]);
    }

    public function destroy(Request $request, Abono $abono): JsonResponse
    {
        $user = $request->user();

        if ($abono->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        $abono->delete();

        return response()->json([
            'success' => true,
            'message' => 'Abono eliminado correctamente'
        ]);
    }
}
