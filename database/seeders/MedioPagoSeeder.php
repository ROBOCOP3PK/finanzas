<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MedioPagoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mediosPago = [
            ['nombre' => 'Davivienda Crédito', 'icono' => 'credit-card', 'activo' => true, 'orden' => 1],
            ['nombre' => 'Daviplata', 'icono' => 'smartphone', 'activo' => true, 'orden' => 2],
            ['nombre' => 'Nequi', 'icono' => 'smartphone', 'activo' => true, 'orden' => 3],
            ['nombre' => 'Efectivo', 'icono' => 'banknote', 'activo' => true, 'orden' => 4],
        ];

        foreach ($mediosPago as $medio) {
            DB::table('medios_pago')->insert([
                ...$medio,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
