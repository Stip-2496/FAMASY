<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component {
    public string $tipDocUsu = '';
    public string $numDocUsu = '';
    public string $nomUsu = '';
    public string $apeUsu = '';
    public string $fecNacUsu = '';
    public string $sexUsu = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public string $celCon = '';
    public string $calDir = '';
    public string $barDir = '';
    public string $ciuDir = '';
    public string $depDir = '';
    public string $codPosDir = '';
    public string $paiDir = '';

    public int $idRolUsu = 0;
    public int $idConUsu = 0;

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
{
    $this->validate([
        // Validación de datos personales...
        'nomUsu' => ['required', 'string', 'max:100'],
        'apeUsu' => ['required', 'string', 'max:100'],
        'tipDocUsu' => ['required', 'string', 'max:10'],
        'numDocUsu' => ['required', 'string', 'max:20', 'unique:users,numDocUsu'],
        'sexUsu' => ['required', 'in:Hombre,Mujer'],
        'fecNacUsu' => ['required', 'date'],

        // Contacto y dirección
        'celCon' => ['required', 'string', 'max:15'],
        'calDir' => ['required'],
        'barDir' => ['required'],
        'ciuDir' => ['required'],
        'depDir' => ['required'],
        'codPosDir' => ['required'],
        'paiDir' => ['required'],

        // Usuario
        'email' => ['required', 'email', 'unique:users,email'],
        'password' => ['required', 'confirmed', Rules\Password::defaults()],
    ]);

    // Crear contacto
    $contacto = \App\Models\Contacto::create([
        'celCon' => $this->celCon,
    ]);

    // Crear dirección (requiere FK a contacto)
    \App\Models\Direccion::create([
        'idConDir' => $contacto->idCon,
        'calDir' => $this->calDir,
        'barDir' => $this->barDir,
        'ciuDir' => $this->ciuDir,
        'depDir' => $this->depDir,
        'codPosDir' => $this->codPosDir,
        'paiDir' => $this->paiDir,
    ]);

    // Crear usuario
    $user = \App\Models\User::create([
        'tipDocUsu' => $this->tipDocUsu,
        'numDocUsu' => $this->numDocUsu,
        'nomUsu' => $this->nomUsu,
        'apeUsu' => $this->apeUsu,
        'fecNacUsu' => $this->fecNacUsu,
        'sexUsu' => $this->sexUsu,
        'email' => $this->email,
        'password' => Hash::make($this->password),
        'idRolUsu' => 1, // Temporal
        'idConUsu' => $contacto->idCon,
    ]);

    event(new Registered($user));
    Auth::login($user);
    $this->redirectIntended(default: route('home', absolute: false), navigate: true);
}
}; ?>

@section('title', 'Regístrate') <!--- título de la página  -->

