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
        // Agregar persona secundaria a usuarios
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('persona_secundaria_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('porcentaje_persona_2', 5, 2)->default(40.00);
        });

        // Agregar user_id a medios de pago (si no existe)
        if (!Schema::hasColumn('medios_pago', 'user_id')) {
            Schema::table('medios_pago', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            });
        }

        // Agregar user_id a categorias (si no existe)
        if (!Schema::hasColumn('categorias', 'user_id')) {
            Schema::table('categorias', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            });
        }

        // Agregar user_id y registrado_por a gastos (si no existen)
        if (!Schema::hasColumn('gastos', 'user_id')) {
            Schema::table('gastos', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            });
        }
        if (!Schema::hasColumn('gastos', 'registrado_por')) {
            Schema::table('gastos', function (Blueprint $table) {
                $table->foreignId('registrado_por')->nullable()->after('tipo')->constrained('users')->nullOnDelete();
            });
        }

        // Agregar user_id a abonos (si no existe)
        if (!Schema::hasColumn('abonos', 'user_id')) {
            Schema::table('abonos', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            });
        }

        // Agregar user_id a conceptos_frecuentes (si no existe)
        if (!Schema::hasColumn('conceptos_frecuentes', 'user_id')) {
            Schema::table('conceptos_frecuentes', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            });
        }

        // Agregar user_id a plantillas (si no existe)
        if (!Schema::hasColumn('plantillas', 'user_id')) {
            Schema::table('plantillas', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            });
        }

        // Agregar user_id a gastos_recurrentes (si no existe)
        if (!Schema::hasColumn('gastos_recurrentes', 'user_id')) {
            Schema::table('gastos_recurrentes', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['persona_secundaria_id']);
            $table->dropColumn(['persona_secundaria_id', 'porcentaje_persona_2']);
        });

        $tables = ['medios_pago', 'categorias', 'abonos', 'conceptos_frecuentes', 'plantillas', 'gastos_recurrentes'];
        foreach ($tables as $tableName) {
            if (Schema::hasColumn($tableName, 'user_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropForeign(['user_id']);
                    $table->dropColumn('user_id');
                });
            }
        }

        if (Schema::hasColumn('gastos', 'user_id')) {
            Schema::table('gastos', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            });
        }
        if (Schema::hasColumn('gastos', 'registrado_por')) {
            Schema::table('gastos', function (Blueprint $table) {
                $table->dropForeign(['registrado_por']);
                $table->dropColumn('registrado_por');
            });
        }
    }
};
