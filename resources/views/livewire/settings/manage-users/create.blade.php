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
            'numDocUsu' => ['required', 'string', 'max:20', 'unique:users,numDocUsu'],
            'nomUsu' => ['required', 'string', 'max:100'],
            'apeUsu' => ['required', 'string', 'max:100'],
            'fecNacUsu' => ['required', 'date'],
            'sexUsu' => ['required', 'in:Hombre,Mujer'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'min:8'],
            'idRolUsu' => ['required', 'exists:rol,idRol'],
            'celCon' => ['required', 'string', 'max:15'],
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
            'numDocUsu' => ['required', 'string', 'max:20', 'unique:users,numDocUsu'],
            'nomUsu' => ['required', 'string', 'max:100'],
            'apeUsu' => ['required', 'string', 'max:100'],
            'fecNacUsu' => ['required', 'date'],
            'sexUsu' => ['required', 'in:Hombre,Mujer'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'min:8'],
            'idRolUsu' => ['required', 'exists:rol,idRol'],
            'celCon' => ['required', 'string', 'max:15'],
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
            'numDocUsu' => ['required', 'string', 'max:20', 'unique:users,numDocUsu'],
            'nomUsu' => ['required', 'string', 'max:100'],
            'apeUsu' => ['required', 'string', 'max:100'],
            'fecNacUsu' => ['required', 'date'],
            'sexUsu' => ['required', 'in:Hombre,Mujer'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'min:8'],
            'idRolUsu' => ['required', 'exists:rol,idRol'],
            
            'celCon' => ['required', 'string', 'max:15'],
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

