<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('servicios', function (Blueprint $table) {
            $table->unsignedTinyInteger('frecuencia_meses')->default(1)->after('referencia');
            $table->unsignedTinyInteger('proximo_mes_pago')->nullable()->after('frecuencia_meses');
            $table->unsignedSmallInteger('proximo_anio_pago')->nullable()->after('proximo_mes_pago');
        });

        // Inicializar servicios existentes con el ciclo actual
        $now = now();
        $mes = $now->month;
        $anio = $now->year;

        \DB::table('servicios')->whereNull('proximo_mes_pago')->update([
            'proximo_mes_pago' => $mes,
            'proximo_anio_pago' => $anio,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('servicios', function (Blueprint $table) {
            $table->dropColumn(['frecuencia_meses', 'proximo_mes_pago', 'proximo_anio_pago']);
        });
    }
};
