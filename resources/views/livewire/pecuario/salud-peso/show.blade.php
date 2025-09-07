<?php
use App\Models\HistorialMedico;
use App\Models\Animal;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component {
    public HistorialMedico $historial;
    public $animal;

    public function mount(HistorialMedico $historial)
    {
        $this->historial = $historial;
        $this->animal = $historial->animal; // Usa la relación definida en el modelo
        
        // Depuración - verifica los datos
        \Log::debug("Cargando historial médico", [
            'historial_id' => $historial->idHisMed,
            'animal_id' => $historial->idAni,
            'animal_data' => $this->animal ? $this->animal->toArray() : null
        ]);
    }
}; ?>

@section('title', 'Detalles del Registro Médico #{{ $historial->idHisMed }}')

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-6xl mx-auto px-4">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-green-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-file-medical text-white text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Detalles del Registro Médico</h1>
                        <p class="text-gray-600">Información completa del registro #{{ $historial->idHisMed }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-600 text-white">
                        <div class="w-2 h-2 bg-white rounded-full mr-2"></div>
                        {{ \Carbon\Carbon::parse($historial->fecHisMed)->format('d/m/Y') }}
                    </span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Sección: Información del Animal -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-paw text-gray-600 text-sm"></i>
                        </div>
                        <h2 class="text-lg font-semibold text-gray-900">Información del Animal</h2>
                    </div>
                </div>
                
                <div class="p-6">
                    @if($historial->idAni)
                        @if($animal && $animal->exists)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">ID del Animal</label>
                                    <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-2 text-gray-900">
                                        {{ $historial->idAni }}
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                                    <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-2 text-gray-900">
                                        {{ $animal->ideAni ?? 'Sin nombre' }}
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Especie</label>
                                    <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-2 text-gray-900">
                                        {{ $animal->espAni }}
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Raza</label>
                                    <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-2 text-gray-900">
                                        {{ $animal->razAni ?? 'No especificada' }}
                                    </div>
                                </div>
                            </div>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Sexo</label>
                                    <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-2 text-gray-900">
                                        {{ $animal->sexAni }}
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Nacimiento</label>
                                    <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-2 text-gray-900">
                                        {{ $animal->fecNacAni ? \Carbon\Carbon::parse($animal->fecNacAni)->format('d/m/Y') : 'N/A' }}
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Peso Actual</label>
                                    <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-2 text-gray-900">
                                        {{ $animal->pesAni ? $animal->pesAni.' kg' : 'N/A' }}
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                                    <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-2 text-gray-900 capitalize">
                                        {{ $animal->estAni ?? 'N/A' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Salud y Reproducción -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Estado de Salud</label>
                                <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-2 text-gray-900 capitalize">
                                    @if($animal->estSaludAni)
                                        <span class="{{ $animal->estSaludAni == 'saludable' ? 'text-green-600' : ($animal->estSaludAni == 'enfermo' ? 'text-red-600' : 'text-yellow-600') }}">
                                            {{ $animal->estSaludAni }}
                                        </span>
                                    @else
                                        N/A
                                    @endif
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Estado Reproductivo</label>
                                <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-2 text-gray-900 capitalize">
                                    {{ $animal->estReproAni ? str_replace('_', ' ', $animal->estReproAni) : 'N/A' }}
                                </div>
                            </div>
                        </div>
                        
                        <!-- Ubicación -->
                        @if($animal->ubicacionAni)
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ubicación</label>
                            <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-2 text-gray-900">
                                {{ $animal->ubicacionAni }}
                            </div>
                        </div>
                        @endif
                        
                        <!-- Foto si existe -->
                        @if($animal->fotoAni)
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Foto del Animal</label>
                            <img src="{{ asset('storage/' . $animal->fotoAni) }}" alt="Foto del animal" 
                                 class="w-32 h-32 object-cover rounded-lg border border-gray-200">
                        </div>
                        @endif
                        @else
                        <div class="text-center py-8">
                            <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-exclamation-triangle text-yellow-600 text-2xl"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Animal no encontrado</h3>
                            <p class="text-gray-500">El animal con ID {{ $historial->idAni }} no existe en la base de datos</p>
                        </div>
                        @endif
                    @else
                    <div class="text-center py-8">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-question-circle text-gray-400 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Animal no asociado</h3>
                        <p class="text-gray-500">No se ha asociado un animal a este registro</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Sección: Detalles del Registro -->
            <div class="space-y-8">
                <!-- Información del Registro -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-clipboard-list text-gray-600 text-sm"></i>
                            </div>
                            <h2 class="text-lg font-semibold text-gray-900">Detalles del Registro</h2>
                        </div>
                    </div>
                    
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Registro</label>
                            <div class="flex items-center gap-2">
                                @if($historial->tipHisMed == 'vacuna')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-syringe mr-1"></i>Vacuna
                                    </span>
                                @elseif($historial->tipHisMed == 'tratamiento')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <i class="fas fa-pills mr-1"></i>Tratamiento
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        <i class="fas fa-weight mr-1"></i>Control
                                    </span>
                                @endif
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha del Procedimiento</label>
                            <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-2 text-gray-900">
                                {{ \Carbon\Carbon::parse($historial->fecHisMed)->format('d/m/Y') }}
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Responsable</label>
                            <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-2 text-gray-900">
                                {{ $historial->responHisMed }}
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Estado del Registro</label>
                            <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-2 text-gray-900 capitalize">
                                {{ $historial->estRecHisMed ?? 'N/A' }}
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Registrado el</label>
                            <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-2 text-gray-900">
                                {{ $historial->created_at->format('d/m/Y H:i') }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Descripción Completa -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-align-left text-gray-600 text-sm"></i>
                            </div>
                            <h2 class="text-lg font-semibold text-gray-900">Descripción</h2>
                        </div>
                    </div>
                    
                    <div class="p-6">
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <p class="text-gray-700 whitespace-pre-line">{{ $historial->desHisMed }}</p>
                        </div>
                    </div>
                </div>

                <!-- Tratamiento (si existe) -->
                @if($historial->traHisMed)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-prescription-bottle-alt text-blue-600 text-sm"></i>
                            </div>
                            <h2 class="text-lg font-semibold text-gray-900">Tratamiento</h2>
                        </div>
                    </div>
                    
                    <div class="p-6">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <p class="text-blue-800 whitespace-pre-line">{{ $historial->traHisMed }}</p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Observaciones -->
                @if($historial->obsHisMed)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-exclamation-circle text-yellow-600 text-sm"></i>
                            </div>
                            <h2 class="text-lg font-semibold text-gray-900">Observaciones</h2>
                        </div>
                    </div>
                    
                    <div class="p-6">
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <p class="text-yellow-800 whitespace-pre-line">{{ $historial->obsHisMed }}</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <a href="{{ route('pecuario.salud-peso.index') }}" wire:navigate
                   class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 border-2 border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 hover:border-gray-400 transition-all duration-200">
                    <i class="fas fa-arrow-left text-sm"></i>
                    Volver al Listado
                </a>
                
                <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                    <a href="{{ route('pecuario.salud-peso.edit', $historial) }}" wire:navigate
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 focus:ring-4 focus:ring-blue-200 transition-all duration-200">
                       <i class="fas fa-edit text-sm"></i>
                       Editar Registro
                    </a>
                    
                    <button wire:click="$dispatch('confirm-delete', { id: {{ $historial->idHisMed }} })"
                            wire:confirm="¿Está seguro que desea eliminar este registro médico?"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 focus:ring-4 focus:ring-red-200 transition-all duration-200">
                            <i class="fas fa-trash-alt text-sm"></i>
                            Eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>