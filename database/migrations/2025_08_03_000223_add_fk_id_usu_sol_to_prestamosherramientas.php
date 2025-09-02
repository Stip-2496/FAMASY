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
        // Primero asegúrate que todos los registros tengan un idUsuSol válido
        DB::table('prestamosherramientas')
            ->whereNull('idUsuSol')
            ->orWhereNotIn('idUsuSol', DB::table('users')->pluck('id'))
            ->update(['idUsuSol' => 5]); 

        // Luego agrega la FK
        Schema::table('prestamosherramientas', function (Blueprint $table) {
            $table->foreign('idUsuSol')
                  ->references('id')
                  ->on('users')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prestamosherramientas', function (Blueprint $table) {
            $table->dropForeign(['idUsuSol']);
        });
    }
};