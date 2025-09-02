<?php
use App\Models\User;
use App\Models\Rol;
use App\Models\Auditoria;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;

new #[Layout('layouts.auth')] class extends Component {
    public User $user;
    public string $tipDocUsu = '';
    public string $numDocUsu = '';
    public string $nomUsu = '';
    public string $apeUsu = '';
    public string $fecNacUsu = '';
    public string $sexUsu = '';
    public string $email = '';
    public int $idRolUsu = 1;

    public string $celCon = '';
    public string $calDir = '';
    public string $barDir = '';
    public string $ciuDir = '';
    public string $depDir = '';
    public string $codPosDir = '';
    public string $paiDir = '';

    public function mount(User $user): void
    {
        $this->user = $user;
        $this->tipDocUsu = $user->tipDocUsu ?? '';
        $this->numDocUsu = $user->numDocUsu ?? '';
        $this->nomUsu = $user->nomUsu ?? '';
        $this->apeUsu = $user->apeUsu ?? '';
        $this->fecNacUsu = $user->fecNacUsu ?? '';
        $this->sexUsu = $user->sexUsu ?? '';
        $this->email = $user->email ?? '';
        $this->idRolUsu = $user->idRolUsu ?? 1;

        $this->celCon = $user->contacto->celCon ?? '';
        $this->calDir = $user->direccion->calDir ?? '';
        $this->barDir = $user->direccion->barDir ?? '';
        $this->ciuDir = $user->direccion->ciuDir ?? '';
        $this->depDir = $user->direccion->depDir ?? '';
        $this->codPosDir = $user->direccion->codPosDir ?? '';
        $this->paiDir = $user->direccion->paiDir ?? '';
    }

    // Este método se ejecutará automáticamente cuando idRolUsu cambie
    public function updatedIdRolUsu($value)
    {
        if ($value == 2) { // 2 = Superadministrador
            $exists = User::where('idRolUsu', 2)
                          ->where('id', '!=', $this->user->id)
                          ->exists();

            if ($exists) {
                // Muestra error debajo del campo
                $this->addError('idRolUsu', 'Solo puede existir un superadministrador en el sistema.');

                // Lanza el modal de advertencia
                $this->dispatch('show-superuser-warning');
                
                // Revertir inmediatamente el valor
                $this->idRolUsu = $this->user->idRolUsu;
            }
        }
    }

    public function revertRoleSelection(): void
    {
        $this->idRolUsu = $this->user->idRolUsu;
        $this->dispatch('notify', [
            'type' => 'info',
            'message' => 'Rol revertido al valor original'
        ]);
    }

    // Método para cancelar cambios (restablece los valores originales)
    public function cancelarCambios(): void
    {
        $this->mount($this->user);
        
        // Disparar evento para mostrar mensaje
        $this->dispatch('actualizacion-cancelada');
    }

    public function updateUser(): void
    {
        // Validación manual para el rol de superadministrador
        if ($this->idRolUsu == 2) {
            $exists = User::where('idRolUsu', 2)
                          ->where('id', '!=', $this->user->id)
                          ->exists();

            if ($exists) {
                $this->addError('idRolUsu', 'Solo puede existir un superadministrador en el sistema.');
                $this->dispatch('show-superuser-warning');
                return;
            }
        }

        // Validación completa
        $validated = $this->validate([
            'tipDocUsu' => ['required', 'string', 'max:10'],
            'numDocUsu' => ['required', 'string', 'max:20', Rule::unique(User::class)->ignore($this->user->id)],
            'nomUsu' => ['required', 'string', 'max:100'],
            'apeUsu' => ['required', 'string', 'max:100'],
            'fecNacUsu' => ['required', 'date'],
            'sexUsu' => ['required', 'in:Hombre,Mujer'],
            'email' => ['required', 'email', 'max:255', Rule::unique(User::class)->ignore($this->user->id)],
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

            if ($this->idRolUsu == 2) {
                $currentSuperuser = User::where('idRolUsu', 2)
                                      ->where('id', '!=', $this->user->id)
                                      ->first();

                if ($currentSuperuser) {
                    // Forzar cambio automático del actual superadmin a admin
                    $currentSuperuser->update(['idRolUsu' => 1]);

                    Auditoria::create([
                        'idUsuAud' => auth()->id(),
                        'usuAud' => auth()->user()->nomUsu . ' ' . auth()->user()->apeUsu,
                        'rolAud' => auth()->user()->rol->nomRol,
                        'opeAud' => 'UPDATE',
                        'tablaAud' => 'users',
                        'regAud' => $currentSuperuser->id,
                        'desAud' => 'Superusuario cambiado a Administrador automáticamente',
                        'ipAud' => request()->ip()
                    ]);
                }
            }

            $this->user->update($validated);

            if($this->user->contacto) {
                $this->user->contacto->update(['celCon' => $this->celCon]);

                if($this->user->direccion) {
                    $this->user->direccion->update([
                        'calDir' => $this->calDir,
                        'barDir' => $this->barDir,
                        'ciuDir' => $this->ciuDir,
                        'depDir' => $this->depDir,
                        'codPosDir' => $this->codPosDir,
                        'paiDir' => $this->paiDir,
                    ]);
                }
            }

            DB::commit();

            // Disparar evento para mostrar mensaje de éxito
            $this->dispatch('actualizacion-exitosa');

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Usuario actualizado correctamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al actualizar usuario: ' . $e->getMessage()
            ]);
        }
    }
}; ?>

