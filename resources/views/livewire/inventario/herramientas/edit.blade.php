<?php
use App\Models\Herramienta;
use App\Models\Proveedor;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component {
    public Herramienta $herramienta;
    public string $nomHer = '';
    public string $catHer = '';
    public string $canHer = '';
    public string $estHer = '';
    public string $ubiHer = '';
    public string $stockMinHer = '';
    public string $stockMaxHer = '';
    public ?int $idProveHer = null;
    public ?string $obsHer = '';
    public string $searchProveedor = '';

    public function mount(Herramienta $herramienta): void
    {
        $this->herramienta = $herramienta;
        $this->fill(
            $herramienta->only([
                'nomHer', 'catHer', 'ubiHer', 'obsHer'
            ])
        );
        $this->canHer = $herramienta->canHer ? (string)$herramienta->canHer : '0';
        $this->estHer = $herramienta->estHer ?? 'bueno';
        $this->stockMinHer = $herramienta->stockMinHer ? (string)$herramienta->stockMinHer : '';
        $this->stockMaxHer = $herramienta->stockMaxHer ? (string)$herramienta->stockMaxHer : '';
        $this->idProveHer = $herramienta->idProveHer;
        $this->searchProveedor = '';
    }

    public function with(): array
    {
        $query = Proveedor::query();
        
        if ($this->searchProveedor) {
            $query->where('nomProve', 'like', "%{$this->searchProveedor}%")
                  ->orWhere('nitProve', 'like', "%{$this->searchProveedor}%");
        }
        
        $proveedores = $query->orderBy('nomProve')->get();
        
        return [
            'proveedores' => $proveedores,
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

    // Validación en tiempo real para campos individuales
    public function validateField($fieldName)
    {
        $this->validateOnly($fieldName, $this->rules());
    }

    // Validación automática cuando los campos cambian
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName, $this->rules());
    }

    public function rules(): array
    {
        return [
            'nomHer' => 'required|string|max:100',
            'catHer' => 'required|string|max:50',
            'canHer' => 'required|integer|min:0',
            'estHer' => 'required|in:bueno,regular,malo',
            'ubiHer' => 'nullable|string|max:100',
            'stockMinHer' => 'nullable|integer|min:0',
            'stockMaxHer' => 'nullable|integer|min:0|gte:stockMinHer',
            'idProveHer' => 'nullable|exists:proveedores,idProve',
            'obsHer' => 'nullable|string'
        ];
    }

    public function messages(): array
    {
        return [
            'nomHer.required' => 'El nombre de la herramienta es obligatorio.',
            'catHer.required' => 'La categoría es obligatoria.',
            'canHer.required' => 'La cantidad es obligatoria.',
            'canHer.integer' => 'La cantidad debe ser un número entero.',
            'estHer.required' => 'El estado es obligatorio.',
            'estHer.in' => 'El estado seleccionado no es válido.',
            'stockMinHer.integer' => 'El stock mínimo debe ser un número entero.',
            'stockMaxHer.integer' => 'El stock máximo debe ser un número entero.',
            'stockMaxHer.gte' => 'El stock máximo debe ser mayor o igual al stock mínimo.',
            'idProveHer.exists' => 'El proveedor seleccionado no es válido.',
        ];
    }

    public function update(): void
    {
        $validated = $this->validate($this->rules());

        try {
            $this->herramienta->update([
                'nomHer' => $this->nomHer,
                'catHer' => $this->catHer,
                'canHer' => $this->canHer ? (int)$this->canHer : 0,
                'estHer' => $this->estHer,
                'ubiHer' => $this->ubiHer ?: null,
                'stockMinHer' => $this->stockMinHer ? (int)$this->stockMinHer : null,
                'stockMaxHer' => $this->stockMaxHer ? (int)$this->stockMaxHer : null,
                'idProveHer' => $this->idProveHer ?: null,
                'obsHer' => $this->obsHer ?: null,
            ]);

            $this->dispatch('actualizacion-exitosa');
            
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al actualizar la herramienta: ' . $e->getMessage()
            ]);
        }
    }

    // Método para resetear el formulario a los valores originales
    public function resetForm(): void
    {
        $this->fill(
            $this->herramienta->only([
                'nomHer', 'catHer', 'ubiHer', 'obsHer'
            ])
        );
        $this->canHer = $this->herramienta->canHer ? (string)$this->herramienta->canHer : '0';
        $this->estHer = $this->herramienta->estHer ?? 'bueno';
        $this->stockMinHer = $this->herramienta->stockMinHer ? (string)$this->herramienta->stockMinHer : '';
        $this->stockMaxHer = $this->herramienta->stockMaxHer ? (string)$this->herramienta->stockMaxHer : '';
        $this->idProveHer = $this->herramienta->idProveHer;
        $this->searchProveedor = '';
        $this->resetErrorBag();
        $this->dispatch('actualizacion-cancelada');
    }

    public function selectProveedor($proveedorId): void
    {
        $this->idProveHer = $proveedorId;
        $this->searchProveedor = '';
    }

    public function clearProveedor(): void
    {
        $this->idProveHer = null;
        $this->searchProveedor = '';
    }
}; ?>

