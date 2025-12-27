<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Gasto extends Model
{
    protected $fillable = [
        'fecha',
        'medio_pago_id',
        'categoria_id',
        'concepto',
        'valor',
        'tipo'
    ];

    protected $casts = [
        'fecha' => 'date',
        'valor' => 'decimal:2'
    ];

    // Constantes para tipos
    const TIPO_PERSONA_1 = 'persona_1';
    const TIPO_PERSONA_2 = 'persona_2';
    const TIPO_CASA = 'casa';

    const TIPOS = [
        self::TIPO_PERSONA_1,
        self::TIPO_PERSONA_2,
        self::TIPO_CASA
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
    public function scopeFecha($query, $desde, $hasta)
    {
        return $query->whereBetween('fecha', [$desde, $hasta]);
    }

    public function scopeTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopeMedioPago($query, $medioPagoId)
    {
        return $query->where('medio_pago_id', $medioPagoId);
    }

    public function scopeCategoria($query, $categoriaId)
    {
        return $query->where('categoria_id', $categoriaId);
    }

    public function scopeDelMes($query, $mes = null, $anio = null)
    {
        $mes = $mes ?? now()->month;
        $anio = $anio ?? now()->year;

        return $query->whereMonth('fecha', $mes)
                     ->whereYear('fecha', $anio);
    }
}
