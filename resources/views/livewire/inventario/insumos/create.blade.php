<?php
use App\Models\Insumo;
use App\Models\Proveedor;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.auth')] class extends Component {
    use WithFileUploads;

    // Propiedades del formulario
    public string $nomIns = '';
    public string $tipIns = '';
    public ?string $marIns = null;
    public string $uniIns = '';
    public ?string $fecVenIns = null;
    public ?float $stockMinIns = null;
    public ?float $stockMaxIns = null;
    public ?int $idProveIns = null;
    public string $estIns = 'disponible';
    public ?string $obsIns = null;
    
    // Datos adicionales
    public $proveedores;
    public $showStockWarning = false;
    public $vencimientoAlert = null;

    // Inicialización
    public function mount(): void
    {
        $this->proveedores = Proveedor::all();
    }

    // Reglas de validación
    public function rules(): array
    {
        return [
            'nomIns' => 'required|string|max:150',
            'tipIns' => 'required|string|max:50',
            'marIns' => 'nullable|string|max:100',
            'uniIns' => 'required|string|max:50',
            'fecVenIns' => 'nullable|date',
            'stockMinIns' => 'nullable|numeric|min:0',
            'stockMaxIns' => 'nullable|numeric|min:0',
            'idProveIns' => 'nullable|exists:proveedores,idProve',
            'estIns' => 'required|in:disponible,agotado,vencido',
            'obsIns' => 'nullable|string'
        ];
    }

    // Validación en tiempo real
    public function updated($property, $value): void
    {
        $this->validateOnly($property);

        if (in_array($property, ['stockMinIns', 'stockMaxIns']) && $this->stockMinIns && $this->stockMaxIns) {
            $this->showStockWarning = $this->stockMinIns > $this->stockMaxIns;
        }

        if ($property === 'fecVenIns' && $value) {
            $this->checkVencimiento($value);
        }
    }

    // Verificar fecha de vencimiento
    protected function checkVencimiento($fecha): void
    {
        $hoy = now();
        $fechaVencimiento = \Carbon\Carbon::parse($fecha);
        $diasRestantes = $hoy->diffInDays($fechaVencimiento, false);

        if ($diasRestantes <= 0) {
            $this->vencimientoAlert = [
                'type' => 'error',
                'message' => 'Este insumo ya está vencido',
                'days' => abs($diasRestantes)
            ];
            $this->estIns = 'vencido';
        } elseif ($diasRestantes <= 30) {
            $this->vencimientoAlert = [
                'type' => 'warning',
                'message' => "Este insumo vencerá en $diasRestantes días",
                'days' => $diasRestantes
            ];
        } elseif ($diasRestantes <= 90) {
            $this->vencimientoAlert = [
                'type' => 'info',
                'message' => "Este insumo vencerá en aproximadamente " . round($diasRestantes/30) . " meses",
                'days' => $diasRestantes
            ];
        } else {
            $this->vencimientoAlert = null;
        }
    }

    // Guardar el insumo
    public function save(): void
    {
        $validated = $this->validate();

        try {
            Insumo::create($validated);
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Insumo creado exitosamente'
            ]);
            
            $this->redirect(route('inventario.insumos.index'), navigate: true);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al crear el insumo: ' . $e->getMessage()
            ]);
        }
    }

    // Consejos por tipo de insumo
    public function getConsejosProperty(): array
    {
        $consejos = [
            'medicamento veterinario' => [
                'titulo' => 'Medicamentos Veterinarios',
                'icon' => 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z',
                'color' => 'text-blue-600',
                'consejos' => [
                    'Registra siempre la concentración (ej: 10mg/ml)',
                    'Verifica fechas de vencimiento estrictamente',
                    'Mantén registro de lotes para trazabilidad',
                    'Almacena según especificaciones del fabricante'
                ],
                'unidades' => ['ml', 'dosis', 'frascos', 'unidades']
            ],
            'concentrado' => [
                'titulo' => 'Concentrados',
                'icon' => 'M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z',
                'color' => 'text-green-600',
                'consejos' => [
                    'Especifica edad del ganado (terneros, adultos)',
                    'Registra composición nutricional',
                    'Controla humedad en almacenamiento',
                    'Rota inventario por fecha de vencimiento'
                ],
                'unidades' => ['kg', 'bultos', 'toneladas']
            ],
            'vacuna' => [
                'titulo' => 'Vacunas',
                'icon' => 'M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2m16-6a5 5 0 100-10 5 5 0 000 10zm-8-3a3 3 0 100-6 3 3 0 000 6z',
                'color' => 'text-red-600',
                'consejos' => [
                    'Mantén cadena de frío estrictamente',
                    'Registra número de dosis por frasco',
                    'Controla fecha de vencimiento muy de cerca',
                    'Documenta tiempo de retiro si aplica'
                ],
                'unidades' => ['dosis', 'frascos', 'unidades']
            ],
            'vitamina' => [
                'titulo' => 'Vitaminas',
                'icon' => 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z',
                'color' => 'text-yellow-600',
                'consejos' => [
                    'Especifica si es hidrosoluble o liposoluble',
                    'Registra dosis recomendada por animal',
                    'Verifica compatibilidad con otros productos',
                    'Almacena en lugar fresco y seco'
                ],
                'unidades' => ['ml', 'g', 'sobres', 'tabletas']
            ],
            'suplemento' => [
                'titulo' => 'Suplementos',
                'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
                'color' => 'text-purple-600',
                'consejos' => [
                    'Detalla composición mineral',
                    'Especifica consumo diario recomendado',
                    'Registra si es para época específica',
                    'Controla mezcla con otros suplementos'
                ],
                'unidades' => ['kg', 'g', 'sobres', 'bultos']
            ]
        ];

        return $consejos[$this->tipIns] ?? [
            'titulo' => 'Consejos Generales',
            'icon' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            'color' => 'text-gray-600',
            'consejos' => [
                'Verifica siempre las fechas de vencimiento',
                'Los lotes ayudan a trazabilidad',
                'Define stock mínimo para alertas automáticas',
                'El stock inicial se registra después de crear el insumo'
            ],
            'unidades' => ['kg', 'g', 'ml', 'unidades']
        ];
    }
}; ?>

