<?php
// resources/views/livewire/inventario/insumos/edit.blade.php

use App\Models\Insumo;
use App\Models\Proveedor;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component {
    public Insumo $insumo;
    public $proveedores;
    
    // Propiedades del formulario
    public string $nomIns;
    public string $tipIns;
    public ?string $marIns = null;
    public string $uniIns;
    public ?string $fecVenIns = null;
    public ?float $stockMinIns = null;
    public ?float $stockMaxIns = null;
    public ?int $idProveIns = null;
    public string $estIns;
    public ?string $obsIns = null;
    
    // Información de tipos de insumo
    public array $infoTipos = [
        'medicamento veterinario' => [
            'titulo' => 'Medicamento Veterinario',
            'color' => 'text-blue-600',
            'info' => [
                'Verificar tiempo de retiro antes del sacrificio',
                'Seguir estrictamente la dosis prescrita',
                'Almacenar según especificaciones del fabricante',
                'Documentar aplicaciones en registro sanitario'
            ]
        ],
        'concentrado' => [
            'titulo' => 'Concentrado',
            'color' => 'text-green-600',
            'info' => [
                'Verificar humedad de almacenamiento (<12%)',
                'Rotación de inventario por fecha (FIFO)',
                'Controlar plagas en área de almacenamiento',
                'Verificar composición nutricional en etiqueta'
            ]
        ],
        'vacuna' => [
            'titulo' => 'Vacuna',
            'color' => 'text-red-600',
            'info' => [
                'Mantener cadena de frío (2-8°C)',
                'No congelar - destruye efectividad',
                'Documentar fecha y hora de aplicación',
                'Respetar calendario de revacunación'
            ]
        ],
        'vitamina' => [
            'titulo' => 'Vitamina',
            'color' => 'text-yellow-600',
            'info' => [
                'Proteger de luz directa',
                'Verificar si es hidrosoluble o liposoluble',
                'Controlar dosis para evitar hipervitaminosis',
                'Verificar compatibilidad con otros productos'
            ]
        ],
        'suplemento' => [
            'titulo' => 'Suplemento',
            'color' => 'text-purple-600',
            'info' => [
                'Ajustar según peso y edad del animal',
                'Verificar interacciones con otros suplementos',
                'Controlar consumo diario recomendado',
                'Almacenar en lugar seco y fresco'
            ]
        ],
        'desparasitante' => [
            'titulo' => 'Desparasitante',
            'color' => 'text-indigo-600',
            'info' => [
                'Verificar dosis según peso del animal',
                'Respetar intervalo entre aplicaciones',
                'Documentar fecha de aplicación',
                'Verificar contraindicaciones'
            ]
        ],
        'antibiotico' => [
            'titulo' => 'Antibiótico',
            'color' => 'text-pink-600',
            'info' => [
                'Respetar tiempo de retiro',
                'Completar ciclo de tratamiento',
                'Verificar sensibilidad bacteriana',
                'Evitar uso indiscriminado'
            ]
        ],
        'sal mineralizada' => [
            'titulo' => 'Sal Mineralizada',
            'color' => 'text-gray-600',
            'info' => [
                'Proveer acceso libre',
                'Proteger de la humedad',
                'Verificar consumo diario',
                'Revisar composición mineral'
            ]
        ]
    ];
    
    // Estado del formulario
    public bool $formModificado = false;
    public ?string $alertaVencimiento = null;
    public string $alertaVencimientoColor = '';

    public function mount(Insumo $insumo): void
    {
        $this->insumo = $insumo;
        $this->proveedores = Proveedor::all();
        
        // Inicializar propiedades del formulario
        $this->fill($insumo->only([
            'nomIns', 'tipIns', 'marIns', 'uniIns', 'estIns', 'obsIns',
            'stockMinIns', 'stockMaxIns', 'idProveIns'
        ]));
        
        if ($insumo->fecVenIns) {
            $this->fecVenIns = $insumo->fecVenIns->format('Y-m-d');
        }
        
        $this->verificarVencimiento();
    }
    
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
    
    public function updated($property): void
    {
        $this->formModificado = true;
        
        if ($property === 'tipIns') {
            $this->actualizarUnidades();
        }
        
        if ($property === 'fecVenIns') {
            $this->verificarVencimiento();
        }
        
        if (in_array($property, ['stockMinIns', 'stockMaxIns'])) {
            $this->validarStock();
        }
    }
    
    public function actualizarUnidades(): void
    {
        // Puedes agregar lógica para actualizar unidades basado en el tipo si es necesario
    }
    
    public function verificarVencimiento(): void
    {
        if (!$this->fecVenIns) {
            $this->alertaVencimiento = null;
            return;
        }
        
        $fecha = new \DateTime($this->fecVenIns);
        $hoy = new \DateTime();
        
        if ($fecha <= $hoy) {
            $this->alertaVencimiento = '¡Atención! Esta fecha de vencimiento ya pasó. El insumo no debe ser utilizado.';
            $this->alertaVencimientoColor = 'bg-red-100 text-red-800';
            return;
        }
        
        $diffTime = $hoy->diff($fecha);
        $diffDays = $diffTime->days;
        
        if ($diffDays <= 7) {
            $this->alertaVencimiento = "Este insumo vencerá en {$diffDays} días. Considera su uso prioritario.";
            $this->alertaVencimientoColor = 'bg-red-100 text-red-800';
        } elseif ($diffDays <= 30) {
            $this->alertaVencimiento = "Este insumo vencerá en {$diffDays} días. Considera su uso prioritario.";
            $this->alertaVencimientoColor = 'bg-yellow-100 text-yellow-800';
        } elseif ($diffDays <= 90) {
            $this->alertaVencimiento = "Este insumo vencerá en {$diffDays} días (".round($diffDays/30)." meses aproximadamente.";
            $this->alertaVencimientoColor = 'bg-blue-100 text-blue-800';
        } else {
            $this->alertaVencimiento = null;
        }
    }
    
    public function validarStock(): void
    {
        $min = $this->stockMinIns ?? 0;
        $max = $this->stockMaxIns ?? 0;
        
        if ($min > 0 && $max > 0 && $min > $max) {
            $this->addError('stockMaxIns', 'El stock máximo debe ser mayor al mínimo');
        } else {
            $this->resetErrorBag('stockMaxIns');
        }
    }
    
    public function update(): void
    {
        $this->validate();
        
        try {
            $data = [
                'nomIns' => $this->nomIns,
                'tipIns' => $this->tipIns,
                'marIns' => $this->marIns,
                'uniIns' => $this->uniIns,
                'fecVenIns' => $this->fecVenIns,
                'stockMinIns' => $this->stockMinIns,
                'stockMaxIns' => $this->stockMaxIns,
                'idProveIns' => $this->idProveIns,
                'estIns' => $this->estIns,
                'obsIns' => $this->obsIns
            ];
            
            $this->insumo->update($data);
            
            session()->flash('success', 'Insumo actualizado exitosamente.');
            $this->redirect(route('inventario.insumos.show', $this->insumo->idIns), navigate: true);
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error al actualizar el insumo: ' . $e->getMessage());
        }
    }
    
    public function resetForm(): void
    {
        $this->fill($this->insumo->only([
            'nomIns', 'tipIns', 'marIns', 'uniIns', 'estIns', 'obsIns',
            'stockMinIns', 'stockMaxIns', 'idProveIns'
        ]));
        
        if ($this->insumo->fecVenIns) {
            $this->fecVenIns = $this->insumo->fecVenIns->format('Y-m-d');
        }
        
        $this->resetErrorBag();
        $this->formModificado = false;
        $this->verificarVencimiento();
    }
}; ?>

