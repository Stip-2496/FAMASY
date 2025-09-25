<?php
use App\Models\Herramienta;
use App\Models\Proveedor;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component {
    public Herramienta $herramienta;
    public string $nomHer = '';
    public string $catHer = '';
    public string $estHer = '';
    public string $ubiHer = '';
    public string $stockMinHer = '';
    public string $stockMaxHer = '';
    public string $idProveHer = '';
    public string $obsHer = '';

    public function mount(Herramienta $herramienta): void
    {
        $this->herramienta = $herramienta;
        $this->nomHer = $herramienta->nomHer ?? '';
        $this->catHer = $herramienta->catHer ?? '';
        $this->estHer = $herramienta->estHer ?? '';
        $this->ubiHer = $herramienta->ubiHer ?? '';
        $this->stockMinHer = $herramienta->stockMinHer ? (string)$herramienta->stockMinHer : '';
        $this->stockMaxHer = $herramienta->stockMaxHer ? (string)$herramienta->stockMaxHer : '';
        $this->idProveHer = $herramienta->idProveHer ? (string)$herramienta->idProveHer : '';
        $this->obsHer = $herramienta->obsHer ?? '';
    }

    public function with(): array
    {
        return [
            'proveedores' => Proveedor::orderBy('nomProve')->get(),
            'categorias' => [
                'veterinaria' => 'Veterinaria',
                'ganadera' => 'Ganadera',
                'agricola' => 'Agrícola',
                'mantenimiento' => 'Mantenimiento',
                'transporte' => 'Transporte',
                'seguridad' => 'Seguridad'
            ],
            'estados' => [
                'bueno' => 'Bueno',
                'regular' => 'Regular',
                'malo' => 'Malo'
            ]
        ];
    }

    public function update(): void
    {
        $this->validate([
            'nomHer' => 'required|string|max:100',
            'catHer' => 'required|string|max:50',
            'estHer' => 'required|in:bueno,regular,malo',
            'ubiHer' => 'nullable|string|max:100',
            'stockMinHer' => 'nullable|integer|min:0',
            'stockMaxHer' => 'nullable|integer|min:0',
            'idProveHer' => 'nullable|exists:proveedores,idProve',
            'obsHer' => 'nullable|string'
        ], [
            'nomHer.required' => 'El nombre de la herramienta es obligatorio.',
            'nomHer.max' => 'El nombre no puede tener más de 100 caracteres.',
            'catHer.required' => 'La categoría es obligatoria.',
            'catHer.max' => 'La categoría no puede tener más de 50 caracteres.',
            'estHer.required' => 'El estado es obligatorio.',
            'estHer.in' => 'El estado debe ser: bueno, regular o malo.',
            'ubiHer.max' => 'La ubicación no puede tener más de 100 caracteres.',
            'stockMinHer.integer' => 'El stock mínimo debe ser un número entero.',
            'stockMinHer.min' => 'El stock mínimo no puede ser negativo.',
            'stockMaxHer.integer' => 'El stock máximo debe ser un número entero.',
            'stockMaxHer.min' => 'El stock máximo no puede ser negativo.',
            'idProveHer.exists' => 'El proveedor seleccionado no es válido.',
        ]);

        try {
            $this->herramienta->update([
                'nomHer' => $this->nomHer,
                'catHer' => $this->catHer,
                'estHer' => $this->estHer,
                'ubiHer' => $this->ubiHer ?: null,
                'stockMinHer' => $this->stockMinHer ? (int)$this->stockMinHer : null,
                'stockMaxHer' => $this->stockMaxHer ? (int)$this->stockMaxHer : null,
                'idProveHer' => $this->idProveHer ?: null,
                'obsHer' => $this->obsHer ?: null,
            ]);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Herramienta actualizada exitosamente.'
            ]);

            $this->redirect(route('inventario.herramientas.show', $this->herramienta), navigate: true);

        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al actualizar la herramienta: ' . $e->getMessage()
            ]);
        }
    }

    public function cancel(): void
    {
        $this->redirect(route('inventario.herramientas.show', $this->herramienta), navigate: true);
    }
}; ?>

