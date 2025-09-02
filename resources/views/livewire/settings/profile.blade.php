<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new #[Layout('layouts.auth')] class extends Component {
    public string $tipDocUsu = '';
    public string $numDocUsu = '';
    public string $nomUsu = '';
    public string $apeUsu = '';
    public string $fecNacUsu = '';
    public string $sexUsu = '';
    public string $email = '';

    public string $celCon = '';
    public string $calDir = '';
    public string $barDir = '';
    public string $ciuDir = '';
    public string $depDir = '';
    public string $codPosDir = '';
    public string $paiDir = '';

    public function mount(): void
    {
        $user = Auth::user();
        $this->tipDocUsu = $user->tipDocUsu ?? '';
        $this->numDocUsu = $user->numDocUsu ?? '';
        $this->nomUsu = $user->nomUsu ?? '';
        $this->apeUsu = $user->apeUsu ?? '';
        $this->fecNacUsu = $user->fecNacUsu ?? '';
        $this->sexUsu = $user->sexUsu ?? '';
        $this->email = $user->email ?? '';

        $this->celCon = $user->contacto->celCon ?? '';
        $this->calDir = $user->direccion->calDir ?? '';
        $this->barDir = $user->direccion->barDir ?? '';
        $this->ciuDir = $user->direccion->ciuDir ?? '';
        $this->depDir = $user->direccion->depDir ?? '';
        $this->codPosDir = $user->direccion->codPosDir ?? '';
        $this->paiDir = $user->direccion->paiDir ?? '';
    }

    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'tipDocUsu' => ['required', 'string', 'max:10'],
            'numDocUsu' => ['required', 'string', 'max:20', Rule::unique(User::class)->ignore($user->id)],
            'nomUsu' => ['required', 'string', 'max:100'],
            'apeUsu' => ['required', 'string', 'max:100'],
            'fecNacUsu' => ['required', 'date'],
            'sexUsu' => ['required', 'in:Hombre,Mujer'],
            'email' => ['required', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'celCon' => ['required', 'string', 'max:15'],
            'calDir' => ['required'],
            'barDir' => ['required'],
            'ciuDir' => ['required'],
            'depDir' => ['required'],
            'codPosDir' => ['required'],
            'paiDir' => ['required'],
        ]);

        $user->update($validated);
        $user->contacto->update(['celCon' => $this->celCon]);
        $user->direccion->update([
            'calDir' => $this->calDir,
            'barDir' => $this->barDir,
            'ciuDir' => $this->ciuDir,
            'depDir' => $this->depDir,
            'codPosDir' => $this->codPosDir,
            'paiDir' => $this->paiDir,
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();
        $this->dispatch('profile-updated');
    }

    public function cancelarCambios(): void
    {
        $this->mount(); 
        $this->dispatch('profile-cancelled');
    }

    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('home'));
            return;
        }

        $user->sendEmailVerificationNotification();
        Session::flash('status', 'verification-link-sent');
    }
};
?>

@section('title', 'Mi perfil') <!--- título de la página  -->

<div class="flex items-center justify-center p-4">
    <div class="w-full max-w-6xl bg-white shadow rounded-lg p-8">
        <!-- Encabezado -->
        <h1 class="text-2xl font-bold text-center text-gray-800 mb-2">Mi Perfil</h1>
        <!-- Mensaje de cambios realizados o no -->
        <div class="text-center text-gray-500 mb-8" x-data="{ showSuccess: false, showCancel: false }" x-on:profile-updated.window="showSuccess = true; showCancel = false; setTimeout(() => showSuccess = false, 3000)" x-on:profile-cancelled.window="showCancel = true; showSuccess = false; setTimeout(() => showCancel = false, 3000)">
            <template x-if="showSuccess">
                <div class="rounded bg-green-100 px-4 text-green-800 border border-green-400">
                    ¡Perfil actualizado correctamente!
                </div>
            </template>

            <template x-if="showCancel">
                <div class="rounded bg-yellow-100 px-4 text-yellow-800 border border-yellow-400">
                    Cambios descartados.
                </div>
            </template>

            <template x-if="!showSuccess && !showCancel">
                <p>Actualiza tu información personal</p>
            </template>
        </div>


        <form wire:submit.prevent="updateProfileInformation">
            <!-- Fila: Información personal + Contacto -->
            <div class="flex flex-col md:flex-row gap-6 mb-6">
                <!-- Información personal -->
                <div class="flex-1 border border-gray-300 rounded-lg p-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Información personal</h2>
                    <div class="flex gap-4 mb-4">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-800 mb-1">Tipo de documento</label>
                            <select wire:model="tipDocUsu" class="border p-2 rounded w-full text-black border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
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
                            <input type="text" wire:model="numDocUsu" class="border p-2 rounded w-full text-black border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
                        </div>
                    </div>

                    <div class="flex gap-4 mb-4">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-800 mb-1">Nombre</label>
                            <input type="text" wire:model="nomUsu" class="border p-2 rounded w-full text-black border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-800 mb-1">Apellido</label>
                            <input type="text" wire:model="apeUsu" class="border p-2 rounded w-full text-black border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-800 mb-1">Fecha de nacimiento</label>
                            <input type="date" wire:model="fecNacUsu" class="border p-2 rounded w-full text-black border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-800 mb-1">Sexo</label>
                            <select wire:model="sexUsu" class="border p-2 rounded w-full text-black bg-white border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
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
                            <input type="text" wire:model="celCon" class="border p-2 rounded w-full text-black border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-800 mb-1">Correo electrónico</label>
                            <input type="email" wire:model="email" class="border p-2 rounded w-full text-black border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fila: Dirección -->
            <div class="border border-gray-300 rounded-lg p-4 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Dirección</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-800 mb-1">Calle</label>
                        <input type="text" wire:model="calDir" class="border p-2 rounded w-full text-black border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-800 mb-1">Barrio</label>
                        <input type="text" wire:model="barDir" class="border p-2 rounded w-full text-black border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-800 mb-1">Ciudad</label>
                        <input type="text" wire:model="ciuDir" class="border p-2 rounded w-full text-black border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-800 mb-1">Departamento</label>
                        <input type="text" wire:model="depDir" class="border p-2 rounded w-full text-black border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-800 mb-1">Código postal</label>
                        <input type="text" wire:model="codPosDir" class="border p-2 rounded w-full text-black border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-800 mb-1">País</label>
                        <input type="text" wire:model="paiDir" class="border p-2 rounded w-full text-black border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
                    </div>
                </div>
            </div>

            <!-- Botón de guardar -->
            <div class="text-center mb-4 space-x-4">
                <button type="submit" class="cursor-pointer px-6 py-2 bg-[#007832] text-white rounded-md font-semibold hover:bg-green-700 transition duration-150">
                    Guardar
                </button>
                <button type="button" wire:click="cancelarCambios" class="cursor-pointer px-6 py-2 bg-red-500 text-white rounded-md font-semibold hover:bg-red-600 transition duration-150">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>
