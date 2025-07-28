<?php
use App\Models\Proveedor;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component {
    public string $nomHer = '';
    public string $catHer = '';
    public ?int $stockMinHer = null;
    public ?int $stockMaxHer = null;
    public ?int $idProveHer = null;
    public string $estHer = 'bueno';
    public ?string $ubiHer = null;
    public ?string $obsHer = null;

    public function rules(): array
    {
        return [
            'nomHer' => 'required|string|max:100',
            'catHer' => 'required|string|max:100',
            'stockMinHer' => 'nullable|integer|min:0',
            'stockMaxHer' => 'nullable|integer|min:0',
            'idProveHer' => 'nullable|exists:proveedores,idProve',
            'estHer' => 'required|in:bueno,regular,malo',
            'ubiHer' => 'nullable|string|max:150',
            'obsHer' => 'nullable|string'
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();
        
        try {
            $herramienta = Herramienta::create($validated);
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Herramienta creada exitosamente.'
            ]);
            
            $this->redirect(route('inventario.herramientas.index'), navigate: true);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al crear la herramienta: ' . $e->getMessage()
            ]);
        }
    }

    public function with(): array
    {
        return [
            'proveedores' => Proveedor::all(),
            'categorias' => [
                'veterinaria' => 'Veterinaria',
                'ganadera' => 'Ganadera',
                'agricola' => 'Agrícola',
                'mantenimiento' => 'Mantenimiento',
                'transporte' => 'Transporte',
                'seguridad' => 'Seguridad'
            ]
        ];
    }
}; ?>

