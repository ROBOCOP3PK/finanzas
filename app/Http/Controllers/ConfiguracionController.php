<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConfiguracionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        return response()->json([
            'success' => true,
            'data' => Configuracion::todas($userId)
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'nombre_persona_1' => 'sometimes|string|max:50',
            'nombre_persona_2' => 'sometimes|nullable|string|max:50',
            'porcentaje_persona_1' => 'sometimes|numeric|min:0|max:100',
            'porcentaje_persona_2' => 'sometimes|numeric|min:0|max:100',
            'tema' => 'sometimes|in:light,dark,system',
            'divisa' => 'sometimes|in:COP,USD,EUR,MXN',
            'formato_divisa' => 'sometimes|in:punto,coma',
            'dia_restablecimiento_servicios' => 'sometimes|integer|min:1|max:31'
        ]);

        $user = $request->user();
        $userId = $user->id;

        // Si se actualiza el dia de restablecimiento, guardarlo en el usuario
        if ($request->has('dia_restablecimiento_servicios')) {
            $user->update([
                'dia_restablecimiento_servicios' => $request->dia_restablecimiento_servicios
            ]);
        }

        $configuraciones = $request->only([
            'nombre_persona_1',
            'nombre_persona_2',
            'porcentaje_persona_1',
            'porcentaje_persona_2',
            'tema',
            'divisa',
            'formato_divisa'
        ]);

        // Validar que los porcentajes sumen 100
        if (isset($configuraciones['porcentaje_persona_1']) || isset($configuraciones['porcentaje_persona_2'])) {
            $porcentaje1 = $configuraciones['porcentaje_persona_1'] ?? Configuracion::porcentajePersona1($userId);
            $porcentaje2 = $configuraciones['porcentaje_persona_2'] ?? Configuracion::porcentajePersona2($userId);

            if (($porcentaje1 + $porcentaje2) != 100) {
                return response()->json([
                    'success' => false,
                    'message' => 'Los porcentajes deben sumar 100%',
                    'errors' => [
                        'porcentaje_persona_1' => ['Los porcentajes deben sumar 100%']
                    ]
                ], 422);
            }

            // Sincronizar porcentaje_persona_2 con la tabla users para calculos de deuda
            $user->update([
                'porcentaje_persona_2' => $porcentaje2
            ]);
        }

        foreach ($configuraciones as $clave => $valor) {
            Configuracion::establecer($userId, $clave, (string) $valor);
        }

        return response()->json([
            'success' => true,
            'data' => Configuracion::todas($userId),
            'message' => 'Configuracion actualizada correctamente'
        ]);
    }
}
