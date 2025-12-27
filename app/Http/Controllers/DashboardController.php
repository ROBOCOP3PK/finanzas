<?php

namespace App\Http\Controllers;

use App\Models\Abono;
use App\Models\Configuracion;
use App\Models\Gasto;
use App\Models\GastoRecurrente;
use App\Models\MedioPago;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $config = Configuracion::todas();
        $saldoPendiente = $this->calcularSaldoPendiente();
        $resumenMes = $this->getResumenMes();
        $porMedioPago = $this->getGastosPorMedioPago();
        $ultimosMovimientos = $this->getUltimosMovimientos();
        $pendientesRecurrentes = GastoRecurrente::pendientes()->count();

        return response()->json([
            'success' => true,
            'data' => [
                'saldo_pendiente' => $saldoPendiente,
                'configuracion' => [
                    'nombre_persona_1' => $config['nombre_persona_1'] ?? 'Persona 1',
                    'nombre_persona_2' => $config['nombre_persona_2'] ?? 'Persona 2',
                    'porcentaje_persona_1' => (float) ($config['porcentaje_persona_1'] ?? 50),
                    'porcentaje_persona_2' => (float) ($config['porcentaje_persona_2'] ?? 50),
                    'tema' => $config['tema'] ?? 'system'
                ],
                'resumen_mes' => $resumenMes,
                'por_medio_pago' => $porMedioPago,
                'ultimos_movimientos' => $ultimosMovimientos,
                'pendientes_recurrentes' => $pendientesRecurrentes
            ]
        ]);
    }

    public function saldo(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'saldo_pendiente' => $this->calcularSaldoPendiente(),
                'nombre_persona' => Configuracion::nombrePersona1()
            ]
        ]);
    }

    public function resumenMes(Request $request): JsonResponse
    {
        $mes = $request->input('mes', now()->month);
        $anio = $request->input('anio', now()->year);

        return response()->json([
            'success' => true,
            'data' => $this->getResumenMes($mes, $anio)
        ]);
    }

    private function calcularSaldoPendiente(): float
    {
        $porcentaje1 = Configuracion::porcentajePersona1() / 100;

        $gastosPersona1 = Gasto::tipo(Gasto::TIPO_PERSONA_1)->sum('valor');
        $gastosCasa = Gasto::tipo(Gasto::TIPO_CASA)->sum('valor');
        $totalAbonos = Abono::sum('valor');

        $saldo = $gastosPersona1 + ($gastosCasa * $porcentaje1) - $totalAbonos;

        return round($saldo, 2);
    }

    private function getResumenMes(?int $mes = null, ?int $anio = null): array
    {
        $mes = $mes ?? now()->month;
        $anio = $anio ?? now()->year;

        $gastosPersona1 = Gasto::delMes($mes, $anio)->tipo(Gasto::TIPO_PERSONA_1)->sum('valor');
        $gastosPersona2 = Gasto::delMes($mes, $anio)->tipo(Gasto::TIPO_PERSONA_2)->sum('valor');
        $gastosCasa = Gasto::delMes($mes, $anio)->tipo(Gasto::TIPO_CASA)->sum('valor');
        $totalAbonos = Abono::delMes($mes, $anio)->sum('valor');

        return [
            'mes' => $mes,
            'anio' => $anio,
            'total_gastos' => round($gastosPersona1 + $gastosPersona2 + $gastosCasa, 2),
            'gastos_persona_1' => round($gastosPersona1, 2),
            'gastos_persona_2' => round($gastosPersona2, 2),
            'gastos_casa' => round($gastosCasa, 2),
            'total_abonos' => round($totalAbonos, 2)
        ];
    }

    private function getGastosPorMedioPago(): array
    {
        $mes = now()->month;
        $anio = now()->year;

        return MedioPago::activos()
            ->ordenados()
            ->get()
            ->mapWithKeys(function ($medioPago) use ($mes, $anio) {
                $total = Gasto::delMes($mes, $anio)
                    ->medioPago($medioPago->id)
                    ->sum('valor');
                return [$medioPago->nombre => round($total, 2)];
            })
            ->toArray();
    }

    private function getUltimosMovimientos(int $limite = 10): array
    {
        $gastos = Gasto::with(['medioPago', 'categoria'])
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

        $abonos = Abono::orderByDesc('fecha')
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