<div class="flex items-center justify-center p-4">
    <div class="w-full max-w-6xl bg-white shadow rounded-lg p-8">
        <!-- Encabezado -->
        <h1 class="text-2xl font-bold text-center text-gray-800 mb-2">¡Regístrate en FAMASY!</h1>
        <p class="text-center text-gray-500 mb-8">Completa tus datos para comenzar</p>

        <form wire:submit.prevent="register">
            <!-- Fila: Información personal + Contacto -->
            <div class="flex flex-col md:flex-row gap-6 mb-6">
                <!-- Información personal -->
                <div class="flex-1 border border-gray-300 rounded-lg p-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Información personal</h2>
                    <div class="flex gap-4 mb-4">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-800 mb-1">Tipo de documento</label>
                            <select wire:model="tipDocUsu" class="border p-2 rounded w-full text-black bg-white">
                                <option disabled selected value="">Seleccione el tipo de documento</option>
                                <option value="CC">Cédula de Ciudadanía</option>
                                <option value="TI">Tarjeta de Identidad</option>
                                <option value="CE">Cédula de Extranjería</option>
                                <option value="PEP">Permiso Especial de Permanencia</option>
                                <option value="PPT">Permiso por Protección Temporal</option>
                            </select>
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-800 mb-1">Número de documento</label>
                            <input type="text" wire:model="numDocUsu" placeholder="0000-000-000" class="border p-2 rounded w-full text-black">
                        </div>
                    </div>

                    <div class="flex gap-4 mb-4">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-800 mb-1">Nombre</label>
                            <input type="text" wire:model="nomUsu" placeholder="Nombre(s)" class="border p-2 rounded w-full text-black">
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-800 mb-1">Apellido</label>
                            <input type="text" wire:model="apeUsu" placeholder="Apellidos" class="border p-2 rounded w-full text-black">
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-800 mb-1">Fecha de nacimiento</label>
                            <input type="date" wire:model="fecNacUsu" class="border p-2 rounded w-full text-black">
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-800 mb-1">Sexo</label>
                            <select wire:model="sexUsu" class="border p-2 rounded w-full text-black bg-white">
                                <option disabled selected value="">Seleccione tu sexo</option>
                                <option value="Mujer">Mujer</option>
                                <option value="Hombre">Hombre</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Contacto -->
                <div class="flex-1 border border-gray-300 rounded-lg p-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Contacto</h2>
                    <div class="flex gap-4">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-800 mb-1">Célular</label>
                            <input type="text" wire:model="celCon" placeholder="000-000-0000" class="border p-2 rounded w-full text-black">
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-800 mb-1">Correo electrónico</label>
                            <input type="email" wire:model="email" placeholder="ejemplo@dominio.com" class="border p-2 rounded w-full text-black">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fila: Dirección + Seguridad -->
            <div class="flex flex-col md:flex-row gap-6 mb-6">
                <!-- Dirección -->
                <div class="flex-1 border border-gray-300 rounded-lg p-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Dirección</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-800 mb-1">Calle</label>
                            <input type="text" wire:model="calDir" placeholder="Calle" class="border p-2 rounded w-full text-black">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-800 mb-1">Barrio</label>
                            <input type="text" wire:model="barDir" placeholder="Barrio" class="border p-2 rounded w-full text-black">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-800 mb-1">Ciudad</label>
                            <input type="text" wire:model="ciuDir" placeholder="Ciudad" class="border p-2 rounded w-full text-black">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-800 mb-1">Departamento</label>
                            <input type="text" wire:model="depDir" placeholder="Departamento" class="border p-2 rounded w-full text-black">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-800 mb-1">Código postal</label>
                            <input type="text" wire:model="codPosDir" placeholder="Código postal" class="border p-2 rounded w-full text-black">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-800 mb-1">País</label>
                            <input type="text" wire:model="paiDir" placeholder="País" class="border p-2 rounded w-full text-black">
                        </div>
                    </div>
                </div>

                <!-- Seguridad -->
                <div class="flex-1 border border-gray-300 rounded-lg p-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Seguridad</h2>
                    <div class="flex gap-4">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-800 mb-1">Contraseña</label>
                            <input type="password" wire:model="password" placeholder="••••••••" class="border p-2 rounded w-full text-black">
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-800 mb-1">Confirmar contraseña</label>
                            <input type="password" wire:model="password_confirmation" placeholder="••••••••" class="border p-2 rounded w-full text-black">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botón de volver y registro -->
            <div class="text-center mb-4 space-x-4">
                <a href="{{ route('welcome') }}" wire:navigation class="cursor-pointer px-6 py-2 bg-gray-500 text-white rounded-md font-semibold hover:bg-gray-600 transition duration-150">
                    Volver
                </a>
                <button type="submit" class="cursor-pointer px-6 py-2 bg-[#007832] text-white rounded-md font-semibold hover:bg-green-700 transition duration-150">
                    Registrarte
                </button>
            </div>

            <!-- Enlace a login -->
            <div class="text-center text-sm text-gray-800">
                ¿Ya tienes cuenta?
                @if (Route::has('login'))
                    <a href="{{ route('login') }}" class="text-green-600 hover:underline">¡Inicia sesión!</a>
                @endif
            </div>
        </form>
    </div>
</div>

