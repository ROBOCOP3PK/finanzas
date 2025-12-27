<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Plantilla extends Model
{
    protected $fillable = [
        'nombre',
        'concepto',
        'medio_pago_id',
        'categoria_id',
        'tipo',
        'valor',
        'uso_count',
        'activo',
        'orden'
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'uso_count' => 'integer',
        'activo' => 'boolean',
        'orden' => 'integer'
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
    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    public function scopeOrdenadas($query)
    {
        return $query->orderBy('orden');
    }

    public function scopeMasUsadas($query, $limite = 6)
    {
        return $query->orderByDesc('uso_count')->limit($limite);
    }

    // Usar plantilla (crea gasto)
    public function usar($fecha, $valorOverride = null): Gasto
    {
        $this->increment('uso_count');

        return Gasto::create([
            'fecha' => $fecha,
            'concepto' => $this->concepto,
            'medio_pago_id' => $this->medio_pago_id,
            'categoria_id' => $this->categoria_id,
            'tipo' => $this->tipo,
            'valor' => $valorOverride ?? $this->valor
        ]);
    }
}
