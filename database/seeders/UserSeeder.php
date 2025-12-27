<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Crear usuarios iniciales
        // Cambiar estos datos por los nombres y emails reales
        User::create([
            'name' => 'Usuario 1',
            'email' => 'usuario1@finanzas.local',
            'password' => Hash::make('password123'),
        ]);

        User::create([
            'name' => 'Usuario 2',
            'email' => 'usuario2@finanzas.local',
            'password' => Hash::make('password123'),
        ]);
    }
}
