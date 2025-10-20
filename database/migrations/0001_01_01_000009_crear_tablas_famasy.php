<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // 1. TABLAS BASE (sin dependencias)
        
        // Tabla animales
        DB::statement("CREATE TABLE animales (
            idAni BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            nitAni VARCHAR(30) UNIQUE,
            espAni VARCHAR(100) NOT NULL,
            razAni VARCHAR(100),
            sexAni ENUM('Hembra', 'Macho') NOT NULL,
            fecNacAni DATE,
            fecComAni DATE,
            pesAni DECIMAL(6,2),
            estAni ENUM('vivo', 'muerto', 'vendido') DEFAULT 'vivo',
            estReproAni ENUM('no_aplica', 'ciclo', 'cubierta', 'gestacion', 'parida') DEFAULT 'no_aplica',
            estSaludAni ENUM('saludable', 'enfermo', 'tratamiento') DEFAULT 'saludable',
            obsAni TEXT,
            ubicacionAni VARCHAR(100),
            proAni VARCHAR(150),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );");

        // Tabla proveedores
        DB::statement("CREATE TABLE proveedores (
            idProve BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            nomProve VARCHAR(100) NOT NULL,
            apeProve VARCHAR(100) NOT NULL,
            nitProve VARCHAR(20),
            conProve VARCHAR(100),
            telProve VARCHAR(20),
            emailProve VARCHAR(100),
            dirProve VARCHAR(255),
            ciuProve VARCHAR(30) NOT NULL,
            depProve VARCHAR(30) NOT NULL,
            tipSumProve VARCHAR(100),
            obsProve TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );");

        // Tabla clientes
        DB::statement("CREATE TABLE clientes (
            idCli BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            nomCli VARCHAR(100) NOT NULL,
            tipDocCli ENUM('NIT','CC','CE','Pasaporte') NULL DEFAULT 'CC',
            docCli VARCHAR(20) NOT NULL UNIQUE,
            telCli VARCHAR(20) NULL,
            emailCli VARCHAR(100) NULL,
            dirCli VARCHAR(255) NULL,
            tipCli ENUM('particular','empresa') NULL DEFAULT 'particular',
            estCli ENUM('activo','inactivo') NULL DEFAULT 'activo',
            obsCli TEXT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );");

        // Tabla comprasGastos
        DB::statement("CREATE TABLE comprasGastos (
            idComGas BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            tipComGas ENUM('compra', 'gasto') NOT NULL,
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

        // Tabla actividades
        DB::statement("CREATE TABLE actividades (
            idAct BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            nomAct VARCHAR(100) NOT NULL,
            tipAct VARCHAR(100),
            desAct TEXT,
            fecAct DATE NOT NULL,
            priAct ENUM('alta', 'media', 'baja') DEFAULT 'media',
            estAct ENUM('pendiente', 'completada', 'vencida') DEFAULT 'pendiente',
            resAct VARCHAR(100),
            obsAct TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );");

        // Tabla categoriascontables
        DB::statement("CREATE TABLE categoriascontables (
            idCatCont BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            nomCatCont VARCHAR(100) NOT NULL,
            tipCatCont ENUM('ingreso','egreso') NOT NULL,
            desCatCont TEXT NULL,
            colorCatCont VARCHAR(7) NULL DEFAULT '#67D432',
            estCatCont TINYINT(1) NULL DEFAULT 1,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );");

        // Tabla config_contable
        DB::statement("CREATE TABLE config_contable (
            idConfigCont BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            conceptoConfig VARCHAR(100) NOT NULL UNIQUE,
            valorConfig TEXT NULL,
            tipoConfig ENUM('texto','numero','fecha','booleano') NULL DEFAULT 'texto',
            desConfig TEXT NULL,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );");

        // 2. TABLAS DE NIVEL 1 (dependen de proveedores)
        
        // Tabla insumos
        DB::statement("CREATE TABLE insumos (
            idIns BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            nomIns VARCHAR(100) NOT NULL,
            tipIns VARCHAR(100),
            marIns VARCHAR(100),
            canIns DECIMAL(10,2),
            stockMinIns DECIMAL(10,2) UNSIGNED,
            stockMaxIns DECIMAL(10,2) UNSIGNED,
            idProveIns BIGINT UNSIGNED,
            uniIns VARCHAR(50) NOT NULL,
            fecVenIns DATE,
            estIns ENUM('disponible', 'agotado', 'vencido') DEFAULT 'disponible',
            obsIns TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            deleted_at TIMESTAMP NULL DEFAULT NULL,
            FOREIGN KEY (idProveIns) REFERENCES proveedores(idProve) ON DELETE SET NULL
        );");

        // Tabla herramientas
        DB::statement("CREATE TABLE herramientas (
            idHer BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            nomHer VARCHAR(100) NOT NULL,
            catHer VARCHAR(100),
            canHer INT UNSIGNED,
            stockMinHer INT UNSIGNED,
            stockMaxHer INT UNSIGNED,
            idProveHer BIGINT UNSIGNED,
            estHer ENUM('bueno', 'regular', 'malo') DEFAULT 'bueno',
            ubiHer VARCHAR(150),
            obsHer TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            deleted_at TIMESTAMP NULL DEFAULT NULL,
            FOREIGN KEY (idProveHer) REFERENCES proveedores(idProve) ON DELETE SET NULL
        );");

        // Tabla historialmedico (depende de animales, insumos, proveedores)
        DB::statement("CREATE TABLE historialmedico (
            idHisMed BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            idAni BIGINT UNSIGNED,
            idIns BIGINT UNSIGNED,
            idProve BIGINT UNSIGNED,
            fecHisMed DATE,
            desHisMed TEXT,
            traHisMed TEXT,
            dosHisMed VARCHAR(50),
            durHisMed VARCHAR(50),
            tipHisMed ENUM('vacuna', 'tratamiento', 'control') NOT NULL,
            responHisMed VARCHAR(100),
            estRecHisMed ENUM('saludable', 'en tratamiento', 'crónico') DEFAULT 'en tratamiento',
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
            canProAni DECIMAL(10,2) DEFAULT NULL,
            uniProAni VARCHAR(20) DEFAULT NULL,
            fecProAni DATE DEFAULT NULL,
            obsProAni TEXT DEFAULT NULL,
            canTotProAni DECIMAL(8,2) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (idAniPro) REFERENCES animales(idAni) ON DELETE SET NULL
        );");

        // Tabla facturas (depende de users y clientes)
        DB::statement("CREATE TABLE facturas (
            idFac BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            idUsuFac BIGINT UNSIGNED,
            idCliFac BIGINT UNSIGNED,
            nomCliFac VARCHAR(100),
            tipDocCliFac ENUM('NIT', 'CC', 'CE', 'Pasaporte') DEFAULT 'CC',
            docCliFac VARCHAR(20),
            fecFac DATE NOT NULL,
            totFac DECIMAL(10,2) NOT NULL,
            subtotalFac DECIMAL(10,2),
            ivaFac DECIMAL(10,2),
            descuentoFac DECIMAL(10,2) DEFAULT 0.00,
            metPagFac VARCHAR(50),
            estFac ENUM('emitida', 'pagada', 'anulada', 'pendiente') DEFAULT 'emitida',
            obsFac TEXT,
            pdfFac VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (idUsuFac) REFERENCES users(id) ON DELETE SET NULL,
            FOREIGN KEY (idCliFac) REFERENCES clientes(idCli) ON DELETE SET NULL
        );");

        // Tabla auditoria (depende de users)
        DB::statement("CREATE TABLE auditoria (
            idAud BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            idUsuAud BIGINT UNSIGNED,
            usuAud VARCHAR(100),
            rolAud VARCHAR(50),
            opeAud ENUM('INSERT', 'UPDATE', 'DELETE', 'LOGIN', 'LOGOUT', 'LOGIN_FAILED') NOT NULL,
            tablaAud VARCHAR(100),
            regAud VARCHAR(100),
            desAud TEXT,
            ipAud VARCHAR(45),
            fecAud TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha y hora de la operación',
            FOREIGN KEY (idUsuAud) REFERENCES users(id) ON DELETE SET NULL
        );");

        // Tabla reportes (depende de users)
        DB::statement("CREATE TABLE reportes (
            idRep BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            idUsuRep BIGINT UNSIGNED,
            nomRep VARCHAR(100) NOT NULL,
            tipRep ENUM('produccion', 'consumo', 'financiero', 'inventario') NOT NULL,
            desRep TEXT,
            fecRep DATE NOT NULL,
            formatoRep ENUM('pdf', 'excel', 'csv') DEFAULT 'pdf',
            archivoRep VARCHAR(255),
            estadoRep ENUM('activo', 'archivado', 'eliminado') DEFAULT 'activo',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (idUsuRep) REFERENCES users(id) ON DELETE SET NULL
        );");

        // 3. TABLAS DE NIVEL 2 (dependen de herramientas y users)
        
        // Tabla prestamosHerramientas
        DB::statement("CREATE TABLE prestamosHerramientas (
            idPreHer BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            idHerPre BIGINT UNSIGNED NOT NULL,
            idUsuPre BIGINT UNSIGNED,
            fecPre DATETIME NOT NULL,
            fecDev DATETIME,
            estPre ENUM('prestado', 'devuelto', 'vencido') DEFAULT 'prestado',
            obsPre TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (idHerPre) REFERENCES herramientas(idHer) ON DELETE CASCADE,
            FOREIGN KEY (idUsuPre) REFERENCES users(id) ON DELETE SET NULL
        );");

        // Tabla mantenimientos
        DB::statement("CREATE TABLE mantenimientos (
            idMan BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            idHerMan BIGINT UNSIGNED,
            nomHerMan VARCHAR(255) NOT NULL,
            fecMan DATE NOT NULL,
            tipMan ENUM('preventivo', 'correctivo', 'predictivo') NOT NULL DEFAULT 'preventivo',
            estMan ENUM('pendiente', 'en proceso', 'completado') NOT NULL DEFAULT 'pendiente',
            desMan TEXT,
            resMan VARCHAR(100),
            obsMan TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (idHerMan) REFERENCES herramientas(idHer) ON DELETE SET NULL
        );");

        // Tabla facturadetalles (depende de facturas, animales, produccionanimal, insumos)
        DB::statement("CREATE TABLE facturadetalles (
            idDetFac BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            idFacDet BIGINT UNSIGNED NOT NULL,
            conceptoDet VARCHAR(200) NOT NULL,
            cantidadDet DECIMAL(10,2) NOT NULL,
            precioUnitDet DECIMAL(10,2) NOT NULL,
            subtotalDet DECIMAL(10,2) NOT NULL,
            idAniDet BIGINT UNSIGNED,
            idProAniDet BIGINT UNSIGNED,
            idInsDet BIGINT UNSIGNED,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (idFacDet) REFERENCES facturas(idFac) ON DELETE CASCADE,
            FOREIGN KEY (idAniDet) REFERENCES animales(idAni) ON DELETE SET NULL,
            FOREIGN KEY (idProAniDet) REFERENCES produccionanimal(idProAni) ON DELETE SET NULL,
            FOREIGN KEY (idInsDet) REFERENCES insumos(idIns) ON DELETE SET NULL
        );");

        // Tabla movimientoscontables
        DB::statement("CREATE TABLE movimientoscontables (
            idMovCont BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            fecMovCont DATE NOT NULL,
            tipoMovCont ENUM('ingreso','egreso') NOT NULL,
            catMovCont VARCHAR(100) NOT NULL,
            conceptoMovCont VARCHAR(200) NOT NULL,
            montoMovCont DECIMAL(12,2) NOT NULL,
            idFacMovCont BIGINT UNSIGNED NULL,
            idComGasMovCont BIGINT UNSIGNED NULL,
            idAniMovCont BIGINT UNSIGNED NULL,
            idProAniMovCont BIGINT UNSIGNED NULL,
            idInvMovCont BIGINT UNSIGNED NULL,
            obsMovCont TEXT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (idFacMovCont) REFERENCES facturas(idFac) ON DELETE SET NULL,
            FOREIGN KEY (idComGasMovCont) REFERENCES comprasGastos(idComGas) ON DELETE SET NULL,
            FOREIGN KEY (idAniMovCont) REFERENCES animales(idAni) ON DELETE SET NULL,
            FOREIGN KEY (idProAniMovCont) REFERENCES produccionanimal(idProAni) ON DELETE SET NULL,
            FOREIGN KEY (idInvMovCont) REFERENCES insumos(idIns) ON DELETE SET NULL,
            INDEX idx_fecMovCont (fecMovCont),
            INDEX idx_catMovCont (catMovCont)
        );");

        // Tabla pagos
        DB::statement("CREATE TABLE pagos (
            idPago BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            idFacPago BIGINT UNSIGNED,
            idComGasPago BIGINT UNSIGNED,
            fecPago DATE NOT NULL,
            montoPago DECIMAL(10,2) NOT NULL,
            metPago ENUM('efectivo', 'transferencia', 'cheque', 'tarjeta', 'credito') NOT NULL,
            numCompPago VARCHAR(50),
            entBancPago VARCHAR(100),
            obsPago TEXT,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (idFacPago) REFERENCES facturas(idFac) ON DELETE SET NULL,
            FOREIGN KEY (idComGasPago) REFERENCES comprasGastos(idComGas) ON DELETE SET NULL,
            INDEX fecPago_idx (fecPago),
            INDEX metPago_idx (metPago)
        );");

        // Tabla cuentaspendientes
        DB::statement("CREATE TABLE cuentaspendientes (
            idCuePen BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            tipCuePen ENUM('por_cobrar','por_pagar') NOT NULL,
            idFacCuePen BIGINT UNSIGNED NULL,
            idComGasCuePen BIGINT UNSIGNED NULL,
            idCliCuePen BIGINT UNSIGNED NULL,
            idProveCuePen BIGINT UNSIGNED NULL,
            montoOriginal DECIMAL(10,2) NOT NULL,
            montoPagado DECIMAL(10,2) NULL DEFAULT 0.00,
            montoSaldo DECIMAL(10,2) NOT NULL,
            fecVencimiento DATE NULL,
            diasVencido INT(11) NULL DEFAULT 0,
            estCuePen ENUM('pendiente','pagado','vencido','parcial') NULL DEFAULT 'pendiente',
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_tipCuePen (tipCuePen),
            INDEX idx_fecVencimiento (fecVencimiento)
        );");

        // 4. TABLAS DE NIVEL 3 (dependen de mantenimientos y prestamosHerramientas)
        
        // Tabla inventario
        DB::statement("CREATE TABLE inventario (
            idInv BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            idIns BIGINT UNSIGNED,
            idHer BIGINT UNSIGNED,
            tipMovInv ENUM('apertura', 'entrada', 'salida', 'consumo', 'prestamo_salida', 'prestamo_retorno', 'perdida', 'ajuste_pos', 'ajuste_neg', 'mantenimiento', 'venta') NOT NULL DEFAULT 'entrada',
            cantMovInv DECIMAL(10,2) NOT NULL,
            uniMovInv VARCHAR(50) NOT NULL,
            costoUnitInv DECIMAL(10,2),
            costoTotInv DECIMAL(12,2),
            fecMovInv TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            loteInv VARCHAR(100),
            fecVenceInv DATE,
            idComGas BIGINT UNSIGNED,
            idFac BIGINT UNSIGNED,
            idMan BIGINT UNSIGNED,
            idPreHer BIGINT UNSIGNED,
            idProve BIGINT UNSIGNED,
            idUsuReg BIGINT UNSIGNED NOT NULL,
            obsInv TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            deleted_at TIMESTAMP NULL DEFAULT NULL,
            FOREIGN KEY (idIns) REFERENCES insumos(idIns) ON DELETE SET NULL,
            FOREIGN KEY (idHer) REFERENCES herramientas(idHer) ON DELETE SET NULL,
            FOREIGN KEY (idComGas) REFERENCES comprasGastos(idComGas) ON DELETE SET NULL,
            FOREIGN KEY (idFac) REFERENCES facturas(idFac) ON DELETE SET NULL,
            FOREIGN KEY (idMan) REFERENCES mantenimientos(idMan) ON DELETE SET NULL,
            FOREIGN KEY (idPreHer) REFERENCES prestamosHerramientas(idPreHer) ON DELETE SET NULL,
            FOREIGN KEY (idProve) REFERENCES proveedores(idProve) ON DELETE SET NULL,
            FOREIGN KEY (idUsuReg) REFERENCES users(id) ON DELETE RESTRICT,
            INDEX tipMovInv_idx (tipMovInv),
            INDEX fecMovInv_idx (fecMovInv),
            INDEX loteInv_idx (loteInv)
        );");

        // 5. TABLAS DE VISTAS (sin claves foráneas, orden flexible)
        
        // Tabla v_alertas_stock_bajo
        DB::statement("CREATE TABLE v_alertas_stock_bajo (
            tipo_item VARCHAR(11) NOT NULL,
            id_item BIGINT UNSIGNED NOT NULL DEFAULT 0,
            nombre_item VARCHAR(100) NOT NULL,
            categoria VARCHAR(100) NULL,
            stockActual DECIMAL(32,2) NULL,
            stock_minimo DECIMAL(12,2) UNSIGNED NULL,
            porcentaje_stock DECIMAL(38,2) NULL,
            ultimoMovimiento TIMESTAMP NULL,
            nivel_alerta VARCHAR(7) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );");

        // Tabla v_cuentas_vencidas
        DB::statement("CREATE TABLE v_cuentas_vencidas (
            idCuePen BIGINT UNSIGNED NOT NULL DEFAULT 0,
            tipCuePen ENUM('por_cobrar','por_pagar') NOT NULL,
            nombre_deudor VARCHAR(100) NULL,
            documento_deudor VARCHAR(20) NULL,
            montoOriginal DECIMAL(10,2) NOT NULL,
            montoPagado DECIMAL(10,2) NULL DEFAULT 0.00,
            montoSaldo DECIMAL(10,2) NOT NULL,
            fecVencimiento DATE NULL,
            dias_vencido INT(7) NULL,
            nivel_urgencia VARCHAR(11) NULL,
            estCuePen ENUM('pendiente','pagado','vencido','parcial') NULL DEFAULT 'pendiente',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );");

        // Tabla v_estado_financiero
        DB::statement("CREATE TABLE v_estado_financiero (
            mes VARCHAR(7) NULL,
            total_ingresos DECIMAL(34,2) NULL,
            total_egresos DECIMAL(34,2) NULL,
            utilidad DECIMAL(34,2) NULL,
            total_movimientos BIGINT(21) NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );");

        // Tabla v_flujo_caja_diario
        DB::statement("CREATE TABLE v_flujo_caja_diario (
            fecha DATE NOT NULL,
            ingresos_dia DECIMAL(32,2) NULL,
            egresos_dia DECIMAL(32,2) NULL,
            flujo_neto DECIMAL(32,2) NULL,
            total_transacciones BIGINT(21) NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );");

        // Tabla v_movimientos_recientes
        DB::statement("CREATE TABLE v_movimientos_recientes (
            idInv BIGINT UNSIGNED NOT NULL DEFAULT 0,
            tipMovInv ENUM('apertura','entrada','salida','consumo','prestamo_salida','prestamo_retorno','perdida','ajuste_pos','ajuste_neg','mantenimiento','venta') NOT NULL DEFAULT 'entrada',
            cantMovInv DECIMAL(10,2) NOT NULL,
            uniMovInv VARCHAR(50) NOT NULL,
            costoTotInv DECIMAL(12,2) NULL,
            fecMovInv TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            obsInv TEXT NULL,
            item VARCHAR(113) NULL,
            categoria VARCHAR(100) NULL,
            proveedor VARCHAR(100) NULL,
            usuario_registro VARCHAR(511) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );");

        // Tabla v_proximos_vencer
        DB::statement("CREATE TABLE v_proximos_vencer (
            lote VARCHAR(100) NULL,
            fecha_vencimiento DATE NULL,
            dias_para_vencer INT(7) NULL,
            producto VARCHAR(100) NOT NULL,
            categoria VARCHAR(100) NULL,
            cantidad_disponible DECIMAL(32,2) NULL,
            unidad VARCHAR(50) NOT NULL,
            nivel_urgencia VARCHAR(8) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );");

        // Tabla v_stock_herramientas
        DB::statement("CREATE TABLE v_stock_herramientas (
            idHer BIGINT UNSIGNED NOT NULL DEFAULT 0,
            nomHer VARCHAR(100) NOT NULL,
            catHer VARCHAR(100) NULL,
            estHer ENUM('bueno','regular','malo') NULL DEFAULT 'bueno',
            ubiHer VARCHAR(150) NULL,
            stockMinHer INT UNSIGNED NULL,
            stockMaxHer INT UNSIGNED NULL,
            idProveHer BIGINT UNSIGNED NULL,
            stockActual DECIMAL(32,2) NULL,
            cantidadPrestada DECIMAL(32,2) NULL,
            ultimoMovimiento TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            totalMovimientos BIGINT(21) NOT NULL DEFAULT 0,
            valorInventario DECIMAL(34,2) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );");

        // Tabla v_stock_insumos
        DB::statement("CREATE TABLE v_stock_insumos (
            idIns BIGINT UNSIGNED NOT NULL DEFAULT 0,
            nomIns VARCHAR(100) NOT NULL,
            tipIns VARCHAR(100) NULL,
            marIns VARCHAR(100) NULL,
            uniIns VARCHAR(50) NOT NULL,
            fecVenIns DATE NULL,
            estIns ENUM('disponible','agotado','vencido') NULL DEFAULT 'disponible',
            stockMinIns DECIMAL(10,2) UNSIGNED NULL,
            stockMaxIns DECIMAL(10,2) UNSIGNED NULL,
            idProveIns BIGINT UNSIGNED NULL,
            stockActual DECIMAL(32,2) NULL,
            ultimoMovimiento TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            totalMovimientos BIGINT(21) NOT NULL DEFAULT 0,
            valorInventario DECIMAL(34,2) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );");

        // Tabla v_ventas_por_cliente
        DB::statement("CREATE TABLE v_ventas_por_cliente (
            idCli BIGINT UNSIGNED NOT NULL DEFAULT 0,
            nomCli VARCHAR(100) NOT NULL,
            tipDocCli ENUM('NIT','CC','CE','Pasaporte') NULL DEFAULT 'CC',
            docCli VARCHAR(20) NOT NULL,
            total_facturas BIGINT(21) NOT NULL DEFAULT 0,
            total_ventas DECIMAL(32,2) NULL,
            promedio_venta DECIMAL(14,6) NULL,
            ultima_venta DATE NULL,
            estado_cliente VARCHAR(8) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        );");
    }

    public function down(): void
    {
        // Orden inverso para eliminar tablas
        Schema::dropIfExists('v_ventas_por_cliente');
        Schema::dropIfExists('v_stock_insumos');
        Schema::dropIfExists('v_stock_herramientas');
        Schema::dropIfExists('v_proximos_vencer');
        Schema::dropIfExists('v_movimientos_recientes');
        Schema::dropIfExists('v_flujo_caja_diario');
        Schema::dropIfExists('v_estado_financiero');
        Schema::dropIfExists('v_cuentas_vencidas');
        Schema::dropIfExists('v_alertas_stock_bajo');
        Schema::dropIfExists('inventario');
        Schema::dropIfExists('cuentaspendientes');
        Schema::dropIfExists('pagos');
        Schema::dropIfExists('movimientoscontables');
        Schema::dropIfExists('facturadetalles');
        Schema::dropIfExists('mantenimientos');
        Schema::dropIfExists('prestamosHerramientas');
        Schema::dropIfExists('reportes');
        Schema::dropIfExists('auditoria');
        Schema::dropIfExists('facturas');
        Schema::dropIfExists('produccionanimal');
        Schema::dropIfExists('historialmedico');
        Schema::dropIfExists('herramientas');
        Schema::dropIfExists('insumos');
        Schema::dropIfExists('config_contable');
        Schema::dropIfExists('categoriascontables');
        Schema::dropIfExists('actividades');
        Schema::dropIfExists('comprasGastos');
        Schema::dropIfExists('clientes');
        Schema::dropIfExists('proveedores');
        Schema::dropIfExists('animales');
    }
};