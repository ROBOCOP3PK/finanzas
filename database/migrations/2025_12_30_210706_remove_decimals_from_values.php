<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Para SQLite, necesitamos recrear las tablas porque no soporta ALTER COLUMN
        // Primero convertimos los valores actuales (que pueden tener decimales) a enteros

        // Gastos
        DB::statement('UPDATE gastos SET valor = CAST(ROUND(valor) AS INTEGER)');

        // Abonos
        DB::statement('UPDATE abonos SET valor = CAST(ROUND(valor) AS INTEGER)');

        // Plantillas
        DB::statement('UPDATE plantillas SET valor = CAST(ROUND(valor) AS INTEGER) WHERE valor IS NOT NULL');

        // Gastos recurrentes
        DB::statement('UPDATE gastos_recurrentes SET valor = CAST(ROUND(valor) AS INTEGER)');

        // Servicios
        DB::statement('UPDATE servicios SET valor_estimado = CAST(ROUND(valor_estimado) AS INTEGER) WHERE valor_estimado IS NOT NULL');

        // Pending expenses
        DB::statement('UPDATE pending_expenses SET valor = CAST(ROUND(valor) AS INTEGER)');
    }

    public function down(): void
    {
        // No hay rollback necesario - los valores ya están redondeados
    }
};
