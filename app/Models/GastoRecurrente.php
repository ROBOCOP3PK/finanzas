<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GastoRecurrente extends Model
{
    protected $table = 'gastos_recurrentes';

    protected $fillable = [
        'concepto',
        'medio_pago_id',
        'categoria_id',
        'tipo',
        'valor',
        'dia_mes',
        'activo',
        'ultimo_registro'
    ];

    protected $casts = [
        'valor' => 'integer',
        'dia_mes' => 'integer',
        'activo' => 'boolean',
        'ultimo_registro' => 'date'
    ];

    // Relaciones
    public function medioPago(): BelongsTo
    {
        return $this->belongsTo(MedioPago::class);
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopePendientes($query)
    {
        $hoy = now();
        $inicioMes = $hoy->copy()->startOfMonth();

        return $query->activos()
            ->where('dia_mes', '<=', $hoy->day)
            ->where(function ($q) use ($inicioMes) {
                $q->whereNull('ultimo_registro')
                  ->orWhere('ultimo_registro', '<', $inicioMes);
            });
    }

    // Verificar si está pendiente este mes
    public function estaPendiente(): bool
    {
        $hoy = now();
        $inicioMes = $hoy->copy()->startOfMonth();

        if (!$this->activo) {
            return false;
        }

        if ($this->dia_mes > $hoy->day) {
            return false;
        }

        if ($this->ultimo_registro === null) {
            return true;
        }

        return $this->ultimo_registro < $inicioMes;
    }

    // Registrar gasto recurrente
    public function registrar(): Gasto
    {
        $fechaGasto = now()->setDay(min($this->dia_mes, now()->daysInMonth));

        $gasto = Gasto::create([
            'fecha' => $fechaGasto,
            'concepto' => $this->concepto,
            'medio_pago_id' => $this->medio_pago_id,
            'categoria_id' => $this->categoria_id,
            'tipo' => $this->tipo,
            'valor' => $this->valor
        ]);

        $this->update(['ultimo_registro' => now()]);

        return $gasto;
    }
}
