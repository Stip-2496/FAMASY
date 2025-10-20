<?php
use App\Models\User;
use App\Models\Rol;
use App\Models\Auditoria;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component {
    public string $tipDocUsu = '';
    public string $numDocUsu = '';
    public string $nomUsu = '';
    public string $apeUsu = '';
    public string $fecNacUsu = '';
    public string $sexUsu = '';
    public string $email = '';
    public string $password = '';
    public int $idRolUsu = 1;

    public string $celCon = '';
    public string $calDir = '';
    public string $barDir = '';
    public string $ciuDir = '';
    public string $depDir = 'Huila';
    public string $codPosDir = '';
    public string $paiDir = 'Colombia';

    // Función para generar contraseña segura
    private function generarContraseñaSegura($longitud = 12)
    {
        $caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()_+-=[]{}|;:,.<>?';
        $contraseña = '';
        $max = strlen($caracteres) - 1;
        
        for ($i = 0; $i < $longitud; $i++) {
            $contraseña .= $caracteres[random_int(0, $max)];
        }
        
        return $contraseña;
    }

    public function mount(): void
    {
        $this->sexUsu = 'Hombre';
        // Generar contraseña automáticamente al cargar el componente
        $this->password = $this->generarContraseñaSegura();
    }

    // Método para limpiar todos los campos y generar nueva contraseña
    public function limpiarFormulario(): void
    {
        // Limpiar todos los campos
        $this->tipDocUsu = '';
        $this->numDocUsu = '';
        $this->nomUsu = '';
        $this->apeUsu = '';
        $this->fecNacUsu = '';
        $this->sexUsu = 'Hombre';
        $this->email = '';
        $this->idRolUsu = 1;

        $this->celCon = '';
        $this->calDir = '';
        $this->barDir = '';
        $this->ciuDir = '';
        $this->depDir = 'Huila';
        $this->codPosDir = '';
        $this->paiDir = 'Colombia';

        // Generar nueva contraseña
        $this->password = $this->generarContraseñaSegura();
        
        // Limpiar errores de validación
        $this->resetErrorBag();
    }

    // Método para cancelar registro (limpia el formulario)
    public function cancelarRegistro(): void
    {
        $this->limpiarFormulario();
        
        // Disparar evento para mostrar mensaje
        $this->dispatch('registro-cancelado');
    }

    // Método para generar nueva contraseña
    public function generarNuevaContraseña(): void
    {
        $this->password = $this->generarContraseñaSegura();
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Nueva contraseña generada'
        ]);
    }

    // Este método se ejecutará automáticamente cuando idRolUsu cambie
    public function updatedIdRolUsu($value)
    {
        if ($value == 2) {
            $existingSuperuser = User::where('idRolUsu', 2)->exists();
            
            if ($existingSuperuser) {
                // Muestra error debajo del campo
                $this->addError('idRolUsu', 'Solo puede existir un superadministrador en el sistema.');
                
                // Lanza el modal de advertencia
                $this->dispatch('show-superuser-warning');
                
                // Revertir inmediatamente el valor
                $this->idRolUsu = 1;
            }
        }
    }

    public function revertRoleSelection(): void
    {
        $this->idRolUsu = 1;
        $this->dispatch('notify', [
            'type' => 'info',
            'message' => 'Rol revertido a Administrador'
        ]);
    }

    // Validación en tiempo real para campos individuales
    public function validateField($fieldName)
    {
        $rules = [
            'tipDocUsu' => ['required', 'string', 'max:10'],
            'numDocUsu' => ['required', 'numeric', 'digits_between:1,10', 'unique:users,numDocUsu'],
            'nomUsu' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z\s]+$/'],
            'apeUsu' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z\s]+$/'],
            'fecNacUsu' => ['required', 'date'],
            'sexUsu' => ['required', 'in:Hombre,Mujer'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'min:8'],
            'idRolUsu' => ['required', 'exists:rol,idRol'],
            'celCon' => ['required', 'numeric', 'digits_between:1,10'],
            'calDir' => ['required', 'string', 'max:100'],
            'barDir' => ['required', 'string', 'max:100'],
            'ciuDir' => ['required', 'string', 'max:100'],
            'depDir' => ['required', 'string', 'max:100'],
            'codPosDir' => ['required', 'string', 'max:20'],
            'paiDir' => ['required', 'string', 'max:100'],
        ];

        $this->validateOnly($fieldName, $rules);
    }

    // Validación automática cuando los campos cambian
    public function updated($propertyName)
    {
        $rules = [
            'tipDocUsu' => ['required', 'string', 'max:10'],
            'numDocUsu' => ['required', 'numeric', 'digits_between:1,10', 'unique:users,numDocUsu'],
            'nomUsu' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z\s]+$/'],
            'apeUsu' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z\s]+$/'],
            'fecNacUsu' => ['required', 'date'],
            'sexUsu' => ['required', 'in:Hombre,Mujer'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'min:8'],
            'idRolUsu' => ['required', 'exists:rol,idRol'],
            'celCon' => ['required', 'numeric', 'digits_between:1,10'],
            'calDir' => ['required', 'string', 'max:100'],
            'barDir' => ['required', 'string', 'max:100'],
            'ciuDir' => ['required', 'string', 'max:100'],
            'depDir' => ['required', 'string', 'max:100'],
            'codPosDir' => ['required', 'string', 'max:20'],
            'paiDir' => ['required', 'string', 'max:100'],
        ];

        $this->validateOnly($propertyName, $rules);
    }

    public function createUser(): void
    {
        // Validación manual para el rol de superadministrador
        if ($this->idRolUsu == 2) {
            $existingSuperuser = User::where('idRolUsu', 2)->exists();
            
            if ($existingSuperuser) {
                $this->addError('idRolUsu', 'Solo puede existir un superadministrador en el sistema.');
                $this->dispatch('show-superuser-warning');
                return;
            }
        }

        $validated = $this->validate([
            'tipDocUsu' => ['required', 'string', 'max:10'],
            'numDocUsu' => ['required', 'numeric', 'digits_between:1,10', 'unique:users,numDocUsu'],
            'nomUsu' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z\s]+$/'],
            'apeUsu' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z\s]+$/'],
            'fecNacUsu' => ['required', 'date'],
            'sexUsu' => ['required', 'in:Hombre,Mujer'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'min:8'],
            'idRolUsu' => ['required', 'exists:rol,idRol'],
            'celCon' => ['required', 'numeric', 'digits_between:1,10'],
            'calDir' => ['required', 'string', 'max:100'],
            'barDir' => ['required', 'string', 'max:100'],
            'ciuDir' => ['required', 'string', 'max:100'],
            'depDir' => ['required', 'string', 'max:100'],
            'codPosDir' => ['required', 'string', 'max:20'],
            'paiDir' => ['required', 'string', 'max:100'],
        ]);

        try {
            DB::beginTransaction();

            // Crear contacto
            $contacto = \App\Models\Contacto::create(['celCon' => $this->celCon]);

            // Crear dirección
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
            $user = User::create([
                'tipDocUsu' => $this->tipDocUsu,
                'numDocUsu' => $this->numDocUsu,
                'nomUsu' => $this->nomUsu,
                'apeUsu' => $this->apeUsu,
                'fecNacUsu' => $this->fecNacUsu,
                'sexUsu' => $this->sexUsu,
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'idRolUsu' => $this->idRolUsu,
                'idConUsu' => $contacto->idCon,
            ]);

            DB::commit();

            // Guardar la contraseña generada para mostrarla en el mensaje
            $passwordGenerada = $this->password;

            // Limpiar el formulario y generar nueva contraseña
            $this->limpiarFormulario();

            // Disparar evento para mostrar mensaje de éxito con la contraseña
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Usuario creado exitosamente. Contraseña generada: ' . $passwordGenerada
            ]);

            // También disparar el evento para el mensaje en el header
            $this->dispatch('registro-exitoso');

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Error al crear usuario: ' . $e->getMessage());
            \Log::error('Trace: ' . $e->getTraceAsString());
            
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al crear usuario: ' . $e->getMessage()
            ]);
        }
    }
}; ?>

