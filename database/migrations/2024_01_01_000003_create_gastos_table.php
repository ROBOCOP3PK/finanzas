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
        Schema::create('gastos', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->foreignId('medio_pago_id')->constrained('medios_pago');
            $table->foreignId('categoria_id')->nullable()->constrained('categorias');
            $table->string('concepto', 255);
            $table->decimal('valor', 12, 2)->unsigned();
            $table->string('tipo', 20); // persona_1, persona_2, casa
            $table->timestamps();

            $table->index('fecha', 'idx_gastos_fecha');
            $table->index('tipo', 'idx_gastos_tipo');
            $table->index('medio_pago_id', 'idx_gastos_medio_pago_id');
            $table->index('categoria_id', 'idx_gastos_categoria_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gastos');
    }
};
