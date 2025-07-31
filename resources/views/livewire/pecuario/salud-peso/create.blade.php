<?php
use App\Models\Animal;
use App\Models\HistorialMedico;
use App\Models\Proveedor;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component {
    // Propiedades para los selectores
    public $especies;
    public $razas = [];
    public $animalesFiltrados = [];
    public $proveedores;
    
    // Propiedades para las selecciones
    public $especieSeleccionada = null;
    public $razaSeleccionada = null;
    public $idAniHis = null;
    public $idProveedor = null;
    
    // Propiedades para mostrar información
    public $animalSeleccionado = null;
    public $proveedorSeleccionado = null;
    
    // Propiedades del formulario
    public $tipHisMed;
    public $fecHisMed;
    public $desHisMed;
    public $responHisMed;
    public $obsHisMed;

    public function mount()
    {
        // Cargar datos iniciales
        $this->especies = Animal::select('espAni')
                              ->where('estAni', 'vivo')
                              ->distinct()
                              ->orderBy('espAni')
                              ->get()
                              ->pluck('espAni');
                              
        $this->proveedores = Proveedor::orderBy('nomProve')->get();
        $this->fecHisMed = date('Y-m-d');
        $this->responHisMed = auth()->user()->name;
    }

    public function updatedEspecieSeleccionada($value)
    {
        // Resetear selecciones dependientes
        $this->reset(['razaSeleccionada', 'idAniHis', 'animalSeleccionado']);
        
        // Cargar razas para la especie seleccionada
        if ($value) {
            $this->razas = Animal::where('espAni', $value)
                               ->where('estAni', 'vivo')
                               ->select('razAni')
                               ->distinct()
                               ->orderBy('razAni')
                               ->get()
                               ->pluck('razAni')
                               ->filter()
                               ->toArray();
        } else {
            $this->razas = [];
        }
    }

    public function updatedRazaSeleccionada($value)
    {
        // Resetear selecciones dependientes
        $this->reset(['idAniHis', 'animalSeleccionado']);
        
        // Cargar animales para la especie y raza seleccionadas
        if ($value && $this->especieSeleccionada) {
            $this->animalesFiltrados = Animal::where('estAni', 'vivo')
                                          ->where('espAni', $this->especieSeleccionada)
                                          ->where('razAni', $value)
                                          ->orderBy('nomAni')
                                          ->get();
        } else {
            $this->animalesFiltrados = [];
        }
    }

    public function updatedIdAniHis($value)
    {
        $this->animalSeleccionado = $value ? Animal::find($value) : null;
    }

    public function updatedIdProveedor($value)
    {
        $this->proveedorSeleccionado = $value ? Proveedor::find($value) : null;
    }

    public function rules()
    {
        return [
            'idAniHis' => 'required|exists:animales,idAni',
            'idProveedor' => 'nullable|exists:proveedores,idProve',
            'tipHisMed' => 'required|in:vacuna,tratamiento,control',
            'fecHisMed' => 'required|date',
            'desHisMed' => 'required|string|max:500',
            'responHisMed' => 'required|string|max:100',
            'obsHisMed' => 'nullable|string|max:500'
        ];
    }

    public function save()
    {
        $validated = $this->validate();
        
        try {
            HistorialMedico::create($validated);
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Registro médico creado exitosamente'
            ]);
            
            return redirect()->route('pecuario.salud-peso.index');
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al crear el registro: ' . $e->getMessage()
            ]);
        }
    }
}; ?>

@section('title', 'Nuevo Registro Médico')

