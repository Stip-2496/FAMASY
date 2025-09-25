<?php
use App\Models\User;
use App\Models\Rol;
use App\Models\Contacto;
use App\Models\Direccion;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

new #[Layout('layouts.auth')] class extends Component {
    use WithPagination;

    public string $search = '';
    public int $perPage = 10;
    public $showDeleteModal = false;
    public $userToDelete = null;

    public function with(): array
    {
        return [
            'users' => User::query()
                ->with(['rol', 'contacto', 'direccion'])
                ->when($this->search, function ($query) {
                    $query->where(function($q) {
                        $q->where('nomUsu', 'like', '%'.$this->search.'%')
                          ->orWhere('apeUsu', 'like', '%'.$this->search.'%')
                          ->orWhere('numDocUsu', 'like', '%'.$this->search.'%')
                          ->orWhere('tipDocUsu', 'like', '%'.$this->search.'%')
                          ->orWhere('email', 'like', '%'.$this->search.'%')
                          ->orWhere('sexUsu', 'like', '%'.$this->search.'%')
                          ->orWhereHas('rol', function($q) {
                              $q->where('nomRol', 'like', '%'.$this->search.'%');
                          })
                          ->orWhereHas('contacto', function($q) {
                              $q->where('celCon', 'like', '%'.$this->search.'%');
                          })
                          ->orWhereHas('direccion', function($q) {
                              $q->where('calDir', 'like', '%'.$this->search.'%')
                                ->orWhere('barDir', 'like', '%'.$this->search.'%')
                                ->orWhere('ciuDir', 'like', '%'.$this->search.'%')
                                ->orWhere('depDir', 'like', '%'.$this->search.'%')
                                ->orWhere('paiDir', 'like', '%'.$this->search.'%');
                          });
                    });
                })
                ->orderBy('nomUsu', 'asc')
                ->paginate($this->perPage)
        ];
    }

    // Determina si un usuario está activo (últimos 30 días)
    public function isUserActive($user): bool
    {
        return $user->last_login_at && $user->last_login_at >= now()->subDays(30);
    }

    public function clearSearch(): void
    {
        $this->search = '';
        $this->resetPage();
    }

    public function updatedSearch($value): void
    {
        $this->resetPage();
    }

    public function confirmDelete($id): void
    {
        $this->userToDelete = $id;
        $this->showDeleteModal = true;
    }

    public function deleteUser(): void
    {
        try {
            DB::beginTransaction();
            
            $user = User::findOrFail($this->userToDelete);
            $contactoId = $user->idConUsu;
            
            // 1. Eliminar usuario primero
            $user->delete();
            
            // 2. Eliminar contacto después (esto eliminará la dirección por la relación)
            if ($contactoId) {
                $contacto = Contacto::find($contactoId);
                if ($contacto) {
                    $contacto->delete();
                }
            }
            
            DB::commit();
            
            $this->showDeleteModal = false;
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Usuario eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al eliminar usuario: ' . $e->getMessage()
            ]);
        } finally {
            $this->userToDelete = null;
        }
    }
}; ?>

@section('title', 'Gestión de Usuarios')

