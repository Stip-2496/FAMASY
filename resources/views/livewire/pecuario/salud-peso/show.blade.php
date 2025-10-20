<?php
use App\Models\HistorialMedico;
use App\Models\Animal;
use App\Models\Proveedor;
use App\Models\Insumo;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.auth')] class extends Component {
    use WithPagination;

    public HistorialMedico $historial;
    public Animal $animal;
    public $proveedor;
    public $insumo;

    public function mount(HistorialMedico $historial)
    {
        $this->historial = $historial;
        $this->animal = $historial->animal ?? new Animal();
        
        // Cargar proveedor e insumo relacionados
        if ($historial->idProve) {
            $this->proveedor = Proveedor::find($historial->idProve);
        }
        
        if ($historial->idIns) {
            $this->insumo = Insumo::find($historial->idIns);
        }
        
        \Log::debug("Cargando historial médico", [
            'historial_id' => $historial->idHisMed,
            'animal_id' => $historial->idAni,
            'animal_data' => $this->animal->exists ? $this->animal->toArray() : null,
            'proveedor_id' => $historial->idProve,
            'insumo_id' => $historial->idIns
        ]);
    }

    public function with(): array
    {
        return [
            'historial_relacionado' => HistorialMedico::where('idAni', $this->historial->idAni)
                ->where('idHisMed', '!=', $this->historial->idHisMed)
                ->orderBy('fecHisMed', 'desc')
                ->paginate(3, ['*'], 'historialPage')
        ];
    }

    public function getEstadoRegistroColor($estado)
    {
        $colores = [
            'activo' => 'bg-gradient-to-r from-green-500 to-emerald-600 text-white',
            'inactivo' => 'bg-gradient-to-r from-red-500 to-red-600 text-white',
            'pendiente' => 'bg-gradient-to-r from-yellow-500 to-yellow-600 text-white',
        ];
        return $colores[$estado] ?? 'bg-gradient-to-r from-gray-500 to-gray-600 text-white';
    }

    public function getTipoRegistroFormateado($tipo)
    {
        $tipos = [
            'vacuna' => 'Vacuna',
            'tratamiento' => 'Tratamiento',
            'control' => 'Control',
        ];
        return $tipos[$tipo] ?? ucfirst($tipo);
    }

    public function getTipoRegistroColor($tipo)
    {
        $colores = [
            'vacuna' => 'bg-gradient-to-r from-green-500 to-emerald-600 text-white',
            'tratamiento' => 'bg-gradient-to-r from-blue-500 to-indigo-600 text-white',
            'control' => 'bg-gradient-to-r from-purple-500 to-purple-600 text-white',
        ];
        return $colores[$tipo] ?? 'bg-gradient-to-r from-gray-500 to-gray-600 text-white';
    }

    public function paginationView()
    {
        return 'vendor.livewire.simple-tailwind';
    }
}; ?>

@section('title', 'Detalles del Registro Médico')

