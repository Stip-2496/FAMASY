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

<div class="min-h-screen py-4">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        @if(session('success'))
            <div class="mb-2 p-2 bg-emerald-50 border border-emerald-200 rounded-lg shadow-sm">
                <div class="flex items-center">
                    <svg class="h-3 w-3 text-emerald-500 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-xs text-emerald-800 font-medium">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-2 p-2 bg-red-50 border border-red-200 rounded-lg shadow-sm">
                <div class="flex items-center">
                    <svg class="h-3 w-3 text-red-500 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-xs text-red-800 font-medium">{{ session('error') }}</p>
                </div>
            </div>
        @endif

<!-- Header -->
<div class="mb-4">
    <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-xl border border-white/20 p-4 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-blue-600/5"></div>
        
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center space-x-2 mb-3 lg:mb-0">
                <div class="p-2 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg transform rotate-3 hover:rotate-0 transition-transform duration-300">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-black bg-gradient-to-r from-gray-900 via-gray-800 to-blue-800 bg-clip-text text-transparent leading-tight">
                        Gestión de Usuarios
                    </h1>
                    <p class="text-gray-600 text-xs">Administra todos los usuarios del sistema</p>
                </div>
            </div>
            
            <div class="flex items-center space-x-2">
                <a href="{{ route('settings.manage-users.create') }}" wire:navigate
                   class="group relative inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-600 hover:from-blue-700 hover:to-blue-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-300 overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                    <svg class="w-4 h-4 mr-1 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    <span class="relative z-10 text-xs">Nuevo Usuario</span>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Barra de búsqueda -->
<div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 p-3 mb-3">
    <div class="flex flex-col sm:flex-row gap-2">
        <div class="flex-1 relative">
            <svg class="w-3.5 h-3.5 text-gray-400 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <input type="text"
                   wire:model.live.debounce.500ms="search"
                   placeholder="Buscar por nombre, documento, email, rol, teléfono o dirección..."
                   class="w-full pl-8 pr-3 py-1.5 text-xs bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>

        @if($search)
        <button wire:click="clearSearch"
                class="cursor-pointer inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-medium rounded-lg shadow hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200">
            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            <span class="text-xs">Limpiar</span>
        </button>
        @endif
    </div>
</div>

<!-- Tabla -->
@if($users->count() > 0)
    <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-xs">
                <thead>
                    <tr class="bg-black">
                        <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">#</th>
                        <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Usuario</th>
                        <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Documento</th>
                        <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Email</th>
                        <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Rol</th>
                        <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Estado</th>
                        <th class="px-2 py-1.5 text-left text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Teléfono</th>
                        <th class="px-2 py-1.5 text-center text-xs font-medium text-white uppercase tracking-wider whitespace-nowrap">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($users as $index => $user)
                    <tr class="hover:bg-gray-50/50 transition-colors duration-200">
                        <td class="px-2 py-1.5 whitespace-nowrap text-xs font-medium text-gray-900">
                            {{ ($users->currentPage() - 1) * $users->perPage() + $index + 1 }}
                        </td>
                        <td class="px-2 py-1.5 whitespace-nowrap">
                            <div class="flex items-center gap-1">
                                <div class="w-5 h-5 bg-blue-100 rounded flex items-center justify-center flex-shrink-0">
                                    <svg class="w-2.5 h-2.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-medium text-gray-900">{{ $user->nomUsu }} {{ $user->apeUsu }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-700">
                            <div class="flex items-center gap-1">
                                <svg class="w-2.5 h-2.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                                </svg>
                                <span class="truncate max-w-[100px]">{{ $user->tipDocUsu }} {{ $user->numDocUsu }}</span>
                            </div>
                        </td>
                        <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-700">
                            <div class="flex items-center gap-1">
                                <svg class="w-2.5 h-2.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                <span class="text-xs text-gray-700">{{ $user->email }}</span>
                            </div>
                        </td>
                        <td class="px-2 py-1.5 whitespace-nowrap">
                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-xs font-medium text-gray-600">
                                @if($user->rol->nomRol === 'Administrador')
                                    <svg class="w-2 h-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                                    </svg>
                                @elseif($user->rol->nomRol === 'Aprendiz')
                                    <svg class="w-2 h-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                @elseif($user->rol->nomRol === 'Superusuario')
                                    <svg class="w-2 h-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                    </svg>
                                @else
                                    <svg class="w-2 h-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                @endif
                                {{ $user->rol->nomRol ?? 'Sin rol' }}
                            </span>
                        </td>
                        <td class="px-2 py-1.5 whitespace-nowrap">
                            @if($this->isUserActive($user))
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700">
                                    <svg class="w-2 h-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    Activo
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-xs font-medium bg-gray-200 text-gray-600">
                                    <svg class="w-2 h-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Inactivo
                                </span>
                            @endif
                        </td>
                        <td class="px-2 py-1.5 whitespace-nowrap text-xs text-gray-700">
                            <div class="flex items-center gap-1">
                                <svg class="w-2.5 h-2.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                                <span class="truncate max-w-[80px]">{{ $user->contacto->celCon ?? 'N/A' }}</span>
                            </div>
                        </td>
                        <td class="px-2 py-1.5 whitespace-nowrap text-center">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('settings.manage-users.show', $user->id) }}" wire:navigate
                                   class="bg-blue-100 hover:bg-blue-200 text-blue-700 text-xs font-medium py-1 px-1.5 rounded transition duration-200">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>
                                <a href="{{ route('settings.manage-users.edit', $user->id) }}" wire:navigate
                                   class="bg-yellow-100 hover:bg-yellow-200 text-yellow-700 text-xs font-medium py-1 px-1.5 rounded transition duration-200">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </a>
                                <button wire:click="confirmDelete({{ $user->id }})"
                                        class="cursor-pointer bg-red-100 hover:bg-red-200 text-red-700 text-xs font-medium py-1 px-1.5 rounded transition duration-200">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        @if($users->hasPages())
            <div class="bg-white px-2 py-1.5 border-t border-gray-200 sm:px-3">
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
    </div>
