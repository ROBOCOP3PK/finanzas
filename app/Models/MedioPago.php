<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedioPago extends Model
{
    protected $table = 'medios_pago';

    protected $fillable = [
        'user_id',
        'nombre',
        'icono',
        'activo',
        'orden'
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden' => 'integer'
    ];

    // Relaciones
    public function gastos(): HasMany
    {
        return $this->hasMany(Gasto::class);
    }

    public function plantillas(): HasMany
    {
        return $this->hasMany(Plantilla::class);
    }

    public function gastosRecurrentes(): HasMany
    {
        return $this->hasMany(GastoRecurrente::class);
    }

    public function conceptosFrecuentes(): HasMany
    {
        return $this->hasMany(ConceptoFrecuente::class);
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeOrdenados($query)
    {
        return $query->orderBy('orden');
    }

    // Verificar si puede eliminarse
    public function puedeEliminarse(): bool
    {
        return $this->gastos()->count() === 0;
    }
}
