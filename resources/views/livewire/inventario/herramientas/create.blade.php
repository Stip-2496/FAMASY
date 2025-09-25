<?php
use App\Models\Herramienta;
use App\Models\Proveedor;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component {
    public string $nomHer = '';
    public string $catHer = '';
    public string $estHer = 'bueno';
    public string $ubiHer = '';
    public string $stockMinHer = '';
    public string $stockMaxHer = '';
    public string $idProveHer = '';
    public string $obsHer = '';

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

    public function save(): void
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
            Herramienta::create([
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

    public function cancel(): void
    {
        $this->redirect(route('inventario.herramientas.index'), navigate: true);
    }
}; ?>

@section('title', 'Nueva Herramienta')

<div class="min-h-screen bg-gray-50">
    <!-- Header Principal -->
    <div class="bg-gradient-to-r from-slate-900 via-purple-900 to-slate-900">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex items-center space-x-6">
                <a href="{{ route('inventario.herramientas.index') }}" wire:navigate 
                   class="group p-3 bg-white/10 backdrop-blur-sm hover:bg-white/20 rounded-2xl transition-all duration-200 border border-white/20">
                    <svg class="w-6 h-6 text-white group-hover:text-emerald-300 transform group-hover:-translate-x-1 transition-all duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <div class="absolute inset-0 bg-gradient-to-r from-emerald-400 to-cyan-400 rounded-2xl blur opacity-75"></div>
                        <div class="relative bg-white p-4 rounded-2xl">
                            <svg class="w-8 h-8 text-slate-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h1 class="text-4xl font-bold text-white mb-2">Nueva Herramienta</h1>
                        <p class="text-xl text-slate-300">Expande tu inventario profesional</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Progreso Visual -->
        <div class="mb-8">
            <div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-slate-800">Progreso del Registro</h3>
                    <span class="text-sm text-slate-600">Paso 1 de 1</span>
                </div>
                <div class="w-full bg-slate-200 rounded-full h-2">
                    <div class="bg-gradient-to-r from-emerald-500 to-cyan-500 h-2 rounded-full w-full transition-all duration-300"></div>
                </div>
            </div>
        </div>

        <!-- Tip de Inicio -->
        <div class="mb-8 bg-gradient-to-r from-emerald-50 to-cyan-50 border-l-4 border-emerald-400 rounded-r-2xl p-6 shadow-sm">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-emerald-500 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-bold text-emerald-800 mb-2">🚀 ¡Comencemos!</h3>
                    <p class="text-emerald-700 leading-relaxed">
                        <strong>Completa la información básica:</strong> Proporciona detalles precisos para facilitar la búsqueda 
                        y gestión futura. Los campos marcados con (*) son obligatorios para un registro exitoso.
                    </p>
                </div>
            </div>
        </div>

        <!-- Formulario Principal -->
        <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-slate-200">
            <div class="bg-gradient-to-r from-slate-800 to-slate-900 px-8 py-6">
                <h3 class="text-2xl font-bold text-white flex items-center">
                    <svg class="w-7 h-7 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Registro de Nueva Herramienta
                </h3>
                <p class="text-slate-300 mt-1">Completa todos los detalles para un registro perfecto</p>
            </div>

            <form wire:submit="save" class="p-8 space-y-10">
                <!-- Sección 1: Información Básica -->
                <div class="space-y-6">
                    <div class="flex items-center space-x-4 pb-4 border-b border-slate-200">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a1.994 1.994 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-xl font-bold text-slate-800">📋 Información Básica</h4>
                            <p class="text-slate-600">Datos principales de identificación</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                        <!-- Nombre de la Herramienta -->
                        <div class="space-y-3">
                            <label for="nomHer" class="block text-sm font-bold text-slate-700">
                                🏷️ Nombre de la Herramienta <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 8.172V5L8 4z"></path>
                                    </svg>
                                </div>
                                <input type="text" 
                                       wire:model="nomHer" 
                                       id="nomHer" 
                                       maxlength="100"
                                       class="block w-full pl-12 pr-4 py-4 text-lg border-2 border-slate-300 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all bg-slate-50 focus:bg-white shadow-sm hover:shadow-md @error('nomHer') border-red-300 ring-2 ring-red-200 @enderror"
                                       placeholder="Ej: Taladro Eléctrico Industrial Bosch">
                            </div>
                            @error('nomHer')
                                <div class="flex items-center mt-2 text-sm text-red-600">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    {{ $message }}
                                </div>
                            @enderror
                            <p class="text-xs text-slate-500 mt-2">💡 Incluye marca y modelo para mejor identificación</p>
                        </div>

                        <!-- Categoría -->
                        <div class="space-y-3">
                            <label for="catHer" class="block text-sm font-bold text-slate-700">
                                📦 Categoría <span class="text-red-500">*</span>
                            </label>
                            <select wire:model="catHer" 
                                    id="catHer"
                                    class="block w-full px-4 py-4 text-lg border-2 border-slate-300 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all bg-slate-50 focus:bg-white shadow-sm hover:shadow-md @error('catHer') border-red-300 ring-2 ring-red-200 @enderror">
                                <option value="">🔽 Seleccionar categoría</option>
                                <option value="veterinaria">🐾 Veterinaria</option>
                                <option value="ganadera">🐄 Ganadera</option>
                                <option value="agricola">🌾 Agrícola</option>
                                <option value="mantenimiento">🔧 Mantenimiento</option>
                                <option value="transporte">🚛 Transporte</option>
                                <option value="seguridad">🛡️ Seguridad</option>
                            </select>
                            @error('catHer')
                                <div class="flex items-center mt-2 text-sm text-red-600">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                        <!-- Estado -->
                        <div class="space-y-3">
                            <label for="estHer" class="block text-sm font-bold text-slate-700">
                                🎯 Estado Inicial <span class="text-red-500">*</span>
                            </label>
                            <select wire:model="estHer" 
                                    id="estHer"
                                    class="block w-full px-4 py-4 text-lg border-2 border-slate-300 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all bg-slate-50 focus:bg-white shadow-sm hover:shadow-md @error('estHer') border-red-300 ring-2 ring-red-200 @enderror">
                                <option value="bueno">🟢 Bueno - Excelente condición</option>
                                <option value="regular">🟡 Regular - Necesita atención</option>
                                <option value="malo">🔴 Malo - Requiere reparación</option>
                            </select>
                            @error('estHer')
                                <div class="flex items-center mt-2 text-sm text-red-600">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Ubicación -->
                        <div class="space-y-3">
                            <label for="ubiHer" class="block text-sm font-bold text-slate-700">
                                📍 Ubicación Física
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </div>
                                <input type="text" 
                                       wire:model="ubiHer" 
                                       id="ubiHer" 
                                       maxlength="100"
                                       class="block w-full pl-12 pr-4 py-4 text-lg border-2 border-slate-300 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all bg-slate-50 focus:bg-white shadow-sm hover:shadow-md"
                                       placeholder="Ej: Almacén Principal - Estante A3">
                            </div>
                            <p class="text-xs text-slate-500 mt-2">💡 Sé específico para facilitar la búsqueda</p>
                        </div>
                    </div>
                </div>

                <!-- Sección 2: Proveedor -->
                <div class="space-y-6 border-t border-slate-200 pt-8">
                    <div class="flex items-center space-x-4 pb-4 border-b border-slate-200">
                        <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-red-600 rounded-2xl flex items-center justify-center shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-xl font-bold text-slate-800">🏢 Información del Proveedor</h4>
                            <p class="text-slate-600">Asigna un proveedor para mejor trazabilidad</p>
                        </div>
                    </div>
                    
                    <div class="space-y-3">
                        <label for="idProveHer" class="block text-sm font-bold text-slate-700">
                            🏪 Proveedor (Opcional)
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            <select wire:model="idProveHer" 
                                    id="idProveHer"
                                    class="block w-full pl-12 pr-4 py-4 text-lg border-2 border-slate-300 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all bg-slate-50 focus:bg-white shadow-sm hover:shadow-md @error('idProveHer') border-red-300 ring-2 ring-red-200 @enderror">
                                <option value="">🔽 Sin proveedor asignado</option>
                                @foreach($proveedores as $proveedor)
                                    <option value="{{ $proveedor->idProve }}">🏪 {{ $proveedor->nomProve }} - {{ $proveedor->nitProve }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('idProveHer')
                            <div class="flex items-center mt-2 text-sm text-red-600">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                {{ $message }}
                            </div>
                        @enderror
                        <div class="bg-blue-50 border-l-4 border-blue-400 p-3 rounded-r-lg">
                            <p class="text-xs text-blue-700">
                                💡 <strong>Consejo:</strong> Asignar un proveedor facilita el seguimiento de garantías y reposiciones
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Sección 3: Control de Stock -->
                <div class="space-y-6 border-t border-slate-200 pt-8">
                    <div class="flex items-center space-x-4 pb-4 border-b border-slate-200">
                        <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-green-600 rounded-2xl flex items-center justify-center shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-xl font-bold text-slate-800">📊 Control de Inventario</h4>
                            <p class="text-slate-600">Define límites para alertas automáticas</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                        <!-- Stock Mínimo -->
                        <div class="space-y-3">
                            <label for="stockMinHer" class="block text-sm font-bold text-slate-700">
                                📉 Stock Mínimo
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                    </svg>
                                </div>
                                <input type="number" 
                                       wire:model="stockMinHer" 
                                       id="stockMinHer" 
                                       min="0"
                                       class="block w-full pl-12 pr-4 py-4 text-lg border-2 border-slate-300 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all bg-slate-50 focus:bg-white shadow-sm hover:shadow-md @error('stockMinHer') border-red-300 ring-2 ring-red-200 @enderror"
                                       placeholder="0">
                            </div>
                            @error('stockMinHer')
                                <div class="flex items-center mt-2 text-sm text-red-600">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    {{ $message }}
                                </div>
                            @enderror
                            <p class="text-xs text-slate-500 mt-2">🚨 Recibirás alertas cuando el stock baje de este límite</p>
                        </div>

                        <!-- Stock Máximo -->
                        <div class="space-y-3">
                            <label for="stockMaxHer" class="block text-sm font-bold text-slate-700">
                                📈 Stock Máximo
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a7.5 7.5 0 0015 0v-5a1.5 1.5 0 00-3 0m-6-3V11m0-5.5v-1a1.5 1.5 0 013 0v1m0 0V11m0-5.5a1.5 1.5 0 013 0v3m0 0V11"></path>
                                    </svg>
                                </div>
                                <input type="number" 
                                       wire:model="stockMaxHer" 
                                       id="stockMaxHer" 
                                       min="0"
                                       class="block w-full pl-12 pr-4 py-4 text-lg border-2 border-slate-300 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all bg-slate-50 focus:bg-white shadow-sm hover:shadow-md @error('stockMaxHer') border-red-300 ring-2 ring-red-200 @enderror"
                                       placeholder="0">
                            </div>
                            @error('stockMaxHer')
                                <div class="flex items-center mt-2 text-sm text-red-600">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    {{ $message }}
                                </div>
                            @enderror
                            <p class="text-xs text-slate-500 mt-2">💰 Evita el sobrestock y optimiza el capital de trabajo</p>
                        </div>
                    </div>
                </div>

                <!-- Sección 4: Información Adicional -->
                <div class="space-y-6 border-t border-slate-200 pt-8">
                    <div class="flex items-center space-x-4 pb-4 border-b border-slate-200">
                        <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl flex items-center justify-center shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-xl font-bold text-slate-800">📝 Información Adicional</h4>
                            <p class="text-slate-600">Detalles extras para referencia futura</p>
                        </div>
                    </div>
                    
                    <!-- Observaciones -->
                    <div class="space-y-3">
                        <label for="obsHer" class="block text-sm font-bold text-slate-700">
                            💬 Observaciones y Notas
                        </label>
                        <textarea wire:model="obsHer" 
                                  id="obsHer" 
                                  rows="5"
                                  class="block w-full px-4 py-4 text-lg border-2 border-slate-300 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all resize-none bg-slate-50 focus:bg-white shadow-sm hover:shadow-md"
                                  placeholder="Ej: Incluye manual en español, garantía de 2 años, requiere mantenimiento cada 6 meses..."></textarea>
                        <div class="bg-amber-50 border-l-4 border-amber-400 p-3 rounded-r-lg">
                            <p class="text-xs text-amber-700">
                                💡 <strong>Sugerencias:</strong> Incluye especificaciones técnicas, instrucciones especiales, 
                                fechas de garantía, o cualquier detalle que facilite su uso futuro.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="flex justify-end space-x-4 pt-8 border-t border-slate-200">
                    <button type="button" 
                            wire:click="cancel"
                            class="group px-8 py-4 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-2xl transition-all duration-200 flex items-center shadow-lg hover:shadow-xl">
                        <svg class="w-5 h-5 mr-3 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="group px-8 py-4 bg-gradient-to-r from-emerald-500 to-cyan-500 hover:from-emerald-600 hover:to-cyan-600 text-white font-bold rounded-2xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 flex items-center">
                        <svg class="w-5 h-5 mr-3 transform group-hover:rotate-90 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        🚀 Crear Herramienta
                    </button>
                </div>
            </form>
        </div>

        <!-- Tips Finales -->
        <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-2xl p-6">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-blue-500 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-lg font-bold text-blue-800 mb-2">✅ Buenas Prácticas</h4>
                        <ul class="text-blue-700 text-sm space-y-1">
                            <li>• Usa nombres descriptivos y únicos</li>
                            <li>• Asigna ubicaciones específicas</li>
                            <li>• Define stocks realistas</li>
                            <li>• Incluye detalles técnicos relevantes</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-emerald-50 to-green-50 border border-emerald-200 rounded-2xl p-6">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-emerald-500 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-lg font-bold text-emerald-800 mb-2">⚡ Después del Registro</h4>
                        <ul class="text-emerald-700 text-sm space-y-1">
                            <li>• Podrás crear préstamos inmediatamente</li>
                            <li>• Configurar recordatorios de mantenimiento</li>
                            <li>• Generar reportes de uso</li>
                            <li>• Trackear movimientos de inventario</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>