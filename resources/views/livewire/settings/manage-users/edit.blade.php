<?php
use App\Models\User;
use App\Models\Rol;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
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

public function updateUser(): void
{
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

    // Verificar si se está cambiando a superusuario
    if ($this->idRolUsu == 2) { // ID de superusuario
        // Buscar el superusuario actual
        $currentSuperuser = User::where('idRolUsu', 2)
                              ->where('id', '!=', $this->user->id)
                              ->first();
        
        if ($currentSuperuser) {
            // Cambiar el superusuario actual a administrador
            $currentSuperuser->update(['idRolUsu' => 1]); // ID de administrador
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

    $this->dispatch('notify', [
        'type' => 'success',
        'message' => 'Usuario actualizado correctamente'
    ]);
}

    public function cancelarCambios(): void
    {
        $this->mount($this->user); 
        $this->dispatch('notify', [
            'type' => 'info',
            'message' => 'Cambios descartados'
        ]);
    }
}; ?>

@section('title', 'Editar Usuario')

<div class="flex items-center justify-center p-4">
    <div class="w-full max-w-6xl bg-white shadow rounded-lg p-8">
        <!-- Encabezado -->
        <h1 class="text-2xl font-bold text-center text-gray-800 mb-2">Editar Usuario</h1>
        <p class="text-center text-gray-500 mb-8">Actualiza la información del usuario</p>

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
                                <option value="Hombre">Hombre</option>
                                <option value="Mujer">Mujer</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Contacto y Rol -->
                <div class="flex-1 border border-gray-300 rounded-lg p-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Contacto y Rol</h2>
                    <div class="flex gap-4 mb-4">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-800 mb-1">Célular</label>
                            <input type="text" wire:model="celCon" class="border p-2 rounded w-full text-black border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-800 mb-1">Correo electrónico</label>
                            <input type="email" wire:model="email" class="border p-2 rounded w-full text-black border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-800 mb-1">Rol</label>
                        <select wire:model="idRolUsu" class="border p-2 rounded w-full text-black bg-white border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
                            @foreach(Rol::all() as $rol)
                            <option value="{{ $rol->idRol }}">{{ $rol->nomRol }}</option>
                            @endforeach
                        </select>
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

            <!-- Botones -->
            <div class="text-center mb-4 space-x-4">
                <button type="submit" class="cursor-pointer px-6 py-2 bg-[#007832] text-white rounded-md font-semibold hover:bg-green-700 transition duration-150">
                    Guardar Cambios
                </button>
                <button type="button" wire:click="cancelarCambios" class="cursor-pointer px-6 py-2 bg-gray-500 text-white rounded-md font-semibold hover:bg-gray-600 transition duration-150">
                    Cancelar
                </button>
                <a href="{{ route('settings.manage-users') }}" wire:navigate class="cursor-pointer px-6 py-2 bg-red-500 text-white rounded-md font-semibold hover:bg-red-600 transition duration-150">
                    Volver
                </a>
            </div>
        </form>
    </div>
</div>