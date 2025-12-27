<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Abono extends Model
{
    protected $fillable = [
        'fecha',
        'valor',
        'nota'
    ];

    protected $casts = [
        'fecha' => 'date',
        'valor' => 'decimal:2'
    ];

    // Scopes
    public function scopeFecha($query, $desde, $hasta)
    {
        return $query->whereBetween('fecha', [$desde, $hasta]);
    }

    public function scopeDelMes($query, $mes = null, $anio = null)
    {
        $mes = $mes ?? now()->month;
        $anio = $anio ?? now()->year;

        return $query->whereMonth('fecha', $mes)
                     ->whereYear('fecha', $anio);
    }
}
