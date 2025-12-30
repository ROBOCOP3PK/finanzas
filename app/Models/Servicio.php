<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Servicio extends Model
{
    protected $fillable = [
        'user_id',
        'categoria_id',
        'nombre',
        'icono',
        'color',
        'valor_estimado',
        'activo',
        'orden'
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden' => 'integer',
        'valor_estimado' => 'decimal:2'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(PagoServicio::class);
    }

    public function pagoMesActual(): HasOne
    {
        return $this->hasOne(PagoServicio::class)
            ->where('mes', now()->month)
            ->where('anio', now()->year);
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeOrdenados($query)
    {
        return $query->orderBy('orden');
    }

    public function scopeDelUsuario($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function estaPagadoMes($mes = null, $anio = null): bool
    {
        $mes = $mes ?? now()->month;
        $anio = $anio ?? now()->year;

        return $this->pagos()
            ->where('mes', $mes)
            ->where('anio', $anio)
            ->exists();
    }

    public function puedeEliminarse(): bool
    {
        return $this->pagos()->count() === 0;
    }
}
