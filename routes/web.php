<?php

use Illuminate\Support\Facades\Route; // Route: Facade para definir rutas de Laravel
use App\Models\User;
use App\Http\Controllers\AnimalController;
use App\Http\Controllers\ProduccionController;
use App\Http\Controllers\HistorialMedicoController;
use App\Http\Controllers\ProveedorController; // Ruta del controlador del módulo proveedor
use Livewire\Volt\Volt; // Volt: Componente de Livewire para rutas simplificadas
use Illuminate\Support\Facades\Storage; // Proporciona métodos para manejar archivos (Storage::exists(), Storage::download(), etc.)

/* Explicación de la estructura de las rutas:
// 1° Parámetro: URL pública (lo que el usuario ve en el navegador). Usar - cuando el nombre es largo. No me lo invento, lo dicen las buenas prácticas 
// 2° Parámetro: Ubicación de la vista (archivo .blade.php)
// 3° "Parámetro" (método): Alias de la ruta (para generar URLs dinámicamente)
*/

    Route::get('/', function () {return view('welcome');})->name('welcome'); // Rutas Públicas (accesibles sin autenticación)
    Route::view('/login', 'login')->name('login'); // Muestra la vista de inicio de sesión |No he podido cambiarle el nombre a la URL|

    Route::middleware(['auth'])->group(function () { // Rutas solo accedidas por usuarios autenticados 'auth'
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

    // Ruta para eliminar usuario (usando el método DELETE)
    Route::delete('Gestionar-usuarios/{user}', function(User $user) {
        try {
            // Eliminar también los registros relacionados
            if($user->contacto) {
                if($user->contacto->direccion) {
                    $user->contacto->direccion->delete();
                }
                $user->contacto->delete();
            }
        
            $user->delete();
        
            return redirect()->route('settings.manage-users')
                ->with('success', 'Usuario eliminado correctamente');
        } catch (\Exception $e) {
            return redirect()->route('settings.manage-users')
                ->with('error', 'Error al eliminar el usuario: ' . $e->getMessage());
        }
    })->name('settings.manage-users.destroy');

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
    
    // Ruta para eliminar (se mantiene como ruta normal ya que requiere método DELETE)
    Route::delete('proveedores/{proveedor}', [ProveedorController::class, 'destroy'])->name('proveedores.destroy');

    // Rutas del módulo contabilidad usando Livewire/Volt
    Route::prefix('contabilidad')->name('contabilidad.')->group(function () {
        Volt::route('/', 'contabilidad.index')->name('index'); // Dashboard principal
        Volt::route('movimientos', 'contabilidad.movimientos.index')->name('movimientos.index'); // Movimientos
        Volt::route('facturas', 'contabilidad.facturas.index')->name('facturas.index'); // Facturas
        Volt::route('gastos', 'contabilidad.gastos.index')->name('gastos.index'); // Gastos        
        Volt::route('pagos', 'contabilidad.pagos.index')->name('pagos.index'); // Pagos      
        Volt::route('cuentas-pendientes', 'contabilidad.cuentas-pendientes.index')->name('cuentas-pendientes.index'); // Cuentas Pendientes     
        Volt::route('reportes', 'contabilidad.reportes.index')->name('reportes.index'); // Reportes     
        Volt::route('configuracion', 'contabilidad.configuracion.index')->name('configuracion.index'); // Configuración
    });
    
    // Módulo Pecuario (Animales, Producción, Salud y Peso)
    Route::prefix('pecuario')->name('pecuario.')->group(function () {
        // Dashboard del módulo pecuario usando Volt
        Volt::route('/', 'pecuario.dashboard')->name('dashboard');

        // Submódulos con Volt
        Volt::route('animales', 'pecuario.animales.index')->name('animales.index');
        Volt::route('animales/crear', 'pecuario.animales.create')->name('animales.create');
        Volt::route('animales/{animal}', 'pecuario.animales.show')->name('animales.show');
        Volt::route('animales/{animal}/editar', 'pecuario.animales.edit')->name('animales.edit');
    
        Volt::route('produccion', 'pecuario.produccion.index')->name('produccion.index');
        Volt::route('produccion/crear', 'pecuario.produccion.create')->name('produccion.create');
        Volt::route('produccion/{produccion}', 'pecuario.produccion.show')->name('produccion.show');
        Volt::route('produccion/{produccion}/editar', 'pecuario.produccion.edit')->name('produccion.edit');
    
        Volt::route('salud-peso', 'pecuario.salud-peso.index')->name('salud-peso.index');
        Volt::route('salud-peso/crear', 'pecuario.salud-peso.create')->name('salud-peso.create');
        Volt::route('salud-peso/{historial}', 'pecuario.salud-peso.show')->name('salud-peso.show');
        Volt::route('salud-peso/{historial}/editar', 'pecuario.salud-peso.edit')->name('salud-peso.edit');

        // Para las acciones de destruir (DELETE) debes mantenerlas como rutas normales
        Route::delete('animales/{animal}', [AnimalController::class, 'destroy'])->name('animales.destroy');
        Route::delete('produccion/{produccion}', [ProduccionController::class, 'destroy'])->name('produccion.destroy');
        Route::delete('salud-peso/{historial}', [HistorialMedicoController::class, 'destroy'])->name('salud-peso.destroy');
    });
 
});

require __DIR__.'/auth.php'; //  Importación de Rutas de Autenticación que vienen con Laravel Breeze/Jetstream (ej. Registro, Login, Verificación de email y Recuperación de contraseña)