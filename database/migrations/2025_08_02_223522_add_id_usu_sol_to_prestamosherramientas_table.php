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
        Schema::table('prestamosherramientas', function (Blueprint $table) {
            $table->unsignedBigInteger('idUsuSol')->after('idUsuPre');
            $table->foreign('idUsuSol')->references('id')->on('users');
        
            // También recomendado: hacer fecDev obligatoria
            $table->date('fecDev')->nullable(false)->change();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prestamosherramientas', function (Blueprint $table) {
            $table->dropForeign(['idUsuSol']);
            $table->dropColumn('idUsuSol');
        
            // Revertir el cambio si es necesario
            $table->date('fecDev')->nullable()->change();
    });
    }
};