<div class="bg-gray-100 min-h-full p-4">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Gestión de Usuarios</h1>
        <p class="text-sm text-gray-600">Administra todos los usuarios del sistema</p>
    </div>

    <!-- Barra de búsqueda -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-4 sm:items-end">
            <div class="flex-1">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Buscar usuarios</label>
                <input type="text"
                wire:model.live.debounce.500ms="search"
                placeholder="Buscar por nombre, apellido, documento, email, teléfono, rol o dirección..."
                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
            </div>
            <div class="flex gap-2">
                <a href="{{ route('settings.manage-users.create') }}" wire:navigate
                    class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-3 rounded-md transition duration-200">
                    <i class="fas fa-plus mr-1"></i>Nuevo Usuario
                </a>
                @if($search)
                <button wire:click="clearSearch"
                    class="cursor-pointer bg-gray-500 hover:bg-gray-600 text-white text-sm font-medium py-2 px-3 rounded-md transition duration-200">
                    <i class="fas fa-times mr-1"></i>Limpiar
                </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Tabla de usuarios -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        @if($users->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-green-600">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-white uppercase tracking-wider">#</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-white uppercase tracking-wider">Nombre</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-white uppercase tracking-wider">Documento</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-white uppercase tracking-wider">Email</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-white uppercase tracking-wider">Rol</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-white uppercase tracking-wider">Estado</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-white uppercase tracking-wider">Teléfono</th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-white uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($users as $index => $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                            {{ ($users->currentPage() - 1) * $users->perPage() + $index + 1 }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $user->nomUsu }} {{ $user->apeUsu }}</div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                            {{ $user->tipDocUsu }} {{ $user->numDocUsu }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                            {{ $user->email }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                {{ $user->rol->nomRol ?? 'Sin rol' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($this->isUserActive($user))
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    <i class="fas fa-circle-check mr-1"></i> Activo
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                    <i class="fas fa-clock mr-1"></i> Inactivo
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                            {{ $user->contacto->celCon ?? 'N/A' }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-center">
                            <div class="flex justify-center space-x-1">
                                <a href="{{ route('settings.manage-users.show', $user->id) }}" wire:navigate
                                    class="bg-blue-500 hover:bg-blue-600 text-white text-xs font-medium py-1 px-2 rounded transition duration-200">
                                    Detalles
                                </a>
                                <a href="{{ route('settings.manage-users.edit', $user->id) }}" wire:navigate
                                    class="bg-yellow-500 hover:bg-yellow-600 text-white text-xs font-medium py-1 px-2 rounded transition duration-200">
                                    Editar
                                </a>
                                <button wire:click="confirmDelete({{ $user->id }})"
                                    class="cursor-pointer bg-red-500 hover:bg-red-600 text-white text-xs font-medium py-1 px-2 rounded transition duration-200">
                                    Eliminar
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Paginación mejorada -->
        @if($users->hasPages())
            <div class="bg-white px-3 py-2 border-t border-gray-200 sm:px-4">
                <div class="flex items-center justify-between">
                    <div class="flex-1 flex justify-between sm:hidden">
                        {{ $users->links() }}
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-xs text-gray-700">
                                Mostrando <span class="font-medium">{{ $users->firstItem() }}</span> a 
                                <span class="font-medium">{{ $users->lastItem() }}</span> de 
                                <span class="font-medium">{{ $users->total() }}</span> usuarios
                            </p>
                        </div>
                        <div>
                            {{ $users->links() }}
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @else
        <div class="px-6 py-8 text-center">
            <i class="fas fa-users text-gray-400 text-3xl mb-3"></i>
            <h3 class="text-base font-medium text-gray-900 mb-1">No hay usuarios registrados</h3>
            <p class="text-sm text-gray-500 mb-4">Comience agregando el primer usuario.</p>
            <a href="{{ route('settings.manage-users.create') }}" wire:navigate
                class="inline-block bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-3 rounded-md transition duration-200">
                <i class="fas fa-plus mr-1"></i>Agregar Usuario
            </a>
        </div>
        @endif
    </div>

    <!-- Modal Eliminar Usuario -->
    @if($showDeleteModal)
    <div class="fixed inset-0 bg-black/20 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-4 rounded-lg max-w-xs w-full mx-2">
            <h2 class="text-lg font-semibold mb-3">Confirmar eliminación</h2>
            <p class="mb-3 text-xs sm:text-sm text-gray-600">
                ¿Está seguro que desea eliminar este usuario? Esta acción no se puede deshacer.
            </p>
            
            @php
                $userToDeleteData = $users->where('id', $userToDelete)->first();
            @endphp
            
            @if($userToDeleteData)
            <div class="mb-3 text-sm">
                <p><strong>Usuario:</strong> {{ $userToDeleteData->nomUsu }} {{ $userToDeleteData->apeUsu }}</p>
                <p><strong>Email:</strong> {{ $userToDeleteData->email }}</p>
                <p><strong>Estado:</strong> 
                    @if($this->isUserActive($userToDeleteData))
                        <span class="text-green-600">Activo</span>
                    @else
                        <span class="text-gray-600">Inactivo</span>
                    @endif
                </p>
            </div>
            @endif
            
            <div class="flex justify-end gap-2">
                <button wire:click="$set('showDeleteModal', false)"
                        class="cursor-pointer px-3 py-1 text-sm bg-gray-300 rounded hover:bg-gray-400 transition">Cancelar</button>
                <button wire:click="deleteUser"
                        class="cursor-pointer px-3 py-1 text-sm bg-red-600 text-white rounded hover:bg-red-700 transition">Eliminar</button>
            </div>
        </div>
    </div>
    @endif

    <!-- Script para notificaciones -->
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('notify', (event) => {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                });
                
                Toast.fire({
                    icon: event.type,
                    title: event.message
                });
            });
        });
    </script>
</div>