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
        Schema::create('database_backups', function (Blueprint $table) {
            $table->id('idBac');
            $table->unsignedBigInteger('idUsuBac');
            $table->string('nomBac', 50)->default('famasy');
            $table->string('verBac', 20); // versión YYYYMMDD_HHMMSS
            $table->string('arcBac', 255); // nombre del archivo
            $table->string('tamBac', 20)->default('0 bytes');
            $table->text('obsBac')->nullable(); // observación
            $table->enum('tipBac', ['export', 'import', 'clean']); // tipo de acción
            $table->timestamps();
            
            // Relación con usuario
            $table->foreign('idUsuBac')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('database_backups');
    }
};