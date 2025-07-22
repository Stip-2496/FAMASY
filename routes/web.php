<?php

use Illuminate\Support\Facades\Route; // Route: Facade para definir rutas de Laravel
use Livewire\Volt\Volt; // Volt: Componente de Livewire para rutas simplificadas
use Illuminate\Support\Facades\Storage; // Proporciona métodos para manejar archivos (Storage::exists(), Storage::download(), etc.)
use App\Http\Controllers\ProveedorController; // Controlador del módulo Proveedores

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
    // Ruta de descarga de la copia de base de datos 
    Route::get('/download-backup/{filename}', function ($filename) {
        if (!Storage::exists("backups/{$filename}")) {
            abort(404, 'El archivo de backup no existe');
        }
        return Storage::download("backups/{$filename}");
    })->name('download.backup');
    // Rutas para el módulo de Proveedores (requieren autenticación)
    Route::resource('proveedores', ProveedorController::class);
    Route::get('proveedores-search', [ProveedorController::class, 'search'])->name('proveedores.search');
});

require __DIR__.'/auth.php'; //  Importación de Rutas de Autenticación que vienen con Laravel Breeze/Jetstream (ej. Registro, Login, Verificación de email y Recuperación de contraseña)