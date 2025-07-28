<?php
// resources/views/livewire/inventario/mantenimientos/edit.blade.php

use App\Models\Mantenimiento;
use App\Models\Herramienta;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component {
    public Mantenimiento $mantenimiento;
    
    // Propiedades públicas para el formulario
    public string $nombreHerramienta;
    public string $fecMan;
    public string $tipMan;
    public string $estMan;
    public ?string $desMan = null;
    public ?string $resMan = null;
    public ?string $obsMan = null;

    // Método para reglas de validación
    public function rules(): array
    {
        $rules = [
            'nombreHerramienta' => 'required|string|max:255',
            'fecMan' => 'required|date',
            'tipMan' => 'required|in:preventivo,correctivo,predictivo',
            'estMan' => 'required|in:pendiente,en proceso,completado',
            'desMan' => 'nullable|string',
            'obsMan' => 'nullable|string'
        ];

        // Si el estado es completado, el resultado es requerido
        if ($this->estMan === 'completado') {
            $rules['resMan'] = 'required|string|max:100';
        } else {
            $rules['resMan'] = 'nullable|string|max:100';
        }

        return $rules;
    }

    // Inicializar componente
    public function mount(Mantenimiento $mantenimiento): void
    {
        $this->mantenimiento = $mantenimiento;
        $this->nombreHerramienta = $mantenimiento->getNombreHerramientaCompleto();
        $this->fecMan = $mantenimiento->fecMan->format('Y-m-d');
        $this->tipMan = $mantenimiento->tipMan;
        $this->estMan = $mantenimiento->estMan;
        $this->desMan = $mantenimiento->desMan;
        $this->resMan = $mantenimiento->resMan;
        $this->obsMan = $mantenimiento->obsMan;
    }

    // Actualizar mantenimiento
    public function update(): void
    {
        $validated = $this->validate();
        
        try {
            $this->mantenimiento->update([
                'nomHerMan' => $validated['nombreHerramienta'],
                'fecMan' => $validated['fecMan'],
                'tipMan' => $validated['tipMan'],
                'estMan' => $validated['estMan'],
                'desMan' => $validated['desMan'],
                'resMan' => $validated['resMan'],
                'obsMan' => $validated['obsMan']
            ]);
            
            session()->flash('success', 'Mantenimiento actualizado exitosamente.');
            $this->redirect(route('inventario.mantenimientos.show', $this->mantenimiento), navigate: true);
        } catch (\Exception $e) {
            session()->flash('error', 'Error al actualizar el mantenimiento: ' . $e->getMessage());
        }
    }

    // Eliminar mantenimiento
    public function delete(): void
    {
        try {
            $this->mantenimiento->delete();
            session()->flash('success', 'Mantenimiento eliminado exitosamente.');
            $this->redirect(route('inventario.mantenimientos.index'), navigate: true);
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar el mantenimiento: ' . $e->getMessage());
        }
    }
}; ?>

@section('title', 'Editar Mantenimiento #' . $mantenimiento->idMan)

