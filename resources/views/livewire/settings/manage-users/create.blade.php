<?php
use App\Models\User;
use App\Models\Rol;
use App\Models\Auditoria;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Rules\SingleSuperAdmin;

new #[Layout('layouts.auth')] class extends Component {
    public string $tipDocUsu = '';
    public string $numDocUsu = '';
    public string $nomUsu = '';
    public string $apeUsu = '';
    public string $fecNacUsu = '';
    public string $sexUsu = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public int $idRolUsu = 1;

    public string $celCon = '';
    public string $calDir = '';
    public string $barDir = '';
    public string $ciuDir = '';
    public string $depDir = 'Huila';
    public string $codPosDir = '';
    public string $paiDir = 'Colombia';

    public function mount(): void
    {
        $this->sexUsu = 'Hombre';
    }

    public function updatedIdRolUsu($value)
    {
        if ($value == 2) {
            $existingSuperuser = User::where('idRolUsu', 2)->exists();
            
            if ($existingSuperuser) {
                $this->dispatch('show-superuser-warning');
            }
        }
    }

    public function revertRoleSelection(): void
    {
        $this->idRolUsu = 1;
    }

    public function createUser(): void
    {
        $validated = $this->validate([
            'tipDocUsu' => ['required', 'string', 'max:10'],
            'numDocUsu' => ['required', 'string', 'max:20', 'unique:users,numDocUsu'],
            'nomUsu' => ['required', 'string', 'max:100'],
            'apeUsu' => ['required', 'string', 'max:100'],
            'fecNacUsu' => ['required', 'date'],
            'sexUsu' => ['required', 'in:Hombre,Mujer'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:8'],
            'idRolUsu' => ['required', 'exists:rol,idRol', new SingleSuperAdmin()],
            
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

            // Manejar cambio de superusuario si es necesario
            if ($this->idRolUsu == 2) {
                $currentSuperuser = User::where('idRolUsu', 2)->first();
            
                if ($currentSuperuser) {
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

            // Crear usuario
            User::create([
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

            $this->redirect(route('settings.manage-users.index'), navigate: true);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al crear usuario: ' . $e->getMessage()
            ]);
        }
    }
}; ?>

@section('title', 'Crear Usuario')

<div class="flex items-center justify-center p-4">
    <div class="w-full max-w-6xl bg-white shadow rounded-lg p-8">
        <!-- Encabezado -->
        <h1 class="text-2xl font-bold text-center text-gray-800 mb-2">Registrar Nuevo Usuario</h1>
        <p class="text-center text-gray-500 mb-8">Complete los datos del nuevo usuario</p>

        <form wire:submit.prevent="createUser">
            <!-- Fila: Información personal + Contacto -->
            <div class="flex flex-col md:flex-row gap-6 mb-6">
                <!-- Información personal -->
                <div class="flex-1 border border-gray-300 rounded-lg p-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Información personal</h2>
                    <div class="flex gap-4 mb-4">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-800 mb-1">Tipo de documento</label>
                            <select wire:model="tipDocUsu" class="w-full border p-2 border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
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
                            <input data-validation="document" data-trim="true" type="text" wire:model="numDocUsu" placeholder="0.000.000.000" class="border p-2 rounded w-full text-black border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
                        </div>
                    </div>

                    <div class="flex gap-4 mb-4">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-800 mb-1">Nombre</label>
                            <input data-validation="text-only" data-trim="true" data-format="capitalize" type="text" wire:model="nomUsu" placeholder="Nombre(s)" class="border p-2 rounded w-full text-black border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-800 mb-1">Apellido</label>
                            <input data-validation="text-only" data-trim="true" data-format="capitalize" type="text" wire:model="apeUsu" placeholder="Apellidos" class="border p-2 rounded w-full text-black border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
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
                            <input data-validation="phone" type="text" wire:model="celCon" placeholder="000-000-0000" class="border p-2 rounded w-full text-black border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-800 mb-1">Correo electrónico</label>
                            <input data-validation="email" data-php-validation="email" data-trim="true" type="email" wire:model="email" placeholder="ejemplo@dominio.com" class="border p-2 rounded w-full text-black border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
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

            <!-- Fila: Dirección + Seguridad -->
            <div class="flex flex-col md:flex-row gap-6 mb-6">
                <!-- Dirección -->
                <div class="flex-1 border border-gray-300 rounded-lg p-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Dirección</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-800 mb-1">Calle</label>
                            <input data-trim="true" data-php-validation="no-html" type="text" wire:model="calDir" placeholder="Calle" class="border p-2 rounded w-full text-black border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-800 mb-1">Barrio</label>
                            <input data-trim="true" data-php-validation="no-html" type="text" wire:model="barDir" placeholder="Barrio" class="border p-2 rounded w-full text-black border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-800 mb-1">Ciudad</label>
                            <input data-trim="true" data-format="capitalize" data-php-validation="no-html" type="text" wire:model="ciuDir" placeholder="Ciudad" class="border p-2 rounded w-full text-black border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-800 mb-1">Departamento</label>
                            <input type="text" wire:model="depDir" placeholder="Departamento" readonly class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg cursor-not-allowed">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-800 mb-1">Código postal</label>
                            <input type="text" wire:model="codPosDir" placeholder="Código postal" class="border p-2 rounded w-full text-black border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-800 mb-1">País</label>
                            <input type="text" wire:model="paiDir" placeholder="País" readonly class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg cursor-not-allowed">
                        </div>
                    </div>
                </div>

                <!-- Seguridad -->
                <div class="flex-1 border border-gray-300 rounded-lg p-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Seguridad</h2>
                    <div class="space-y-4">
                        <!-- Campo de Contraseña -->
                        <div>
                            <label class="block text-sm font-medium text-gray-800 mb-1">Contraseña</label>
                            <div class="relative" x-data="{ show: false, hasValue: false }">
                                <input data-validation="password" 
                                       wire:model="password" 
                                       :type="show ? 'text' : 'password'" 
                                       placeholder="••••••••" 
                                       class="w-full px-3 py-2 border border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white pr-10"
                                       @input="const val = $event.target.value; hasValue = val.length > 0; if (val.length === 0) show = false;">

                                <button type="button"
                                        x-show="hasValue"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center justify-center text-black w-10 h-full"
                                        @click="show = !show"
                                        x-cloak>
                                    <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="cursor-pointer h-5 w-5" fill="none"
                                         viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7s-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg x-show="show" xmlns="http://www.w3.org/2000/svg" class="cursor-pointer h-5 w-5" fill="none"
                                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M10.733 5.076a10.744 10.744 0 0 1 11.205 6.575 1 1 0 0 1 0 .696 10.747 10.747 0 0 1-1.444 2.49"/>
                                        <path d="M14.084 14.158a3 3 0 0 1-4.242-4.242"/>
                                        <path d="M17.479 17.499a10.75 10.75 0 0 1-15.417-5.151 1 1 0 0 1 0-.696 10.75 10.75 0 0 1 4.446-5.143"/>
                                        <path d="m2 2 20 20"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones -->
            <div class="flex justify-center space-x-4 mt-6">
                <a href="{{ route('settings.manage-users') }}" wire:navigate class="cursor-pointer px-6 py-2 bg-gray-500 text-white rounded-md font-semibold hover:bg-gray-600 transition duration-150">
                    Cancelar
                </a>
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
            html: `Al asignar el rol de Superusuario:<br>
                  • El superusuario actual será cambiado a Administrador automáticamente<br>
                  • Usted obtendrá todos los permisos de superusuario<br>
                  • Esta acción se registrará en el sistema de auditoría`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Continuar',
            cancelButtonText: 'Cancelar',
            customClass: {
                popup: 'text-sm'
            }
        }).then((result) => {
            if (!result.isConfirmed) {
                console.log('User cancelled, reverting role selection');
                Livewire.dispatch('revert-role-selection');
            } else {
                console.log('User confirmed superuser assignment');
            }
        });
    });
});
</script>
