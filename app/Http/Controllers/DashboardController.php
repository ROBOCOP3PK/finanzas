<?php

namespace App\Http\Controllers;

use App\Models\Gasto;
use App\Models\GastoRecurrente;
use App\Models\MedioPago;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $deudaPersona2 = $user->calcularDeudaPersona2();
        $gastoMes = $user->gastoMesActual();
        $resumenMes = $this->getResumenMes($user);
        $porMedioPago = $this->getGastosPorMedioPago($user);
        $ultimosMovimientos = $this->getUltimosMovimientos($user);
        $pendientesRecurrentes = GastoRecurrente::where('user_id', $user->id)
            ->where('activo', true)
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'deuda_persona_2' => $deudaPersona2,
                'gasto_mes_actual' => $gastoMes,
                'porcentaje_persona_2' => (float) $user->porcentaje_persona_2,
                'persona_secundaria' => $user->personaSecundaria ? [
                    'id' => $user->personaSecundaria->id,
                    'nombre' => $user->personaSecundaria->name
                ] : null,
                'resumen_mes' => $resumenMes,
                'por_medio_pago' => $porMedioPago,
                'por_categoria' => $this->getGastosPorCategoria($user),
                'ultimos_movimientos' => $ultimosMovimientos,
                'pendientes_recurrentes' => $pendientesRecurrentes
            ]
        ]);
    }

    public function saldo(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'deuda_persona_2' => $user->calcularDeudaPersona2(),
                'gasto_mes_actual' => $user->gastoMesActual()
            ]
        ]);
    }

    public function resumenMes(Request $request): JsonResponse
    {
        $user = $request->user();
        $mes = $request->input('mes', now()->month);
        $anio = $request->input('anio', now()->year);

        return response()->json([
            'success' => true,
            'data' => $this->getResumenMes($user, $mes, $anio)
        ]);
    }

    public function porCategoria(Request $request): JsonResponse
    {
        $user = $request->user();
        $mes = $request->input('mes', now()->month);
        $anio = $request->input('anio', now()->year);

        return response()->json([
            'success' => true,
            'data' => [
                'mes' => (int) $mes,
                'anio' => (int) $anio,
                'categorias' => $this->getGastosPorCategoria($user, $mes, $anio)
            ]
        ]);
    }

    public function gastosPorCategoria(Request $request, int $categoriaId): JsonResponse
    {
        $user = $request->user();
        $mes = $request->input('mes', now()->month);
        $anio = $request->input('anio', now()->year);

        $gastos = $user->gastos()
            ->delMes($mes, $anio)
            ->where('categoria_id', $categoriaId)
            ->with(['medioPago', 'categoria'])
            ->orderByDesc('fecha')
            ->get()
            ->map(function ($gasto) {
                return [
                    'id' => $gasto->id,
                    'fecha' => $gasto->fecha->format('Y-m-d'),
                    'concepto' => $gasto->concepto,
                    'valor' => $gasto->valor,
                    'tipo' => $gasto->tipo,
                    'medio_pago' => $gasto->medioPago->nombre ?? null
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $gastos
        ]);
    }

    private function getResumenMes($user, ?int $mes = null, ?int $anio = null): array
    {
        $mes = $mes ?? now()->month;
        $anio = $anio ?? now()->year;

        $gastosPersonal = $user->gastos()->delMes($mes, $anio)->tipo(Gasto::TIPO_PERSONAL)->sum('valor');
        $gastosPareja = $user->gastos()->delMes($mes, $anio)->tipo(Gasto::TIPO_PAREJA)->sum('valor');
        $gastosCompartido = $user->gastos()->delMes($mes, $anio)->tipo(Gasto::TIPO_COMPARTIDO)->sum('valor');
        $totalAbonos = $user->abonos()->delMes($mes, $anio)->sum('valor');

        $totalGastos = $gastosPersonal + $gastosPareja + $gastosCompartido;

        return [
            'mes' => $mes,
            'anio' => $anio,
            'total_gastos' => round($totalGastos, 2),
            'gastos_personal' => round($gastosPersonal, 2),
            'gastos_pareja' => round($gastosPareja, 2),
            'gastos_compartido' => round($gastosCompartido, 2),
            'total_abonos' => round($totalAbonos, 2)
        ];
    }

    private function getGastosPorMedioPago($user): array
    {
        $mes = now()->month;
        $anio = now()->year;

        return MedioPago::where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhereNull('user_id');
            })
            ->activos()
            ->ordenados()
            ->get()
            ->mapWithKeys(function ($medioPago) use ($user, $mes, $anio) {
                $total = $user->gastos()
                    ->delMes($mes, $anio)
                    ->medioPago($medioPago->id)
                    ->sum('valor');
                return [$medioPago->nombre => round($total, 2)];
            })
            ->toArray();
    }

    private function getGastosPorCategoria($user, ?int $mes = null, ?int $anio = null): array
    {
        $mes = $mes ?? now()->month;
        $anio = $anio ?? now()->year;

        $gastos = $user->gastos()
            ->delMes($mes, $anio)
            ->with('categoria')
            ->get()
            ->groupBy('categoria_id');

        $totalMes = $user->gastos()->delMes($mes, $anio)->sum('valor');

        $resultado = [];
        foreach ($gastos as $gastosCategoria) {
            $categoria = $gastosCategoria->first()->categoria;
            if (!$categoria) continue;

            $total = $gastosCategoria->sum('valor');
            $porcentaje = $totalMes > 0 ? round(($total / $totalMes) * 100, 1) : 0;

            $resultado[] = [
                'categoria_id' => $categoria->id,
                'nombre' => $categoria->nombre,
                'icono' => $categoria->icono,
                'color' => $categoria->color,
                'total' => round($total, 2),
                'porcentaje' => $porcentaje
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
            ->map(function ($gasto) {
                return [
                    'id' => $gasto->id,
                    'tipo_movimiento' => 'gasto',
                    'fecha' => $gasto->fecha->format('Y-m-d'),
                    'concepto' => $gasto->concepto,
                    'valor' => $gasto->valor,
                    'tipo' => $gasto->tipo,
                    'medio_pago' => $gasto->medioPago->nombre ?? null,
                    'categoria' => $gasto->categoria->nombre ?? null,
                    'categoria_color' => $gasto->categoria->color ?? null
                ];
            });

        $abonos = $user->abonos()
            ->orderByDesc('fecha')
            ->orderByDesc('created_at')
            ->limit($limite)
            ->get()
            ->map(function ($abono) {
                return [
                    'id' => $abono->id,
                    'tipo_movimiento' => 'abono',
                    'fecha' => $abono->fecha->format('Y-m-d'),
                    'concepto' => $abono->nota ?? 'Abono',
                    'valor' => $abono->valor,
                    'tipo' => null,
                    'medio_pago' => null,
                    'categoria' => null,
                    'categoria_color' => null
                ];
            });

        return $gastos->concat($abonos)
            ->sortByDesc('fecha')
            ->take($limite)
            ->values()
            ->toArray();
    }
}
