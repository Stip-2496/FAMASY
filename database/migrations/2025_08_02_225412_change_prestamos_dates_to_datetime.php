<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement('ALTER TABLE prestamosherramientas MODIFY fecPre DATETIME NOT NULL');
        DB::statement('ALTER TABLE prestamosherramientas MODIFY fecDev DATETIME NULL');
    }

    public function down()
    {
        DB::statement('ALTER TABLE prestamosherramientas MODIFY fecPre DATE NOT NULL');
        DB::statement('ALTER TABLE prestamosherramientas MODIFY fecDev DATE NULL');
    }
};