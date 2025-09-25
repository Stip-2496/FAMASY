<?php
use App\Models\HistorialMedico;
use App\Models\Animal;
use App\Models\Proveedor;
use App\Models\Insumo;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component {
    public HistorialMedico $historial;
    public Animal $animal;
    public $proveedores;
    public $insumos;
    public $proveedorSeleccionado;
    public $insumoSeleccionado;
    
    public $fecHisMed;
    public $desHisMed;
    public $traHisMed;
    public $idIns;
    public $dosHisMed;
    public $durHisMed;
    public $responHisMed;
    public $estRecHisMed;
    public $obsHisMed2;
    public $resHisMed;
    public $obsHisMed;
    public $idProveedor;

    public function mount(HistorialMedico $historial)
    {
        $this->historial = $historial;
        $this->animal = Animal::findOrFail($historial->idAni);
        $this->proveedores = Proveedor::orderBy('nomProve')->get();
        $this->insumos = Insumo::orderBy('nomIns')->get();
        
        // Asignar todos los campos según la estructura de la tabla
        $this->fecHisMed = $historial->fecHisMed;
        $this->desHisMed = $historial->desHisMed;
        $this->traHisMed = $historial->traHisMed;
        $this->idIns = $historial->idIns;
        $this->dosHisMed = $historial->dosHisMed;
        $this->durHisMed = $historial->durHisMed;
        $this->responHisMed = $historial->responHisMed;
        $this->estRecHisMed = $historial->estRecHisMed;
        $this->obsHisMed2 = $historial->obsHisMed2;
        $this->resHisMed = $historial->resHisMed;
        $this->obsHisMed = $historial->obsHisMed;
        $this->idProveedor = $historial->idProveedor;
        
        if ($this->idProveedor) {
            $this->proveedorSeleccionado = Proveedor::find($this->idProveedor);
        }
        
        if ($this->idIns) {
            $this->insumoSeleccionado = Insumo::find($this->idIns);
        }
    }

    public function rules()
    {
        return [
            'fecHisMed' => 'required|date',
            'desHisMed' => 'required|string|max:500',
            'traHisMed' => 'nullable|string|max:500',
            'idIns' => 'nullable|exists:insumos,idIns',
            'dosHisMed' => 'nullable|string|max:50',
            'durHisMed' => 'nullable|string|max:50',
            'responHisMed' => 'required|string|max:100',
            'estRecHisMed' => 'nullable|in:saludable,en tratamiento,crónico',
            'obsHisMed2' => 'nullable|string|max:500',
            'resHisMed' => 'nullable|string|max:100',
            'obsHisMed' => 'nullable|string|max:500',
            'idProveedor' => 'nullable|exists:proveedores,idProve'
        ];
    }

    public function updatedIdProveedor($value)
    {
        $this->proveedorSeleccionado = $value ? Proveedor::find($value) : null;
    }

    public function updatedIdIns($value)
    {
        $this->insumoSeleccionado = $value ? Insumo::find($value) : null;
    }

    public function update()
    {
        $validated = $this->validate();
        
        try {
            $this->historial->update($validated);
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Registro médico actualizado exitosamente'
            ]);
            
            return redirect()->route('pecuario.salud-peso.show', $this->historial->idHisMed);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al actualizar el registro: ' . $e->getMessage()
            ]);
        }
    }
}; ?>

