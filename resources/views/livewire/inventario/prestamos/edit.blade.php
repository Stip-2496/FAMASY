<?php
// resources/views/livewire/inventario/prestamos/edit.blade.php

use App\Models\PrestamoHerramienta;
use App\Models\Herramienta;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component {
    public PrestamoHerramienta $prestamo;
    
    // Form properties
    public string $idHerPre;
    public string $idUsuPre;
    public string $fecPre;
    public ?string $fecDev = null;
    public string $estPre;
    public ?string $obsPre = null;
    
    // Data for selects
    public $herramientas;
    public $usuarios;
    
    public function rules(): array
    {
        return [
            'idHerPre' => 'required|exists:herramientas,idHer',
            'idUsuPre' => 'required|exists:users,id',
            'fecPre' => 'required|date',
            'fecDev' => 'nullable|date|after_or_equal:fecPre',
            'estPre' => 'required|in:prestado,devuelto,vencido',
            'obsPre' => 'nullable|string'
        ];
    }
    
    public function mount(PrestamoHerramienta $prestamo): void
    {
        $this->prestamo = $prestamo;
        $this->fill($prestamo->only([
            'idHerPre', 'idUsuPre', 'fecPre', 'fecDev', 'estPre', 'obsPre'
        ]));
        
        // Load data for selects
        $this->herramientas = Herramienta::all();
        $this->usuarios = User::all();
    }
    
    public function update(): void
    {
        $validated = $this->validate();
        
        try {
            $this->prestamo->update($validated);
            session()->flash('success', 'Préstamo actualizado exitosamente');
            $this->redirect(route('inventario.prestamos.index'), navigate: true);
        } catch (\Exception $e) {
            session()->flash('error', 'Error al actualizar el préstamo: ' . $e->getMessage());
        }
    }
    
    public function updatedEstPre($value): void
    {
        if ($value === 'devuelto' && empty($this->fecDev)) {
            $this->fecDev = now()->toDateString();
        }
    }
}; ?>

