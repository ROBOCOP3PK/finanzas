<?php

namespace Database\Seeders;

use App\Models\MedioPago;
use App\Models\User;
use Illuminate\Database\Seeder;

class MedioPagoSeeder extends Seeder
{
    public static function mediosPagoDefault(): array
    {
        return [
            ['nombre' => 'Tarjeta Crédito', 'icono' => 'pi pi-credit-card', 'activo' => true, 'orden' => 1],
            ['nombre' => 'Efectivo', 'icono' => 'pi pi-money-bill', 'activo' => true, 'orden' => 2],
        ];
    }

    public function run(): void
    {
        $user = User::first();
        if (!$user) {
            return;
        }

        foreach (self::mediosPagoDefault() as $medio) {
            MedioPago::create([
                ...$medio,
                'user_id' => $user->id,
            ]);
        }
    }

    public static function crearParaUsuario(int $userId): void
    {
        foreach (self::mediosPagoDefault() as $medio) {
            MedioPago::create([
                ...$medio,
                'user_id' => $userId,
            ]);
        }
    }
}
