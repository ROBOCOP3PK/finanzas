<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos_servicios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('servicio_id')->constrained('servicios')->onDelete('cascade');
            $table->foreignId('gasto_id')->nullable()->constrained('gastos')->onDelete('set null');
            $table->integer('mes');
            $table->integer('anio');
            $table->date('fecha_pago');
            $table->timestamps();

            $table->unique(['servicio_id', 'mes', 'anio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos_servicios');
    }
};