<div class="flex items-center justify-center p-4">
    <div class="w-full max-w-6xl bg-white shadow rounded-lg p-8 relative">
        <!-- Botón Volver en esquina superior derecha -->
        <div class="absolute top-4 right-4">
            <a href="{{ route('settings.manage-users') }}" wire:navigate
                class="bg-gray-500 hover:bg-gray-600 text-white text-sm font-medium py-1.5 px-3 rounded transition duration-200">
                <i class="fas fa-arrow-left mr-1"></i>Volver
            </a>
        </div>

        <!-- Encabezado centrado con mensajes de retroalimentación -->
        <div class="text-center mb-8" x-data="{ showSuccess: false, showCancel: false }" 
             x-on:registro-exitoso.window="showSuccess = true; showCancel = false; setTimeout(() => showSuccess = false, 3000)" 
             x-on:registro-cancelado.window="showCancel = true; showSuccess = false; setTimeout(() => showCancel = false, 3000)">
            
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Registrar Nuevo Usuario</h1>
            
            <template x-if="showSuccess">
                <div class="rounded bg-green-100 px-4 text-green-800 border border-green-400 mb-2">
                    ¡Usuario registrado exitosamente!
                </div>
            </template>

            <template x-if="showCancel">
                <div class="rounded bg-yellow-100 px-4 text-yellow-800 border border-yellow-400 mb-2">
                    Formulario limpiado. Puede registrar un nuevo usuario.
                </div>
            </template>

            <template x-if="!showSuccess && !showCancel">
                <p class="text-gray-500">Complete los datos del nuevo usuario</p>
            </template>
        </div>

        <form wire:submit.prevent="createUser">
            <!-- Fila: Información personal + Contacto -->
            <div class="flex flex-col md:flex-row gap-6 mb-6">
                <!-- Información personal -->
                <div class="flex-1 border border-gray-300 rounded-lg p-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Información personal</h2>
                    <div class="flex gap-4 mb-4">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-800 mb-1">Tipo de documento</label>
                            <select wire:model="tipDocUsu" wire:blur="validateField('tipDocUsu')" class="w-full border p-2 border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
                                <option disabled selected value="">Seleccione el tipo de documento</option>
                                <option value="CC">Cédula de Ciudadanía</option>
                                <option value="TI">Tarjeta de Identidad</option>
                                <option value="CE">Cédula de Extranjería</option>
                                <option value="PEP">Permiso Especial de Permanencia</option>
                                <option value="PPT">Permiso por Protección Temporal</option>
                            </select>
                            @error('tipDocUsu') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-800 mb-1">Número de documento</label>
                            <input type="text" wire:model="numDocUsu" wire:blur="validateField('numDocUsu')" placeholder="0.000.000.000" class="border p-2 rounded w-full text-black border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
                            @error('numDocUsu') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex gap-4 mb-4">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-800 mb-1">Nombre</label>
                            <input type="text" wire:model="nomUsu" wire:blur="validateField('nomUsu')" placeholder="Nombre(s)" class="border p-2 rounded w-full text-black border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
                            @error('nomUsu') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-800 mb-1">Apellido</label>
                            <input type="text" wire:model="apeUsu" wire:blur="validateField('apeUsu')" placeholder="Apellidos" class="border p-2 rounded w-full text-black border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
                            @error('apeUsu') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-800 mb-1">Fecha de nacimiento</label>
                            <input type="date" wire:model="fecNacUsu" wire:blur="validateField('fecNacUsu')" class="border p-2 rounded w-full text-black border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
                            @error('fecNacUsu') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-800 mb-1">Sexo</label>
                            <select wire:model="sexUsu" wire:blur="validateField('sexUsu')" class="border p-2 rounded w-full text-black bg-white border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
                                <option value="Hombre">Hombre</option>
                                <option value="Mujer">Mujer</option>
                            </select>
                            @error('sexUsu') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Contacto y Rol -->
                <div class="flex-1 border border-gray-300 rounded-lg p-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Contacto y Rol</h2>
                    <div class="flex gap-4 mb-4">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-800 mb-1">Célular</label>
                            <input type="text" wire:model="celCon" wire:blur="validateField('celCon')" placeholder="000-000-0000" class="border p-2 rounded w-full text-black border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
                            @error('celCon') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-800 mb-1">Correo electrónico</label>
                            <input type="email" wire:model="email" wire:blur="validateField('email')" placeholder="ejemplo@dominio.com" class="border p-2 rounded w-full text-black border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
                            @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-800 mb-1">Rol</label>
                        <!-- Cambiado a wire:model.live para que se ejecute updatedIdRolUsu automáticamente -->
                        <select wire:model.live="idRolUsu" wire:blur="validateField('idRolUsu')" class="border p-2 rounded w-full text-black bg-white border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
                            @foreach(Rol::all() as $rol)
                            <option value="{{ $rol->idRol }}">{{ $rol->nomRol }}</option>
                            @endforeach
                        </select>
                        @error('idRolUsu') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
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
                            <input type="text" wire:model="calDir" wire:blur="validateField('calDir')" placeholder="Calle" class="border p-2 rounded w-full text-black border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
                            @error('calDir') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-800 mb-1">Barrio</label>
                            <input type="text" wire:model="barDir" wire:blur="validateField('barDir')" placeholder="Barrio" class="border p-2 rounded w-full text-black border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
                            @error('barDir') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-800 mb-1">Ciudad</label>
                            <input type="text" wire:model="ciuDir" wire:blur="validateField('ciuDir')" placeholder="Ciudad" class="border p-2 rounded w-full text-black border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
                            @error('ciuDir') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-800 mb-1">Departamento</label>
                            <input type="text" wire:model="depDir" wire:blur="validateField('depDir')" placeholder="Departamento" readonly class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg cursor-not-allowed">
                            @error('depDir') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-800 mb-1">Código postal</label>
                            <input type="text" wire:model="codPosDir" wire:blur="validateField('codPosDir')" placeholder="Código postal" class="border p-2 rounded w-full text-black border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
                            @error('codPosDir') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-800 mb-1">País</label>
                            <input type="text" wire:model="paiDir" wire:blur="validateField('paiDir')" placeholder="País" readonly class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg cursor-not-allowed">
                            @error('paiDir') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Seguridad -->
                <div class="flex-1 border border-gray-300 rounded-lg p-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Seguridad</h2>
                    <div class="space-y-4">
                        <!-- Campo de Contraseña Generada Automáticamente -->
                        <div>
                            <label class="block text-sm font-medium text-gray-800 mb-1">Contraseña Generada</label>
                            <div class="bg-blue-50 border border-blue-200 rounded-md p-3">
                                <p class="text-sm font-medium text-blue-800 mb-1">Contraseña generada automáticamente:</p>
                                <div class="mt-1 flex items-center">
                                    <input type="text" 
                                           wire:model="password"
                                           readonly class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg cursor-not-allowed">
                                    <button type="button" 
                                            wire:click="generarNuevaContraseña"
                                            class="ml-2 px-3 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        Regenerar
                                    </button>
                                    <button type="button" 
                                            onclick="copiarContraseña()"
                                            class="ml-2 px-3 py-2 bg-green-600 text-white text-sm rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                                        Copiar
                                    </button>
                                </div>
                                <p class="mt-1 text-xs text-blue-600">Esta contraseña se asignará al usuario al hacer clic en "Registrar Usuario"</p>
                            </div>
                            @error('password') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones -->
            <div class="flex justify-center space-x-4 mt-6">
                <button type="button" wire:click="cancelarRegistro" class="cursor-pointer px-6 py-2 bg-gray-500 text-white rounded-md font-semibold hover:bg-gray-600 transition duration-150">
                    Cancelar
                </button>
                <button type="submit" class="cursor-pointer px-6 py-2 bg-[#007832] text-white rounded-md font-semibold hover:bg-green-700 transition duration-150">
                    Registrar Usuario
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
                popup: 'text-sm'
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