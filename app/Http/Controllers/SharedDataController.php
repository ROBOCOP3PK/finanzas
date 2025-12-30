<?php

namespace App\Http\Controllers;

use App\Models\DataShare;
use App\Models\Gasto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SharedDataController extends Controller
{
    /**
     * Dashboard del propietario (vista de invitado)
     */
    public function dashboard(Request $request, DataShare $dataShare): JsonResponse
    {
        $this->authorizeGuestAccess($request, $dataShare);

        $owner = $dataShare->owner;

        $deudaPersona2 = $owner->calcularDeudaPersona2();
        $gastoMes = $owner->gastoMesActual();
        $resumenMes = $this->getResumenMes($owner);
        $porCategoria = $this->getGastosPorCategoria($owner);
        $ultimosMovimientos = $this->getUltimosMovimientos($owner);

        return response()->json([
            'success' => true,
            'data' => [
                'owner' => [
                    'id' => $owner->id,
                    'name' => $owner->name
                ],
                'deuda_persona_2' => $deudaPersona2,
                'gasto_mes_actual' => $gastoMes,
                'porcentaje_persona_2' => $owner->porcentaje_persona_2,
                'resumen_mes' => $resumenMes,
                'por_categoria' => $porCategoria,
                'ultimos_movimientos' => $ultimosMovimientos
            ]
        ]);
    }

    /**
     * Historial de gastos del propietario
     */
    public function gastos(Request $request, DataShare $dataShare): JsonResponse
    {
        $this->authorizeGuestAccess($request, $dataShare);

        $owner = $dataShare->owner;

        $query = $owner->gastos()
            ->with(['medioPago', 'categoria'])
            ->orderByDesc('fecha')
            ->orderByDesc('created_at');

        // Aplicar filtros
        if ($request->filled('desde') && $request->filled('hasta')) {
            $query->whereBetween('fecha', [$request->desde, $request->hasta]);
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
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

    /**
     * Categorias del propietario (para formulario)
     */
    public function categorias(Request $request, DataShare $dataShare): JsonResponse
    {
        $this->authorizeGuestAccess($request, $dataShare);

        $categorias = $dataShare->owner->categorias()
            ->where('activo', true)
            ->orderBy('orden')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categorias
        ]);
    }

    /**
     * Medios de pago del propietario (para formulario)
     */
    public function mediosPago(Request $request, DataShare $dataShare): JsonResponse
    {
        $this->authorizeGuestAccess($request, $dataShare);

        $mediosPago = $dataShare->owner->mediosPago()
            ->where('activo', true)
            ->orderBy('orden')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $mediosPago
        ]);
    }

    /**
     * Verificar que el usuario sea el invitado con acceso activo
     */
    private function authorizeGuestAccess(Request $request, DataShare $dataShare): void
    {
        $user = $request->user();

        if ($dataShare->guest_id !== $user->id || $dataShare->status !== DataShare::STATUS_ACCEPTED) {
            abort(403, 'No tienes acceso a estos datos');
        }
    }

    private function getResumenMes($user, ?int $mes = null, ?int $anio = null): array
    {
        $mes = $mes ?? now()->month;
        $anio = $anio ?? now()->year;

        $gastosPersonal = $user->gastos()
            ->whereMonth('fecha', $mes)
            ->whereYear('fecha', $anio)
            ->where('tipo', Gasto::TIPO_PERSONAL)
            ->sum('valor');

        $gastosPareja = $user->gastos()
            ->whereMonth('fecha', $mes)
            ->whereYear('fecha', $anio)
            ->where('tipo', Gasto::TIPO_PAREJA)
            ->sum('valor');

        $gastosCompartido = $user->gastos()
            ->whereMonth('fecha', $mes)
            ->whereYear('fecha', $anio)
            ->where('tipo', Gasto::TIPO_COMPARTIDO)
            ->sum('valor');

        $totalAbonos = $user->abonos()
            ->whereMonth('fecha', $mes)
            ->whereYear('fecha', $anio)
            ->sum('valor');

        return [
            'mes' => $mes,
            'anio' => $anio,
            'total_gastos' => round($gastosPersonal + $gastosPareja + $gastosCompartido, 2),
            'gastos_personal' => round($gastosPersonal, 2),
            'gastos_pareja' => round($gastosPareja, 2),
            'gastos_compartido' => round($gastosCompartido, 2),
            'total_abonos' => round($totalAbonos, 2)
        ];
    }

    private function getGastosPorCategoria($user, ?int $mes = null, ?int $anio = null): array
    {
        $mes = $mes ?? now()->month;
        $anio = $anio ?? now()->year;

        $gastos = $user->gastos()
            ->whereMonth('fecha', $mes)
            ->whereYear('fecha', $anio)
            ->with('categoria')
            ->get()
            ->groupBy('categoria_id');

        $totalMes = $user->gastos()
            ->whereMonth('fecha', $mes)
            ->whereYear('fecha', $anio)
            ->sum('valor');

        $resultado = [];
        foreach ($gastos as $categoriaId => $items) {
            $categoria = $items->first()->categoria;
            $total = $items->sum('valor');

            $resultado[] = [
                'categoria_id' => $categoriaId,
                'nombre' => $categoria?->nombre ?? 'Sin categoria',
                'icono' => $categoria?->icono,
                'color' => $categoria?->color ?? '#6B7280',
                'total' => round($total, 2),
                'porcentaje' => $totalMes > 0 ? round(($total / $totalMes) * 100, 1) : 0
            ];
        }

        // Ordenar por total descendente
        usort($resultado, fn($a, $b) => $b['total'] <=> $a['total']);

        return $resultado;
    }

    private function getUltimosMovimientos($user, int $limite = 10): array
    {
        $gastos = $user->gastos()
            ->with(['medioPago', 'categoria'])
            ->orderByDesc('fecha')
            ->orderByDesc('created_at')
            ->limit($limite)
            ->get()
            ->map(fn($gasto) => [
                'id' => $gasto->id,
                'tipo_movimiento' => 'gasto',
                'fecha' => $gasto->fecha->format('Y-m-d'),
                'concepto' => $gasto->concepto,
                'valor' => $gasto->valor,
                'tipo' => $gasto->tipo,
                'categoria' => $gasto->categoria?->nombre,
                'categoria_color' => $gasto->categoria?->color,
                'medio_pago' => $gasto->medioPago?->nombre
            ])
            ->toArray();

        return $gastos;
    }
}