<div class="container mx-auto px-4 py-6">
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <!-- Header -->
        <div class="bg-green-700 text-white px-6 py-4 rounded-t-lg flex items-center gap-2">
            <i class="fas fa-plus-circle"></i>
            <h5 class="text-lg font-semibold">Nuevo Registro Médico</h5>
        </div>
        
        <div class="p-6">
            <form wire:submit="save" class="space-y-6">
                <!-- Selectores jerárquicos para animales -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Selector de Especie -->
                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Especie <span class="text-red-500">*</span></label>
                        <select 
                            wire:model="especieSeleccionada" 
                            wire:change="$refresh"
                            class="w-full border border-gray-300 rounded px-3 py-2"
                            required
                        >
                            <option value="">Seleccionar especie</option>
                            @foreach($especies as $especie)
                                <option value="{{ $especie }}">{{ $especie }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Selector de Raza -->
                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Raza <span class="text-red-500">*</span></label>
                        <select 
                            wire:model="razaSeleccionada" 
                            wire:change="$refresh"
                            class="w-full border border-gray-300 rounded px-3 py-2"
                            @if(!$especieSeleccionada) disabled @endif
                            required
                        >
                            <option value="">Seleccionar raza</option>
                            @foreach($razas as $raza)
                                <option value="{{ $raza }}">{{ $raza }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Selector de Animal -->
                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Animal <span class="text-red-500">*</span></label>
                        <select 
                            wire:model="idAniHis" 
                            class="w-full border border-gray-300 rounded px-3 py-2"
                            @if(!$razaSeleccionada) disabled @endif
                            required
                        >
                            <option value="">Seleccionar animal</option>
                            @foreach($animalesFiltrados as $animal)
                                <option value="{{ $animal->idAni }}">
                                    @if($animal->nomAni)
                                        {{ $animal->nomAni }} (ID: {{ $animal->idAni }})
                                    @else
                                        Animal #{{ $animal->idAni }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <!-- Información del animal seleccionado -->
                @if($animalSeleccionado)
                    <div class="mt-2 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <h4 class="font-semibold text-gray-800 mb-2">Información del Animal</h4>
                                <div class="space-y-1 text-sm">
                                    <div><span class="font-medium">ID:</span> {{ $animalSeleccionado->idAni }}</div>
                                    <div><span class="font-medium">Nombre:</span> {{ $animalSeleccionado->nomAni ?? 'Sin nombre' }}</div>
                                    <div><span class="font-medium">Especie:</span> {{ $animalSeleccionado->espAni }}</div>
                                    <div><span class="font-medium">Raza:</span> {{ $animalSeleccionado->razAni ?? 'No especificada' }}</div>
                                </div>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-800 mb-2">Detalles Adicionales</h4>
                                <div class="space-y-1 text-sm">
                                    <div><span class="font-medium">Sexo:</span> {{ $animalSeleccionado->sexAni }}</div>
                                    @if($animalSeleccionado->fecNacAni)
                                        <div><span class="font-medium">Nacimiento:</span> {{ date('d/m/Y', strtotime($animalSeleccionado->fecNacAni)) }}</div>
                                    @endif
                                    <div><span class="font-medium">Estado:</span> {{ ucfirst($animalSeleccionado->estAni) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

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
                                <div><strong>ID:</strong> {{ $proveedorSeleccionado->idProve }}</div>
                                <div><strong>NIT:</strong> {{ $proveedorSeleccionado->nitProve ?? 'No especificado' }}</div>
                                <div><strong>Contacto:</strong> {{ $proveedorSeleccionado->conProve ?? 'No especificado' }}</div>
                                <div><strong>Teléfono:</strong> {{ $proveedorSeleccionado->telProve ?? 'No especificado' }}</div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Campos del formulario -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Tipo de Registro <span class="text-red-500">*</span></label>
                        <select wire:model="tipHisMed" class="w-full border border-gray-300 rounded px-3 py-2" required>
                            <option value="">Seleccionar tipo</option>
                            <option value="vacuna">Vacuna</option>
                            <option value="tratamiento">Tratamiento</option>
                            <option value="control">Control de Peso</option>
                        </select>
                        @error('tipHisMed') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Fecha <span class="text-red-500">*</span></label>
                        <input type="date" wire:model="fecHisMed" class="w-full border border-gray-300 rounded px-3 py-2" required>
                        @error('fecHisMed') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Responsable <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="responHisMed" class="w-full border border-gray-300 rounded px-3 py-2" required>
                        @error('responHisMed') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label class="block mb-1 font-medium text-gray-700">Descripción <span class="text-red-500">*</span></label>
                    <textarea wire:model="desHisMed" rows="3" class="w-full border border-gray-300 rounded px-3 py-2" required></textarea>
                    @error('desHisMed') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block mb-1 font-medium text-gray-700">Observaciones</label>
                    <textarea wire:model="obsHisMed" rows="2" class="w-full border border-gray-300 rounded px-3 py-2"></textarea>
                    @error('obsHisMed') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="flex justify-between items-center">
                    <a href="{{ route('pecuario.salud-peso.index') }}" wire:navigate
                       class="inline-flex items-center gap-2 px-4 py-2 border border-green-600 rounded text-green-700 hover:bg-green-100">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>