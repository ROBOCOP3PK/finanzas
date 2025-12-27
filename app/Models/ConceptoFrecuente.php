<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConceptoFrecuente extends Model
{
    protected $table = 'conceptos_frecuentes';

    protected $fillable = [
        'concepto',
        'medio_pago_id',
        'tipo',
        'uso_count',
        'es_favorito'
    ];

    protected $casts = [
        'uso_count' => 'integer',
        'es_favorito' => 'boolean'
    ];

    // Relaciones
    public function medioPago(): BelongsTo
    {
        return $this->belongsTo(MedioPago::class);
    }

    // Scopes
    public function scopeFavoritos($query)
    {
        return $query->where('es_favorito', true);
    }

    public function scopeMasUsados($query, $limite = 10)
    {
        return $query->orderByDesc('uso_count')->limit($limite);
    }

    public function scopeBuscar($query, $texto)
    {
        return $query->where('concepto', 'like', '%' . $texto . '%');
    }

    // Incrementar uso
    public function incrementarUso(): void
    {
        $this->increment('uso_count');
    }

    // Buscar o crear concepto
    public static function registrarUso(string $concepto, ?int $medioPagoId = null, ?string $tipo = null): self
    {
        $registro = self::firstOrCreate(
            ['concepto' => $concepto],
            ['medio_pago_id' => $medioPagoId, 'tipo' => $tipo]
        );
        $registro->incrementarUso();
        return $registro;
    }
}
