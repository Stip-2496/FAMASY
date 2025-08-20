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
        if (!Schema::hasTable('direccion')) {
            Schema::create('direccion', function (Blueprint $table) {
                $table->id('idDir');
                $table->unsignedBigInteger('idConDir');

                $table->string('calDir', 20);
                $table->string('barDir', 20);
                $table->string('ciuDir', 20);
                $table->string('depDir', 20);
                $table->string('codPosDir', 20);
                $table->string('paiDir', 20);

                $table->foreign('idConDir')
                      ->references('idCon')
                      ->on('contacto')
                      ->onDelete('cascade')
                      ->onUpdate('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('direccion');
    }
};
