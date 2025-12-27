<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    protected $table = 'configuraciones';

    protected $fillable = [
        'clave',
        'valor'
    ];

    // Método estático para obtener valor
    public static function obtener(string $clave, $default = null)
    {
        $config = self::where('clave', $clave)->first();
        return $config ? $config->valor : $default;
    }

    // Método estático para establecer valor
    public static function establecer(string $clave, string $valor): self
    {
        return self::updateOrCreate(
            ['clave' => $clave],
            ['valor' => $valor]
        );
    }

    // Obtener todas las configuraciones como array
    public static function todas(): array
    {
        return self::pluck('valor', 'clave')->toArray();
    }

    // Obtener porcentaje persona 1
    public static function porcentajePersona1(): float
    {
        return (float) self::obtener('porcentaje_persona_1', 50);
    }

    // Obtener porcentaje persona 2
    public static function porcentajePersona2(): float
    {
        return (float) self::obtener('porcentaje_persona_2', 50);
    }

    // Obtener nombre persona 1
    public static function nombrePersona1(): string
    {
        return self::obtener('nombre_persona_1', 'Persona 1');
    }

    // Obtener nombre persona 2
    public static function nombrePersona2(): string
    {
        return self::obtener('nombre_persona_2', 'Persona 2');
    }

    // Obtener tema
    public static function tema(): string
    {
        return self::obtener('tema', 'system');
    }
}
