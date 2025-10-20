<?php
use App\Models\Animal;
use App\Models\Proveedor;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component {
    public Animal $animal;
    public $showDeleteModal = false;
    public $proveedorInfo = null;

    public function mount(Animal $animal)
    {
        $this->animal = $animal;
        $this->cargarInformacionProveedor();
    }

    private function cargarInformacionProveedor(): void
    {
        if ($this->animal->proAni) {
            // Buscar el proveedor por nombre en el campo proAni
            $this->proveedorInfo = Proveedor::where('nomProve', $this->animal->proAni)->first();
        }
    }

    public function confirmDelete(): void
    {
        $this->showDeleteModal = true;
    }

    public function deleteAnimal(): void
    {
        try {
            $this->animal->delete();
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Animal eliminado correctamente'
            ]);

            $this->redirect(route('pecuario.animales.index'), navigate: true);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al eliminar el animal: ' . $e->getMessage()
            ]);
        }
    }
}; ?>

@section('title', 'Detalles del Animal')

<div class="flex items-center justify-center min-h-screen py-3">
    <div class="w-full max-w-7xl mx-auto bg-white/80 backdrop-blur-xl shadow rounded-3xl p-3 relative border border-white/20">
        <!-- Encabezado -->
        <div class="text-center mb-3">
            <div class="flex justify-center mb-1">
                <div class="p-2 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl shadow-xl transform rotate-3 hover:rotate-0 transition-transform duration-300">
                    <div class="w-4 h-4 text-white flex items-center justify-center">
                        <i class="fas fa-paw text-base"></i>
                    </div>
                </div>
            </div>
            <h1 class="text-base font-black bg-gradient-to-r from-gray-900 via-gray-800 to-green-800 bg-clip-text text-transparent mb-1">
                Detalles del Animal
            </h1>
            <p class="text-gray-600 text-xs">Información completa del animal</p>
        </div>

        <!-- Botones -->
        <div class="absolute top-2 right-2 flex space-x-2">
            <a href="{{ route('pecuario.animales.edit', $animal->idAni) }}" wire:navigate
               class="group relative inline-flex items-center px-2 py-1 bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                <svg class="w-3 h-3 mr-1 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                <span class="relative z-10 text-xs">Editar</span>
            </a>
            <a href="{{ route('pecuario.animales.index') }}" wire:navigate
               class="group relative inline-flex items-center px-2 py-1 bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                <svg class="w-3 h-3 mr-1 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span class="relative z-10 text-xs">Volver</span>
            </a>
        </div>

        <!-- Tarjeta de información principal -->
        <div class="border border-gray-300 rounded-3xl overflow-hidden bg-gradient-to-br from-white to-gray-50">
            <div class="h-1.5 bg-gradient-to-r from-[#000000] to-[#39A900]"></div>
            
            <div class="p-2">
                <!-- Grid de información -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-2">
                    <!-- Información -->
                    <div class="bg-white rounded-2xl p-2 border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-300">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl shadow-md">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xs font-bold text-gray-900">Información</h3>
                        </div>
                        <div class="bg-blue-50/50 border border-blue-200 rounded-xl p-2">
                            <div class="space-y-2">
                                <div class="flex items-center justify-between py-1 border-b border-blue-100">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <span class="text-xs text-black font-medium">NIT del Animal</span>
                                    </div>
                                    <p class="text-xs text-black font-semibold">{{ $animal->nitAni ?? 'No registrado' }}</p>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-blue-100">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <span class="text-xs text-black font-medium">Especie</span>
                                    </div>
                                    <p class="text-xs text-black font-semibold">{{ $animal->espAni ?? 'No registrado' }}</p>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-blue-100">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4c2.21 0 4 1.79 4 4s-1.79 4-4 4-4-1.79-4-4 1.79-4 4-4zm0 12c-3.31 0-6 2.69-6 6h12c0-3.31-2.69-6-6-6z"></path>
                                        </svg>
                                        <span class="text-xs text-black font-medium">Raza</span>
                                    </div>
                                    <p class="text-xs text-black font-semibold">{{ $animal->razAni ?? 'No especificada' }}</p>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-blue-100">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                        </svg>
                                        <span class="text-xs text-black font-medium">Sexo</span>
                                    </div>
                                    <p class="text-xs text-black font-semibold">
                                        @if($animal->sexAni === 'Hembra')
                                            <span class="inline-flex items-center text-xs font-semibold px-1.5 py-0.5 rounded-full bg-gradient-to-r from-pink-500 to-pink-600 text-white shadow-sm">
                                                Hembra
                                            </span>
                                        @else
                                            <span class="inline-flex items-center text-xs font-semibold px-1.5 py-0.5 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 text-white shadow-sm">
                                                Macho
                                            </span>
                                        @endif
                                    </p>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-blue-100">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-4m6 2l3.975 1.425M21 6l-3 9a5.002 5.002 0 01-6.001 0m3-9l-3 9"></path>
                                        </svg>
                                        <span class="text-xs text-black font-medium">Peso</span>
                                    </div>
                                    <p class="text-xs text-black font-semibold">{{ $animal->pesAni ? $animal->pesAni.' kg' : 'No registrado' }}</p>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-blue-100">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <span class="text-xs text-black font-medium">Fecha de Nacimiento</span>
                                    </div>
                                    <p class="text-xs text-black font-semibold">
                                        @if($animal->fecNacAni)
                                            {{ \Carbon\Carbon::parse($animal->fecNacAni)->format('d/m/Y') }}
                                            <span class="text-xs text-black">({{ \Carbon\Carbon::parse($animal->fecNacAni)->age }} años)</span>
                                        @else
                                            No registrada
                                        @endif
                                    </p>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-blue-100">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        <span class="text-xs text-black font-medium">Ubicación</span>
                                    </div>
                                    <p class="text-xs text-black font-semibold">{{ $animal->ubicacionAni ?? 'No registrada' }}</p>
                                </div>
                                @if($animal->fotoAni)
                                <div class="flex items-center justify-between py-1">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <span class="text-xs text-black font-medium">Foto</span>
                                    </div>
                                    <img src="{{ asset('storage/' . $animal->fotoAni) }}" alt="Foto del animal" 
                                         class="w-12 h-12 object-cover rounded-2xl border border-blue-200 shadow-sm">
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

