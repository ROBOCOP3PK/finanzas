<?php

namespace App\Console\Commands;

use App\Models\PagoServicio;
use App\Models\Servicio;
use App\Models\User;
use Illuminate\Console\Command;

class RecalcularCiclosPagos extends Command
{
    protected $signature = 'pagos:recalcular-ciclos';
    protected $description = 'Recalcula el mes/anio de los pagos de servicios basandose en la fecha_pago y el dia de restablecimiento del usuario';

    public function handle()
    {
        $this->info('Recalculando ciclos de pagos de servicios...');

        $pagos = PagoServicio::with('servicio.user')->get();
        $actualizados = 0;

        foreach ($pagos as $pago) {
            $user = $pago->servicio->user ?? null;
            if (!$user) {
                continue;
            }

            $diaRestablecimiento = $user->dia_restablecimiento_servicios ?? 1;
            $fechaPago = new \DateTime($pago->fecha_pago);

            $ciclo = $this->calcularCicloFacturacion($fechaPago, $diaRestablecimiento);

            if ($pago->mes !== $ciclo['mes'] || $pago->anio !== $ciclo['anio']) {
                $this->line("Pago ID {$pago->id}: {$pago->mes}/{$pago->anio} -> {$ciclo['mes']}/{$ciclo['anio']} (fecha: {$pago->fecha_pago})");

                $pago->mes = $ciclo['mes'];
                $pago->anio = $ciclo['anio'];
                $pago->save();
                $actualizados++;
            }
        }

        $this->info("Se actualizaron {$actualizados} pagos.");

        return Command::SUCCESS;
    }

    private function calcularCicloFacturacion(\DateTimeInterface $fecha, int $diaRestablecimiento): array
    {
        $dia = (int) $fecha->format('d');
        $mes = (int) $fecha->format('n');
        $anio = (int) $fecha->format('Y');

        // Si el día de restablecimiento es 1, usamos el mes calendario normal
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
}
