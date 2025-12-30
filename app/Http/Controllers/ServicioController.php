<?php

namespace App\Http\Controllers;

use App\Http\Requests\ServicioRequest;
use App\Models\Gasto;
use App\Models\PagoServicio;
use App\Models\Servicio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServicioController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $mes = $request->input('mes', now()->month);
        $anio = $request->input('anio', now()->year);

        $servicios = Servicio::where('user_id', $user->id)
            ->with(['categoria', 'pagos' => function ($q) use ($mes, $anio) {
                $q->where('mes', $mes)->where('anio', $anio);
            }])
            ->ordenados()
            ->get()
            ->map(function ($servicio) use ($mes, $anio) {
                $servicio->pagado = $servicio->pagos->isNotEmpty();
                $servicio->pago_mes = $servicio->pagos->first();
                unset($servicio->pagos);
                return $servicio;
            });

        return response()->json([
            'success' => true,
            'data' => $servicios
        ]);
    }

    public function store(ServicioRequest $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validated();
        $data['user_id'] = $user->id;

        $servicio = Servicio::create($data);

        return response()->json([
            'success' => true,
            'data' => $servicio->load('categoria'),
            'message' => 'Servicio creado correctamente'
        ], 201);
    }

    public function show(Request $request, Servicio $servicio): JsonResponse
    {
        $user = $request->user();

        if ($servicio->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $servicio->load('categoria')
        ]);
    }

    public function update(ServicioRequest $request, Servicio $servicio): JsonResponse
    {
        $user = $request->user();

        if ($servicio->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        $servicio->update($request->validated());

        return response()->json([
            'success' => true,
            'data' => $servicio->load('categoria'),
            'message' => 'Servicio actualizado correctamente'
        ]);
    }

    public function destroy(Request $request, Servicio $servicio): JsonResponse
    {
        $user = $request->user();

        if ($servicio->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        $servicio->delete();

        return response()->json([
            'success' => true,
            'message' => 'Servicio eliminado correctamente'
        ]);
    }

    public function marcarPagado(Request $request, Servicio $servicio): JsonResponse
    {
        $user = $request->user();

        if ($servicio->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        $request->validate([
            'fecha' => 'nullable|date',
            'valor' => 'nullable|numeric|min:0.01',
            'medio_pago_id' => 'nullable|exists:medios_pago,id',
            'tipo' => 'nullable|in:personal,pareja,compartido',
            'crear_gasto' => 'boolean'
        ]);

        $mes = now()->month;
        $anio = now()->year;
        $fecha = $request->input('fecha', now()->toDateString());

        // Verificar si ya está pagado este mes
        $pagoExistente = PagoServicio::where('servicio_id', $servicio->id)
            ->where('mes', $mes)
            ->where('anio', $anio)
            ->first();

        if ($pagoExistente) {
            return response()->json([
                'success' => false,
                'message' => 'Este servicio ya está marcado como pagado este mes'
            ], 422);
        }

        $gastoId = null;

        // Crear gasto si se solicita
        if ($request->boolean('crear_gasto', true)) {
            $gasto = Gasto::create([
                'user_id' => $user->id,
                'fecha' => $fecha,
                'categoria_id' => $servicio->categoria_id,
                'medio_pago_id' => $request->input('medio_pago_id'),
                'concepto' => $servicio->nombre,
                'valor' => $request->input('valor', $servicio->valor_estimado ?? 0),
                'tipo' => $request->input('tipo', 'personal'),
                'registrado_por' => $user->id
            ]);
            $gastoId = $gasto->id;
        }

        // Crear registro de pago
        $pago = PagoServicio::create([
            'servicio_id' => $servicio->id,
            'gasto_id' => $gastoId,
            'mes' => $mes,
            'anio' => $anio,
            'fecha_pago' => $fecha
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'pago' => $pago,
                'gasto_id' => $gastoId
            ],
            'message' => 'Servicio marcado como pagado'
        ]);
    }

    public function desmarcarPagado(Request $request, Servicio $servicio): JsonResponse
    {
        $user = $request->user();

        if ($servicio->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        $mes = now()->month;
        $anio = now()->year;

        $pago = PagoServicio::where('servicio_id', $servicio->id)
            ->where('mes', $mes)
            ->where('anio', $anio)
            ->first();

        if (!$pago) {
            return response()->json([
                'success' => false,
                'message' => 'Este servicio no está marcado como pagado este mes'
            ], 422);
        }

        // Si tiene gasto asociado, eliminarlo también
        if ($pago->gasto_id) {
            Gasto::destroy($pago->gasto_id);
        }

        $pago->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pago desmarcado correctamente'
        ]);
    }

    public function pendientes(Request $request): JsonResponse
    {
        $user = $request->user();
        $mes = now()->month;
        $anio = now()->year;

        $serviciosActivos = Servicio::where('user_id', $user->id)
            ->activos()
            ->pluck('id');

        $serviciosPagados = PagoServicio::whereIn('servicio_id', $serviciosActivos)
            ->where('mes', $mes)
            ->where('anio', $anio)
            ->pluck('servicio_id');

        $pendientes = Servicio::where('user_id', $user->id)
            ->activos()
            ->whereNotIn('id', $serviciosPagados)
            ->with('categoria')
            ->ordenados()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $pendientes
        ]);
    }

    public function alertas(Request $request): JsonResponse
    {
        $user = $request->user();
        $diaRestablecimiento = $user->dia_restablecimiento_servicios ?? 1;
        $hoy = now();

        // Calcular días hasta el restablecimiento
        $fechaRestablecimiento = now()->copy()->day($diaRestablecimiento);

        // Si ya pasó el día este mes, calcular para el próximo mes
        if ($hoy->day >= $diaRestablecimiento) {
            $fechaRestablecimiento->addMonth();
        }

        // Ajustar si el mes no tiene ese día
        $ultimoDiaMes = $fechaRestablecimiento->copy()->endOfMonth()->day;
        if ($diaRestablecimiento > $ultimoDiaMes) {
            $fechaRestablecimiento->day($ultimoDiaMes);
        }

        $diasRestantes = (int) ceil($hoy->diffInDays($fechaRestablecimiento, false));

        // Servicios pendientes de pago
        $mes = now()->month;
        $anio = now()->year;

        $serviciosActivos = Servicio::where('user_id', $user->id)
            ->activos()
            ->count();

        $serviciosPagados = PagoServicio::whereIn('servicio_id', function ($q) use ($user) {
                $q->select('id')->from('servicios')->where('user_id', $user->id);
            })
            ->where('mes', $mes)
            ->where('anio', $anio)
            ->count();

        $pendientes = $serviciosActivos - $serviciosPagados;

        // Alerta si faltan 3 días o menos y hay pendientes
        $mostrarAlerta = $diasRestantes <= 3 && $pendientes > 0;

        return response()->json([
            'success' => true,
            'data' => [
                'dias_restantes' => $diasRestantes,
                'fecha_restablecimiento' => $fechaRestablecimiento->toDateString(),
                'servicios_pendientes' => $pendientes,
                'servicios_pagados' => $serviciosPagados,
                'servicios_total' => $serviciosActivos,
                'mostrar_alerta' => $mostrarAlerta
            ]
        ]);
    }

    public function reordenar(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'orden' => 'required|array',
            'orden.*' => 'integer|exists:servicios,id'
        ]);

        foreach ($request->orden as $index => $id) {
            Servicio::where('id', $id)
                ->where('user_id', $user->id)
                ->update(['orden' => $index + 1]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Orden actualizado correctamente'
        ]);
    }
}
