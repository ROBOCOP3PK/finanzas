<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\User;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    public static function categoriasDefault(): array
    {
        return [
            [
                'nombre' => 'Comida',
                'icono' => 'pi pi-shopping-cart',
                'color' => '#EF4444',
                'activo' => true,
                'orden' => 1
            ],
            [
                'nombre' => 'Hogar',
                'icono' => 'pi pi-home',
                'color' => '#8B5CF6',
                'activo' => true,
                'orden' => 2
            ],
            [
                'nombre' => 'Transporte',
                'icono' => 'pi pi-car',
                'color' => '#3B82F6',
                'activo' => true,
                'orden' => 3
            ],
            [
                'nombre' => 'Personal',
                'icono' => 'pi pi-user',
                'color' => '#EC4899',
                'activo' => true,
                'orden' => 4
            ],
            [
                'nombre' => 'Prestamo',
                'icono' => 'pi pi-wallet',
                'color' => '#F59E0B',
                'activo' => true,
                'orden' => 5
            ],
            [
                'nombre' => 'Regalos',
                'icono' => 'pi pi-gift',
                'color' => '#10B981',
                'activo' => true,
                'orden' => 6
            ],
            [
                'nombre' => 'Servicios',
                'icono' => 'pi pi-bolt',
                'color' => '#6366F1',
                'activo' => true,
                'orden' => 7
            ],
            [
                'nombre' => 'Viajes',
                'icono' => 'pi pi-send',
                'color' => '#14B8A6',
                'activo' => true,
                'orden' => 8
            ],
        ];
    }

    public function run(): void
    {
        $user = User::first();
        $userId = $user?->id;

        foreach (self::categoriasDefault() as $categoria) {
            Categoria::create([
                ...$categoria,
                'user_id' => $userId,
            ]);
        }
    }

    public static function crearParaUsuario(int $userId): void
    {
        foreach (self::categoriasDefault() as $categoria) {
            Categoria::create([
                ...$categoria,
                'user_id' => $userId,
            ]);
        }
    }
}