<!-- Proveedor -->
<div class="bg-white rounded-2xl p-2 border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-300">
    <div class="flex items-center justify-between mb-2">
        <div class="flex items-center space-x-2">
            <div class="p-1.5 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-md">
                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                </svg>
            </div>
            <h3 class="text-xs font-bold text-gray-900">Proveedor</h3>
        </div>
        @if($proveedorInfo)
            <a href="{{ route('proveedores.show', $proveedorInfo->idProve) }}" wire:navigate
               class="bg-purple-100 hover:bg-purple-200 text-purple-700 text-xs font-medium py-1 px-1.5 rounded transition duration-200 flex items-center space-x-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
                <span>Ver Proveedor</span>
            </a>
        @endif
    </div>
    <div class="bg-purple-50/50 border border-purple-200 rounded-xl p-2">
        <div class="space-y-2">
            @if($proveedorInfo)
                <div class="flex items-center justify-between py-1 border-b border-purple-100">
                    <div class="flex items-center space-x-2">
                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span class="text-xs text-black font-medium">NIT del proveedor</span>
                    </div>
                    <p class="text-xs text-black font-semibold">{{ $proveedorInfo->nitProve ?? 'No especificado' }}</p>
                </div>
                <div class="flex items-center justify-between py-1 border-b border-purple-100">
                    <div class="flex items-center space-x-2">
                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4c2.21 0 4 1.79 4 4s-1.79 4-4 4-4-1.79-4-4 1.79-4 4-4zm0 12c-3.31 0-6 2.69-6 6h12c0-3.31-2.69-6-6-6z"></path>
                        </svg>
                        <span class="text-xs text-black font-medium">Nombre</span>
                    </div>
                    <p class="text-xs text-black font-semibold">{{ $proveedorInfo->nomProve ?? 'No especificado' }} {{ $proveedorInfo->apeProve ?? 'No especificado' }}</p>
                </div>
                <div class="flex items-center justify-between py-1 border-b border-purple-100">
                    <div class="flex items-center space-x-2">
                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span class="text-xs text-black font-medium">Tipo de suministro</span>
                    </div>
                    <p class="text-xs text-black font-semibold">{{ $proveedorInfo->tipSumProve ?? 'No especificado' }}</p>
                </div>
                @if($proveedorInfo->conProve)
                <div class="flex items-center justify-between py-1 border-b border-purple-100">
                    <div class="flex items-center space-x-2">
                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                        <span class="text-xs text-black font-medium">Contacto</span>
                    </div>
                    <p class="text-xs text-black font-semibold">{{ $proveedorInfo->conProve }}</p>
                </div>
                @endif
                @if($proveedorInfo->telProve)
                <div class="flex items-center justify-between py-1 border-b border-purple-100">
                    <div class="flex items-center space-x-2">
                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                        <span class="text-xs text-black font-medium">Teléfono</span>
                    </div>
                    <p class="text-xs text-black font-semibold">{{ $proveedorInfo->telProve }}</p>
                </div>
                @endif
                @if($proveedorInfo->dirProve)
                <div class="flex items-center justify-between py-1 border-b border-purple-100">
                    <div class="flex items-center space-x-2">
                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span class="text-xs text-black font-medium">Dirección</span>
                    </div>
                    <p class="text-xs text-black font-semibold">{{ $proveedorInfo->dirProve }}</p>
                </div>
                @endif
                @if($proveedorInfo->emaProve)
                <div class="flex items-center justify-between py-1 border-b border-purple-100">
                    <div class="flex items-center space-x-2">
                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <span class="text-xs text-black font-medium">Email</span>
                    </div>
                    <p class="text-xs text-black font-semibold">{{ $proveedorInfo->emaProve }}</p>
                </div>
                @endif
            @else
                <div class="flex items-center justify-between py-1">
                    <div class="flex items-center space-x-2">
                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <span class="text-xs text-black font-medium">Información del proveedor</span>
                    </div>
                    <p class="text-xs text-black font-semibold italic">No disponible</p>
                </div>
            @endif
            <div class="flex items-center justify-between py-1 border-t border-purple-100 mt-2 pt-2">
                <div class="flex items-center space-x-2">
                    <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span class="text-xs text-black font-medium">Fecha de Compra/Ingreso</span>
                </div>
                <p class="text-xs text-black font-semibold">
                    @if($animal->fecComAni)
                        {{ \Carbon\Carbon::parse($animal->fecComAni)->format('d/m/Y') }}
                    @else
                        No registrada
                    @endif
                </p>
            </div>
        </div>
    </div>
