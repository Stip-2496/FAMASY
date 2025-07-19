<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Http\Controllers\ProveedorController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('/login', 'login')->name('login');

Route::view('/home', 'auth.home')
    ->middleware(['auth', 'verified'])
    ->name('home');

// Rutas para el módulo de Proveedores (fuera del middleware auth)
Route::resource('proveedores', ProveedorController::class);
Route::get('proveedores-search', [ProveedorController::class, 'search'])->name('proveedores.search');


Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});


require __DIR__ . '/auth.php';
