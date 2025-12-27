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
        Schema::create('conceptos_frecuentes', function (Blueprint $table) {
            $table->id();
            $table->string('concepto', 255);
            $table->foreignId('medio_pago_id')->nullable()->constrained('medios_pago');
            $table->string('tipo', 20)->nullable(); // persona_1, persona_2, casa
            $table->integer('uso_count')->default(1);
            $table->boolean('es_favorito')->default(false);
            $table->timestamps();

            $table->index('concepto', 'idx_conceptos_frecuentes_concepto');
            $table->index('uso_count', 'idx_conceptos_frecuentes_uso_count');
            $table->index('es_favorito', 'idx_conceptos_frecuentes_favorito');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conceptos_frecuentes');
    }
};