</div>

                    <!-- Estados -->
                    <div class="bg-white rounded-2xl p-2 border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-300">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl shadow-md">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                                </svg>
                            </div>
                            <h3 class="text-xs font-bold text-gray-900">Estados</h3>
                        </div>
                        <div class="bg-green-50/50 border border-green-200 rounded-xl p-2">
                            <div class="space-y-2">
                                <div class="flex items-center justify-between py-1 border-b border-green-100">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                        </svg>
                                        <p class="text-xs text-black font-medium">Estado General</p>
                                    </div>
                                    <p class="text-xs text-green-900 font-semibold">
                                        @switch($animal->estAni)
                                            @case('vivo')
                                                <span class="inline-flex items-center text-xs font-semibold px-1.5 py-0.5 rounded-full bg-gradient-to-r from-green-500 to-emerald-600 text-white shadow-sm">
                                                    <svg class="w-2.5 h-2.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                                    </svg>
                                                    Vivo
                                                </span>
                                                @break
                                            @case('muerto')
                                                <span class="inline-flex items-center text-xs font-semibold px-1.5 py-0.5 rounded-full bg-gradient-to-r from-red-500 to-red-600 text-white shadow-sm">
                                                    <svg class="w-2.5 h-2.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                    Muerto
                                                </span>
                                                @break
                                            @default
                                                <span class="inline-flex items-center text-xs font-semibold px-1.5 py-0.5 rounded-full bg-gradient-to-r from-yellow-500 to-yellow-600 text-white shadow-sm">
                                                    <svg class="w-2.5 h-2.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                                    </svg>
                                                    Vendido
                                                </span>
                                        @endswitch
                                    </p>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-green-100">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                        </svg>
                                        <span class="text-xs text-black font-medium">Estado de Salud</span>
                                    </div>
                                    <p class="text-xs text-green-900 font-semibold">
                                        @switch($animal->estSaludAni)
                                            @case('saludable')
                                                <span class="inline-flex items-center text-xs font-semibold px-1.5 py-0.5 rounded-full bg-gradient-to-r from-green-500 to-emerald-600 text-white shadow-sm">
                                                    Saludable
                                                </span>
                                                @break
                                            @case('enfermo')
                                                <span class="inline-flex items-center text-xs font-semibold px-1.5 py-0.5 rounded-full bg-gradient-to-r from-red-500 to-red-600 text-white shadow-sm">
                                                    Enfermo
                                                </span>
                                                @break
                                            @default
                                                <span class="inline-flex items-center text-xs font-semibold px-1.5 py-0.5 rounded-full bg-gradient-to-r from-yellow-500 to-yellow-600 text-white shadow-sm">
                                                    En tratamiento
                                                </span>
                                        @endswitch
                                    </p>
                                </div>
                                <div class="flex items-center justify-between py-1">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                        </svg>
                                        <span class="text-xs text-black font-medium">Estado Reproductivo</span>
                                    </div>
                                    <p class="text-xs text-green-900 font-semibold">
                                        @php
                                            $estadosRepro = [
                                                'no_aplica' => ['label' => 'No aplica', 'color' => 'gray', 'icon' => 'M18.364 5.636l-1.414-1.414M5.636 18.364l1.414 1.414M5.636 5.636l1.414 1.414M18.364 18.364l-1.414-1.414'],
                                                'ciclo' => ['label' => 'En ciclo', 'color' => 'blue', 'icon' => 'M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 01-8 8h4l-3 3 3 3h-4a8 8 0 01-8-8z'],
                                                'cubierta' => ['label' => 'Cubierta', 'color' => 'purple', 'icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z'],
                                                'gestacion' => ['label' => 'Gestación', 'color' => 'pink', 'icon' => 'M21 12a9 9 0 01-9 9c-.663 0-1.315-.071-1.95-.21C7.987 18.837 6 15.85 6 12c0-3.85 1.987-6.837 4.05-8.79.635-.14 1.287-.21 1.95-.21a9 9 0 019 9z'],
                                                'parida' => ['label' => 'Parida', 'color' => 'green', 'icon' => 'M9 9l3-3m0 0l3 3m-3-3v12m-6-9H3m18 0h-6']
                                            ];
                                            $estadoActual = $estadosRepro[$animal->estReproAni] ?? $estadosRepro['no_aplica'];
                                        @endphp
                                        <span class="inline-flex items-center text-xs font-semibold px-1.5 py-0.5 rounded-full bg-gradient-to-r from-{{ $estadoActual['color'] }}-500 to-{{ $estadoActual['color'] }}-600 text-white shadow-sm">
                                            {{ $estadoActual['label'] }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información Adicional -->
                    <div class="bg-white rounded-2xl p-2 border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-300">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl shadow-md">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xs font-bold text-gray-900">Información Adicional</h3>
                        </div>
                        <div class="bg-yellow-50/50 border border-yellow-200 rounded-xl p-2">
                            @if($animal->obsAni)
                                <p class="text-xs text-black whitespace-pre-line">{{ $animal->obsAni }}</p>
                            @else
                                <p class="text-xs text-black italic">No hay observaciones disponibles</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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