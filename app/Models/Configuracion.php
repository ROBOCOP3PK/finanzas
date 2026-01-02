<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Configuracion extends Model
{
    protected $table = 'configuraciones';

    protected $fillable = [
        'user_id',
        'clave',
        'valor'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Método estático para obtener valor por usuario
    public static function obtener(int $userId, string $clave, $default = null)
    {
        $config = self::where('user_id', $userId)
            ->where('clave', $clave)
            ->first();
        return $config ? $config->valor : $default;
    }

    // Método estático para establecer valor por usuario
    public static function establecer(int $userId, string $clave, string $valor): self
    {
        return self::updateOrCreate(
            ['user_id' => $userId, 'clave' => $clave],
            ['valor' => $valor]
        );
    }

    // Obtener todas las configuraciones del usuario como array
    public static function todas(int $userId): array
    {
        return self::where('user_id', $userId)
            ->pluck('valor', 'clave')
            ->toArray();
    }

    // Obtener porcentaje persona 1
    public static function porcentajePersona1(int $userId): float
    {
        return (float) self::obtener($userId, 'porcentaje_persona_1', 50);
    }

    // Obtener porcentaje persona 2
    public static function porcentajePersona2(int $userId): float
    {
        return (float) self::obtener($userId, 'porcentaje_persona_2', 50);
    }

    // Obtener nombre persona 1
    public static function nombrePersona1(int $userId): string
    {
        return self::obtener($userId, 'nombre_persona_1', 'Persona 1');
    }

    // Obtener nombre persona 2 (vacio por defecto = modo individual)
    public static function nombrePersona2(int $userId): string
    {
        return self::obtener($userId, 'nombre_persona_2', '');
    }

    // Obtener tema
    public static function tema(int $userId): string
    {
        return self::obtener($userId, 'tema', 'system');
    }

    // Obtener divisa
    public static function divisa(int $userId): string
    {
        return self::obtener($userId, 'divisa', 'COP');
    }

    // Obtener formato de divisa (punto o coma como separador de miles)
    public static function formatoDivisa(int $userId): string
    {
        return self::obtener($userId, 'formato_divisa', 'punto');
    }
}
