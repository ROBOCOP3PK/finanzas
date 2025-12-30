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
        Schema::create('pending_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('data_share_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();

            // Datos del gasto propuesto
            $table->date('fecha');
            $table->foreignId('medio_pago_id')->nullable()->constrained('medios_pago')->nullOnDelete();
            $table->foreignId('categoria_id')->nullable()->constrained('categorias')->nullOnDelete();
            $table->string('concepto');
            $table->decimal('valor', 12, 2);
            $table->enum('tipo', ['personal', 'pareja', 'compartido'])->default('personal');

            // Estado de aprobacion
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('resulting_gasto_id')->nullable()->constrained('gastos')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();

            $table->timestamps();

            $table->index(['owner_id', 'status']);
            $table->index(['created_by', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pending_expenses');
    }
};
