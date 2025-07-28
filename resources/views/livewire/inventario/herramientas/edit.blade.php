<?php
use App\Models\Herramienta;
use App\Models\Proveedor;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component {
    public Herramienta $herramienta;
    public $proveedores;
    
    // Propiedades públicas para binding directo
    public string $nomHer;
    public ?string $catHer = null;
    public ?int $stockMinHer = null;
    public ?int $stockMaxHer = null;
    public ?int $idProveHer = null;
    public string $estHer;
    public ?string $ubiHer = null;
    public ?string $obsHer = null;

    public function rules(): array
    {
        return [
            'nomHer' => 'required|string|max:100',
            'catHer' => 'nullable|string|max:100',
            'stockMinHer' => 'nullable|integer|min:0',
            'stockMaxHer' => 'nullable|integer|min:0|gt:stockMinHer',
            'idProveHer' => 'nullable|exists:proveedores,idProve',
            'estHer' => 'required|in:bueno,regular,malo',
            'ubiHer' => 'nullable|string|max:150',
            'obsHer' => 'nullable|string'
        ];
    }

    public function mount(Herramienta $herramienta): void
    {
        $this->herramienta = $herramienta;
        $this->proveedores = Proveedor::all();
        
        // Llenamos las propiedades públicas con los datos de la herramienta
        $this->fill(
            $herramienta->only([
                'nomHer', 'catHer', 'stockMinHer', 'stockMaxHer',
                'idProveHer', 'estHer', 'ubiHer', 'obsHer'
            ])
        );
    }

    public function save(): void
    {
        $validated = $this->validate();
        
        try {
            $this->herramienta->update($validated);
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Herramienta actualizada exitosamente.'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al actualizar: ' . $e->getMessage()
            ]);
        }
    }

    public function resetForm(): void
    {
        $this->fill(
            $this->herramienta->only([
                'nomHer', 'catHer', 'stockMinHer', 'stockMaxHer',
                'idProveHer', 'estHer', 'ubiHer', 'obsHer'
            ])
        );
        $this->resetErrorBag();
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Editar Herramienta
                    </h1>
                    <p class="mt-1 text-sm text-gray-600">ID: {{ $herramienta->idHer }} - {{ $herramienta->nomHer }}</p>
                    <div class="mt-2 flex items-center text-xs text-gray-500">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Última actualización: {{ $herramienta->updated_at->format('d/m/Y H:i') }}
                    </div>
                </div>
                <div class="mt-4 sm:mt-0 flex space-x-3">
                    <a href="{{ route('inventario.herramientas.show', $herramienta->idHer) }}" 
                       class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg shadow-sm transition duration-150 ease-in-out">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        Ver Detalle
                    </a>
                    <a href="{{ route('inventario.herramientas.index') }}" 
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
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-medium text-white flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                </svg>
                                Información de la Herramienta
                            </h3>
                            <span class="bg-blue-100 text-blue-800 text-sm font-medium px-2.5 py-0.5 rounded">
                                ID: {{ $herramienta->idHer }}
                            </span>
                        </div>
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
                                        <option value="veterinaria">Veterinaria</option>
                                        <option value="ganadera">Ganadera</option>
                                        <option value="agricola">Agrícola</option>
                                        <option value="mantenimiento">Mantenimiento</option>
                                        <option value="transporte">Transporte</option>
                                        <option value="seguridad">Seguridad</option>
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
                                            <option value="{{ $proveedor->idProve }}">
                                                {{ $proveedor->nomProve }}
                                            </option>
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
                                <a href="{{ route('inventario.herramientas.show', $herramienta->idHer) }}" 
                                   class="px-4 py-2 bg-purple-300 hover:bg-purple-400 text-purple-800 font-medium rounded-lg transition duration-150 ease-in-out">
                                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    Ver Detalle
                                </a>
                                <a href="{{ route('inventario.herramientas.index') }}" 
                                   class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium rounded-lg transition duration-150 ease-in-out">
                                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    Cancelar
                                </a>
                                <button type="button" 
                                        wire:click="resetForm"
                                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-150 ease-in-out">
                                    Restaurar
                                </button>
                                <button type="submit" 
                                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-150 ease-in-out">
                                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                                    </svg>
                                    Actualizar Herramienta
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Panel Lateral -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Stock Actual -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 bg-green-600 rounded-t-lg">
                        <h3 class="text-lg font-medium text-white flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                            Stock Actual
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="text-center">
                            @php
                                $stockActual = $herramienta->stockActual ?? 0;
                                $stockMin = $herramienta->stockMinHer ?? 0;
                                $nivelStock = 'normal';
                                $colorStock = 'text-green-600';
                                
                                if ($stockMin > 0) {
                                    if ($stockActual <= $stockMin) {
                                        $nivelStock = 'crítico';
                                        $colorStock = 'text-red-600';
                                    } elseif ($stockActual <= ($stockMin * 1.5)) {
                                        $nivelStock = 'bajo';
                                        $colorStock = 'text-yellow-600';
                                    }
                                }
                            @endphp
                            
                            <div class="text-4xl font-bold {{ $colorStock }} mb-2">{{ $stockActual }}</div>
                            <p class="text-gray-600 mb-2">unidades disponibles</p>
                            <span class="inline-flex px-3 py-1 text-sm font-medium rounded-full 
                                {{ $nivelStock == 'crítico' ? 'bg-red-100 text-red-800' : ($nivelStock == 'bajo' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }}">
                                Stock {{ $nivelStock }}
                            </span>
                            
                            @if($herramienta->stockMinHer || $herramienta->stockMaxHer)
                            <div class="grid grid-cols-2 gap-4 mt-4 text-center">
                                @if($herramienta->stockMinHer)
                                <div class="bg-red-50 p-2 rounded">
                                    <div class="text-xs text-gray-600">Mínimo</div>
                                    <div class="font-bold text-red-600">{{ $herramienta->stockMinHer }}</div>
                                </div>
                                @endif
                                @if($herramienta->stockMaxHer)
                                <div class="bg-green-50 p-2 rounded">
                                    <div class="text-xs text-gray-600">Máximo</div>
                                    <div class="font-bold text-green-600">{{ $herramienta->stockMaxHer }}</div>
                                </div>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Acciones Rápidas -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 bg-yellow-600 rounded-t-lg">
                        <h3 class="text-lg font-medium text-white flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            Acciones Rápidas
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            <a href="#" class="flex items-center w-full p-3 text-left bg-green-50 hover:bg-green-100 rounded-lg transition duration-150 ease-in-out">
                                <svg class="w-5 h-5 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                <span class="text-green-800 font-medium">Registrar Entrada</span>
                            </a>
                            <a href="#" class="flex items-center w-full p-3 text-left bg-red-50 hover:bg-red-100 rounded-lg transition duration-150 ease-in-out">
                                <svg class="w-5 h-5 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                </svg>
                                <span class="text-red-800 font-medium">Registrar Salida</span>
                            </a>
                            <a href="#" class="flex items-center w-full p-3 text-left bg-yellow-50 hover:bg-yellow-100 rounded-lg transition duration-150 ease-in-out">
                                <svg class="w-5 h-5 text-yellow-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h4a1 1 0 011 1v2h4a1 1 0 110 2h-1v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6H3a1 1 0 110-2h4z"></path>
                                </svg>
                                <span class="text-yellow-800 font-medium">Préstamo</span>
                            </a>
                            <a href="#" class="flex items-center w-full p-3 text-left bg-blue-50 hover:bg-blue-100 rounded-lg transition duration-150 ease-in-out">
                                <svg class="w-5 h-5 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                </svg>
                                <span class="text-blue-800 font-medium">Mantenimiento</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Información del Sistema -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 bg-gray-600 rounded-t-lg">
                        <h3 class="text-lg font-medium text-white flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Información del Sistema
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">ID:</span>
                                <span class="font-medium text-gray-900">{{ $herramienta->idHer }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Creado:</span>
                                <span class="text-gray-900">{{ $herramienta->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Actualizado:</span>
                                <span class="text-gray-900">{{ $herramienta->updated_at->format('d/m/Y H:i') }}</span>
                            </div>
                            @if(isset($herramienta->ultimoMovimiento))
                            <div class="flex justify-between">
                                <span class="text-gray-500">Último Mov.:</span>
                                <span class="text-gray-900">{{ $herramienta->ultimoMovimiento->format('d/m/Y') }}</span>
                            </div>
                            @endif
                            @if(isset($herramienta->totalMovimientos))
                            <div class="flex justify-between">
                                <span class="text-gray-500">Movimientos:</span>
                                <span class="font-bold text-gray-900">{{ $herramienta->totalMovimientos }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@script
<script>
    // Advertir al usuario si sale sin guardar
    window.addEventListener('beforeunload', function(e) {
        if (@this.formModified) {
            e.preventDefault();
            e.returnValue = '¿Está seguro de que desea salir? Los cambios no guardados se perderán.';
        }
    });
</script>
@endscript