@section('title', 'Editar Registro Médico')

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-6xl mx-auto px-4">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-green-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-edit text-white text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Editar Registro Médico</h1>
                        <p class="text-gray-600">Modifique la información del registro médico</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-600 text-white">
                        <div class="w-2 h-2 bg-white rounded-full mr-2"></div>
                        Editando registro
                    </span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-paw text-gray-600 text-sm"></i>
                    </div>
                    <h2 class="text-lg font-semibold text-gray-900">Información del Animal</h2>
                </div>
            </div>
            
            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">ID del Animal</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-hashtag text-gray-400"></i>
                            </div>
                            <input type="text" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg bg-gray-50 cursor-not-allowed" 
                                   value="{{ $animal->idAni }}" readonly>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nombre del Animal</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-tag text-gray-400"></i>
                            </div>
                            <input type="text" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg bg-gray-50 cursor-not-allowed" 
                                   value="{{ $animal->ideAni ?? 'Sin nombre' }}" readonly>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Especie</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-dove text-gray-400"></i>
                            </div>
                            <input type="text" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg bg-gray-50 cursor-not-allowed" 
                                   value="{{ $animal->espAni }}" readonly>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Raza</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-paw text-gray-400"></i>
                            </div>
                            <input type="text" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg bg-gray-50 cursor-not-allowed" 
                                   value="{{ $animal->razAni ?? 'No especificada' }}" readonly>
                        </div>
                    </div>
                </div>

                @if($historial->tipHisMed == 'control')
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6 flex items-center gap-3">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-weight text-green-600"></i>
                    </div>
                    <div>
                        <p class="text-green-800 font-medium">Peso actual del animal: <strong>{{ $animal->pesAni }} kg</strong></p>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <form wire:submit="update" class="space-y-8 mt-8">
            <!-- Sección: Información del Registro -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-file-medical text-gray-600 text-sm"></i>
                        </div>
                        <h2 class="text-lg font-semibold text-gray-900">Información del Registro</h2>
                    </div>
                </div>
                
                <div class="p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Registro</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-tag text-gray-400"></i>
                                </div>
                                <input type="text" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg bg-gray-50 cursor-not-allowed" 
                                       value="{{ ucfirst($historial->tipHisMed) }}" readonly>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Fecha <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-calendar text-gray-400"></i>
                                </div>
                                <input type="date" wire:model="fecHisMed" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors" required>
                            </div>
                            @error('fecHisMed') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Descripción <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <div class="absolute top-3 left-3">
                                <i class="fas fa-align-left text-gray-400"></i>
                            </div>
                            <textarea wire:model="desHisMed" rows="3" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors resize-none" required></textarea>
                        </div>
                        @error('desHisMed') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Responsable <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-user text-gray-400"></i>
                            </div>
                            <input type="text" wire:model="responHisMed" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors" required>
                        </div>
                        @error('responHisMed') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Sección: Información del Proveedor -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-truck text-gray-600 text-sm"></i>
                        </div>
                        <h2 class="text-lg font-semibold text-gray-900">Información del Proveedor</h2>
                    </div>
                </div>
                
                <div class="p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Proveedor</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-user-tie text-gray-400"></i>
                                </div>
                                <select 
                                    wire:model="idProveedor" 
                                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                                >
                                    <option value="">Seleccionar proveedor</option>
                                    @foreach($proveedores as $proveedor)
                                        <option value="{{ $proveedor->idProve }}">
                                            {{ $proveedor->nomProve }} ({{ $proveedor->tipSumProve ?? 'Sin tipo' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    @if($proveedorSeleccionado)
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mt-6">
                            <h4 class="font-semibold text-blue-900 mb-4 flex items-center gap-2">
                                <i class="fas fa-info-circle text-blue-600"></i>
                                Información del Proveedor
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="font-medium text-gray-700">NIT:</span>
                                    <span class="text-gray-900 ml-1">{{ $proveedorSeleccionado->nitProve ?? 'No especificado' }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Contacto:</span>
                                    <span class="text-gray-900 ml-1">{{ $proveedorSeleccionado->conProve ?? 'No especificado' }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Teléfono:</span>
                                    <span class="text-gray-900 ml-1">{{ $proveedorSeleccionado->telProve ?? 'No especificado' }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Dirección:</span>
                                    <span class="text-gray-900 ml-1">{{ $proveedorSeleccionado->dirProve ?? 'No especificado' }}</span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Sección: Información Específica según Tipo -->
            @if($historial->tipHisMed == 'tratamiento')
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-pills text-gray-600 text-sm"></i>
                        </div>
                        <h2 class="text-lg font-semibold text-gray-900">Datos del Tratamiento</h2>
                    </div>
                </div>
                
                <div class="p-8">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Medicamento/Insumo</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-pills text-gray-400"></i>
                                </div>
                                <select wire:model="idIns" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors">
                                    <option value="">Seleccionar insumo</option>
                                    @foreach($insumos as $insumo)
                                        <option value="{{ $insumo->idIns }}">
                                            {{ $insumo->nomIns }} ({{ $insumo->preIns ?? 'Sin precio' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Dosis</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-syringe text-gray-400"></i>
                                </div>
                                <input type="text" wire:model="dosHisMed" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors" 
                                       placeholder="Ej: 5 ml, 2 tabletas">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Duración</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-clock text-gray-400"></i>
                                </div>
                                <input type="text" wire:model="durHisMed" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors" 
                                       placeholder="Ej: 7 días, 2 semanas">
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tratamiento aplicado</label>
                        <div class="relative">
                            <div class="absolute top-3 left-3">
                                <i class="fas fa-prescription text-gray-400"></i>
                            </div>
                            <textarea wire:model="traHisMed" rows="2" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors resize-none"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if($historial->tipHisMed == 'control')
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-weight text-gray-600 text-sm"></i>
                        </div>
                        <h2 class="text-lg font-semibold text-gray-900">Control de Peso/Salud</h2>
                    </div>
                </div>
                
                <div class="p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Resultado</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-check-circle text-gray-400"></i>
                                </div>
                                <input type="text" wire:model="resHisMed" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors" 
                                       placeholder="Ej: Peso normal, Fiebre detectada">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Sección: Información Adicional -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-sticky-note text-gray-600 text-sm"></i>
                        </div>
                        <h2 class="text-lg font-semibold text-gray-900">Información Adicional</h2>
                    </div>
                </div>
                
                <div class="p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Estado de Recuperación</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-heart text-gray-400"></i>
                                </div>
                                <select wire:model="estRecHisMed" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors">
                                    <option value="saludable">Saludable</option>
                                    <option value="en tratamiento">En tratamiento</option>
                                    <option value="crónico">Crónico</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Observaciones</label>
                        <div class="relative">
                            <div class="absolute top-3 left-3">
                                <i class="fas fa-eye text-gray-400"></i>
                            </div>
                            <textarea wire:model="obsHisMed" rows="2" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors resize-none"></textarea>
                        </div>
                        @error('obsHisMed') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Observaciones Adicionales</label>
                        <div class="relative">
                            <div class="absolute top-3 left-3">
                                <i class="fas fa-clipboard-list text-gray-400"></i>
                            </div>
                            <textarea wire:model="obsHisMed2" rows="2" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors resize-none"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="flex justify-between items-center bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <a href="{{ route('pecuario.salud-peso.index') }}" 
                   wire:navigate
                   class="inline-flex items-center gap-2 px-6 py-3 border-2 border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 hover:border-gray-400 transition-all duration-200">
                    <i class="fas fa-arrow-left text-sm"></i>
                    Volver
                </a>
                <button type="submit" 
                        class="inline-flex items-center gap-2 px-8 py-3 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 focus:ring-4 focus:ring-green-200 transition-all duration-200 shadow-sm hover:shadow-md">
                    <i class="fas fa-save text-sm"></i>
                    Actualizar Registro
                </button>
            </div>
        </form>
    </div>
</div>