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

@section('title', 'Mi perfil')

<div class="flex items-center justify-center min-h-screen py-3">
    <div class="w-full max-w-7xl mx-auto bg-white/80 backdrop-blur-xl shadow rounded-3xl p-3 relative border border-white/20">
        <!-- Encabezado -->
        <div class="text-center mb-3"
             x-data="{ showSuccess: false, showCancel: false }" 
             x-on:profile-updated.window="showSuccess = true; showCancel = false; setTimeout(() => showSuccess = false, 3000)" 
             x-on:profile-cancelled.window="showCancel = true; showSuccess = false; setTimeout(() => showCancel = false, 3000)">
            <div class="flex justify-center mb-1">
                <div class="p-2 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl shadow-xl transform rotate-3 hover:rotate-0 transition-transform duration-300">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
            </div>
            <h1 class="text-base font-black bg-gradient-to-r from-gray-900 via-gray-800 to-green-800 bg-clip-text text-transparent mb-1">
                Mi Perfil
            </h1>
            
            <template x-if="showSuccess">
                <div class="rounded bg-green-100 px-2 py-1 text-green-800 border border-green-400 text-xs mb-1 font-semibold">
                    ¡Perfil actualizado correctamente!
                </div>
            </template>

            <template x-if="showCancel">
                <div class="rounded bg-yellow-100 px-2 py-1 text-yellow-800 border border-yellow-400 text-xs mb-1 font-semibold">
                    Cambios descartados.
                </div>
            </template>

            <template x-if="!showSuccess && !showCancel">
                <p class="text-gray-600 text-xs">Actualiza tu información personal</p>
            </template>
        </div>

        <form wire:submit.prevent="updateProfileInformation" class="space-y-2">
            <!-- Fila: Información personal + Contacto -->
            <div class="flex flex-col md:flex-row gap-2">
                <!-- Información personal -->
                <div class="flex-1 border border-gray-300 rounded-3xl overflow-hidden">
                    <div class="h-1.5 bg-gradient-to-r from-[#000000] to-[#39A900]"></div>
                    <div class="p-2">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl shadow-lg">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xs font-bold text-gray-900">Información personal</h2>
                                <p class="text-gray-600 text-[10px]">Datos de identificación del usuario</p>
                            </div>
                        </div>

                        <div class="flex gap-2 mb-2">
                            <div class="flex-1">
                                <label for="tipDocUsu" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Tipo de documento <span class="text-red-500">*</span>
                                </label>
                                <div class="relative group">
                                    <select wire:model="tipDocUsu" id="tipDocUsu"
                                            class="cursor-pointer w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300 group-hover:shadow-xl appearance-none text-xs">
                                        <option disabled selected value="">Seleccione el tipo de documento</option>
                                        <option value="CC">Cédula de Ciudadanía</option>
                                        <option value="TI">Tarjeta de Identidad</option>
                                        <option value="CE">Cédula de Extranjería</option>
                                        <option value="PEP">Permiso Especial de Permanencia</option>
                                        <option value="PPT">Permiso por Protección Temporal</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-1.5 pointer-events-none">
                                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-1">
                                <label for="numDocUsu" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Número de documento <span class="text-red-500">*</span>
                                </label>
                                <div class="relative group">
                                    <input type="text" wire:model="numDocUsu" id="numDocUsu"
                                           class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs">
                                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-indigo-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-2 mb-2">
                            <div class="flex-1">
                                <label for="nomUsu" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Nombre <span class="text-red-500">*</span>
                                </label>
                                <div class="relative group">
                                    <input type="text" wire:model="nomUsu" id="nomUsu"
                                           class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs">
                                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-indigo-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                            </div>
                            <div class="flex-1">
                                <label for="apeUsu" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Apellido <span class="text-red-500">*</span>
                                </label>
                                <div class="relative group">
                                    <input type="text" wire:model="apeUsu" id="apeUsu"
                                           class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs">
                                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-indigo-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <div class="flex-1">
                                <label for="fecNacUsu" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Fecha de nacimiento <span class="text-red-500">*</span>
                                </label>
                                <div class="relative group">
                                    <input type="date" wire:model="fecNacUsu" id="fecNacUsu"
                                           class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs">
                                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-indigo-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                            </div>
                            <div class="flex-1">
                                <label for="sexUsu" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Sexo <span class="text-red-500">*</span>
                                </label>
                                <div class="relative group">
                                    <select wire:model="sexUsu" id="sexUsu"
                                            class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl appearance-none text-xs">
                                        <option disabled selected value="">Seleccione tu sexo</option>
                                        <option value="Mujer">Mujer</option>
                                        <option value="Hombre">Hombre</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-1.5 pointer-events-none">
                                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contacto -->
                <div class="flex-1 border border-gray-300 rounded-3xl overflow-hidden">
                    <div class="h-1.5 bg-gradient-to-r from-[#39A900] to-[#000000]"></div>
                    <div class="p-2">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl shadow-lg">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xs font-bold text-gray-900">Contacto</h2>
                                <p class="text-gray-600 text-[10px]">Información de comunicación</p>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <div class="flex-1">
                                <label for="celCon" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Célular <span class="text-red-500">*</span>
                                </label>
                                <div class="relative group">
                                    <input type="text" wire:model="celCon" id="celCon"
                                           class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs">
                                    <div class="absolute inset-0 bg-gradient-to-r from-purple-500/5 to-pink-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                            </div>
                            <div class="flex-1">
                                <label for="email" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Correo electrónico <span class="text-red-500">*</span>
                                </label>
                                <div class="relative group">
                                    <input type="email" wire:model="email" id="email"
                                           class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs">
                                    <div class="absolute inset-0 bg-gradient-to-r from-purple-500/5 to-pink-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fila: Dirección -->
            <div class="border border-gray-300 rounded-3xl overflow-hidden">
                <div class="h-1.5 bg-gradient-to-r from-[#000000] to-[#39A900]"></div>
                <div class="p-2">
                    <div class="flex items-center space-x-2 mb-2">
                        <div class="p-1.5 bg-gradient-to-br from-orange-500 to-red-600 rounded-xl shadow-lg">
                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xs font-bold text-gray-900">Dirección</h2>
                            <p class="text-gray-600 text-[10px]">Ubicación del usuario</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2 mb-2">
                        <div>
                            <label for="calDir" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                Calle
                            </label>
                            <div class="relative group">
                                <input type="text" wire:model="calDir" id="calDir"
                                       class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs">
                                <div class="absolute inset-0 bg-gradient-to-r from-orange-500/5 to-red-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                            </div>
                        </div>
                        <div>
                            <label for="barDir" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                Barrio
                            </label>
                            <div class="relative group">
                                <input type="text" wire:model="barDir" id="barDir"
                                       class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs">
                                <div class="absolute inset-0 bg-gradient-to-r from-orange-500/5 to-red-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                            </div>
                        </div>
                        <div>
                            <label for="ciuDir" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                Ciudad
                            </label>
                            <div class="relative group">
                                <input type="text" wire:model="ciuDir" id="ciuDir"
                                       class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs">
                                <div class="absolute inset-0 bg-gradient-to-r from-orange-500/5 to-red-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                        <div>
                            <label for="depDir" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                Departamento
                            </label>
                            <input type="text" wire:model="depDir" id="depDir" readonly
                                   class="w-full px-1.5 py-1 bg-gray-100 border-2 border-gray-200 rounded-2xl shadow-lg cursor-not-allowed text-gray-600 text-xs">
                        </div>
                        <div>
                            <label for="codPosDir" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                Código postal
                            </label>
                            <div class="relative group">
                                <input type="text" wire:model="codPosDir" id="codPosDir"
                                       class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs">
                                <div class="absolute inset-0 bg-gradient-to-r from-orange-500/5 to-red-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                            </div>
                        </div>
                        <div>
                            <label for="paiDir" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                País
                            </label>
                            <input type="text" wire:model="paiDir" id="paiDir" readonly
                                   class="w-full px-1.5 py-1 bg-gray-100 border-2 border-gray-200 rounded-2xl shadow-lg cursor-not-allowed text-gray-600 text-xs">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones -->
            <div class="flex justify-center space-x-2 pt-2">
                <button type="button" wire:click="cancelarCambios"
                        class="cursor-pointer group relative inline-flex items-center justify-center px-2.5 py-1 bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                    <svg class="w-3 h-3 mr-1.5 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    <span class="relative z-10 text-xs">Cancelar</span>
                </button>
                <button type="submit"
                        class="cursor-pointer group relative inline-flex items-center justify-center px-2.5 py-1 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                    <svg class="w-3 h-3 mr-1.5 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="relative z-10 text-xs">Guardar</span>
                </button>
            </div>
        </form>
    </div>
</div>