@section('title', 'Crear Usuario')

<div class="flex items-center justify-center min-h-screen py-3">
    <div class="w-full max-w-7xl mx-auto bg-white/80 backdrop-blur-xl shadow rounded-3xl p-3 relative border border-white/20">
        <!-- Botón Volver -->
        <div class="absolute top-2 right-2">
            <a href="{{ route('settings.manage-users') }}" wire:navigate
               class="group relative inline-flex items-center px-2 py-1 bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                <svg class="w-3 h-3 mr-1 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span class="relative z-10 text-xs">Volver</span>
            </a>
        </div>

        <!-- Encabezado -->
        <div class="text-center mb-3"
             x-data="{ showSuccess: false, showCancel: false }"
             x-on:registro-exitoso.window="showSuccess = true; showCancel = false; setTimeout(() => showSuccess = false, 3000)"
             x-on:registro-cancelado.window="showCancel = true; showSuccess = false; setTimeout(() => showCancel = false, 3000)">
            <div class="flex justify-center mb-1">
                <div class="p-2 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl shadow-xl transform rotate-3 hover:rotate-0 transition-transform duration-300">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                    </svg>
                </div>
            </div>
            <h1 class="text-base font-black bg-gradient-to-r from-gray-900 via-gray-800 to-green-800 bg-clip-text text-transparent mb-1">
                Registrar Nuevo Usuario
            </h1>
            
            <template x-if="showSuccess">
                <div class="rounded bg-green-100 px-2 py-1 text-green-800 border border-green-400 text-xs mb-1 font-semibold">
                    ¡Usuario registrado exitosamente!
                </div>
            </template>

            <template x-if="showCancel">
                <div class="rounded bg-yellow-100 px-2 py-1 text-yellow-800 border border-yellow-400 text-xs mb-1 font-semibold">
                    Formulario limpiado. Puede registrar un nuevo usuario.
                </div>
            </template>

            <template x-if="!showSuccess && !showCancel">
                <p class="text-gray-600 text-xs">Complete los datos del nuevo usuario</p>
            </template>
        </div>

        <form wire:submit.prevent="createUser" class="space-y-2">
            <!-- Fila 1 -->
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
                                    <select wire:model="tipDocUsu" wire:blur="validateField('tipDocUsu')" id="tipDocUsu"
                                            class="cursor-pointer w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300 group-hover:shadow-xl appearance-none text-xs @error('tipDocUsu') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror">
                                        <option disabled selected value="">Seleccione el tipo de documento</option>
                                        <option value="CC">Cédula de Ciudadanía</option>
                                        <option value="TI">Tarjeta de Identidad</option>
                                        <option value="CE">Cédula de Extranjería</option>
                                        <option value="PEP">Permiso Especial</option>
                                        <option value="PPT">Protección Temporal</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-1.5 pointer-events-none">
                                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </div>
                                @error('tipDocUsu')
                                    <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                        <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                            <div class="flex-1">
                                <label for="numDocUsu" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Número de documento <span class="text-red-500">*</span>
                                </label>
                                <div class="relative group">
                                    <input type="number" wire:model="numDocUsu" wire:blur="validateField('numDocUsu')" id="numDocUsu"
                                           placeholder="0.000.000.000"
                                           class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('numDocUsu') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror">
                                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-indigo-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('numDocUsu')
                                    <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                        <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>

                        <div class="flex gap-2 mb-2">
                            <div class="flex-1">
                                <label for="nomUsu" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Nombre <span class="text-red-500">*</span>
                                </label>
                                <div class="relative group">
                                    <input type="text" wire:model="nomUsu" wire:blur="validateField('nomUsu')" id="nomUsu"
                                           placeholder="Nombre(s)" pattern="[A-Za-z\s]+"
                                           class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('nomUsu') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror">
                                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-indigo-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('nomUsu')
                                    <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                        <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                            <div class="flex-1">
                                <label for="apeUsu" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Apellido <span class="text-red-500">*</span>
                                </label>
                                <div class="relative group">
                                    <input type="text" wire:model="apeUsu" wire:blur="validateField('apeUsu')" id="apeUsu"
                                           placeholder="Apellidos" pattern="[A-Za-z\s]+"
                                           class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('apeUsu') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror">
                                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-indigo-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('apeUsu')
                                    <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                        <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <div class="flex-1">
                                <label for="fecNacUsu" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Fecha de nacimiento <span class="text-red-500">*</span>
                                </label>
                                <div class="relative group">
                                    <input type="date" wire:model="fecNacUsu" wire:blur="validateField('fecNacUsu')" id="fecNacUsu"
                                           class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('fecNacUsu') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror">
                                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-indigo-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('fecNacUsu')
                                    <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                        <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                            <div class="flex-1">
                                <label for="sexUsu" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Sexo <span class="text-red-500">*</span>
                                </label>
                                <div class="relative group">
                                    <select wire:model="sexUsu" wire:blur="validateField('sexUsu')" id="sexUsu"
                                            class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl appearance-none text-xs @error('sexUsu') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror">
                                        <option value="Hombre">Hombre</option>
                                        <option value="Mujer">Mujer</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-1.5 pointer-events-none">
                                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </div>
                                @error('sexUsu')
                                    <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                        <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contacto y Rol -->
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
                                <h2 class="text-xs font-bold text-gray-900">Contacto y Rol</h2>
                                <p class="text-gray-600 text-[10px]">Información de comunicación</p>
                            </div>
                        </div>

                        <div class="flex gap-2 mb-2">
                            <div class="flex-1">
                                <label for="celCon" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Celular <span class="text-red-500">*</span>
                                </label>
                                <div class="relative group">
                                    <input type="number" wire:model="celCon" wire:blur="validateField('celCon')" id="celCon"
                                           placeholder="000-000-0000"
                                           class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('celCon') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror">
                                    <div class="absolute inset-0 bg-gradient-to-r from-purple-500/5 to-pink-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('celCon')
                                    <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                        <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                            <div class="flex-1">
                                <label for="email" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Correo electrónico <span class="text-red-500">*</span>
                                </label>
                                <div class="relative group">
                                    <input type="email" wire:model="email" wire:blur="validateField('email')" id="email"
                                           placeholder="ejemplo@dominio.com"
                                           class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('email') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror">
                                    <div class="absolute inset-0 bg-gradient-to-r from-purple-500/5 to-pink-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('email')
                                    <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                        <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label for="idRolUsu" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                Rol <span class="text-red-500">*</span>
                            </label>
                            <div class="relative group">
                                <select wire:model.live="idRolUsu" wire:blur="validateField('idRolUsu')" id="idRolUsu"
                                        class="cursor-pointer w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl appearance-none text-xs @error('idRolUsu') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror">
                                    @foreach(Rol::all() as $rol)
                                    <option value="{{ $rol->idRol }}">{{ $rol->nomRol }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-1.5 pointer-events-none">
                                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                            </div>
                            @error('idRolUsu')
                                <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                    <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fila 2 -->
            <div class="flex flex-col md:flex-row gap-2">
                <!-- Dirección -->
                <div class="flex-1/3 border border-gray-300 rounded-3xl overflow-hidden">
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
                                    <input type="text" wire:model="calDir" wire:blur="validateField('calDir')" id="calDir"
                                           placeholder="Calle"
                                           class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs">
                                    <div class="absolute inset-0 bg-gradient-to-r from-orange-500/5 to-red-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                            </div>
                            <div>
                                <label for="barDir" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Barrio
                                </label>
                                <div class="relative group">
                                    <input type="text" wire:model="barDir" wire:blur="validateField('barDir')" id="barDir"
                                           placeholder="Barrio"
                                           class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs">
                                    <div class="absolute inset-0 bg-gradient-to-r from-orange-500/5 to-red-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                            </div>
                            <div>
                                <label for="ciuDir" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Ciudad
                                </label>
                                <div class="relative group">
                                    <input type="text" wire:model="ciuDir" wire:blur="validateField('ciuDir')" id="ciuDir"
                                           placeholder="Ciudad"
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
                                    <input type="text" wire:model="codPosDir" wire:blur="validateField('codPosDir')" id="codPosDir"
                                           placeholder="Código postal"
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

                <!-- Seguridad -->
                <div class="flex-1 border border-gray-300 rounded-3xl overflow-hidden">
                    <div class="h-1.5 bg-gradient-to-r from-[#39A900] to-[#000000]"></div>
                    <div class="p-2">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-cyan-500 to-blue-600 rounded-xl shadow-lg">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xs font-bold text-gray-900">Seguridad</h2>
                                <p class="text-gray-600 text-[10px]">Contraseña del usuario</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                Contraseña Generada
                            </label>
                            <div class="bg-gradient-to-br from-blue-50 to-cyan-50 border-2 border-blue-200 rounded-2xl p-2">
                                <p class="text-[10px] font-bold text-blue-900 mb-1">Contraseña generada automáticamente:</p>
                                <div class="flex items-center mb-2">
                                    <input type="text" wire:model="password" readonly
                                           class="w-full px-1.5 py-1 bg-white border-2 border-blue-200 rounded-2xl shadow-lg cursor-not-allowed text-gray-700 font-mono text-center text-xs font-bold">
                                    <button type="button" wire:click="generarNuevaContraseña"
                                            class="cursor-pointer ml-1.5 px-1.5 py-1 bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-300">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                    </button>
                                    <button type="button" onclick="copiarContraseña()"
                                            class="cursor-pointer ml-1.5 px-1.5 py-1 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-300">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                        </svg>
                                    </button>
                                </div>
                                <p class="text-[10px] text-blue-700 leading-relaxed">
                                    Esta contraseña se asignará al usuario al hacer clic en "Registrar Usuario".
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones -->
            <div class="flex justify-center space-x-2 pt-2">
                <button type="button" wire:click="cancelarRegistro"
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
                    <span class="relative z-10 text-xs">Registrar Usuario</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('livewire:initialized', () => {
    console.log('Livewire initialized - superuser script loaded');
    
    Livewire.on('show-superuser-warning', () => {
        console.log('show-superuser-warning event received');
        
        Swal.fire({
            title: '¡Advertencia!',
            html: `No puede asignar el rol de Superusuario porque ya existe uno en el sistema.<br>
                  Solo puede haber un Superusuario a la vez.`,
            icon: 'warning',
            confirmButtonText: 'Entendido',
            customClass: {
                popup: 'text-xs'
            }
        }).then(() => {
            // Revertir la selección después de que el usuario cierre la alerta
            Livewire.dispatch('revert-role-selection');
        });
    });
});

// Función para copiar la contraseña al portapapeles
function copiarContraseña() {
    const contraseñaInput = document.querySelector('input[wire\\:model="password"]');
    contraseñaInput.select();
    document.execCommand('copy');
    
    // Mostrar notificación
    const originalText = event.target.textContent;
    event.target.textContent = '¡Copiada!';
    event.target.classList.add('bg-green-800');
    event.target.classList.remove('bg-green-600');
    
    setTimeout(() => {
        event.target.textContent = originalText;
        event.target.classList.remove('bg-green-800');
        event.target.classList.add('bg-green-600');
    }, 2000);
}
</script>