<?php
use App\Models\User;
use App\Models\Rol;
use App\Models\Contacto;
use App\Models\Direccion;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

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

<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-gray-800 mb-2">Gestión de Usuarios</h1>
        <p class="text-gray-600">Administra todos los usuarios del sistema</p>
    </div>

    <!-- Barra de búsqueda -->
    <div class="bg-white rounded-lg shadow-md border-2 border-gray-200 p-4 mb-6">
        <div class="flex gap-4 items-end">
            <div class="flex-1">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Buscar usuarios</label>
                <input type="text"
                wire:model.live.debounce.500ms="search"
                placeholder="Buscar por nombre, apellido, documento, email, teléfono, rol o dirección..."
                class="w-full px-3 py-2 border border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
            </div>
            <div class="flex gap-2">
                <a href="{{ route('settings.manage-users.create') }}" wire:navigate
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-plus mr-2"></i>Nuevo Usuario
                </a>
                @if($search)
                <button wire:click="clearSearch"
                    class="cursor-pointer bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-times mr-2"></i>Limpiar
                </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Tabla de usuarios -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        @if($users->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-green-600">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Nombre</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Documento</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Rol</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Teléfono</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($users as $index => $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ ($users->currentPage() - 1) * $users->perPage() + $index + 1 }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $user->nomUsu }} {{ $user->apeUsu }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $user->tipDocUsu }} {{ $user->numDocUsu }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $user->email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                {{ $user->rol->nomRol ?? 'Sin rol' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $user->contacto->celCon ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <div class="flex justify-center space-x-2">
                                <a href="{{ route('settings.manage-users.show', $user->id) }}" wire:navigate
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded text-sm transition duration-200">
                                    Detalles
                                </a>
                                <a href="{{ route('settings.manage-users.edit', $user->id) }}" wire:navigate
                                    class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-1 px-3 rounded text-sm transition duration-200">
                                    Editar
                                </a>
                                <button wire:click="confirmDelete({{ $user->id }})"
                                    class="cursor-pointer bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded text-sm transition duration-200">
                                    Eliminar
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <div class="px-6 py-4 bg-gray-50">
            {{ $users->links() }}
        </div>
        @else
        <div class="px-6 py-12 text-center">
            <i class="fas fa-users text-gray-400 text-4xl mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No hay usuarios registrados</h3>
            <p class="text-gray-500 mb-4">Comience agregando el primer usuario.</p>
            <a href="{{ route('settings.manage-users.create') }}" wire:navigate
                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                <i class="fas fa-plus mr-2"></i>Agregar Usuario
            </a>
        </div>
        @endif
    </div>

    <!-- Modal Eliminar Usuario -->
    @if($showDeleteModal)
    <div class="fixed inset-0 bg-black/20 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg max-w-sm w-full">
            <h2 class="text-xl font-semibold mb-4">Confirmar eliminación</h2>
            <p class="mb-4 text-sm text-gray-600">
                ¿Está seguro que desea eliminar este usuario? Esta acción no se puede deshacer.
            </p>
            
            @php
                $userToDeleteData = $users->where('id', $userToDelete)->first();
            @endphp
            
            @if($userToDeleteData)
            <div class="mb-4">
                <p><strong>Usuario:</strong> {{ $userToDeleteData->nomUsu }} {{ $userToDeleteData->apeUsu }}</p>
                <p><strong>Email:</strong> {{ $userToDeleteData->email }}</p>
            </div>
            @endif
            
            <div class="flex justify-end gap-3">
                <button wire:click="$set('showDeleteModal', false)"
                        class="cursor-pointer px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 transition">Cancelar</button>
                <button wire:click="deleteUser"
                        class="cursor-pointer px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition">Confirmar Eliminación</button>
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