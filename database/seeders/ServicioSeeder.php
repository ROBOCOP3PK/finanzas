<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Servicio;
use App\Models\User;
use Illuminate\Database\Seeder;

class ServicioSeeder extends Seeder
{
    public static function serviciosDefault(): array
    {
        return [
            [
                'nombre' => 'Luz',
                'icono' => 'pi pi-bolt',
                'color' => '#F59E0B',
                'activo' => true,
                'orden' => 1
            ],
            [
                'nombre' => 'Agua',
                'icono' => 'pi pi-cloud',
                'color' => '#3B82F6',
                'activo' => true,
                'orden' => 2
            ],
            [
                'nombre' => 'Gas',
                'icono' => 'pi pi-sun',
                'color' => '#EF4444',
                'activo' => true,
                'orden' => 3
            ],
            [
                'nombre' => 'Internet',
                'icono' => 'pi pi-wifi',
                'color' => '#8B5CF6',
                'activo' => true,
                'orden' => 4
            ],
            [
                'nombre' => 'Datos',
                'icono' => 'pi pi-mobile',
                'color' => '#EC4899',
                'activo' => true,
                'orden' => 5
            ],
            [
                'nombre' => 'iCloud',
                'icono' => 'pi pi-cloud',
                'color' => '#6366F1',
                'activo' => true,
                'orden' => 6
            ],
        ];
    }

    public function run(): void
    {
        $user = User::first();
        if (!$user) {
            return;
        }

        $categoriaServicios = Categoria::where('user_id', $user->id)
            ->where('nombre', 'Servicios')
            ->first();

        $categoriaId = $categoriaServicios?->id;

        foreach (self::serviciosDefault() as $servicio) {
            Servicio::create([
                ...$servicio,
                'user_id' => $user->id,
                'categoria_id' => $categoriaId,
            ]);
        }
    }

    public static function crearParaUsuario(int $userId): void
    {
        $categoriaServicios = Categoria::where('user_id', $userId)
            ->where('nombre', 'Servicios')
            ->first();

        $categoriaId = $categoriaServicios?->id;

        foreach (self::serviciosDefault() as $servicio) {
            Servicio::create([
                ...$servicio,
                'user_id' => $userId,
                'categoria_id' => $categoriaId,
            ]);
        }
    }
}
