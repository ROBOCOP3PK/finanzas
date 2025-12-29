<?php

namespace App\Http\Controllers;

use App\Http\Requests\GastoRequest;
use App\Models\ConceptoFrecuente;
use App\Models\Gasto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

        // Si no se proporciona fecha, usar la fecha actual
        if (empty($data['fecha'])) {
            $data['fecha'] = now()->toDateString();
        }

        // Si no se proporciona tipo, usar 'personal' por defecto
        if (empty($data['tipo'])) {
            $data['tipo'] = 'personal';
        }

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

    public function exportar(Request $request): StreamedResponse
    {
        $user = $request->user();
        $exportarTodos = $request->boolean('exportar_todos', false);

        $query = $user->gastos()
            ->with(['medioPago', 'categoria'])
            ->orderByDesc('fecha')
            ->orderByDesc('created_at');

        // Solo aplicar filtros de fecha si no se exportan todos
        if (!$exportarTodos) {
            if ($request->filled('desde') && $request->filled('hasta')) {
                $query->fecha($request->desde, $request->hasta);
            }
        }

        // Filtro por tipo
        if ($request->filled('tipo')) {
            $query->tipo($request->tipo);
        }

        // Filtro por categoría
        if ($request->filled('categoria_id')) {
            $query->categoria($request->categoria_id);
        }

        $gastos = $query->get();

        $filename = 'gastos_' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($gastos) {
            $handle = fopen('php://output', 'w');

            // BOM para UTF-8 (Excel compatibility)
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header
            fputcsv($handle, [
                'Fecha',
                'Concepto',
                'Valor',
                'Tipo',
                'Categoría',
                'Medio de Pago',
                'Creado'
            ]);

            // Data
            foreach ($gastos as $gasto) {
                fputcsv($handle, [
                    $gasto->fecha,
                    $gasto->concepto,
                    $gasto->valor,
                    $gasto->tipo,
                    $gasto->categoria?->nombre ?? '',
                    $gasto->medioPago?->nombre ?? '',
                    $gasto->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
