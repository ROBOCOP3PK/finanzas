<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\User;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener el primer usuario (o crear categorías globales si no hay usuarios)
        $user = User::first();
        $userId = $user?->id;

        $categorias = [
            [
                'nombre' => 'Comida',
                'icono' => 'utensils',
                'color' => '#EF4444',
                'activo' => true,
                'orden' => 1
            ],
            [
                'nombre' => 'Hogar',
                'icono' => 'home',
                'color' => '#8B5CF6',
                'activo' => true,
                'orden' => 2
            ],
            [
                'nombre' => 'Transporte',
                'icono' => 'car',
                'color' => '#3B82F6',
                'activo' => true,
                'orden' => 3
            ],
            [
                'nombre' => 'Personal',
                'icono' => 'user',
                'color' => '#EC4899',
                'activo' => true,
                'orden' => 4
            ],
            [
                'nombre' => 'Préstamo',
                'icono' => 'banknotes',
                'color' => '#F59E0B',
                'activo' => true,
                'orden' => 5
            ],
            [
                'nombre' => 'Regalos',
                'icono' => 'gift',
                'color' => '#10B981',
                'activo' => true,
                'orden' => 6
            ],
            [
                'nombre' => 'Servicios',
                'icono' => 'zap',
                'color' => '#6366F1',
                'activo' => true,
                'orden' => 7
            ],
            [
                'nombre' => 'Viajes',
                'icono' => 'plane',
                'color' => '#14B8A6',
                'activo' => true,
                'orden' => 8
            ],
        ];

        foreach ($categorias as $categoria) {
            Categoria::create([
                ...$categoria,
                'user_id' => $userId,
            ]);
        }
    }
}
