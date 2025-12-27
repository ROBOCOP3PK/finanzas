<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categorias = [
            ['nombre' => 'Alimentación', 'icono' => 'utensils', 'color' => '#EF4444', 'activo' => true, 'orden' => 1],
            ['nombre' => 'Transporte', 'icono' => 'car', 'color' => '#3B82F6', 'activo' => true, 'orden' => 2],
            ['nombre' => 'Servicios', 'icono' => 'zap', 'color' => '#F59E0B', 'activo' => true, 'orden' => 3],
            ['nombre' => 'Entretenimiento', 'icono' => 'film', 'color' => '#8B5CF6', 'activo' => true, 'orden' => 4],
            ['nombre' => 'Salud', 'icono' => 'heart-pulse', 'color' => '#10B981', 'activo' => true, 'orden' => 5],
            ['nombre' => 'Otros', 'icono' => 'more-horizontal', 'color' => '#6B7280', 'activo' => true, 'orden' => 6],
        ];

        foreach ($categorias as $categoria) {
            DB::table('categorias')->insert([
                ...$categoria,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
