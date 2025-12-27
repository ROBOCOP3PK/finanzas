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
        Schema::create('gastos_recurrentes', function (Blueprint $table) {
            $table->id();
            $table->string('concepto', 255);
            $table->foreignId('medio_pago_id')->constrained('medios_pago');
            $table->foreignId('categoria_id')->nullable()->constrained('categorias');
            $table->string('tipo', 20); // persona_1, persona_2, casa
            $table->decimal('valor', 12, 2)->unsigned();
            $table->integer('dia_mes'); // 1-31
            $table->boolean('activo')->default(true);
            $table->date('ultimo_registro')->nullable();
            $table->timestamps();

            $table->index('activo', 'idx_gastos_recurrentes_activo');
            $table->index('dia_mes', 'idx_gastos_recurrentes_dia_mes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gastos_recurrentes');
    }
};