@section('title', 'Editar Préstamo')

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                        <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Editar Préstamo
                    </h1>
                    <p class="mt-2 text-gray-600">Modifique la información del préstamo de herramienta</p>
                </div>
                <a href="{{ route('inventario.prestamos.index') }}" wire:navigate
                   class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Volver
                </a>
            </div>
        </div>

        <!-- Información actual del préstamo -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <h3 class="text-lg font-semibold text-blue-900 mb-2 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Información Actual
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div>
                    <span class="font-medium text-blue-800">Herramienta:</span>
                    <p class="text-blue-700">{{ $prestamo->herramienta->codHer }} - {{ $prestamo->herramienta->nomHer }}</p>
                </div>
                <div>
                    <span class="font-medium text-blue-800">Usuario:</span>
                    <p class="text-blue-700">{{ $prestamo->usuario->name }}</p>
                </div>
                <div>
                    <span class="font-medium text-blue-800">Estado Actual:</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        @if($prestamo->estPre == 'prestado') bg-yellow-100 text-yellow-800
                        @elseif($prestamo->estPre == 'devuelto') bg-green-100 text-green-800
                        @else bg-red-100 text-red-800 @endif">
                        {{ ucfirst($prestamo->estPre) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Formulario -->
        <div class="bg-white shadow-xl rounded-lg overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                <h2 class="text-xl font-semibold text-white flex items-center">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Actualizar Información
                </h2>
            </div>

            <div class="p-6">
                @if($errors->any())
                    <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6 rounded-r-lg">
                        <div class="flex items-center mb-2">
                            <svg class="w-5 h-5 text-red-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-sm font-medium text-red-800">Se encontraron errores:</p>
                        </div>
                        <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form wire:submit="update">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        
                        <!-- Herramienta -->
                        <div class="space-y-2">
                            <label for="idHerPre" class="block text-sm font-medium text-gray-700">
                                <svg class="w-4 h-4 text-blue-500 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                                </svg>
                                Herramienta *
                            </label>
                            <select wire:model="idHerPre" id="idHerPre" required
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 @error('idHerPre') border-red-300 @enderror">
                                <option value="">Seleccione una herramienta...</option>
                                @foreach($herramientas as $herramienta)
                                    <option value="{{ $herramienta->idHer }}" 
                                            @selected($idHerPre == $herramienta->idHer)
                                            data-disponible="{{ $herramienta->canHer }}"
                                            data-codigo="{{ $herramienta->codHer }}">
                                        {{ $herramienta->codHer }} - {{ $herramienta->nomHer }}
                                        (Disponibles: {{ $herramienta->canHer }})
                                    </option>
                                @endforeach
                            </select>
                            @error('idHerPre')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-gray-500">
                                <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Solo se muestran herramientas con stock disponible
                            </p>
                        </div>

                        <!-- Usuario -->
                        <div class="space-y-2">
                            <label for="idUsuPre" class="block text-sm font-medium text-gray-700">
                                <svg class="w-4 h-4 text-blue-500 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Usuario *
                            </label>
                            <select wire:model="idUsuPre" id="idUsuPre" required
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 @error('idUsuPre') border-red-300 @enderror">
                                <option value="">Seleccione un usuario...</option>
                                @foreach($usuarios as $usuario)
                                    <option value="{{ $usuario->id }}" 
                                            @selected($idUsuPre == $usuario->id)>
                                        {{ $usuario->name }} - {{ $usuario->email }}
                                    </option>
                                @endforeach
                            </select>
                            @error('idUsuPre')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Fecha de Préstamo -->
                        <div class="space-y-2">
                            <label for="fecPre" class="block text-sm font-medium text-gray-700">
                                <svg class="w-4 h-4 text-blue-500 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                Fecha de Préstamo *
                            </label>
                            <input type="date" wire:model="fecPre" id="fecPre" required
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 @error('fecPre') border-red-300 @enderror">
                            @error('fecPre')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Fecha de Devolución -->
                        <div class="space-y-2">
                            <label for="fecDev" class="block text-sm font-medium text-gray-700">
                                <svg class="w-4 h-4 text-green-500 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Fecha de Devolución
                            </label>
                            <input type="date" wire:model="fecDev" id="fecDev"
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200 @error('fecDev') border-red-300 @enderror">
                            @error('fecDev')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-gray-500">
                                <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Fecha real de devolución (dejar vacío si no se ha devuelto)
                            </p>
                        </div>

                        <!-- Estado -->
                        <div class="space-y-2 lg:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">
                                <svg class="w-4 h-4 text-blue-500 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"></path>
                                </svg>
                                Estado *
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <label class="relative cursor-pointer">
                                    <input type="radio" wire:model="estPre" value="prestado" 
                                           class="sr-only peer">
                                    <div class="p-3 border-2 border-gray-300 rounded-lg peer-checked:border-yellow-500 peer-checked:bg-yellow-50 hover:bg-gray-50 transition-colors duration-200">
                                        <div class="flex items-center">
                                            <svg class="w-5 h-5 text-yellow-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"></path>
                                            </svg>
                                            <span class="font-medium text-gray-900">Prestado</span>
                                        </div>
                                    </div>
                                </label>
                                
                                <label class="relative cursor-pointer">
                                    <input type="radio" wire:model="estPre" value="devuelto" 
                                           class="sr-only peer">
                                    <div class="p-3 border-2 border-gray-300 rounded-lg peer-checked:border-green-500 peer-checked:bg-green-50 hover:bg-gray-50 transition-colors duration-200">
                                        <div class="flex items-center">
                                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            <span class="font-medium text-gray-900">Devuelto</span>
                                        </div>
                                    </div>
                                </label>
                                
                                <label class="relative cursor-pointer">
                                    <input type="radio" wire:model="estPre" value="vencido" 
                                           class="sr-only peer">
                                    <div class="p-3 border-2 border-gray-300 rounded-lg peer-checked:border-red-500 peer-checked:bg-red-50 hover:bg-gray-50 transition-colors duration-200">
                                        <div class="flex items-center">
                                            <svg class="w-5 h-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <span class="font-medium text-gray-900">Vencido</span>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            @error('estPre')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Observaciones -->
                    <div class="mt-6 space-y-2">
                        <label for="obsPre" class="block text-sm font-medium text-gray-700">
                            <svg class="w-4 h-4 text-blue-500 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Observaciones
                        </label>
                        <textarea wire:model="obsPre" id="obsPre" rows="4" 
                                  placeholder="Escriba cualquier observación adicional sobre el préstamo..."
                                  class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 @error('obsPre') border-red-300 @enderror"></textarea>
                        @error('obsPre')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Botones -->
                    <div class="mt-8 flex flex-col sm:flex-row gap-3 sm:justify-end">
                        <a href="{{ route('inventario.prestamos.index') }}" wire:navigate
                           class="inline-flex justify-center items-center px-6 py-3 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Cancelar
                        </a>
                        <button type="submit"
                                class="inline-flex justify-center items-center px-6 py-3 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 transform hover:scale-105">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                            </svg>
                            Actualizar Préstamo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@script
<script>
document.addEventListener('livewire:initialized', () => {
    // Validación de fechas
    const fecPre = document.getElementById('fecPre');
    const fecDev = document.getElementById('fecDev');
    
    fecPre.addEventListener('change', function() {
        if (fecDev.value && fecDev.value < this.value) {
            fecDev.value = '';
            alert('⚠️ La fecha de devolución no puede ser anterior al préstamo');
        }
        fecDev.min = this.value;
    });
    
    // Validar al cambiar fecha de devolución
    fecDev.addEventListener('change', function() {
        if (this.value && fecPre.value && this.value < fecPre.value) {
            this.value = '';
            alert('⚠️ La fecha de devolución no puede ser anterior al préstamo');
        }
    });
    
    // Información de herramientas
    const selectHerramienta = document.getElementById('idHerPre');
    selectHerramienta.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const disponibles = selectedOption.dataset.disponible;
        
        if (disponibles && parseInt(disponibles) <= 0) {
            alert('⚠️ Esta herramienta no tiene stock disponible');
        }
    });
});
</script>
@endscript