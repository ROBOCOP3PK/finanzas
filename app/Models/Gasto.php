<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Gasto extends Model
{
    protected $fillable = [
        'user_id',
        'fecha',
        'medio_pago_id',
        'categoria_id',
        'concepto',
        'valor',
        'tipo',
        'registrado_por'
    ];

    protected $casts = [
        'fecha' => 'date',
        'valor' => 'integer'
    ];

    // Constantes para tipos
    const TIPO_PERSONAL = 'personal';   // 100% del usuario principal
    const TIPO_PAREJA = 'pareja';       // 100% de la persona secundaria (genera deuda)
    const TIPO_COMPARTIDO = 'compartido'; // Se divide por porcentaje

    const TIPOS = [
        self::TIPO_PERSONAL,
        self::TIPO_PAREJA,
        self::TIPO_COMPARTIDO
    ];

    // Relaciones
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    public function medioPago(): BelongsTo
    {
        return $this->belongsTo(MedioPago::class);
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    // Scopes
    public function scopeDelUsuario($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeFecha($query, $desde, $hasta)
    {
        return $query->whereDate('fecha', '>=', $desde)
                     ->whereDate('fecha', '<=', $hasta);
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
