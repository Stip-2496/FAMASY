<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

/* Explicación de la estructura de las rutas:
// 1° Parámetro: URL pública (lo que el usuario ve en el navegador). Usar - cuando el nombre es largo. No me lo invento, lo dicen las buenas prácticas 
// 2° Parámetro: Ubicación de la vista (archivo .blade.php)
// 3° "Parámetro" (método): Alias de la ruta (para generar URLs dinámicamente)
*/

Route::get('/', function () {return view('welcome');})->name('welcome'); // Rutas Públicas (accesibles sin autenticación)
Route::view('/login', 'login')->name('login'); // Muestra la vista de inicio de sesión |No he podido cambiarle el nombre a la URL|

Route::middleware(['auth'])->group(function () { // Rutas solo accedidas por usuarios autenticados 'auth'
    // Ruta de logout silencioso
    Route::post('/logout-silent', function() {
        auth()->logout();
        session()->invalidate(); // Destruye la sesión completamente
        return response()->noContent();
    })->name('logout.silent');
    
    Route::redirect('settings', 'settings/profile'); 
    Route::view('Inicio', 'auth.home')->middleware(['verified'])->name('dashboard');
    Volt::route('Perfil', 'settings.profile')->name('settings.profile');
    Volt::route('Configuración/Contraseña', 'settings.password')->name('settings.password');
    Volt::route('Configuración/Base-de-datos', 'settings.database')->name('settings.database');
    
    // Rutas para gestión de usuarios
    Volt::route('Gestionar-usuarios', 'settings.manage-users.index')->name('settings.manage-users');
    Volt::route('Gestionar-usuarios/crear', 'settings.manage-users.create')->name('settings.manage-users.create');
    Volt::route('Gestionar-usuarios/{user}/editar', 'settings.manage-users.edit')->name('settings.manage-users.edit');
    Volt::route('Gestionar-usuarios/{user}', 'settings.manage-users.show')->name('settings.manage-users.show');

    // Ruta de descarga de la copia de base de datos 
    Route::get('/download-backup/{filename}', function ($filename) {
        if (!Storage::exists("backups/{$filename}")) {
            abort(404, 'El archivo de backup no existe');
        }
        return Storage::download("backups/{$filename}");
    })->name('download.backup');

    // Rutas para el módulo de Proveedores
    Volt::route('proveedores', 'proveedores.index')->name('proveedores.index');
    Volt::route('proveedores/crear', 'proveedores.create')->name('proveedores.create');
    Volt::route('proveedores/{proveedor}/editar', 'proveedores.edit')->name('proveedores.edit');
    Volt::route('proveedores/{proveedor}', 'proveedores.show')->name('proveedores.show');
    
    // Rutas del módulo contabilidad 
    Route::prefix('contabilidad')->name('contabilidad.')->group(function () {
        
        // Dashboard principal - Volt Component
        Volt::route('/', 'contabilidad.index')->name('index');

        // FACTURAS
    Route::prefix('facturas')->name('facturas.')->group(function () {
        Volt::route('/', 'contabilidad.facturas.index')->name('index');
        Route::get('/{id}/pdf', function($id) {
            $factura = DB::table('facturas as f')
                ->leftJoin('clientes as c', 'f.idCliFac', '=', 'c.idCli')
                ->where('f.idFac', $id)
                ->select('f.*', 'c.nomCli', 'c.dirCli')
                ->first();

            if (!$factura) {
                abort(404, 'Factura no encontrada');
            }

            $detalles = DB::table('facturadetalles')->where('idFacDet', $id)->get();
            $pdf = PDF::loadView('pdf.factura', compact('factura', 'detalles'));
            return $pdf->download('Factura-' . $id . '.pdf');
        })->name('pdf');
    });

    // COMPRAS
    Route::prefix('compras')->name('compras.')->group(function () {
        Volt::route('/', 'contabilidad.compras.index')->name('index');
        Route::get('/{id}/pdf', function($id) {
            $compra = DB::table('comprasgastos as cg')
                ->leftJoin('proveedores as p', 'cg.idProve', '=', 'p.idProve')
                ->where('cg.idComGas', $id)
                ->select('cg.*', 'p.nomProve', 'p.dirProve')
                ->first();

            if (!$compra) {
                abort(404, 'Compra no encontrada');
            }

            $pdf = PDF::loadView('pdf.compra-gasto', compact('compra'));
            return $pdf->download('Compra-' . $id . '.pdf');
        })->name('pdf');
    });
        
        // Movimientos - Volt Component
        Route::prefix('movimientos')->name('movimientos.')->group(function () {
            Volt::route('/', 'contabilidad.movimientos.index')->name('index');
        });
        
        // Reportes - Volt Component  
        Route::prefix('reportes')->name('reportes.')->group(function () {
            Volt::route('/', 'contabilidad.reportes.index')->name('index');
        });
        
        // Facturas - Volt Component
        Route::prefix('facturas')->name('facturas.')->group(function () {
            Volt::route('/', 'contabilidad.facturas.index')->name('index');
            Volt::route('/crear', 'contabilidad.facturas.create')->name('create');
            Volt::route('/{factura}', 'contabilidad.facturas.show')->name('show');
            Volt::route('/{factura}/editar', 'contabilidad.facturas.edit')->name('edit');
        });
        

        
        // Pagos - Volt Component
        Route::prefix('pagos')->name('pagos.')->group(function () {
            Volt::route('/', 'contabilidad.pagos.index')->name('index');
            Volt::route('/crear', 'contabilidad.pagos.create')->name('create');
            Volt::route('/{pago}', 'contabilidad.pagos.show')->name('show');
            Volt::route('/{pago}/editar', 'contabilidad.pagos.edit')->name('edit');
            
            // Ruta para descargar PDF de pagos
                Route::get('/{id}/pdf', function($id) {
    $pago = DB::table('comprasgastos as cg')
        ->leftJoin('cuentaspendientes as cp', 'cg.idComGas', '=', 'cp.idComGasCuePen')
        ->select('cg.*', 'cp.estCuePen as estado_cuenta')
        ->where('cg.idComGas', $id)
        ->first();

    if (!$pago) {
        abort(404, 'Pago no encontrado');
    }

    $pdf = PDF::loadView('pdf.comprobante-pago', ['pago' => $pago]);
    $pdf->setPaper('A4', 'portrait');
    
    $nombreArchivo = 'comprobante-pago-' . str_pad($pago->idComGas, 6, '0', STR_PAD_LEFT) . '.pdf';
    
    return $pdf->download($nombreArchivo);
})->name('pdf');
            });
        

        Route::prefix('gastos')->name('gastos.')->group(function () {
    Volt::route('/', 'contabilidad.gastos.index')->name('index');
    Volt::route('/crear', 'contabilidad.gastos.create')->name('create');
    Volt::route('/{gasto}', 'contabilidad.gastos.show')->name('show');
    Volt::route('/{gasto}/editar', 'contabilidad.gastos.edit')->name('edit');
    
    // Agregar esta ruta para PDF
    Route::get('/{id}/pdf', function($id) {
        $gasto = DB::table('comprasgastos as cg')
            ->leftJoin('proveedores as p', 'cg.idProve', '=', 'p.idProve')
            ->select('cg.*', 'p.nomProve', 'p.nitProve', 'p.telProve', 'p.emailProve', 'p.dirProve')
            ->where('cg.idComGas', $id)
            ->where('cg.tipComGas', 'gasto')
            ->first();

        if (!$gasto) {
            abort(404, 'Gasto no encontrado');
        }

        $datosGranja = [
            'nombre' => 'FAMASY',
            'nombre_completo' => 'Finca Agropecuaria Familiar Sostenible',
            'nit' => '900.123.456-7',
            'direccion' => 'Vereda La Esperanza, Pitalito, Huila, Colombia',
            'telefono' => '+57 318 123 4567',
            'email' => 'contacto@famasy.com'
        ];

        $pdf = PDF::loadView('pdf.comprobante-gasto', compact('gasto', 'datosGranja'));
        $pdf->setPaper('A4', 'portrait');
        
        $nombreArchivo = 'comprobante-gasto-' . str_pad($gasto->idComGas, 6, '0', STR_PAD_LEFT) . '.pdf';
        
        return $pdf->download($nombreArchivo);
    })->name('pdf');
    });

// Cuentas Pendientes - Volt Component
Route::prefix('cuentas-pendientes')->name('cuentas-pendientes.')->group(function () {
    Volt::route('/', 'contabilidad.cuentas-pendientes.index')->name('index');
    Volt::route('/crear', 'contabilidad.cuentas-pendientes.create')->name('create');
    Volt::route('/{cuenta}', 'contabilidad.cuentas-pendientes.show')->name('show');
    Volt::route('/{cuenta}/editar', 'contabilidad.cuentas-pendientes.edit')->name('edit');
    
    // Ruta para PDF de cuentas pendientes
    Route::get('/{id}/pdf', function($id) {
        try {
            // Obtener datos completos de la cuenta pendiente
            $cuenta = DB::table('cuentaspendientes as cp')
                ->leftJoin('clientes as c', 'cp.idCliCuePen', '=', 'c.idCli')
                ->leftJoin('proveedores as p', 'cp.idProveCuePen', '=', 'p.idProve')
                ->leftJoin('facturas as f', 'cp.idFacCuePen', '=', 'f.idFac')
                ->leftJoin('comprasgastos as cg', 'cp.idComGasCuePen', '=', 'cg.idComGas')
                ->select(
                    'cp.*',
                    // Datos del cliente
                    'c.nomCli as cliente_nombre',
                    'c.docCli as cliente_documento', 
                    'c.telCli as cliente_telefono',
                    'c.emailCli as cliente_email',
                    'c.dirCli as cliente_direccion',
                    'c.tipDocCli as cliente_tipo_documento',
                    // Datos del proveedor
                    'p.nomProve as proveedor_nombre',
                    'p.nitProve as proveedor_documento',
                    'p.telProve as proveedor_telefono', 
                    'p.emailProve as proveedor_email',
                    'p.dirProve as proveedor_direccion',
                    'p.conProve as proveedor_contacto',
                    // Datos de factura
                    'f.idFac as factura_numero',
                    'f.totFac as factura_total',
                    'f.fecFac as factura_fecha',
                    // Datos de compra/gasto
                    'cg.desComGas as compra_descripcion',
                    'cg.catComGas as compra_categoria',
                    'cg.provComGas as compra_proveedor'
                )
                ->where('cp.idCuePen', $id)
                ->first();

            if (!$cuenta) {
                abort(404, 'Cuenta pendiente no encontrada');
            }

            // Calcular información adicional
            $diasVencimiento = [
                'tipo' => 'normal',
                'dias' => 0,
                'texto' => 'Al día'
            ];
            
            if ($cuenta->fecVencimiento) {
                $vencimiento = \Carbon\Carbon::parse($cuenta->fecVencimiento);
                $hoy = \Carbon\Carbon::now();
                
                if ($vencimiento->isPast()) {
                    $diasVencidos = $hoy->diffInDays($vencimiento);
                    $diasVencimiento = [
                        'tipo' => 'vencido',
                        'dias' => $diasVencidos,
                        'texto' => $diasVencidos . ' días vencido'
                    ];
                } elseif ($vencimiento->diffInDays($hoy) <= 7) {
                    $diasRestantes = $vencimiento->diffInDays($hoy);
                    $diasVencimiento = [
                        'tipo' => 'proximo',
                        'dias' => $diasRestantes,
                        'texto' => 'Vence en ' . $diasRestantes . ' días'
                    ];
                } else {
                    $diasRestantes = $vencimiento->diffInDays($hoy);
                    $diasVencimiento = [
                        'tipo' => 'normal',
                        'dias' => $diasRestantes,
                        'texto' => 'Vence en ' . $diasRestantes . ' días'
                    ];
                }
            }

            $porcentajePago = 0;
            if ($cuenta->montoOriginal > 0) {
                $porcentajePago = round(($cuenta->montoPagado / $cuenta->montoOriginal) * 100, 1);
            }

            // IMPORTANTE: Cambiar pdfs por pdf
            $pdf = PDF::loadView('pdf.comprobante-cuenta-pendiente', compact('cuenta', 'diasVencimiento', 'porcentajePago'));
            $pdf->setPaper('A4', 'portrait');

            $tipoCorto = $cuenta->tipCuePen === 'por_cobrar' ? 'CxC' : 'CxP';
            $nombreArchivo = "FAMASY_{$tipoCorto}_{$cuenta->idCuePen}_" . date('Ymd') . ".pdf";

            return $pdf->download($nombreArchivo);
            
        } catch (\Exception $e) {
            // Log del error para debugging
            \Log::error('Error generando PDF cuenta pendiente: ' . $e->getMessage());
            abort(500, 'Error al generar PDF: ' . $e->getMessage());
        }
    })->name('pdf');
});
        
        // Configuración - Volt Component
        Route::prefix('configuracion')->name('configuracion.')->group(function () {
            Volt::route('/', 'contabilidad.configuracion.index')->name('index');
        });
    });
    
    // Módulo Pecuario (Animales, Producción, Salud y Peso)
    Route::prefix('pecuario')->name('pecuario.')->group(function () {

        Volt::route('/', 'pecuario.dashboard')->name('dashboard'); // Dashboard del módulo pecuario usando Volt

        // Submódulos 
        Volt::route('animales', 'pecuario.animales.index')->name('animales.index');
        Volt::route('animales/crear', 'pecuario.animales.create')->name('animales.create');
        Volt::route('animales/{animal:idAni}', 'pecuario.animales.show')->name('animales.show');
        Volt::route('animales/{animal}/editar', 'pecuario.animales.edit')->name('animales.edit');
    
        Volt::route('produccion', 'pecuario.produccion.index')->name('produccion.index');
        Volt::route('produccion/crear', 'pecuario.produccion.create')->name('produccion.create');
        Volt::route('produccion/{produccion}', 'pecuario.produccion.show')->name('produccion.show');
        Volt::route('produccion/{produccion}/editar', 'pecuario.produccion.edit')->name('produccion.edit');
    
        Volt::route('salud-peso', 'pecuario.salud-peso.index')->name('salud-peso.index');
        Volt::route('salud-peso/crear', 'pecuario.salud-peso.create')->name('salud-peso.create');
        Volt::route('salud-peso/{historial}', 'pecuario.salud-peso.show')->name('salud-peso.show');
        Volt::route('salud-peso/{historial}/editar', 'pecuario.salud-peso.edit')->name('salud-peso.edit');
    });

    // Módulo de Inventario
    Volt::route('inventario', 'inventario.dashboard')->name('inventario.dashboard');

    Route::prefix('inventario')->name('inventario.')->group(function () {
        // Herramientas
        Volt::route('herramientas', 'inventario.herramientas.index')->name('herramientas.index');
        Volt::route('herramientas/crear', 'inventario.herramientas.create')->name('herramientas.create');
        Volt::route('herramientas/{herramienta}', 'inventario.herramientas.show')->name('herramientas.show');
        Volt::route('herramientas/{herramienta}/editar', 'inventario.herramientas.edit')->name('herramientas.edit');
    
        // Insumos
        Volt::route('insumos', 'inventario.insumos.index')->name('insumos.index');
        Volt::route('insumos/crear', 'inventario.insumos.create')->name('insumos.create');
        Volt::route('insumos/{insumo}', 'inventario.insumos.show')->name('insumos.show');
        Volt::route('insumos/{insumo}/editar', 'inventario.insumos.edit')->name('insumos.edit');
    
        // Movimientos
        Volt::route('movimientos/stock', 'inventario.movimientos.stock')->name('movimientos.stock');
        Volt::route('movimientos', 'inventario.movimientos.index')->name('movimientos.index');
        Volt::route('movimientos/crear', 'inventario.movimientos.create')->name('movimientos.create');
        Volt::route('movimientos/{movimiento}', 'inventario.movimientos.show')->name('movimientos.show');
        Volt::route('movimientos/{movimiento}/editar', 'inventario.movimientos.edit')->name('movimientos.edit');
    
        // Préstamos
        Volt::route('prestamos', 'inventario.prestamos.index')->name('prestamos.index');
        Volt::route('prestamos/crear', 'inventario.prestamos.create')->name('prestamos.create');
        Volt::route('prestamos/{prestamo}', 'inventario.prestamos.show')->name('prestamos.show');
        Volt::route('prestamos/{prestamo}/editar', 'inventario.prestamos.edit')->name('prestamos.edit');
        Volt::route('prestamos/{prestamo}/devolver', 'inventario.prestamos.devolver')->name('prestamos.devolver');
    
        // Mantenimientos
        Volt::route('mantenimientos', 'inventario.mantenimientos.index')->name('mantenimientos.index');
        Volt::route('mantenimientos/crear', 'inventario.mantenimientos.create')->name('mantenimientos.create');
        Volt::route('mantenimientos/{mantenimiento}', 'inventario.mantenimientos.show')->name('mantenimientos.show');
        Volt::route('mantenimientos/{mantenimiento}/editar', 'inventario.mantenimientos.edit')->name('mantenimientos.edit');
        Volt::route('mantenimientos/{mantenimiento}/completar', 'inventario.mantenimientos.completar')->name('mantenimientos.completar');
        Route::delete('mantenimientos/{mantenimiento}', function() {abort(404);})->name('mantenimientos.destroy');
    });   
    
});

require __DIR__.'/auth.php'; //  Importación de Rutas de Autenticación que vienen con Laravel Breeze/Jetstream (ej. Registro, Login, Verificación de email y Recuperación de contraseña)


