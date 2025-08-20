<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sessions') && Schema::hasColumn('sessions', 'user_id')) {
            Schema::table('sessions', function (Blueprint $table) {
                // Asegura que la columna sea nullable y del tipo correcto
                $table->unsignedBigInteger('user_id')->nullable()->change();

                // Borra la FK si ya existe (si fue definida anteriormente)
                $table->dropForeign(['user_id']); // Esto es seguro, Laravel lo ignora si no existe

                // Crea la nueva FK con SET NULL
                $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sessions') && Schema::hasColumn('sessions', 'user_id')) {
            Schema::table('sessions', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });
        }
    }
};