<div class="flex items-center justify-center min-h-screen py-3">
    <div class="w-full max-w-7xl mx-auto bg-white/80 backdrop-blur-xl shadow rounded-3xl p-3 relative border border-white/20">
        <!-- Encabezado -->
        <div class="text-center mb-3">
            <div class="flex justify-center mb-1">
                <div class="p-2 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl shadow-xl transform rotate-3 hover:rotate-0 transition-transform duration-300">
                    <div class="w-4 h-4 text-white flex items-center justify-center">
                        <i class="fas fa-heartbeat text-base"></i>
                    </div>
                </div>
            </div>
            <h1 class="text-base font-black bg-gradient-to-r from-gray-900 via-gray-800 to-green-800 bg-clip-text text-transparent mb-1">
                Detalles del Registro Médico
            </h1>
            <p class="text-gray-600 text-xs">Información completa del registro</p>
        </div>

        <!-- Botones -->
        <div class="absolute top-2 right-2 flex space-x-2">
            <a href="{{ route('pecuario.salud-peso.edit', $historial->idHisMed) }}" wire:navigate class="group relative inline-flex items-center px-2 py-1 bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                <svg class="w-3 h-3 mr-1 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                <span class="relative z-10 text-xs">Editar</span>
            </a>
            <a href="{{ route('pecuario.salud-peso.index') }}" wire:navigate
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
            
            <div class="p-4">
                <!-- Grid de información principal -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
                    <!-- Columna Izquierda -->
                    <div class="space-y-4">
                        <!-- Información del Animal -->
                        <div class="bg-white rounded-2xl p-4 border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-300">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center space-x-2">
                                    <div class="p-1.5 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl shadow-md">
                                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4c2.21 0 4 1.79 4 4s-1.79 4-4 4-4-1.79-4-4 1.79-4 4-4zm0 12c-3.31 0-6 2.69-6 6h12c0-3.31-2.69-6-6-6z"></path>
                                        </svg>
                                    </div>
                                    <h3 class="text-sm font-bold text-gray-900">Información del Animal</h3>
                                </div>
                                @if($historial->animal)
                                    <a href="{{ route('pecuario.animales.show', $historial->animal->idAni) }}" wire:navigate
                                       class="group relative inline-flex items-center px-2 py-1 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                                        <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                                        <svg class="w-3 h-3 mr-1 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        <span class="relative z-10 text-xs">Ver Animal</span>
                                    </a>
                                @endif
                            </div>
                            @if($animal && $animal->exists)
                                <div class="bg-blue-50/50 border border-blue-200 rounded-xl p-3">
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between py-1 border-b border-blue-100">
                                            <div class="flex items-center space-x-2">
                                                <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                                <span class="text-xs font-medium text-black">NIT del Animal</span>
                                            </div>
                                            <p class="text-xs font-semibold text-black">{{ $animal->nitAni ?? 'No registrado' }}</p>
                                        </div>
                                        <div class="flex items-center justify-between py-1 border-b border-blue-100">
                                            <div class="flex items-center space-x-2">
                                                <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                                <span class="text-xs font-medium text-black">Especie</span>
                                            </div>
                                            <p class="text-xs font-semibold text-black">{{ $animal->espAni ?? 'No registrado' }}</p>
                                        </div>
                                        <div class="flex items-center justify-between py-1 border-b border-blue-100">
                                            <div class="flex items-center space-x-2">
                                                <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4c2.21 0 4 1.79 4 4s-1.79 4-4 4-4-1.79-4-4 1.79-4 4-4zm0 12c-3.31 0-6 2.69-6 6h12c0-3.31-2.69-6-6-6z"></path>
                                                </svg>
                                                <span class="text-xs font-medium text-black">Raza</span>
                                            </div>
                                            <p class="text-xs font-semibold text-black">{{ $animal->razAni ?? 'No especificada' }}</p>
                                        </div>
                                        @if($animal->ubicacionAni)
                                            <div class="flex items-center justify-between py-1 border-b border-blue-100">
                                                <div class="flex items-center space-x-2">
                                                    <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    </svg>
                                                    <span class="text-xs font-medium text-black">Ubicación</span>
                                                </div>
                                                <p class="text-xs font-semibold text-black">{{ $animal->ubicacionAni }}</p>
                                            </div>
                                        @endif
                                        @if($animal->fecNacAni)
                                            <div class="flex items-center justify-between py-1 border-b border-blue-100">
                                                <div class="flex items-center space-x-2">
                                                    <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                    </svg>
                                                    <span class="text-xs font-medium text-black">Fecha de Nacimiento</span>
                                                </div>
                                                <p class="text-xs font-semibold text-black">
                                                    {{ \Carbon\Carbon::parse($animal->fecNacAni)->format('d/m/Y') }}
                                                    <span class="text-xs text-black">({{ \Carbon\Carbon::parse($animal->fecNacAni)->age }} años)</span>
                                                </p>
                                            </div>
                                        @endif
                                        @if($animal->pesAni)
                                            <div class="flex items-center justify-between py-1 border-b border-blue-100">
                                                <div class="flex items-center space-x-2">
                                                    <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-4m6 2l3.975 1.425M21 6l-3 9a5.002 5.002 0 01-6.001 0m3-9l-3 9"></path>
                                                    </svg>
                                                    <span class="text-xs font-medium text-black">Peso</span>
                                                </div>
                                                <p class="text-xs font-semibold text-black">{{ $animal->pesAni }} kg</p>
                                            </div>
                                        @endif
                                        <div class="flex items-center justify-between py-1 border-b border-blue-100">
                                            <div class="flex items-center space-x-2">
                                                <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                                </svg>
                                                <span class="text-xs font-medium text-black">Sexo</span>
                                            </div>
                                            <p class="text-xs font-semibold text-black">
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
                                    </div>
                                </div>
                            @else
                                <div class="text-center py-3">
                                    <p class="text-xs text-gray-500">Animal no encontrado</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Columna Derecha -->
                    <div class="space-y-4">
                        <!-- Información Adicional -->
                        <div class="bg-white rounded-2xl p-4 border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-300">
                            <div class="flex items-center space-x-2 mb-3">
                                <div class="p-1.5 bg-gradient-to-br from-gray-500 to-gray-600 rounded-xl shadow-md">
                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-sm font-bold text-gray-900">Información Adicional</h3>
                            </div>
                            <div class="bg-gray-50/50 border border-gray-200 rounded-xl p-3">
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between py-1 border-b border-gray-100">
                                        <div class="flex items-center space-x-2">
                                            <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            <span class="text-xs font-medium text-black">Estado del Registro</span>
                                        </div>
                                        <p class="text-xs font-semibold text-black">
                                            <span class="inline-flex items-center text-xs font-semibold px-1.5 py-0.5 rounded-full {{ $this->getEstadoRegistroColor($historial->estRecHisMed) }} shadow-sm">
                                                {{ ucfirst($historial->estRecHisMed ?? 'N/A') }}
                                            </span>
                                        </p>
                                    </div>
                                    <div class="flex items-center justify-between py-1 border-b border-gray-100">
                                        <div class="flex items-center space-x-2">
                                            <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            <span class="text-xs font-medium text-black">Dosis</span>
                                        </div>
                                        <p class="text-xs font-semibold text-black">{{ $historial->dosHisMed ?? 'N/A' }}</p>
                                    </div>
                                    <div class="flex items-center justify-between py-1 border-b border-gray-100">
                                        <div class="flex items-center space-x-2">
                                            <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <span class="text-xs font-medium text-black">Duración</span>
                                        </div>
                                        <p class="text-xs font-semibold text-black">{{ $historial->durHisMed ?? 'N/A' }}</p>
                                    </div>
                                    <div class="flex items-center justify-between py-1 border-b border-gray-100">
                                        <div class="flex items-center space-x-2">
                                            <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            <span class="text-xs font-medium text-black">Resultado</span>
                                        </div>
                                        <p class="text-xs font-semibold text-black">{{ $historial->resHisMed ?? 'N/A' }}</p>
                                    </div>
                                    <div class="flex items-center justify-between py-1">
                                        <div class="flex items-center space-x-2">
                                            <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            <span class="text-xs font-medium text-black">Registrado el</span>
                                        </div>
                                        <p class="text-xs font-semibold text-black">{{ $historial->created_at->format('d/m/Y H:i') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sección de Registro Médico y Proveedor en la misma fila -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
                    <!-- Información del Registro Médico -->
                    <div class="bg-white rounded-2xl p-4 border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-300">
                        <div class="flex items-center space-x-2 mb-3">
                            <div class="p-1.5 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl shadow-md">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <h3 class="text-sm font-bold text-gray-900">Información del Registro Médico</h3>
                        </div>
                        <div class="bg-green-50/50 border border-green-200 rounded-xl p-3">
                            <div class="space-y-2">
                                <div class="flex items-center justify-between py-1 border-b border-green-100">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <span class="text-xs font-medium text-black">Tipo de Registro</span>
                                    </div>
                                    <p class="text-xs font-semibold text-black">
                                        <span class="inline-flex items-center text-xs font-semibold px-1.5 py-0.5 rounded-full {{ $this->getTipoRegistroColor($historial->tipHisMed) }} shadow-sm">
                                            {{ $this->getTipoRegistroFormateado($historial->tipHisMed) }}
                                        </span>
                                    </p>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-green-100">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <span class="text-xs font-medium text-black">Fecha del Procedimiento</span>
                                    </div>
                                    <p class="text-xs font-semibold text-black">{{ \Carbon\Carbon::parse($historial->fecHisMed)->format('d/m/Y') }}</p>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-green-100">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                        </svg>
                                        <span class="text-xs font-medium text-black">Responsable</span>
                                    </div>
                                    <p class="text-xs font-semibold text-black">{{ $historial->responHisMed }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información del Proveedor -->
                    <div class="bg-white rounded-2xl p-4 border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-300">
                        <div class="flex items-center space-x-2 mb-3">
                            <div class="p-1.5 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-md">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                            </div>
                            <h3 class="text-sm font-bold text-gray-900">Información del Proveedor</h3>
                        </div>
                        <div class="bg-purple-50/50 border border-purple-200 rounded-xl p-3">
                            <div class="space-y-2">
                                <div class="flex items-center justify-between py-1 border-b border-purple-100">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                        </svg>
                                        <span class="text-xs font-medium text-black">Procedencia/Proveedor</span>
                                    </div>
                                    <p class="text-xs font-semibold text-black">
                                        @if($proveedor)
                                            {{ $proveedor->nomProve }} {{ $proveedor->apeProve }}
                                            @if($proveedor->tipSumProve)
                                                <span class="text-gray-500">({{ $proveedor->tipSumProve }})</span>
                                            @endif
                                        @else
                                            {{ $historial->proveeHisMed ?? 'No especificado' }}
                                        @endif
                                    </p>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-purple-100">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span class="text-xs font-medium text-black">Medicamento/Insumo</span>
                                    </div>
                                    <p class="text-xs font-semibold text-black">
                                        @if($insumo)
                                            {{ $insumo->nomIns }}
                                            @if($insumo->marIns)
                                                - {{ $insumo->marIns }}
                                            @endif
                                            @if($insumo->tipIns)
                                                <span class="text-gray-500">({{ $insumo->tipIns }})</span>
                                            @endif
                                        @else
                                            {{ $historial->medicamentoHisMed ?? 'No especificado' }}
                                        @endif
                                    </p>
                                </div>
                                @if($proveedor && $proveedor->conProve)
                                <div class="flex items-center justify-between py-1 border-b border-purple-100">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                        </svg>
                                        <span class="text-xs font-medium text-black">Contacto</span>
                                    </div>
                                    <p class="text-xs font-semibold text-black">{{ $proveedor->conProve }}</p>
                                </div>
                                @endif
                                @if($proveedor && $proveedor->telProve)
                                <div class="flex items-center justify-between py-1">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                        </svg>
                                        <span class="text-xs font-medium text-black">Teléfono</span>
                                    </div>
                                    <p class="text-xs font-semibold text-black">{{ $proveedor->telProve }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información Médica - En la parte inferior -->
                <div class="bg-white rounded-2xl p-4 border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-300">
                    <div class="flex items-center space-x-2 mb-3">
                        <div class="p-1.5 bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl shadow-md">
                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-gray-900">Información Médica</h3>
                    </div>
                    
                    <!-- Tres columnas para los campos -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                        <!-- Columna 1: Padecimiento -->
                        <div>
                            <label class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                Padecimiento
                            </label>
                            @if($historial->desHisMed)
                                <div class="relative group">
                                    <div class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg text-xs min-h-[120px]">
                                        <p class="text-xs text-black whitespace-pre-line">{{ $historial->desHisMed }}</p>
                                    </div>
                                    <div class="absolute inset-0 bg-gradient-to-r from-yellow-500/5 to-orange-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                            @else
                                <div class="w-full px-1.5 py-1 bg-gray-50 border-2 border-gray-200 rounded-2xl shadow-lg text-xs min-h-[120px] flex items-center justify-center">
                                    <p class="text-xs text-gray-400">No especificado</p>
                                </div>
                            @endif
                        </div>

                        <!-- Columna 2: Tratamiento -->
                        <div>
                            <label class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                Tratamiento
                            </label>
                            @if($historial->traHisMed)
                                <div class="relative group">
                                    <div class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg text-xs min-h-[120px]">
                                        <p class="text-xs text-black whitespace-pre-line">{{ $historial->traHisMed }}</p>
                                    </div>
                                    <div class="absolute inset-0 bg-gradient-to-r from-yellow-500/5 to-orange-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                            @else
                                <div class="w-full px-1.5 py-1 bg-gray-50 border-2 border-gray-200 rounded-2xl shadow-lg text-xs min-h-[120px] flex items-center justify-center">
                                    <p class="text-xs text-gray-400">No especificado</p>
                                </div>
                            @endif
                        </div>

                        <!-- Columna 3: Observaciones -->
                        <div>
                            <label class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                Observaciones
                            </label>
                            @if($historial->obsHisMed)
                                <div class="relative group">
                                    <div class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg text-xs min-h-[120px]">
                                        <p class="text-xs text-black whitespace-pre-line">{{ $historial->obsHisMed }}</p>
                                    </div>
                                    <div class="absolute inset-0 bg-gradient-to-r from-yellow-500/5 to-orange-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                            @else
                                <div class="w-full px-1.5 py-1 bg-gray-50 border-2 border-gray-200 rounded-2xl shadow-lg text-xs min-h-[120px] flex items-center justify-center">
                                    <p class="text-xs text-gray-400">No especificado</p>
                                </div>
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