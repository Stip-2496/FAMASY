<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;


return new class extends Migration
{
    /**
    * Se usa DB::statement para mantener compatibilidad exacta con la estructura existente
    * y poder definir directamente los tipos de columna TIMESTAMP con valores DEFAULT.
    */
    public function up(): void
    {
        DB::statement("CREATE TABLE proveedores (
        idProve BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        nomProve VARCHAR(100) NOT NULL, 
        nitProve VARCHAR(20), 
        conProve VARCHAR(100), 
        telProve VARCHAR(20), 
        emailProve VARCHAR(100), 
        dirProve VARCHAR(255), 
        tipSumProve VARCHAR(100), 
        obsProve TEXT, 
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP TABLE IF EXISTS proveedores");
    }
};
