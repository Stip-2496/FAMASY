<?php
use App\Models\HistorialMedico;
use App\Models\Animal;
use App\Models\Proveedor;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component {
    public HistorialMedico $historial;
    public Animal $animal;
    public $proveedores;
    public $proveedorSeleccionado;
    
    public $fecHisMed;
    public $desHisMed;
    public $responHisMed;
    public $obsHisMed;
    public $idProveedor;

    public function mount(HistorialMedico $historial)
    {
        $this->historial = $historial;
        $this->animal = Animal::findOrFail($historial->idAniHis);
        $this->proveedores = Proveedor::orderBy('nomProve')->get();
        
        $this->fecHisMed = $historial->fecHisMed;
        $this->desHisMed = $historial->desHisMed;
        $this->responHisMed = $historial->responHisMed;
        $this->obsHisMed = $historial->obsHisMed;
        $this->idProveedor = $historial->idProveedor;
        
        if ($this->idProveedor) {
            $this->proveedorSeleccionado = Proveedor::find($this->idProveedor);
        }
    }

    public function rules()
    {
        return [
            'fecHisMed' => 'required|date',
            'desHisMed' => 'required|string|max:500',
            'responHisMed' => 'required|string|max:100',
            'obsHisMed' => 'nullable|string|max:500',
            'idProveedor' => 'nullable|exists:proveedores,idProve'
        ];
    }

    public function updatedIdProveedor($value)
    {
        $this->proveedorSeleccionado = $value ? Proveedor::find($value) : null;
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

<div class="container mx-auto px-4 py-6">
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <!-- Header -->
        <div class="bg-green-700 text-white px-6 py-4 rounded-t-lg flex items-center gap-2">
            <i class="fas fa-edit"></i>
            <h5 class="text-lg font-semibold">Editar Registro Médico</h5>
        </div>
        
        <div class="p-6">
            <!-- Información del Animal -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block mb-1 font-medium text-gray-700">ID del Animal</label>
                    <input type="text" class="w-full border border-gray-300 rounded px-3 py-2 bg-gray-100 cursor-not-allowed" 
                           value="ID {{ $animal->idAni }}" readonly>
                </div>
                
                <div>
                    <label class="block mb-1 font-medium text-gray-700">Nombre del Animal</label>
                    <input type="text" class="w-full border border-gray-300 rounded px-3 py-2 bg-gray-100 cursor-not-allowed" 
                           value="{{ $animal->nomAni ?? 'Sin nombre' }}" readonly>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block mb-1 font-medium text-gray-700">Especie</label>
                    <input type="text" class="w-full border border-gray-300 rounded px-3 py-2 bg-gray-100 cursor-not-allowed" 
                           value="{{ $animal->espAni }}" readonly>
                </div>
                
                <div>
                    <label class="block mb-1 font-medium text-gray-700">Raza</label>
                    <input type="text" class="w-full border border-gray-300 rounded px-3 py-2 bg-gray-100 cursor-not-allowed" 
                           value="{{ $animal->razAni ?? 'No especificada' }}" readonly>
                </div>
            </div>

            @if($historial->tipHisMed == 'control')
            <div class="bg-green-100 text-green-800 p-3 rounded mb-6 flex items-center gap-2">
                <i class="fas fa-info-circle"></i> 
                <span>Peso actual del animal: <strong>{{ $animal->pesAni }} kg</strong></span>
            </div>
            @endif

            <form wire:submit="update" class="space-y-6">
                <!-- Selector de Proveedor -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Proveedor</label>
                        <select 
                            wire:model="idProveedor" 
                            class="w-full border border-gray-300 rounded px-3 py-2"
                        >
                            <option value="">Seleccionar proveedor</option>
                            @foreach($proveedores as $proveedor)
                                <option value="{{ $proveedor->idProve }}">
                                    {{ $proveedor->nomProve }} ({{ $proveedor->tipSumProve ?? 'Sin tipo' }})
                                </option>
                            @endforeach
                        </select>
                        
                        @if($proveedorSeleccionado)
                            <div class="mt-2 p-2 bg-gray-50 rounded text-sm">
                                <div class="font-medium">Información del proveedor:</div>
                                <div><strong>NIT:</strong> {{ $proveedorSeleccionado->nitProve ?? 'No especificado' }}</div>
                                <div><strong>Contacto:</strong> {{ $proveedorSeleccionado->conProve ?? 'No especificado' }}</div>
                            </div>
                        @endif
                    </div>
                    
                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Tipo de Registro</label>
                        <input type="text" class="w-full border border-gray-300 rounded px-3 py-2 bg-gray-100 cursor-not-allowed" 
                               value="{{ ucfirst($historial->tipHisMed) }}" readonly>
                    </div>
                </div>

                <!-- Campos específicos según tipo de registro -->
                @if($historial->tipHisMed == 'vacuna')
                <div class="border border-gray-300 rounded p-4 bg-gray-50">
                    <h6 class="font-semibold mb-2 text-gray-800">Datos de Vacunación</h6>
                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Descripción <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="desHisMed" class="w-full border border-gray-300 rounded px-3 py-2" required>
                        @error('desHisMed') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>
                @endif

                @if($historial->tipHisMed == 'tratamiento')
                <div class="border border-gray-300 rounded p-4 bg-gray-50">
                    <h6 class="font-semibold mb-2 text-gray-800">Datos de Tratamiento</h6>
                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Descripción <span class="text-red-500">*</span></label>
                        <textarea wire:model="desHisMed" rows="3" class="w-full border border-gray-300 rounded px-3 py-2" required></textarea>
                        @error('desHisMed') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>
                @endif

                @if($historial->tipHisMed == 'control')
                <div class="border border-gray-300 rounded p-4 bg-gray-50">
                    <h6 class="font-semibold mb-2 text-gray-800">Control de Peso</h6>
                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Descripción <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="desHisMed" class="w-full border border-gray-300 rounded px-3 py-2" required>
                        @error('desHisMed') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>
                @endif

                <!-- Campos comunes -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Fecha <span class="text-red-500">*</span></label>
                        <input type="date" wire:model="fecHisMed" class="w-full border border-gray-300 rounded px-3 py-2" required>
                        @error('fecHisMed') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Responsable <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="responHisMed" class="w-full border border-gray-300 rounded px-3 py-2" required>
                        @error('responHisMed') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label class="block mb-1 font-medium text-gray-700">Observaciones</label>
                    <textarea wire:model="obsHisMed" rows="2" class="w-full border border-gray-300 rounded px-3 py-2"></textarea>
                    @error('obsHisMed') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="flex justify-between items-center">
                    <a href="{{ route('pecuario.salud-peso.index') }}" wire:navigate
                       class="inline-flex items-center gap-2 px-4 py-2 border border-gray-400 rounded text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                        <i class="fas fa-save"></i> Actualizar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div> 