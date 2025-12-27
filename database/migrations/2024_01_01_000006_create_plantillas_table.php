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
        Schema::create('plantillas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50);
            $table->string('concepto', 255);
            $table->foreignId('medio_pago_id')->constrained('medios_pago');
            $table->foreignId('categoria_id')->nullable()->constrained('categorias');
            $table->string('tipo', 20); // persona_1, persona_2, casa
            $table->decimal('valor', 12, 2)->nullable();
            $table->integer('uso_count')->default(0);
            $table->boolean('activo')->default(true);
            $table->integer('orden')->default(0);
            $table->timestamps();

            $table->index('activo', 'idx_plantillas_activo');
            $table->index('orden', 'idx_plantillas_orden');
            $table->index('uso_count', 'idx_plantillas_uso_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plantillas');
    }
};
