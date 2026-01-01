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
    /**
     * Calcula el mes y año del ciclo de facturación basado en el día de restablecimiento.
     *
     * Lógica:
     * - El ciclo va del día de restablecimiento hasta el día anterior del mes siguiente
     * - Ejemplo con día 27: del 27 de diciembre al 26 de enero = ciclo de enero
     * - Si día >= día de restablecimiento, pertenece al ciclo del mes siguiente
     * - EXCEPCIÓN: Si día de restablecimiento es 1, usamos mes calendario normal
     */
    private function calcularCicloFacturacion(\DateTimeInterface $fecha, int $diaRestablecimiento): array
    {
        $dia = (int) $fecha->format('d');
        $mes = (int) $fecha->format('n');
        $anio = (int) $fecha->format('Y');

        // Si el día de restablecimiento es 1, usamos el mes calendario normal
        // (el ciclo de enero es del 1 al 31 de enero)
        if ($diaRestablecimiento === 1) {
            return ['mes' => $mes, 'anio' => $anio];
        }

        // Para otros días, si estamos en o después del día de restablecimiento,
        // pertenecemos al ciclo del mes siguiente
        if ($dia >= $diaRestablecimiento) {
            $mes++;
            if ($mes > 12) {
                $mes = 1;
                $anio++;
            }
        }

        return ['mes' => $mes, 'anio' => $anio];
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $diaRestablecimiento = $user->dia_restablecimiento_servicios ?? 1;

        // Calcular el ciclo actual basado en la fecha de hoy
        $cicloActual = $this->calcularCicloFacturacion(now(), $diaRestablecimiento);
        $mes = $request->input('mes', $cicloActual['mes']);
        $anio = $request->input('anio', $cicloActual['anio']);

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
        $diaRestablecimiento = $user->dia_restablecimiento_servicios ?? 1;

        $data = $request->validated();
        $data['user_id'] = $user->id;

        // Inicializar próximo pago al ciclo actual
        $ciclo = $this->calcularCicloFacturacion(now(), $diaRestablecimiento);
        $data['proximo_mes_pago'] = $ciclo['mes'];
        $data['proximo_anio_pago'] = $ciclo['anio'];

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

        // Cargar categoria y estado de pago del ciclo actual
        $diaRestablecimiento = $user->dia_restablecimiento_servicios ?? 1;
        $ciclo = $this->calcularCicloFacturacion(now(), $diaRestablecimiento);
        $mes = $ciclo['mes'];
        $anio = $ciclo['anio'];

        $servicio->load(['categoria', 'pagos' => function ($q) use ($mes, $anio) {
            $q->where('mes', $mes)->where('anio', $anio);
        }]);

        $servicio->pagado = $servicio->pagos->isNotEmpty();
        $servicio->pago_mes = $servicio->pagos->first();
        unset($servicio->pagos);

        return response()->json([
            'success' => true,
            'data' => $servicio,
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

        $diaRestablecimiento = $user->dia_restablecimiento_servicios ?? 1;
        $fecha = $request->input('fecha', now()->toDateString());
        $fechaPago = new \DateTime($fecha);

        // Calcular el ciclo de facturación basado en la fecha del pago
        $ciclo = $this->calcularCicloFacturacion($fechaPago, $diaRestablecimiento);
        $mes = $ciclo['mes'];
        $anio = $ciclo['anio'];

        // Verificar si ya está pagado en este ciclo
        $pagoExistente = PagoServicio::where('servicio_id', $servicio->id)
            ->where('mes', $mes)
            ->where('anio', $anio)
            ->first();

        if ($pagoExistente) {
            return response()->json([
                'success' => false,
                'message' => 'Este servicio ya está marcado como pagado en este ciclo'
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

        // Calcular próximo pago según frecuencia
        $frecuencia = $servicio->frecuencia_meses ?? 1;
        $proximoMes = $mes + $frecuencia;
        $proximoAnio = $anio;
        while ($proximoMes > 12) {
            $proximoMes -= 12;
            $proximoAnio++;
        }
        $servicio->update([
            'proximo_mes_pago' => $proximoMes,
            'proximo_anio_pago' => $proximoAnio
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'pago' => $pago,
                'gasto_id' => $gastoId,
                'proximo_pago' => [
                    'mes' => $proximoMes,
                    'anio' => $proximoAnio
                ]
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

        $diaRestablecimiento = $user->dia_restablecimiento_servicios ?? 1;
        $ciclo = $this->calcularCicloFacturacion(now(), $diaRestablecimiento);
        $mes = $ciclo['mes'];
        $anio = $ciclo['anio'];

        $pago = PagoServicio::where('servicio_id', $servicio->id)
            ->where('mes', $mes)
            ->where('anio', $anio)
            ->first();

        if (!$pago) {
            return response()->json([
                'success' => false,
                'message' => 'Este servicio no está marcado como pagado en este ciclo'
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
        $diaRestablecimiento = $user->dia_restablecimiento_servicios ?? 1;
        $ciclo = $this->calcularCicloFacturacion(now(), $diaRestablecimiento);
        $mes = $ciclo['mes'];
        $anio = $ciclo['anio'];

        $serviciosPagados = PagoServicio::whereIn('servicio_id', function ($q) use ($user) {
                $q->select('id')->from('servicios')->where('user_id', $user->id);
            })
            ->where('mes', $mes)
            ->where('anio', $anio)
            ->pluck('servicio_id');

        // Solo mostrar servicios cuyo próximo pago sea <= ciclo actual
        $pendientes = Servicio::where('user_id', $user->id)
            ->activos()
            ->whereNotIn('id', $serviciosPagados)
            ->where(function ($q) use ($mes, $anio) {
                $q->whereNull('proximo_mes_pago')
                  ->orWhere('proximo_anio_pago', '<', $anio)
                  ->orWhere(function ($q2) use ($mes, $anio) {
                      $q2->where('proximo_anio_pago', '=', $anio)
                         ->where('proximo_mes_pago', '<=', $mes);
                  });
            })
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

        // Calcular días hasta el próximo restablecimiento
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

        // Usar el ciclo de facturación actual
        $ciclo = $this->calcularCicloFacturacion($hoy, $diaRestablecimiento);
        $mes = $ciclo['mes'];
        $anio = $ciclo['anio'];

        $serviciosActivos = Servicio::where('user_id', $user->id)
            ->activos()
            ->count();

        $serviciosPagados = PagoServicio::whereIn('servicio_id', function ($q) use ($user) {
                $q->select('id')->from('servicios')->where('user_id', $user->id);
            })
            ->where('mes', $mes)
            ->where('anio', $anio)
            ->pluck('servicio_id');

        // Contar solo servicios cuyo próximo pago sea <= ciclo actual y no estén pagados
        $pendientes = Servicio::where('user_id', $user->id)
            ->activos()
            ->whereNotIn('id', $serviciosPagados)
            ->where(function ($q) use ($mes, $anio) {
                $q->whereNull('proximo_mes_pago')
                  ->orWhere('proximo_anio_pago', '<', $anio)
                  ->orWhere(function ($q2) use ($mes, $anio) {
                      $q2->where('proximo_anio_pago', '=', $anio)
                         ->where('proximo_mes_pago', '<=', $mes);
                  });
            })
            ->count();

        // Alerta si faltan 3 días o menos y hay pendientes
        $mostrarAlerta = $diasRestantes <= 3 && $pendientes > 0;

        return response()->json([
            'success' => true,
            'data' => [
                'dias_restantes' => $diasRestantes,
                'fecha_restablecimiento' => $fechaRestablecimiento->toDateString(),
                'servicios_pendientes' => $pendientes,
                'servicios_pagados' => $serviciosPagados->count(),
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
