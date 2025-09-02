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

<div class="container mx-auto px-4 py-6">
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <!-- Encabezado -->
        <div class="bg-green-700 text-white px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <i class="fas fa-file-medical text-xl"></i>
                <h2 class="text-xl font-bold">Detalles del Registro Médico #{{ $historial->idHisMed }}</h2>
            </div>
            <div class="flex gap-2">
                <span class="px-3 py-1 bg-white/20 rounded-full text-sm">
                    {{ \Carbon\Carbon::parse($historial->fecHisMed)->format('d/m/Y') }}
                </span>
            </div>
        </div>

        <!-- Cuerpo -->
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <!-- Sección Animal -->
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <h3 class="text-lg font-semibold mb-3 text-gray-800 border-b pb-2 flex items-center gap-2">
                        <i class="fas fa-paw"></i> Información del Animal
                        <span class="ml-auto text-sm font-normal">
                            ID: {{ $historial->idAni ?: 'N/A' }}
                        </span>
                    </h3>
                    
                    @if($historial->idAni)
                        @if($animal && $animal->exists)
                        <div class="grid grid-cols-2 gap-4">
                            <!-- Columna Izquierda -->
                            <div class="space-y-3">
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Nombre</p>
                                    <p class="text-gray-800">{{ $animal->ideAni ?? 'Sin nombre' }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Especie/Raza</p>
                                    <p class="text-gray-800">{{ $animal->espAni }} / {{ $animal->razAni ?? 'N/A' }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Sexo</p>
                                    <p class="text-gray-800">{{ $animal->sexAni }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Fecha Nacimiento</p>
                                    <p class="text-gray-800">
                                        {{ $animal->fecNacAni ? \Carbon\Carbon::parse($animal->fecNacAni)->format('d/m/Y') : 'N/A' }}
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Columna Derecha -->
                            <div class="space-y-3">
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Peso Actual</p>
                                    <p class="text-gray-800">{{ $animal->pesAni ? $animal->pesAni.' kg' : 'N/A' }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Estado</p>
                                    <p class="text-gray-800 capitalize">{{ $animal->estAni ?? 'N/A' }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Salud</p>
                                    <p class="text-gray-800 capitalize">
                                        @if($animal->estSaludAni)
                                        <span class="{{ $animal->estSaludAni == 'saludable' ? 'text-green-600' : ($animal->estSaludAni == 'enfermo' ? 'text-red-600' : 'text-yellow-600') }}">
                                            {{ $animal->estSaludAni }}
                                        </span>
                                        @else
                                        N/A
                                        @endif
                                    </p>
                                </div>
                                
                                <div>
                                    <p class="text-sm font-medium text-gray-600">Reproducción</p>
                                    <p class="text-gray-800 capitalize">{{ $animal->estReproAni ? str_replace('_', ' ', $animal->estReproAni) : 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Ubicación -->
                        @if($animal->ubicacionAni)
                        <div class="mt-4 pt-3 border-t">
                            <p class="text-sm font-medium text-gray-600">Ubicación</p>
                            <p class="text-gray-800">{{ $animal->ubicacionAni }}</p>
                        </div>
                        @endif
                        
                        <!-- Foto si existe -->
                        @if($animal->fotoAni)
                        <div class="mt-4 pt-3 border-t">
                            <p class="text-sm font-medium text-gray-600 mb-2">Foto</p>
                            <img src="{{ asset('storage/' . $animal->fotoAni) }}" alt="Foto del animal" class="w-32 h-32 object-cover rounded-md border">
                        </div>
                        @endif
                        @else
                        <div class="text-center text-yellow-600 py-4">
                            <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                            <p>El animal con ID {{ $historial->idAni }} no existe en la base de datos</p>
                        </div>
                        @endif
                    @else
                    <div class="text-center text-gray-500 py-4">
                        <i class="fas fa-question-circle text-2xl mb-2"></i>
                        <p>No se ha asociado un animal a este registro</p>
                    </div>
                    @endif
                </div>

                <!-- Sección Registro Médico -->
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <h3 class="text-lg font-semibold mb-3 text-gray-800 border-b pb-2 flex items-center gap-2">
                        <i class="fas fa-clipboard-list"></i> Detalles del Registro
                    </h3>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Tipo</p>
                            <p>
                                @if($historial->tipHisMed == 'vacuna')
                                    <span class="px-2 py-0.5 rounded text-xs font-semibold bg-green-100 text-green-800">VACUNA</span>
                                @elseif($historial->tipHisMed == 'tratamiento')
                                    <span class="px-2 py-0.5 rounded text-xs font-semibold bg-blue-100 text-blue-800">TRATAMIENTO</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-xs font-semibold bg-purple-100 text-purple-800">CONTROL</span>
                                @endif
                            </p>
                        </div>
                        
                        <div>
                            <p class="text-sm font-medium text-gray-600">Fecha</p>
                            <p class="text-gray-800">{{ \Carbon\Carbon::parse($historial->fecHisMed)->format('d/m/Y') }}</p>
                        </div>
                        
                        <div>
                            <p class="text-sm font-medium text-gray-600">Responsable</p>
                            <p class="text-gray-800">{{ $historial->responHisMed }}</p>
                        </div>
                        
                        <div>
                            <p class="text-sm font-medium text-gray-600">Estado del Registro</p>
                            <p class="text-gray-800 capitalize">{{ $historial->estRecHisMed ?? 'N/A' }}</p>
                        </div>
                        
                        <div>
                            <p class="text-sm font-medium text-gray-600">Registrado el</p>
                            <p class="text-gray-800">{{ $historial->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Descripción Completa -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold mb-3 text-gray-800 border-b pb-2 flex items-center gap-2">
                    <i class="fas fa-align-left"></i> Descripción
                </h3>
                <div class="bg-white p-4 rounded-lg border border-gray-200">
                    <p class="text-gray-700 whitespace-pre-line">{{ $historial->desHisMed }}</p>
                </div>
            </div>

            <!-- Tratamiento (si existe) -->
            @if($historial->traHisMed)
            <div class="mb-8">
                <h3 class="text-lg font-semibold mb-3 text-gray-800 border-b pb-2 flex items-center gap-2">
                    <i class="fas fa-prescription-bottle-alt"></i> Tratamiento
                </h3>
                <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                    <p class="text-gray-700 whitespace-pre-line">{{ $historial->traHisMed }}</p>
                </div>
            </div>
            @endif

            <!-- Observaciones -->
            @if($historial->obsHisMed)
            <div class="mb-8">
                <h3 class="text-lg font-semibold mb-3 text-gray-800 border-b pb-2 flex items-center gap-2">
                    <i class="fas fa-exclamation-circle"></i> Observaciones
                </h3>
                <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
                    <p class="text-yellow-800 whitespace-pre-line">{{ $historial->obsHisMed }}</p>
                </div>
            </div>
            @endif

            <!-- Botones de Acción -->
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4 border-t pt-6">
                <a href="{{ route('pecuario.salud-peso.index') }}" wire:navigate
                   class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg transition duration-200">
                   <i class="fas fa-arrow-left mr-2"></i> Volver al Listado
                </a>
                
                <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                    <a href="{{ route('pecuario.salud-peso.edit', $historial) }}" wire:navigate
                       class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition duration-200">
                       <i class="fas fa-edit mr-2"></i> Editar Registro
                    </a>
                    
                    <button wire:click="$dispatch('confirm-delete', { id: {{ $historial->idHisMed }} })"
                            wire:confirm="¿Está seguro que desea eliminar este registro médico?"
                            class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition duration-200">
                            <i class="fas fa-trash-alt mr-2"></i> Eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>