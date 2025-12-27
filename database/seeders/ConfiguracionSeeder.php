<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConfiguracionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $configuraciones = [
            ['clave' => 'nombre_persona_1', 'valor' => 'Persona 1'],
            ['clave' => 'nombre_persona_2', 'valor' => 'Persona 2'],
            ['clave' => 'porcentaje_persona_1', 'valor' => '50'],
            ['clave' => 'porcentaje_persona_2', 'valor' => '50'],
            ['clave' => 'tema', 'valor' => 'system'],
        ];

        foreach ($configuraciones as $config) {
            DB::table('configuraciones')->insert([
                ...$config,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
