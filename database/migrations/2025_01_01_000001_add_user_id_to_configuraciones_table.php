<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Agregar user_id a la tabla configuraciones
        Schema::table('configuraciones', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->onDelete('cascade');
        });

        // Cambiar el índice único para que sea clave + user_id
        Schema::table('configuraciones', function (Blueprint $table) {
            $table->dropUnique(['clave']);
            $table->unique(['user_id', 'clave']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('configuraciones', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'clave']);
            $table->unique('clave');
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