@section('title', 'Nuevo Insumo')

<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                        <svg class="w-8 h-8 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Nuevo Insumo
                    </h1>
                    <p class="mt-1 text-sm text-gray-600">Registra un nuevo insumo en el inventario</p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <a href="{{ route('inventario.insumos.index') }}" wire:navigate
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
                    <div class="px-6 py-4 bg-green-600 rounded-t-lg">
                        <h3 class="text-lg font-medium text-white flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                            </svg>
                            Información del Insumo
                        </h3>
                    </div>
                    <div class="p-6">
                        <form wire:submit="save">
                            <!-- Nombre y Tipo -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label for="nomIns" class="block text-sm font-medium text-gray-700 mb-2">
                                        Nombre del Insumo <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" 
                                           id="nomIns" 
                                           wire:model="nomIns" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 @error('nomIns') border-red-500 @enderror"
                                           placeholder="Ej: Vacuna contra Aftosa"
                                           required>
                                    @error('nomIns')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="tipIns" class="block text-sm font-medium text-gray-700 mb-2">
                                        Tipo de Insumo <span class="text-red-500">*</span>
                                    </label>
                                    <select id="tipIns" 
                                            wire:model="tipIns" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 @error('tipIns') border-red-500 @enderror"
                                            required>
                                        <option value="">Seleccionar tipo</option>
                                        <option value="medicamento veterinario">Medicamento Veterinario</option>
                                        <option value="concentrado">Concentrado</option>
                                        <option value="vacuna">Vacuna</option>
                                        <option value="vitamina">Vitamina</option>
                                        <option value="suplemento">Suplemento</option>
                                        <option value="desparasitante">Desparasitante</option>
                                        <option value="antibiotico">Antibiótico</option>
                                        <option value="sal mineralizada">Sal Mineralizada</option>
                                    </select>
                                    @error('tipIns')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Marca y Unidad de Medida -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label for="marIns" class="block text-sm font-medium text-gray-700 mb-2">Marca</label>
                                    <input type="text" 
                                           id="marIns" 
                                           wire:model="marIns" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 @error('marIns') border-red-500 @enderror"
                                           placeholder="Ej: Zoetis, Bayer">
                                    @error('marIns')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="uniIns" class="block text-sm font-medium text-gray-700 mb-2">
                                        Unidad de Medida <span class="text-red-500">*</span>
                                    </label>
                                    <select id="uniIns" 
                                            wire:model="uniIns" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 @error('uniIns') border-red-500 @enderror"
                                            required>
                                        <option value="">Seleccionar unidad</option>
                                        <option value="kg">Kilogramos (kg)</option>
                                        <option value="g">Gramos (g)</option>
                                        <option value="litros">Litros (L)</option>
                                        <option value="ml">Mililitros (ml)</option>
                                        <option value="dosis">Dosis</option>
                                        <option value="unidades">Unidades</option>
                                        <option value="frascos">Frascos</option>
                                        <option value="sobres">Sobres</option>
                                        <option value="tabletas">Tabletas</option>
                                        <option value="bultos">Bultos</option>
                                    </select>
                                    @error('uniIns')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Fechas - Solo vencimiento -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label for="fecVenIns" class="block text-sm font-medium text-gray-700 mb-2">
                                        Fecha de Vencimiento
                                    </label>
                                    <input type="date" 
                                           id="fecVenIns" 
                                           wire:model="fecVenIns" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 @error('fecVenIns') border-red-500 @enderror">
                                    @error('fecVenIns')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    <p class="mt-1 text-xs text-gray-500">
                                        <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                        </svg>
                                        Para alertas de vencimiento
                                    </p>
                                </div>
                                <div>
                                    <!-- Campo vacío para mantener el grid -->
                                </div>
                            </div>

                            <!-- Alertas de Vencimiento -->
                            @if($vencimientoAlert)
                            <div class="mb-6">
                                <div class="bg-{{ $vencimientoAlert['type'] }}-50 border border-{{ $vencimientoAlert['type'] }}-200 text-{{ $vencimientoAlert['type'] }}-800 px-4 py-3 rounded-lg flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div>
                                        <strong>{{ ucfirst($vencimientoAlert['type']) }}:</strong> {{ $vencimientoAlert['message'] }}
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Control de Stock -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                                <div>
                                    <label for="stockMinIns" class="block text-sm font-medium text-gray-700 mb-2">Stock Mínimo</label>
                                    <input type="number" 
                                           id="stockMinIns" 
                                           wire:model="stockMinIns" 
                                           min="0" 
                                           step="0.01"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 @error('stockMinIns') border-red-500 @enderror"
                                           placeholder="Cantidad mínima">
                                    @error('stockMinIns')
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
                                    <label for="stockMaxIns" class="block text-sm font-medium text-gray-700 mb-2">Stock Máximo</label>
                                    <input type="number" 
                                           id="stockMaxIns" 
                                           wire:model="stockMaxIns" 
                                           min="0" 
                                           step="0.01"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 @error('stockMaxIns') border-red-500 @enderror"
                                           placeholder="Cantidad máxima">
                                    @error('stockMaxIns')
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
                                    <label for="idProveIns" class="block text-sm font-medium text-gray-700 mb-2">Proveedor</label>
                                    <select id="idProveIns" 
                                            wire:model="idProveIns" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 @error('idProveIns') border-red-500 @enderror">
                                        <option value="">Sin proveedor</option>
                                        @foreach($proveedores as $proveedor)
                                            <option value="{{ $proveedor->idProve }}">
                                                {{ $proveedor->nomProve }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('idProveIns')
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

                            <!-- Estado -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                                <div>
                                    <label for="estIns" class="block text-sm font-medium text-gray-700 mb-2">
                                        Estado <span class="text-red-500">*</span>
                                    </label>
                                    <select id="estIns" 
                                            wire:model="estIns" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 @error('estIns') border-red-500 @enderror"
                                            required>
                                        <option value="disponible">Disponible</option>
                                        <option value="agotado">Agotado</option>
                                        <option value="vencido">Vencido</option>
                                    </select>
                                    @error('estIns')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="md:col-span-2">
                                    @if($showStockWarning)
                                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm text-yellow-700">
                                                    El stock mínimo no puede ser mayor al stock máximo
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Observaciones -->
                            <div class="mb-6">
                                <label for="obsIns" class="block text-sm font-medium text-gray-700 mb-2">Observaciones</label>
                                <textarea id="obsIns" 
                                          wire:model="obsIns" 
                                          rows="4" 
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 @error('obsIns') border-red-500 @enderror"
                                          placeholder="Observaciones adicionales sobre el insumo, modo de uso, contraindicaciones, etc..."></textarea>
                                @error('obsIns')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Botones -->
                            <div class="flex items-center justify-end space-x-4">
                                <a href="{{ route('inventario.insumos.index') }}" wire:navigate
                                   class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium rounded-lg transition duration-150 ease-in-out">
                                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    Cancelar
                                </a>
                                <button type="submit" 
                                        class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition duration-150 ease-in-out">
                                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                                    </svg>
                                    Guardar Insumo
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Panel de Ayuda -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Consejos por Tipo -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 bg-yellow-500 rounded-t-lg">
                        <h3 class="text-lg font-medium text-white flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                            </svg>
                            Consejos por Tipo
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <h4 class="font-medium {{ $this->consejos['color'] }} flex items-center mb-3">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $this->consejos['icon'] }}"></path>
                                </svg>
                                {{ $this->consejos['titulo'] }}
                            </h4>
                            <div class="space-y-2">
                                @foreach($this->consejos['consejos'] as $consejo)
                                <div class="flex items-start text-sm text-gray-600">
                                    <svg class="w-4 h-4 {{ $this->consejos['color'] }} mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    {{ $consejo }}
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Unidades Recomendadas -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 bg-blue-600 rounded-t-lg">
                        <h3 class="text-lg font-medium text-white flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                            Unidades por Tipo
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3 text-sm">
                            <div>
                                <h5 class="font-medium text-gray-900 text-blue-600">Medicamentos/Vacunas:</h5>
                                <p class="text-gray-600">ml, dosis, frascos, unidades</p>
                            </div>
                            <div>
                                <h5 class="font-medium text-gray-900 text-green-600">Concentrados:</h5>
                                <p class="text-gray-600">kg, bultos (40kg), toneladas</p>
                            </div>
                            <div>
                                <h5 class="font-medium text-gray-900 text-yellow-600">Vitaminas/Suplementos:</h5>
                                <p class="text-gray-600">ml, g, kg, sobres, tabletas</p>
                            </div>
                            <div>
                                <h5 class="font-medium text-gray-900 text-purple-600">Sal Mineralizada:</h5>
                                <p class="text-gray-600">kg, bultos (25kg), sacos</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Proveedores Disponibles -->
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
                                    wire:click="$set('idProveIns', {{ $proveedor->idProve }})"
                                    class="text-green-600 hover:text-green-800 text-sm font-medium">
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

                <!-- Información Importante -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0118 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Importante</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Verifica siempre las fechas de vencimiento</li>
                                    <li>Los lotes ayudan a trazabilidad</li>
                                    <li>Define stock mínimo para alertas automáticas</li>
                                    <li>El stock inicial se registra después de crear el insumo</li>
                                    <li>Los stocks se calcularán con el módulo de Movimientos</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('livewire:initialized', () => {
    // Auto-capitalizar primera letra del nombre
    document.getElementById('nomIns').addEventListener('blur', function() {
        @this.set('nomIns', this.value.charAt(0).toUpperCase() + this.value.slice(1));
    });

    // Confirmación antes de guardar
    Livewire.on('confirmSave', (event) => {
        Swal.fire({
            title: '¿Está seguro?',
            text: "¿Desea guardar este nuevo insumo?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, guardar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Livewire.dispatch('save');
            }
        });
    });
});
</script>
@endpush