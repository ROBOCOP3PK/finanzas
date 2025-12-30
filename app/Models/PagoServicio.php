<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PagoServicio extends Model
{
    protected $table = 'pagos_servicios';

    protected $fillable = [
        'servicio_id',
        'gasto_id',
        'mes',
        'anio',
        'fecha_pago'
    ];

    protected $casts = [
        'mes' => 'integer',
        'anio' => 'integer',
        'fecha_pago' => 'date'
    ];

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class);
    }

    public function gasto(): BelongsTo
    {
        return $this->belongsTo(Gasto::class);
    }

    public function scopeDelMes($query, $mes = null, $anio = null)
    {
        $mes = $mes ?? now()->month;
        $anio = $anio ?? now()->year;

        return $query->where('mes', $mes)->where('anio', $anio);
    }
}