@section('title', 'Editar Insumo')

<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                        @php
                            $iconoColor = match(strtolower($insumo->tipIns)) {
                                'medicamento veterinario', 'medicamento' => 'text-blue-600',
                                'concentrado' => 'text-green-600',
                                'vacuna' => 'text-red-600',
                                'vitamina' => 'text-yellow-600',
                                'suplemento' => 'text-purple-600',
                                default => 'text-gray-600'
                            };
                        @endphp
                        <svg class="w-8 h-8 {{ $iconoColor }} mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Editar Insumo
                    </h1>
                    <p class="mt-1 text-sm text-gray-600">ID: {{ $insumo->idIns }} - {{ $insumo->nomIns }}</p>
                    <div class="mt-2 flex items-center text-xs text-gray-500">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Última actualización: {{ $insumo->updated_at->format('d/m/Y H:i') }}
                    </div>
                </div>
                <div class="mt-4 sm:mt-0 flex space-x-3">
                    <a href="{{ route('inventario.insumos.show', $insumo->idIns) }}" 
                       wire:navigate
                       class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg shadow-sm transition duration-150 ease-in-out">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        Ver Detalle
                    </a>
                    <a href="{{ route('inventario.insumos.index') }}" 
                       wire:navigate
                       class="inline-flex items-center px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium rounded-lg shadow-sm transition duration-150 ease-in-out">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Volver
                    </a>
                </div>
            </div>
        </div>

        <!-- Alertas -->
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-600 px-4 py-3 rounded-lg flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        <!-- Nota Temporal -->
        <div class="mb-6 bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-lg flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
            </svg>
            <div>
                <strong>Información:</strong> El stock actual aparece en 0 temporalmente. Se calculará automáticamente cuando implementemos el módulo de Movimientos de Inventario.
            </div>
        </div>

        <!-- Formulario -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Formulario Principal -->
            <div class="lg:col-span-2">
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 bg-green-600 rounded-t-lg">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-medium text-white flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                                </svg>
                                Información del Insumo
                            </h3>
                            <span class="bg-green-100 text-green-800 text-sm font-medium px-2.5 py-0.5 rounded">
                                ID: {{ $insumo->idIns }}
                            </span>
                        </div>
                    </div>
                    <div class="p-6">
                        <form wire:submit="update">
                            @csrf
                            
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
                                            required
                                            wire:change="actualizarUnidades">
                                        <option value="">Seleccionar tipo</option>
                                        <option value="medicamento veterinario" {{ old('tipIns', $tipIns) == 'medicamento veterinario' ? 'selected' : '' }}>Medicamento Veterinario</option>
                                        <option value="concentrado" {{ old('tipIns', $tipIns) == 'concentrado' ? 'selected' : '' }}>Concentrado</option>
                                        <option value="vacuna" {{ old('tipIns', $tipIns) == 'vacuna' ? 'selected' : '' }}>Vacuna</option>
                                        <option value="vitamina" {{ old('tipIns', $tipIns) == 'vitamina' ? 'selected' : '' }}>Vitamina</option>
                                        <option value="suplemento" {{ old('tipIns', $tipIns) == 'suplemento' ? 'selected' : '' }}>Suplemento</option>
                                        <option value="desparasitante" {{ old('tipIns', $tipIns) == 'desparasitante' ? 'selected' : '' }}>Desparasitante</option>
                                        <option value="antibiotico" {{ old('tipIns', $tipIns) == 'antibiotico' ? 'selected' : '' }}>Antibiótico</option>
                                        <option value="sal mineralizada" {{ old('tipIns', $tipIns) == 'sal mineralizada' ? 'selected' : '' }}>Sal Mineralizada</option>
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
                                        <option value="kg" {{ old('uniIns', $uniIns) == 'kg' ? 'selected' : '' }}>Kilogramos (kg)</option>
                                        <option value="g" {{ old('uniIns', $uniIns) == 'g' ? 'selected' : '' }}>Gramos (g)</option>
                                        <option value="litros" {{ old('uniIns', $uniIns) == 'litros' ? 'selected' : '' }}>Litros (L)</option>
                                        <option value="ml" {{ old('uniIns', $uniIns) == 'ml' ? 'selected' : '' }}>Mililitros (ml)</option>
                                        <option value="dosis" {{ old('uniIns', $uniIns) == 'dosis' ? 'selected' : '' }}>Dosis</option>
                                        <option value="unidades" {{ old('uniIns', $uniIns) == 'unidades' ? 'selected' : '' }}>Unidades</option>
                                        <option value="frascos" {{ old('uniIns', $uniIns) == 'frascos' ? 'selected' : '' }}>Frascos</option>
                                        <option value="sobres" {{ old('uniIns', $uniIns) == 'sobres' ? 'selected' : '' }}>Sobres</option>
                                        <option value="tabletas" {{ old('uniIns', $uniIns) == 'tabletas' ? 'selected' : '' }}>Tabletas</option>
                                        <option value="bultos" {{ old('uniIns', $uniIns) == 'bultos' ? 'selected' : '' }}>Bultos</option>
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
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 @error('fecVenIns') border-red-500 @enderror"
                                           wire:change="verificarVencimiento">
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
                            @if($alertaVencimiento)
                                <div class="mb-6 p-4 {{ $alertaVencimientoColor }} rounded-lg flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    <div>{{ $alertaVencimiento }}</div>
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
                                            <option value="{{ $proveedor->idProve }}" 
                                                    {{ old('idProveIns', $idProveIns) == $proveedor->idProve ? 'selected' : '' }}>
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
                                        <option value="disponible" {{ old('estIns', $estIns) == 'disponible' ? 'selected' : '' }}>Disponible</option>
                                        <option value="agotado" {{ old('estIns', $estIns) == 'agotado' ? 'selected' : '' }}>Agotado</option>
                                        <option value="vencido" {{ old('estIns', $estIns) == 'vencido' ? 'selected' : '' }}>Vencido</option>
                                    </select>
                                    @error('estIns')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="md:col-span-2">
                                    <!-- Campos adicionales en el futuro -->
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
                                <a href="{{ route('inventario.insumos.show', $insumo->idIns) }}" 
                                   wire:navigate
                                   class="px-4 py-2 bg-purple-300 hover:bg-purple-400 text-purple-800 font-medium rounded-lg transition duration-150 ease-in-out">
                                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    Ver Detalle
                                </a>
                                <a href="{{ route('inventario.insumos.index') }}" 
                                   wire:navigate
                                   class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium rounded-lg transition duration-150 ease-in-out">
                                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    Cancelar
                                </a>
                                <button type="button" 
                                        wire:click="resetForm"
                                        class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white font-medium rounded-lg transition duration-150 ease-in-out">
                                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                    Restaurar
                                </button>
                                <button type="submit" 
                                        class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition duration-150 ease-in-out">
                                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                                    </svg>
                                    Actualizar Insumo
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
                    <div class="px-6 py-4 bg-blue-600 rounded-t-lg">
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
                                $stockActual = 0; // Se calculará cuando implementemos movimientos
                                $stockMin = $insumo->stockMinIns ?? 0;
                                $nivelStock = 'temporal';
                                $colorStock = 'text-gray-600';
                            @endphp
                            
                            <div class="text-4xl font-bold {{ $colorStock }} mb-2">{{ $stockActual }}</div>
                            <p class="text-gray-600 mb-2">{{ $insumo->uniIns }} disponibles</p>
                            <span class="inline-flex px-3 py-1 text-sm font-medium rounded-full bg-gray-100 text-gray-800">
                                Stock Temporal
                            </span>
                            <div class="mt-2 text-xs text-gray-500">
                                Se calculará con movimientos
                            </div>
                            
                            @if($insumo->stockMinIns || $insumo->stockMaxIns)
                            <div class="grid grid-cols-2 gap-4 mt-4 text-center">
                                @if($insumo->stockMinIns)
                                <div class="bg-red-50 p-2 rounded">
                                    <div class="text-xs text-gray-600">Mínimo</div>
                                    <div class="font-bold text-red-600">{{ $insumo->stockMinIns }}</div>
                                </div>
                                @endif
                                @if($insumo->stockMaxIns)
                                <div class="bg-green-50 p-2 rounded">
                                    <div class="text-xs text-gray-600">Máximo</div>
                                    <div class="font-bold text-green-600">{{ $insumo->stockMaxIns }}</div>
                                </div>
                                @endif
                            </div>
                            @endif

                            <!-- Alerta de Vencimiento -->
                            @if($insumo->fecVenIns)
                                @php
                                    $diasParaVencer = now()->diffInDays($insumo->fecVenIns, false);
                                    $vencimientoAlerta = '';
                                    $vencimientoColor = '';
                                    
                                    if ($diasParaVencer < 0) {
                                        $vencimientoAlerta = 'Vencido';
                                        $vencimientoColor = 'bg-red-100 text-red-800';
                                    } elseif ($diasParaVencer <= 7) {
                                        $vencimientoAlerta = 'Vence en ' . $diasParaVencer . ' días';
                                        $vencimientoColor = 'bg-red-100 text-red-800';
                                    } elseif ($diasParaVencer <= 30) {
                                        $vencimientoAlerta = 'Vence en ' . $diasParaVencer . ' días';
                                        $vencimientoColor = 'bg-yellow-100 text-yellow-800';
                                    }
                                @endphp
                                
                                @if($vencimientoAlerta)
                                <div class="mt-4 p-3 {{ $vencimientoColor }} rounded-lg">
                                    <div class="text-sm font-medium">{{ $vencimientoAlerta }}</div>
                                    <div class="text-xs">{{ $insumo->fecVenIns->format('d/m/Y') }}</div>
                                </div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Información del Tipo -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 bg-yellow-500 rounded-t-lg">
                        <h3 class="text-lg font-medium text-white flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                            </svg>
                            Información del Tipo
                        </h3>
                    </div>
                    <div class="p-6">
                        @if(array_key_exists($tipIns, $infoTipos))
                            @php $info = $infoTipos[$tipIns]; @endphp
                            <div>
                                <h4 class="font-medium {{ $info['color'] }} mb-3">{{ $info['titulo'] }}</h4>
                                <div class="space-y-2">
                                    @foreach($info['info'] as $item)
                                        <div class="flex items-start text-sm text-gray-600">
                                            <svg class="w-4 h-4 {{ $info['color'] }} mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ $item }}
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="text-sm text-gray-600">Selecciona un tipo de insumo para ver información específica.</div>
                        @endif
                    </div>
                </div>

                <!-- Acciones Rápidas -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 bg-purple-600 rounded-t-lg">
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
                            <a href="#" class="flex items-center w-full p-3 text-left bg-blue-50 hover:bg-blue-100 rounded-lg transition duration-150 ease-in-out">
                                <svg class="w-5 h-5 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                                </svg>
                                <span class="text-blue-800 font-medium">Registrar Consumo</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Información del Sistema -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 bg-gray-600 rounded-t-lg">
                        <h3 class="text-lg font-medium text-white flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-16 0 9 9 0 0116 0z"></path>
                            </svg>
                            Información del Sistema
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">ID:</span>
                                <span class="font-medium text-gray-900">{{ $insumo->idIns }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Creado:</span>
                                <span class="text-gray-900">{{ $insumo->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Actualizado:</span>
                                <span class="text-gray-900">{{ $insumo->updated_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Movimientos:</span>
                                <span class="font-bold text-gray-900">0</span>
                            </div>
                            <div class="text-xs text-gray-500 mt-2">
                                <em>Los movimientos se contarán cuando se implemente el módulo correspondiente</em>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('livewire:initialized', () => {
    // Validación de stock mínimo/máximo en tiempo real
    Livewire.on('stockMinIns', (value) => {
        const input = document.getElementById('stockMinIns');
        if (value < 0) {
            input.value = 0;
        }
    });
    
    Livewire.on('stockMaxIns', (value) => {
        const input = document.getElementById('stockMaxIns');
        if (value < 0) {
            input.value = 0;
        }
    });
    
    // Confirmación antes de salir si hay cambios sin guardar
    window.addEventListener('beforeunload', (e) => {
        if (@this.formModificado) {
            e.preventDefault();
            e.returnValue = '¿Está seguro de que desea salir? Los cambios no guardados se perderán.';
        }
    });
});
</script>