<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                        <svg class="w-8 h-8 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Editar Mantenimiento #{{ $mantenimiento->idMan }}
                    </h1>
                    <p class="mt-1 text-sm text-gray-600">Modifica los datos del mantenimiento</p>
                </div>
                <div class="mt-4 sm:mt-0 flex space-x-3">
                    <a href="{{ route('inventario.mantenimientos.show', $mantenimiento) }}" 
                       wire:navigate
                       class="inline-flex items-center px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium rounded-lg shadow-sm transition duration-150 ease-in-out">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Cancelar
                    </a>
                </div>
            </div>
        </div>

        <!-- Formulario -->
        <form wire:submit="update" id="editMantenimientoForm">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Información Principal -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Datos del Mantenimiento -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 bg-green-600 rounded-t-lg">
                            <h3 class="text-lg font-medium text-white flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                                Información del Mantenimiento
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Herramienta -->
                                <div class="md:col-span-2">
                                    <label for="nombreHerramienta" class="block text-sm font-medium text-gray-700 mb-2">
                                        Nombre de la Herramienta <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" wire:model="nombreHerramienta" id="nombreHerramienta" required maxlength="255"
                                           placeholder="Ingrese el nombre de la herramienta..."
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('nombreHerramienta') border-red-500 @enderror">
                                    @error('nombreHerramienta')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    
                                    @if($mantenimiento->herramienta)
                                    <div class="mt-2 bg-blue-50 border border-blue-200 rounded-lg p-3">
                                        <div class="flex items-start">
                                            <svg class="w-4 h-4 text-blue-600 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <p class="text-xs text-blue-800">
                                                <strong>Herramienta registrada:</strong> Esta herramienta está en el sistema.
                                                Puedes cambiar el nombre si es necesario.
                                            </p>
                                        </div>
                                    </div>
                                    @else
                                    <div class="mt-2 bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                                        <div class="flex items-start">
                                            <svg class="w-4 h-4 text-yellow-600 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.314 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                            </svg>
                                            <p class="text-xs text-yellow-800">
                                                <strong>Herramienta personalizada:</strong> Esta herramienta no está registrada en el sistema.
                                            </p>
                                        </div>
                                    </div>
                                    @endif
                                </div>

                                <!-- Tipo de Mantenimiento -->
                                <div>
                                    <label for="tipMan" class="block text-sm font-medium text-gray-700 mb-2">
                                        Tipo de Mantenimiento <span class="text-red-500">*</span>
                                    </label>
                                    <select wire:model="tipMan" id="tipMan" required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('tipMan') border-red-500 @enderror">
                                        <option value="">Seleccione un tipo</option>
                                        <option value="preventivo">Preventivo</option>
                                        <option value="correctivo">Correctivo</option>
                                        <option value="predictivo">Predictivo</option>
                                    </select>
                                    @error('tipMan')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Estado -->
                                <div>
                                    <label for="estMan" class="block text-sm font-medium text-gray-700 mb-2">
                                        Estado <span class="text-red-500">*</span>
                                    </label>
                                    <select wire:model="estMan" id="estMan" required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('estMan') border-red-500 @enderror">
                                        <option value="">Seleccione un estado</option>
                                        <option value="pendiente">Pendiente</option>
                                        <option value="en proceso">En Proceso</option>
                                        <option value="completado">Completado</option>
                                    </select>
                                    @error('estMan')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Fecha del Mantenimiento -->
                                <div>
                                    <label for="fecMan" class="block text-sm font-medium text-gray-700 mb-2">
                                        Fecha Programada <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" wire:model="fecMan" id="fecMan" required
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('fecMan') border-red-500 @enderror">
                                    @error('fecMan')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Creado -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Fecha de Creación</label>
                                    <div class="px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-600">
                                        {{ $mantenimiento->created_at ? $mantenimiento->created_at->format('d/m/Y H:i') : 'No disponible' }}
                                    </div>
                                </div>

                                <!-- Resultado (solo si está completado) -->
                                <div id="campoResultado" class="md:col-span-2" style="{{ $estMan === 'completado' ? 'display: block;' : 'display: none;' }}">
                                    <label for="resMan" class="block text-sm font-medium text-gray-700 mb-2">
                                        Resultado del Mantenimiento
                                        <span id="resultadoRequerido" class="text-red-500" style="{{ $estMan === 'completado' ? 'display: inline;' : 'display: none;' }}">*</span>
                                    </label>
                                    <input type="text" wire:model="resMan" id="resMan" maxlength="100"
                                           placeholder="Describa el resultado del mantenimiento..."
                                           {{ $estMan === 'completado' ? 'required' : '' }}
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('resMan') border-red-500 @enderror">
                                    @error('resMan')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Descripción -->
                            <div class="mt-6">
                                <label for="desMan" class="block text-sm font-medium text-gray-700 mb-2">
                                    Descripción del Mantenimiento
                                </label>
                                <textarea wire:model="desMan" id="desMan" rows="4"
                                          placeholder="Describa detalladamente el mantenimiento a realizar..."
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('desMan') border-red-500 @enderror"></textarea>
                                @error('desMan')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Observaciones -->
                            <div class="mt-6">
                                <label for="obsMan" class="block text-sm font-medium text-gray-700 mb-2">
                                    Observaciones
                                </label>
                                <textarea wire:model="obsMan" id="obsMan" rows="3"
                                          placeholder="Observaciones adicionales sobre el mantenimiento..."
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('obsMan') border-red-500 @enderror"></textarea>
                                @error('obsMan')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panel Lateral -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Estado Actual -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 bg-blue-600 rounded-t-lg">
                            <h3 class="text-lg font-medium text-white flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Estado Actual
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <!-- Estado -->
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-700">Estado:</span>
                                    @php
                                        $estadoColors = [
                                            'pendiente' => 'bg-red-100 text-red-800',
                                            'en proceso' => 'bg-yellow-100 text-yellow-800',
                                            'completado' => 'bg-green-100 text-green-800'
                                        ];
                                        $colorClass = $estadoColors[$mantenimiento->estMan] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $colorClass }}">
                                        {{ ucfirst($mantenimiento->estMan) }}
                                    </span>
                                </div>

                                <!-- Tipo -->
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-700">Tipo:</span>
                                    @php
                                        $tipoColors = [
                                            'preventivo' => 'bg-green-100 text-green-800',
                                            'correctivo' => 'bg-red-100 text-red-800',
                                            'predictivo' => 'bg-blue-100 text-blue-800'
                                        ];
                                        $colorClass = $tipoColors[$mantenimiento->tipMan] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $colorClass }}">
                                        {{ ucfirst($mantenimiento->tipMan) }}
                                    </span>
                                </div>

                                <!-- Fecha programada original -->
                                <div>
                                    <span class="text-sm font-medium text-gray-700">Fecha original:</span>
                                    <p class="text-sm text-gray-900 mt-1">{{ $mantenimiento->fecMan->format('d/m/Y') }}</p>
                                </div>

                                @if($mantenimiento->resMan)
                                <div>
                                    <span class="text-sm font-medium text-gray-700">Resultado actual:</span>
                                    <p class="text-sm text-gray-900 mt-1">{{ $mantenimiento->resMan }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Información de la Herramienta -->
                    @if($mantenimiento->herramienta)
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 bg-purple-600 rounded-t-lg">
                            <h3 class="text-lg font-medium text-white flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 8.172V5L8 4z"></path>
                                </svg>
                                Herramienta Registrada
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-3">
                                <div>
                                    <span class="text-sm font-medium text-gray-700">Nombre:</span>
                                    <p class="text-sm text-gray-900">{{ $mantenimiento->herramienta->nomHer }}</p>
                                </div>
                                
                                @if($mantenimiento->herramienta->catHer)
                                <div>
                                    <span class="text-sm font-medium text-gray-700">Categoría:</span>
                                    <p class="text-sm text-gray-900">{{ $mantenimiento->herramienta->catHer }}</p>
                                </div>
                                @endif

                                @if($mantenimiento->herramienta->marHer)
                                <div>
                                    <span class="text-sm font-medium text-gray-700">Marca:</span>
                                    <p class="text-sm text-gray-900">{{ $mantenimiento->herramienta->marHer }}</p>
                                </div>
                                @endif

                                @if($mantenimiento->herramienta->estHer)
                                <div>
                                    <span class="text-sm font-medium text-gray-700">Estado:</span>
                                    <p class="text-sm text-gray-900">{{ ucfirst($mantenimiento->herramienta->estHer) }}</p>
                                </div>
                                @endif
                            </div>

                            <div class="mt-4">
                                <a href="{{ route('inventario.herramientas.show', $mantenimiento->herramienta) }}" 
                                   wire:navigate
                                   class="w-full inline-flex items-center justify-center px-3 py-2 border border-purple-300 rounded-md shadow-sm text-sm font-medium text-purple-700 bg-white hover:bg-purple-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                    Ver Herramienta Completa
                                </a>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Acciones -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 bg-green-600 rounded-t-lg">
                            <h3 class="text-lg font-medium text-white flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                                Acciones
                            </h3>
                        </div>
                        <div class="p-6 space-y-3">
                            <button type="submit" 
                                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition duration-150 ease-in-out">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Guardar Cambios
                            </button>

                            <a href="{{ route('inventario.mantenimientos.show', $mantenimiento) }}" 
                               wire:navigate
                               class="w-full inline-flex items-center justify-center px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium rounded-lg transition duration-150 ease-in-out">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Cancelar
                            </a>

                            @if($mantenimiento->estMan === 'pendiente')
                            <button type="button" 
                                    wire:click="delete"
                                    wire:confirm="¿Está seguro de eliminar este mantenimiento? Esta acción no se puede deshacer."
                                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition duration-150 ease-in-out">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Eliminar Mantenimiento
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('livewire:initialized', () => {
    // Mostrar/ocultar campo resultado según estado
    Livewire.on('estMan', (value) => {
        const campoResultado = document.getElementById('campoResultado');
        const resultadoInput = document.getElementById('resMan');
        const resultadoRequerido = document.getElementById('resultadoRequerido');
        
        if (value === 'completado') {
            campoResultado.style.display = 'block';
            resultadoInput.required = true;
            resultadoRequerido.style.display = 'inline';
        } else {
            campoResultado.style.display = 'none';
            resultadoInput.required = false;
            resultadoRequerido.style.display = 'none';
        }
    });
});
</script>
@endsection