@section('title', 'Editar Herramienta')

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header Mejorado -->
        <div class="mb-8">
            <div class="flex items-center space-x-4">
                <a href="{{ route('inventario.herramientas.show', $herramienta) }}" wire:navigate 
                   class="group p-3 bg-white hover:bg-gray-50 rounded-xl shadow-md hover:shadow-lg transition-all duration-200">
                    <svg class="w-6 h-6 text-gray-600 group-hover:text-gray-800 transform group-hover:-translate-x-1 transition-all duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <div class="flex items-center space-x-4">
                    <div class="bg-gradient-to-r from-indigo-500 to-blue-600 p-3 rounded-2xl shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-4xl font-bold text-gray-900">Editar Herramienta</h1>
                        <p class="mt-2 text-lg text-gray-600">
                            Modificando: <span class="font-semibold text-blue-600">{{ $herramienta->nomHer }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información Actual de la Herramienta -->
        <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-2xl p-6 mb-8 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-semibold mb-2">Información Actual</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <p class="text-blue-100 text-sm">ID</p>
                            <p class="font-semibold">#{{ $herramienta->idHer }}</p>
                        </div>
                        <div>
                            <p class="text-blue-100 text-sm">Estado</p>
                            <p class="font-semibold">{{ ucfirst($herramienta->estHer) }}</p>
                        </div>
                        <div>
                            <p class="text-blue-100 text-sm">Última Actualización</p>
                            <p class="font-semibold">{{ $herramienta->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>
                <div class="hidden md:block">
                    <div class="w-16 h-16 bg-white bg-opacity-20 rounded-2xl flex items-center justify-center">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulario Mejorado -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="bg-gradient-to-r from-indigo-600 to-blue-600 px-8 py-6">
                <h3 class="text-2xl font-semibold text-white flex items-center">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Actualizar Información
                </h3>
            </div>

            <form wire:submit="update" class="px-8 py-8 space-y-8">
                <!-- Información Básica -->
                <div class="space-y-6">
                    <div class="border-l-4 border-blue-500 pl-4">
                        <h4 class="text-lg font-semibold text-gray-900 mb-1">Información Básica</h4>
                        <p class="text-sm text-gray-600">Datos principales de la herramienta</p>
                    </div>
                    
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <!-- Nombre de la Herramienta -->
                        <div class="space-y-2">
                            <label for="nomHer" class="block text-sm font-semibold text-gray-700">
                                Nombre de la Herramienta <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    </svg>
                                </div>
                                <input type="text" 
                                       wire:model="nomHer" 
                                       id="nomHer" 
                                       maxlength="100"
                                       class="pl-10 w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('nomHer') border-red-300 ring-2 ring-red-200 @enderror"
                                       placeholder="Ej: Taladro Eléctrico Bosch">
                            </div>
                            @error('nomHer')
                                <p class="mt-2 text-sm text-red-600 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Categoría -->
                        <div class="space-y-2">
                            <label for="catHer" class="block text-sm font-semibold text-gray-700">
                                Categoría <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <select wire:model="catHer" 
                                        id="catHer"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('catHer') border-red-300 ring-2 ring-red-200 @enderror">
                                    <option value="">Seleccionar categoría</option>
                                    @foreach($categorias as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('catHer')
                                <p class="mt-2 text-sm text-red-600 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <!-- Estado -->
                        <div class="space-y-2">
                            <label for="estHer" class="block text-sm font-semibold text-gray-700">
                                Estado <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <select wire:model="estHer" 
                                        id="estHer"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('estHer') border-red-300 ring-2 ring-red-200 @enderror">
                                    @foreach($estados as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('estHer')
                                <p class="mt-2 text-sm text-red-600 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Ubicación -->
                        <div class="space-y-2">
                            <label for="ubiHer" class="block text-sm font-semibold text-gray-700">
                                Ubicación
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </div>
                                <input type="text" 
                                       wire:model="ubiHer" 
                                       id="ubiHer" 
                                       maxlength="100"
                                       class="pl-10 w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                       placeholder="Ej: Almacén A - Estante 3">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Proveedor -->
                <div class="space-y-6 pt-6 border-t border-gray-200">
                    <div class="border-l-4 border-orange-500 pl-4">
                        <h4 class="text-lg font-semibold text-gray-900 mb-1">Información del Proveedor</h4>
                        <p class="text-sm text-gray-600">Proveedor que suministra esta herramienta</p>
                    </div>
                    
                    <div class="space-y-2">
                        <label for="idProveHer" class="block text-sm font-semibold text-gray-700">
                            Proveedor
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            <select wire:model="idProveHer" 
                                    id="idProveHer"
                                    class="pl-10 w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('idProveHer') border-red-300 ring-2 ring-red-200 @enderror">
                                <option value="">Sin proveedor</option>
                                @foreach($proveedores as $proveedor)
                                    <option value="{{ $proveedor->idProve }}">{{ $proveedor->nomProve }} - {{ $proveedor->nitProve }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('idProveHer')
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                        <p class="mt-2 text-sm text-gray-500 flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Opcional: Selecciona el proveedor de esta herramienta
                        </p>
                    </div>
                </div>

                <!-- Información de Stock -->
                <div class="space-y-6 pt-6 border-t border-gray-200">
                    <div class="border-l-4 border-green-500 pl-4">
                        <h4 class="text-lg font-semibold text-gray-900 mb-1">Control de Inventario</h4>
                        <p class="text-sm text-gray-600">Parámetros de stock para el inventario</p>
                    </div>
                    
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <!-- Stock Mínimo -->
                        <div class="space-y-2">
                            <label for="stockMinHer" class="block text-sm font-semibold text-gray-700">
                                Stock Mínimo
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                    </svg>
                                </div>
                                <input type="number" 
                                       wire:model="stockMinHer" 
                                       id="stockMinHer" 
                                       min="0"
                                       class="pl-10 w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('stockMinHer') border-red-300 ring-2 ring-red-200 @enderror"
                                       placeholder="0">
                            </div>
                            @error('stockMinHer')
                                <p class="mt-2 text-sm text-red-600 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Stock Máximo -->
                        <div class="space-y-2">
                            <label for="stockMaxHer" class="block text-sm font-semibold text-gray-700">
                                Stock Máximo
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a7.5 7.5 0 0015 0v-5a1.5 1.5 0 00-3 0m-6-3V11m0-5.5v-1a1.5 1.5 0 013 0v1m0 0V11m0-5.5a1.5 1.5 0 013 0v3m0 0V11"></path>
                                    </svg>
                                </div>
                                <input type="number" 
                                       wire:model="stockMaxHer" 
                                       id="stockMaxHer" 
                                       min="0"
                                       class="pl-10 w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all @error('stockMaxHer') border-red-300 ring-2 ring-red-200 @enderror"
                                       placeholder="0">
                            </div>
                            @error('stockMaxHer')
                                <p class="mt-2 text-sm text-red-600 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Información Adicional -->
                <div class="space-y-6 pt-6 border-t border-gray-200">
                    <div class="border-l-4 border-purple-500 pl-4">
                        <h4 class="text-lg font-semibold text-gray-900 mb-1">Información Adicional</h4>
                        <p class="text-sm text-gray-600">Detalles y observaciones especiales</p>
                    </div>
                    
                    <!-- Observaciones -->
                    <div class="space-y-2">
                        <label for="obsHer" class="block text-sm font-semibold text-gray-700">
                            Observaciones
                        </label>
                        <textarea wire:model="obsHer" 
                                  id="obsHer" 
                                  rows="4"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all resize-none"
                                  placeholder="Observaciones especiales, características técnicas, mantenimiento requerido, etc..."></textarea>
                    </div>
                </div>

                <!-- Botones Mejorados -->
                <div class="flex justify-end space-x-4 pt-8 border-t border-gray-200">
                    <button type="button" 
                            wire:click="cancel"
                            class="group px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl transition-all duration-200 flex items-center">
                        <svg class="w-5 h-5 mr-2 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="group px-8 py-3 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 flex items-center">
                        <svg class="w-5 h-5 mr-2 transform group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Actualizar Herramienta
                    </button>
                </div>
            </form>
        </div>

        <!-- Información de ayuda Mejorada -->
        <div class="mt-8 bg-gradient-to-r from-indigo-50 to-blue-50 rounded-2xl p-6 border border-indigo-200">
            <div class="flex">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center">
                        <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-indigo-900 mb-2">Información importante</h3>
                    <div class="text-sm text-indigo-800 space-y-1">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Los campos marcados con (*) son obligatorios
                        </div>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Los stocks mínimo y máximo son opcionales
                        </div>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            El proveedor se puede cambiar en cualquier momento
                        </div>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Los cambios se guardan inmediatamente al enviar el formulario
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>