@section('title', 'Editar Usuario')

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
             x-on:actualizacion-exitosa.window="showSuccess = true; showCancel = false; setTimeout(() => showSuccess = false, 3000)" 
             x-on:actualizacion-cancelada.window="showCancel = true; showSuccess = false; setTimeout(() => showCancel = false, 3000)">
            
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Editar Usuario</h1>
            
            <template x-if="showSuccess">
                <div class="rounded bg-green-100 px-4 text-green-800 border border-green-400 mb-2">
                    ¡Usuario actualizado exitosamente!
                </div>
            </template>

            <template x-if="showCancel">
                <div class="rounded bg-yellow-100 px-4 text-yellow-800 border border-yellow-400 mb-2">
                    Cambios descartados. Los datos se han restablecido.
                </div>
            </template>

            <template x-if="!showSuccess && !showCancel">
                <p class="text-gray-500">Actualiza la información del usuario</p>
            </template>
        </div>

        <form wire:submit.prevent="updateUser">
            <!-- Fila: Información personal + Contacto -->
            <div class="flex flex-col md:flex-row gap-6 mb-6">
                <!-- Información personal -->
                <div class="flex-1 border border-gray-300 rounded-lg p-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Información personal</h2>
                    <div class="flex gap-4 mb-4">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-800 mb-1">Tipo de documento</label>
                            <select wire:model="tipDocUsu" class="w-full border p-2 border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
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
                            <input type="text" wire:model="numDocUsu" placeholder="0.000.000.000" class="border p-2 rounded w-full text-black border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
                            @error('numDocUsu') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex gap-4 mb-4">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-800 mb-1">Nombre</label>
                            <input type="text" wire:model="nomUsu" placeholder="Nombre(s)" class="border p-2 rounded w-full text-black border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
                            @error('nomUsu') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-800 mb-1">Apellido</label>
                            <input type="text" wire:model="apeUsu" placeholder="Apellidos" class="border p-2 rounded w-full text-black border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
                            @error('apeUsu') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-800 mb-1">Fecha de nacimiento</label>
                            <input type="date" wire:model="fecNacUsu" class="border p-2 rounded w-full text-black border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
                            @error('fecNacUsu') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-800 mb-1">Sexo</label>
                            <select wire:model="sexUsu" class="border p-2 rounded w-full text-black bg-white border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
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
                            <input type="text" wire:model="celCon" placeholder="000-000-0000" class="border p-2 rounded w-full text-black border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
                            @error('celCon') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-800 mb-1">Correo electrónico</label>
                            <input type="email" wire:model="email" placeholder="ejemplo@dominio.com" class="border p-2 rounded w-full text-black border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
                            @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-800 mb-1">Rol</label>
                        <!-- Cambiado a wire:model.live para que se ejecute updatedIdRolUsu automáticamente -->
                        <select wire:model.live="idRolUsu" class="border p-2 rounded w-full text-black bg-white border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
                            @foreach(Rol::all() as $rol)
                            <option value="{{ $rol->idRol }}">{{ $rol->nomRol }}</option>
                            @endforeach
                        </select>
                        @error('idRolUsu') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Fila: Dirección -->
            <div class="border border-gray-300 rounded-lg p-4 mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Dirección</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-800 mb-1">Calle</label>
                        <input type="text" wire:model="calDir" placeholder="Calle" class="border p-2 rounded w-full text-black border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
                        @error('calDir') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-800 mb-1">Barrio</label>
                        <input type="text" wire:model="barDir" placeholder="Barrio" class="border p-2 rounded w-full text-black border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
                        @error('barDir') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-800 mb-1">Ciudad</label>
                        <input type="text" wire:model="ciuDir" placeholder="Ciudad" class="border p-2 rounded w-full text-black border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
                        @error('ciuDir') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-800 mb-1">Departamento</label>
                        <input type="text" wire:model="depDir" placeholder="Departamento" readonly class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg cursor-not-allowed">
                        @error('depDir') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-800 mb-1">Código postal</label>
                        <input type="text" wire:model="codPosDir" placeholder="Código postal" class="border p-2 rounded w-full text-black border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
                        @error('codPosDir') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-800 mb-1">País</label>
                        <input type="text" wire:model="paiDir" placeholder="País" readonly class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg cursor-not-allowed">
                        @error('paiDir') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Botones -->
            <div class="flex justify-center space-x-4 mt-6">
                <button type="button" wire:click="cancelarCambios" class="cursor-pointer px-6 py-2 bg-gray-500 text-white rounded-md font-semibold hover:bg-gray-600 transition duration-150">
                    Cancelar
                </button>
                <button type="submit" class="cursor-pointer px-6 py-2 bg-[#007832] text-white rounded-md font-semibold hover:bg-green-700 transition duration-150">
                    Actualizar Usuario
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
</script>