<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Tabla animales
        DB::statement("CREATE TABLE animales (
            idAni BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            nitAni VARCHAR(30) UNIQUE,
            espAni VARCHAR(100) NOT NULL,
            nomAni VARCHAR(100),
            razAni VARCHAR(100),
            sexAni ENUM('Hembra', 'Macho') NOT NULL,
            fecNacAni DATE,
            fecComAni DATE,
            pesAni DECIMAL(6,2),
            estAni ENUM('vivo','muerto','vendido') DEFAULT 'vivo',
            estReproAni ENUM('no_aplica','ciclo','cubierta','gestacion','parida') DEFAULT 'no_aplica',
            estSaludAni ENUM('saludable','enfermo','tratamiento') DEFAULT 'saludable',
            obsAni TEXT,
            fotoAni VARCHAR(255),
            ubicacionAni VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );");

            // Tabla historialmedico 
        DB::statement("CREATE TABLE historialmedico (
            idHisMed BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            idAni BIGINT UNSIGNED,
            idIns BIGINT UNSIGNED,
            fecHisMed DATE,
            desHisMed TEXT,
            traHisMed TEXT,
            dosHisMed VARCHAR(50),
            durHisMed VARCHAR(50),
            responHisMed VARCHAR(100),
            estRecHisMed ENUM('saludable','en tratamiento','crónico') DEFAULT 'en tratamiento',
            obsHisMed2 TEXT,
            tipHisMed ENUM('vacuna','tratamiento','control') NOT NULL,
            resHisMed VARCHAR(100),
            obsHisMed TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (idAni) REFERENCES animales(idAni) ON DELETE SET NULL
        );");

 

    // Tabla produccionanimal
        DB::statement("CREATE TABLE produccionanimal (
            idProAni BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            idAniPro BIGINT UNSIGNED,
            tipProAni ENUM(
                'leche bovina',
                'venta en pie bovino',
                'lana ovina',
                'venta en pie ovino',
                'leche ovina',
                'venta gallinas en pie',
                'huevo A',
                'huevo AA',
                'huevo AAA',
                'huevo Jumbo',
                'huevo B',
                'huevo C',
                'venta pollo engorde',
                'otros'
            ) NOT NULL,
            canProAni DECIMAL(10,2) DEFAULT NULL COMMENT 'Cantidad producida',
            uniProAni VARCHAR(20) DEFAULT NULL,
            fecProAni DATE DEFAULT NULL,
            obsProAni TEXT DEFAULT NULL,
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            canTotProAni DECIMAL(8,2) DEFAULT NULL
        );");


        // Tabla herramientas
        DB::statement("CREATE TABLE herramientas (
            idHer BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            nomHer VARCHAR(100) NOT NULL,
            catHer VARCHAR(100),
            canHer INT UNSIGNED DEFAULT 1,
            estHer ENUM('bueno','regular','malo') DEFAULT 'bueno',
            ubiHer VARCHAR(150),
            obsHer TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );");

        // Tabla prestamosHerramientas
        DB::statement("CREATE TABLE prestamosHerramientas (
            idPreHer BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            idHerPre BIGINT UNSIGNED NOT NULL,
            idUsuPre BIGINT UNSIGNED,
            fecPre DATE NOT NULL,
            fecDev DATE,
            estPre ENUM('prestado', 'devuelto', 'vencido') DEFAULT 'prestado',
            obsPre TEXT,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (idHerPre) REFERENCES herramientas(idHer) ON DELETE CASCADE,
            FOREIGN KEY (idUsuPre) REFERENCES users(id) ON DELETE SET NULL
        );");

        // Tabla mantenimientos
        DB::statement("CREATE TABLE mantenimientos (
            idMan BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            idHerMan BIGINT UNSIGNED NOT NULL,
            fecMan DATE NOT NULL,
            tipMan ENUM('preventivo','correctivo','predictivo') NOT NULL DEFAULT 'preventivo',
            estMan ENUM('pendiente','en proceso','completado') NOT NULL DEFAULT 'pendiente',
            desMan TEXT,
            resMan VARCHAR(100),
            obsMan TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (idHerMan) REFERENCES herramientas(idHer) ON DELETE CASCADE
        );");

        // Tabla insumos
        DB::statement("CREATE TABLE insumos (
            idIns BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            nomIns VARCHAR(100) NOT NULL,
            tipIns VARCHAR(100),
            marIns VARCHAR(100),
            canIns DECIMAL(10,2) NOT NULL,
            uniIns VARCHAR(50) NOT NULL,
            fecVenIns DATE,
            estIns ENUM('disponible','agotado','vencido') DEFAULT 'disponible',
            obsIns TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );");

        // Tabla comprasGastos
        DB::statement("CREATE TABLE comprasGastos (
            idComGas BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            tipComGas ENUM('compra','gasto') NOT NULL,
            catComGas VARCHAR(100),
            desComGas TEXT,
            monComGas DECIMAL(10,2) NOT NULL,
            fecComGas DATE NOT NULL,
            metPagComGas VARCHAR(50),
            provComGas VARCHAR(100),
            docComGas VARCHAR(255),
            obsComGas TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );");

        // Tabla facturas
        DB::statement("CREATE TABLE facturas (
            idFac BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            idUsuFac BIGINT UNSIGNED,
            nomCliFac VARCHAR(100),
            tipDocCliFac ENUM('NIT','CC','CE','Pasaporte') DEFAULT 'CC',
            docCliFac VARCHAR(20),
            fecFac DATE NOT NULL,
            totFac DECIMAL(10,2) NOT NULL,
            metPagFac VARCHAR(50),
            estFac ENUM('emitida','pagada','anulada','pendiente') DEFAULT 'emitida',
            obsFac TEXT,
            pdfFac VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (idUsuFac) REFERENCES users(id) ON DELETE SET NULL
        );");

        // Tabla proveedores
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
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );");

        // Tabla auditoria
        DB::statement("CREATE TABLE auditoria (
            idAud BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            idUsuAud BIGINT UNSIGNED,
            usuAud VARCHAR(100),
            rolAud VARCHAR(50),
            opeAud ENUM('INSERT','UPDATE','DELETE','LOGIN','LOGOUT') NOT NULL,
            tablaAud VARCHAR(100),
            regAud VARCHAR(100),
            desAud TEXT,
            ipAud VARCHAR(45),
            fecAud TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha y hora de la operación',
            FOREIGN KEY (idUsuAud) REFERENCES users(id) ON DELETE SET NULL
        );");

        // Tabla reportes
        DB::statement("CREATE TABLE reportes (
            idRep BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            idUsuRep BIGINT UNSIGNED,
            nomRep VARCHAR(100) NOT NULL,
            tipRep ENUM('produccion','consumo','financiero','inventario') NOT NULL,
            desRep TEXT,
            fecRep DATE NOT NULL,
            formatoRep ENUM('pdf','excel','csv') DEFAULT 'pdf',
            archivoRep VARCHAR(255),
            estadoRep ENUM('activo','archivado','eliminado') DEFAULT 'activo',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (idUsuRep) REFERENCES users(id) ON DELETE SET NULL
        );");

        // Tabla actividades
        DB::statement("CREATE TABLE actividades (
            idAct BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            nomAct VARCHAR(100) NOT NULL,
            tipAct VARCHAR(100),
            desAct TEXT,
            fecAct DATE NOT NULL,
            priAct ENUM('alta','media','baja') DEFAULT 'media',
            estAct ENUM('pendiente','completada','vencida') DEFAULT 'pendiente',
            resAct VARCHAR(100),
            obsAct TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );");
    }

    public function down(): void
    {
     
    }
};