<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                        <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Nueva Herramienta
                    </h1>
                    <p class="mt-1 text-sm text-gray-600">Registra una nueva herramienta en el inventario</p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <a href="{{ route('inventario.herramientas.index') }}" wire:navigate
                       class="inline-flex items-center px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium rounded-lg shadow-sm transition duration-150 ease-in-out">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Volver
                    </a>
                </div>
            </div>
        </div>

        <!-- Formulario -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Formulario Principal -->
            <div class="lg:col-span-2">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 bg-blue-600 rounded-t-lg">
                        <h3 class="text-lg font-medium text-white flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            </svg>
                            Información de la Herramienta
                        </h3>
                    </div>
                    <div class="p-6">
                        <form wire:submit="save" id="formHerramienta">
                            <!-- Nombre y Categoría -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                                <div class="md:col-span-2">
                                    <label for="nomHer" class="block text-sm font-medium text-gray-700 mb-2">
                                        Nombre de la Herramienta <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" 
                                           id="nomHer" 
                                           wire:model="nomHer" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('nomHer') border-red-500 @enderror"
                                           placeholder="Ej: Jeringa Veterinaria 50ml"
                                           required>
                                    @error('nomHer')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="catHer" class="block text-sm font-medium text-gray-700 mb-2">
                                        Categoría <span class="text-red-500">*</span>
                                    </label>
                                    <select id="catHer" 
                                            wire:model="catHer" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('catHer') border-red-500 @enderror"
                                            required>
                                        <option value="">Seleccionar categoría</option>
                                        @foreach($categorias as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('catHer')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Estado y Ubicación -->
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                                <div>
                                    <label for="estHer" class="block text-sm font-medium text-gray-700 mb-2">
                                        Estado <span class="text-red-500">*</span>
                                    </label>
                                    <select id="estHer" 
                                            wire:model="estHer" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('estHer') border-red-500 @enderror"
                                            required>
                                        <option value="bueno">Bueno</option>
                                        <option value="regular">Regular</option>
                                        <option value="malo">Malo</option>
                                    </select>
                                    @error('estHer')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="md:col-span-3">
                                    <label for="ubiHer" class="block text-sm font-medium text-gray-700 mb-2">Ubicación</label>
                                    <input type="text" 
                                           id="ubiHer" 
                                           wire:model="ubiHer" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('ubiHer') border-red-500 @enderror"
                                           placeholder="Ej: Botiquín veterinario, Corral principal">
                                    @error('ubiHer')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Control de Stock -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                                <div>
                                    <label for="stockMinHer" class="block text-sm font-medium text-gray-700 mb-2">Stock Mínimo</label>
                                    <input type="number" 
                                           id="stockMinHer" 
                                           wire:model="stockMinHer" 
                                           min="0" 
                                           step="1"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('stockMinHer') border-red-500 @enderror"
                                           placeholder="Cantidad mínima">
                                    @error('stockMinHer')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    <p class="mt-1 text-xs text-gray-500">
                                        <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                        </svg>
                                        Para alertas de reabastecimiento
                                    </p>
                                </div>
                                <div>
                                    <label for="stockMaxHer" class="block text-sm font-medium text-gray-700 mb-2">Stock Máximo</label>
                                    <input type="number" 
                                           id="stockMaxHer" 
                                           wire:model="stockMaxHer" 
                                           min="0" 
                                           step="1"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('stockMaxHer') border-red-500 @enderror"
                                           placeholder="Cantidad máxima">
                                    @error('stockMaxHer')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    <p class="mt-1 text-xs text-gray-500">
                                        <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                        </svg>
                                        Cantidad máxima operativa
                                    </p>
                                </div>
                                <div>
                                    <label for="idProveHer" class="block text-sm font-medium text-gray-700 mb-2">Proveedor</label>
                                    <select id="idProveHer" 
                                            wire:model="idProveHer" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('idProveHer') border-red-500 @enderror">
                                        <option value="">Sin proveedor</option>
                                        @foreach($proveedores as $proveedor)
                                            <option value="{{ $proveedor->idProve }}">{{ $proveedor->nomProve }}</option>
                                        @endforeach
                                    </select>
                                    @error('idProveHer')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    <p class="mt-1 text-xs text-gray-500">
                                        <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                        </svg>
                                        Proveedor habitual
                                    </p>
                                </div>
                            </div>

                            <!-- Observaciones -->
                            <div class="mb-6">
                                <label for="obsHer" class="block text-sm font-medium text-gray-700 mb-2">Observaciones</label>
                                <textarea id="obsHer" 
                                          wire:model="obsHer" 
                                          rows="4" 
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 @error('obsHer') border-red-500 @enderror"
                                          placeholder="Observaciones adicionales sobre la herramienta..."></textarea>
                                @error('obsHer')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Botones -->
                            <div class="flex items-center justify-end space-x-4">
                                <a href="{{ route('inventario.herramientas.index') }}" wire:navigate
                                   class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium rounded-lg transition duration-150 ease-in-out">
                                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    Cancelar
                                </a>
                                <button type="submit" 
                                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-150 ease-in-out">
                                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                                    </svg>
                                    Guardar Herramienta
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Panel de Ayuda -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Consejos -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 bg-yellow-500 rounded-t-lg">
                        <h3 class="text-lg font-medium text-white flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                            </svg>
                            Consejos para el Registro
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <h4 class="font-medium text-gray-900 flex items-center">
                                <svg class="w-4 h-4 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                </svg>
                                Nombre de la Herramienta
                            </h4>
                            <p class="text-sm text-gray-600">
                                Usa nombres descriptivos que incluyan marca, modelo o características importantes.
                            </p>
                        </div>
                        
                        <div>
                            <h4 class="font-medium text-gray-900 flex items-center">
                                <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                                Categorías
                            </h4>
                            <ul class="text-sm text-gray-600 space-y-1">
                                <li><span class="font-medium">Veterinaria:</span> Jeringas, termómetros, equipos médicos</li>
                                <li><span class="font-medium">Ganadera:</span> Básculas, marcadores, equipos de manejo</li>
                                <li><span class="font-medium">Agrícola:</span> Herramientas de campo y cultivo</li>
                                <li><span class="font-medium">Mantenimiento:</span> Herramientas generales de reparación</li>
                            </ul>
                        </div>

                        <div>
                            <h4 class="font-medium text-gray-900 flex items-center">
                                <svg class="w-4 h-4 text-purple-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                                Control de Stock
                            </h4>
                            <p class="text-sm text-gray-600">
                                Define límites mínimos y máximos para recibir alertas automáticas de reabastecimiento.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Información -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Importante</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <p>Una vez guardada, podrás registrar el stock inicial desde el detalle de la herramienta.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Proveedores disponibles -->
                @if($proveedores->count() > 0)
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 bg-gray-600 rounded-t-lg">
                        <h3 class="text-lg font-medium text-white flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                            </svg>
                            Proveedores Disponibles
                        </h3>
                    </div>
                    <div class="p-6">
                        @foreach($proveedores->take(5) as $proveedor)
                        <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-gray-200' : '' }}">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">{{ $proveedor->nomProve }}</p>
                                <p class="text-xs text-gray-500">{{ $proveedor->tipSumProve }}</p>
                            </div>
                            <button type="button" 
                                    wire:click="$set('idProveHer', {{ $proveedor->idProve }})"
                                    class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                Seleccionar
                            </button>
                        </div>
                        @endforeach
                        
                        @if($proveedores->count() > 5)
                        <div class="text-center mt-3">
                            <p class="text-xs text-gray-500">y {{ $proveedores->count() - 5 }} más...</p>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
// Validación en tiempo real de stock
document.addEventListener('DOMContentLoaded', function() {
    const stockMin = document.getElementById('stockMinHer');
    const stockMax = document.getElementById('stockMaxHer');
    
    function validarStock() {
        const min = parseFloat(stockMin.value) || 0;
        const max = parseFloat(stockMax.value) || 0;
        
        if (min > 0 && max > 0 && min > max) {
            stockMax.setCustomValidity('El stock máximo debe ser mayor al mínimo');
            stockMax.classList.add('border-red-500');
            stockMax.classList.remove('border-gray-300');
        } else {
            stockMax.setCustomValidity('');
            stockMax.classList.remove('border-red-500');
            stockMax.classList.add('border-gray-300');
        }
    }
    
    stockMin.addEventListener('change', validarStock);
    stockMax.addEventListener('change', validarStock);
});

// Auto-capitalizar primera letra del nombre
document.getElementById('nomHer').addEventListener('blur', function() {
    this.value = this.value.charAt(0).toUpperCase() + this.value.slice(1);
});
</script>