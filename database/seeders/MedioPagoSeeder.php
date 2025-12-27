<?php

namespace Database\Seeders;

use App\Models\MedioPago;
use App\Models\User;
use Illuminate\Database\Seeder;

class MedioPagoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener el primer usuario
        $user = User::first();
        $userId = $user?->id;

        $mediosPago = [
            ['nombre' => 'Davivienda Crédito', 'icono' => 'credit-card', 'activo' => true, 'orden' => 1],
            ['nombre' => 'Daviplata', 'icono' => 'smartphone', 'activo' => true, 'orden' => 2],
            ['nombre' => 'Nequi', 'icono' => 'smartphone', 'activo' => true, 'orden' => 3],
            ['nombre' => 'Efectivo', 'icono' => 'banknote', 'activo' => true, 'orden' => 4],
        ];

        foreach ($mediosPago as $medio) {
            MedioPago::create([
                ...$medio,
                'user_id' => $userId,
            ]);
        }
    }
}