@section('title', 'Editar Herramienta')

<div class="flex items-center justify-center min-h-screen py-3">
    <div class="w-full max-w-7xl mx-auto bg-white/80 backdrop-blur-xl shadow rounded-3xl p-3 relative border border-white/20">
        <!-- Botón Volver -->
        <div class="absolute top-2 right-2">
            <a href="{{ route('inventario.herramientas.index', $herramienta) }}" wire:navigate
               class="group relative inline-flex items-center px-2 py-1 bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                <svg class="w-3 h-3 mr-1 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span class="relative z-10 text-xs">Volver</span>
            </a>
        </div>

        <!-- Encabezado -->
        <div class="text-center mb-3"
             x-data="{ showSuccess: false, showCancel: false }" 
             x-on:actualizacion-exitosa.window="showSuccess = true; showCancel = false; setTimeout(() => showSuccess = false, 3000)" 
             x-on:actualizacion-cancelada.window="showCancel = true; showSuccess = false; setTimeout(() => showCancel = false, 3000)">
            <div class="flex justify-center mb-1">
                <div class="p-2 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl shadow-xl transform rotate-3 hover:rotate-0 transition-transform duration-300">
                    <div class="w-4 h-4 text-white flex items-center justify-center">
                        <i class="fas fa-tools text-sm"></i>
                    </div>
                </div>
            </div>
            <h1 class="text-base font-black bg-gradient-to-r from-gray-900 via-gray-800 to-green-800 bg-clip-text text-transparent mb-1">
                Editar Herramienta
            </h1>
            
            <template x-if="showSuccess">
                <div class="rounded bg-green-100 px-2 py-1 text-green-800 border border-green-400 text-xs mb-1 font-semibold">
                    ¡Herramienta actualizada exitosamente!
                </div>
            </template>

            <template x-if="showCancel">
                <div class="rounded bg-yellow-100 px-2 py-1 text-yellow-800 border border-yellow-400 text-xs mb-1 font-semibold">
                    Cambios descartados. Los datos se han restablecido.
                </div>
            </template>

            <template x-if="!showSuccess && !showCancel">
                <p class="text-gray-600 text-xs">Actualiza la información de la herramienta</p>
            </template>
        </div>

        <!-- Formulario -->
        <form wire:submit="update" class="space-y-2">
            <!-- Fila 1: Información básica -->
            <div class="flex flex-col md:flex-row gap-2">
                <!-- Información Básica -->
                <div class="flex-1 border border-gray-300 rounded-3xl overflow-hidden">
                    <div class="h-1.5 bg-gradient-to-r from-[#000000] to-[#39A900]"></div>
                    <div class="p-2">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl shadow-lg">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xs font-bold text-gray-900">Información Básica</h2>
                                <p class="text-gray-600 text-[10px]">Datos principales de identificación</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            <!-- Nombre de la Herramienta -->
                            <div>
                                <label for="nomHer" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Nombre <span class="text-red-500">*</span>
                                </label>
                                <div class="relative group">
                                    <input type="text"
                                           id="nomHer"
                                           wire:model="nomHer"
                                           wire:blur="validateField('nomHer')"
                                           class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('nomHer') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                                           placeholder="Ej: Taladro Eléctrico Industrial Bosch"
                                           maxlength="100"
                                           required>
                                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-indigo-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('nomHer')
                                <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                    <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>

                            <!-- Categoría -->
                            <div>
                                <label for="catHer" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Categoría <span class="text-red-500">*</span>
                                </label>
                                <div class="relative group">
                                    <select id="catHer"
                                            wire:model="catHer"
                                            wire:blur="validateField('catHer')"
                                            class="cursor-pointer w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl appearance-none text-xs @error('catHer') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror">
                                        <option value="">Seleccionar categoría</option>
                                        <option value="veterinaria">Veterinaria</option>
                                        <option value="ganadera">Ganadera</option>
                                        <option value="agricola">Agrícola</option>
                                        <option value="mantenimiento">Mantenimiento</option>
                                        <option value="transporte">Transporte</option>
                                        <option value="seguridad">Seguridad</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-1.5 pointer-events-none">
                                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </div>
                                @error('catHer')
                                <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                    <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>

                            <!-- Estado -->
                            <div>
                                <label for="estHer" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Estado <span class="text-red-500">*</span>
                                </label>
                                <div class="relative group">
                                    <select id="estHer"
                                            wire:model="estHer"
                                            wire:blur="validateField('estHer')"
                                            class="cursor-pointer w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl appearance-none text-xs @error('estHer') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror">
                                        <option value="bueno">Bueno - Excelente condición</option>
                                        <option value="regular">Regular - Necesita atención</option>
                                        <option value="malo">Malo - Requiere reparación</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-1.5 pointer-events-none">
                                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </div>
                                @error('estHer')
                                <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                    <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>

                            <!-- Ubicación -->
                            <div>
                                <label for="ubiHer" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Ubicación
                                </label>
                                <div class="relative group">
                                    <input type="text"
                                           id="ubiHer"
                                           wire:model="ubiHer"
                                           wire:blur="validateField('ubiHer')"
                                           class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('ubiHer') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                                           placeholder="Ej: Almacén Principal - Estante A3"
                                           maxlength="100">
                                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-indigo-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('ubiHer')
                                <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                    <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información del Proveedor -->
                <div class="flex-1 border border-gray-300 rounded-3xl overflow-hidden">
                    <div class="h-1.5 bg-gradient-to-r from-[#39A900] to-[#000000]"></div>
                    <div class="p-2">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-orange-500 to-red-600 rounded-xl shadow-lg">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xs font-bold text-gray-900">Información del Proveedor</h2>
                                <p class="text-gray-600 text-[10px]">Asigna un proveedor para mejor trazabilidad</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-2">
                            <!-- Proveedor Seleccionado -->
                            @if($idProveHer)
                                @php
                                    $proveedorSeleccionado = $proveedores->find($idProveHer);
                                @endphp
                                @if($proveedorSeleccionado)
                                    <div class="mb-2 p-2 bg-green-50 border border-green-200 rounded-2xl">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-6 w-6">
                                                    <div class="h-6 w-6 rounded-full bg-green-100 text-green-600 flex items-center justify-center">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                                        </svg>
                                                    </div>
                                                </div>
                                                <div class="ml-2">
                                                    <p class="text-xs font-medium text-green-800">{{ $proveedorSeleccionado->nomProve }}</p>
                                                    <p class="text-[10px] text-green-600">{{ $proveedorSeleccionado->nitProve }}</p>
                                                </div>
                                            </div>
                                            <button type="button" 
                                                    wire:click="clearProveedor"
                                                    class="cursor-pointer text-green-600 hover:text-green-800">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            @else
                                <!-- Búsqueda de Proveedor -->
                                <div class="space-y-2">
                                    <div class="flex space-x-1">
                                        <div class="flex-1">
                                            <input type="text" 
                                                   wire:model.live.debounce.300ms="searchProveedor"
                                                   class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 text-xs"
                                                   placeholder="Buscar proveedor por nombre o NIT...">
                                        </div>
                                    </div>

                                    <!-- Lista de Proveedores -->
                                    @if($searchProveedor && $proveedores->count() > 0)
                                        <div class="border border-gray-300 rounded-2xl max-h-32 overflow-y-auto">
                                            @foreach($proveedores as $proveedor)
                                                <button type="button" 
                                                        wire:click="selectProveedor({{ $proveedor->idProve }})"
                                                        class="cursor-pointer w-full px-2 py-1.5 text-left hover:bg-gray-50 border-b border-gray-100 last:border-b-0 flex items-center text-xs">
                                                    <div class="flex-shrink-0 h-5 w-5">
                                                        <div class="h-5 w-5 rounded-full bg-gray-100 text-gray-600 flex items-center justify-center">
                                                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                                            </svg>
                                                        </div>
                                                    </div>
                                                    <div class="ml-2">
                                                        <p class="text-xs font-medium text-gray-900">{{ $proveedor->nomProve }}</p>
                                                        <p class="text-[10px] text-gray-500">{{ $proveedor->nitProve }}</p>
                                                    </div>
                                                </button>
                                            @endforeach
                                        </div>
                                    @elseif($searchProveedor && $proveedores->count() === 0)
                                        <div class="text-center py-2 text-gray-500 text-[10px]">
                                            No se encontraron proveedores con ese criterio
                                        </div>
                                    @endif
                                </div>
                            @endif
                            @error('idProveHer')
                                <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                    <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fila 2: Control de Stock y Observaciones -->
            <div class="flex flex-col md:flex-row gap-2">
                <!-- Control de Stock -->
                <div class="flex-1 border border-gray-300 rounded-3xl overflow-hidden">
                    <div class="h-1.5 bg-gradient-to-r from-[#000000] to-[#39A900]"></div>
                    <div class="p-2">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-emerald-500 to-green-600 rounded-xl shadow-lg">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xs font-bold text-gray-900">Control de Stock</h2>
                                <p class="text-gray-600 text-[10px]">Define límites para alertas automáticas</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                            <!-- Cantidad -->
                            <div>
                                <label for="canHer" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Cantidad <span class="text-red-500">*</span>
                                </label>
                                <div class="relative group">
                                    <input type="number"
                                           id="canHer"
                                           wire:model="canHer"
                                           wire:blur="validateField('canHer')"
                                           class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('canHer') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                                           placeholder="0"
                                           min="0"
                                           required>
                                    <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/5 to-green-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('canHer')
                                <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                    <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>

                            <!-- Stock Mínimo -->
                            <div>
                                <label for="stockMinHer" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Stock Mínimo
                                </label>
                                <div class="relative group">
                                    <input type="number"
                                           id="stockMinHer"
                                           wire:model="stockMinHer"
                                           wire:blur="validateField('stockMinHer')"
                                           class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('stockMinHer') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                                           placeholder="0"
                                           min="0">
                                    <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/5 to-green-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('stockMinHer')
                                <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                    <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>

                            <!-- Stock Máximo -->
                            <div>
                                <label for="stockMaxHer" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Stock Máximo
                                </label>
                                <div class="relative group">
                                    <input type="number"
                                           id="stockMaxHer"
                                           wire:model="stockMaxHer"
                                           wire:blur="validateField('stockMaxHer')"
                                           class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('stockMaxHer') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                                           placeholder="0"
                                           min="0">
                                    <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/5 to-green-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('stockMaxHer')
                                <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                    <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Observaciones -->
                <div class="flex-1 border border-gray-300 rounded-3xl overflow-hidden">
                    <div class="h-1.5 bg-gradient-to-r from-[#39A900] to-[#000000]"></div>
                    <div class="p-2">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl shadow-lg">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xs font-bold text-gray-900">Observaciones</h2>
                                <p class="text-gray-600 text-[10px]">Detalles extras para referencia futura</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-2">
                            <!-- Observaciones -->
                            <div>
                                <label for="obsHer" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Observaciones y Notas
                                </label>
                                <div class="relative group">
                                    <textarea id="obsHer"
                                              wire:model="obsHer"
                                              wire:blur="validateField('obsHer')"
                                              rows="4"
                                              class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('obsHer') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                                              placeholder="Ej: Incluye manual en español, garantía de 2 años, requiere mantenimiento cada 6 meses..."></textarea>
                                    <div class="absolute inset-0 bg-gradient-to-r from-purple-500/5 to-pink-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('obsHer')
                                <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                    <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones -->
            <div class="flex justify-center space-x-2 pt-2">
                <button type="button" wire:click="resetForm"
                        class="cursor-pointer group relative inline-flex items-center justify-center px-2.5 py-1 bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                    <svg class="w-3 h-3 mr-1.5 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    <span class="relative z-10 text-xs">Cancelar</span>
                </button>
                <button type="submit"
                        class="cursor-pointer group relative inline-flex items-center justify-center px-2.5 py-1 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                    <svg class="w-3 h-3 mr-1.5 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="relative z-10 text-xs">Actualizar Herramienta</span>
                </button>
            </div>
        </form>
    </div>
</div>