@else
    <!-- Estado Vacío -->
    <div class="bg-white border border-gray-200 rounded-b-lg shadow-sm">
        <div class="text-center py-4 px-4">
            <div class="w-8 h-8 bg-gray-100 rounded-full mx-auto mb-2 flex items-center justify-center">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
            </div>
            <h3 class="text-sm font-medium text-gray-900 mb-1">No se encontraron usuarios</h3>
            <p class="text-xs text-gray-600 mb-2">
                @if($search)
                    No hay resultados para "{{ $search }}". Intenta con otros términos de búsqueda.
                @else
                    No hay usuarios registrados en el sistema.
                @endif
            </p>
            <a href="{{ route('settings.manage-users.create') }}" wire:navigate
               class="cursor-pointer inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-blue-600 to-blue-800 hover:from-blue-700 hover:to-blue-900 text-white font-medium rounded-lg shadow hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200">
                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                <span class="text-xs">Registrar usuario</span>
            </a>
        </div>
    </div>
@endif

<!-- Modal Confirmar Eliminación -->
@if($showDeleteModal)
<div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 z-50" wire:click.self="$set('showDeleteModal', false)">
    <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-5 transform transition-all duration-300">
        <div class="text-center">
            <div class="p-2.5 bg-gradient-to-br from-red-500 to-pink-600 rounded-full w-14 h-14 mx-auto mb-3 flex items-center justify-center">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
            </div>
            <h3 class="text-base font-bold text-gray-900 mb-1.5">Confirmar Eliminación</h3>
            <p class="text-xs text-gray-600 mb-3">¿Estás seguro de que deseas eliminar este usuario? Esta acción no se puede deshacer.</p>
            
            @php
                $userToDeleteData = $users->where('id', $userToDelete)->first();
            @endphp
            
            @if($userToDeleteData)
            <div class="bg-gray-50 rounded-lg p-2.5 mb-3 text-left">
                <div class="space-y-1 text-xs">
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-700">Usuario:</span>
                        <span class="text-gray-900">{{ $userToDeleteData->nomUsu }} {{ $userToDeleteData->apeUsu }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-700">Email:</span>
                        <span class="text-gray-900">{{ $userToDeleteData->email }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium text-gray-700">Estado:</span>
                        @if($this->isUserActive($userToDeleteData))
                            <span class="text-green-600 font-semibold">Activo</span>
                        @else
                            <span class="text-gray-600">Inactivo</span>
                        @endif
                    </div>
                </div>
            </div>
            @endif
            
            <div class="flex space-x-2">
                <button wire:click="$set('showDeleteModal', false)" 
                        class="flex-1 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg shadow hover:shadow transition-all duration-200 text-xs">
                    Cancelar
                </button>
                <button wire:click="deleteUser" 
                        class="flex-1 px-3 py-1.5 bg-gradient-to-r from-red-600 to-pink-600 hover:from-red-700 hover:to-pink-700 text-white font-medium rounded-lg shadow hover:shadow transform hover:-translate-y-0.5 transition-all duration-200 flex items-center justify-center text-xs">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Eliminar
                </button>
            </div>
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