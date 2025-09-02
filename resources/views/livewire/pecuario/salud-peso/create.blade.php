<?php
use App\Models\Animal;
use App\Models\HistorialMedico;
use App\Models\Proveedor;
use App\Models\Insumo;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component {
    // Propiedades para los selectores
    public $especies;
    public $razas = [];
    public $animalesFiltrados = [];
    public $proveedores;
    public $insumos;
    
    // Propiedades para las selecciones
    public $especieSeleccionada = null;
    public $razaSeleccionada = null;
    public $idAniHis = null;
    public $idProveedor = null;
    public $idIns = null;
    
    // Propiedades para mostrar información
    public $animalSeleccionado = null;
    public $proveedorSeleccionado = null;
    public $insumoSeleccionado = null;
    
    // Propiedades del formulario
    public $tipHisMed;
    public $fecHisMed;
    public $desHisMed;
    public $responHisMed;
    public $obsHisMed;
    public $traHisMed;
    public $dosHisMed;
    public $durHisMed;
    public $estRecHisMed = 'en tratamiento';
    public $resHisMed;
    public $obsHisMed2;

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
        
        // Cargar insumos/medicamentos disponibles
        $this->insumos = Insumo::where('estIns', 'disponible')
                              ->orderBy('nomIns')
                              ->get();
        
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
                                          ->orderBy('ideAni')
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

    public function updatedIdIns($value)
    {
        $this->insumoSeleccionado = $value ? Insumo::find($value) : null;
    }

    public function rules()
    {
        return [
            'idAniHis' => 'required|exists:animales,idAni',
            'idProveedor' => 'nullable|exists:proveedores,idProve',
            'idIns' => 'nullable|exists:insumos,idIns',
            'tipHisMed' => 'required|in:vacuna,tratamiento,control',
            'fecHisMed' => 'required|date',
            'desHisMed' => 'required|string|max:65535',
            'traHisMed' => 'nullable|string|max:65535',
            'dosHisMed' => 'nullable|string|max:50',
            'durHisMed' => 'nullable|string|max:50',
            'responHisMed' => 'required|string|max:100',
            'estRecHisMed' => 'required|in:saludable,en tratamiento,crónico',
            'resHisMed' => 'nullable|string|max:100',
            'obsHisMed' => 'nullable|string|max:65535',
            'obsHisMed2' => 'nullable|string|max:65535'
        ];
    }

    public function save()
    {
        $this->validate();
        
        try {
            // Preparar los datos para crear el registro
            $data = [
                'idAni' => $this->idAniHis, // Mapear correctamente el ID del animal
                'fecHisMed' => $this->fecHisMed,
                'desHisMed' => $this->desHisMed,
                'traHisMed' => $this->traHisMed,
                'tipHisMed' => $this->tipHisMed,
                'responHisMed' => $this->responHisMed,
                'obsHisMed' => $this->obsHisMed,
                'idIns' => $this->idIns,
                'dosHisMed' => $this->dosHisMed,
                'durHisMed' => $this->durHisMed,
                'estRecHisMed' => $this->estRecHisMed,
                'resHisMed' => $this->resHisMed,
                'obsHisMed2' => $this->obsHisMed2,
                'idProve' => $this->idProveedor, // Si necesitas guardar el proveedor también
            ];

            // Filtrar valores nulos/vacíos para campos opcionales pero mantener idAni
            $filteredData = array_filter($data, function($value, $key) {
                // Siempre mantener idAni, tipHisMed, fecHisMed, desHisMed, responHisMed, estRecHisMed
                if (in_array($key, ['idAni', 'tipHisMed', 'fecHisMed', 'desHisMed', 'responHisMed', 'estRecHisMed'])) {
                    return true;
                }
                return $value !== null && $value !== '';
            }, ARRAY_FILTER_USE_BOTH);

            // Debug - opcional, remover en producción
            \Log::info('Datos a guardar:', $filteredData);

            HistorialMedico::create($filteredData);
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Registro médico creado exitosamente'
            ]);
            
            return redirect()->route('pecuario.salud-peso.index');
            
        } catch (\Exception $e) {
            \Log::error('Error creando historial médico: ' . $e->getMessage());
            
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
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:border-green-500 focus:ring focus:ring-green-200"
                            required
                        >
                            <option value="">Seleccionar especie</option>
                            @foreach($especies as $especie)
                                <option value="{{ $especie }}">{{ $especie }}</option>
                            @endforeach
                        </select>
                        @error('especieSeleccionada') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    
                    <!-- Selector de Raza -->
                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Raza <span class="text-red-500">*</span></label>
                        <select 
                            wire:model="razaSeleccionada" 
                            wire:change="$refresh"
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:border-green-500 focus:ring focus:ring-green-200"
                            @if(!$especieSeleccionada) disabled @endif
                            required
                        >
                            <option value="">Seleccionar raza</option>
                            @foreach($razas as $raza)
                                <option value="{{ $raza }}">{{ $raza }}</option>
                            @endforeach
                        </select>
                        @error('razaSeleccionada') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    
                    <!-- Selector de Animal -->
                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Animal <span class="text-red-500">*</span></label>
                        <select 
                            wire:model="idAniHis" 
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:border-green-500 focus:ring focus:ring-green-200"
                            @if(!$razaSeleccionada) disabled @endif
                            required
                        >
                            <option value="">Seleccionar animal</option>
                            @foreach($animalesFiltrados as $animal)
                                <option value="{{ $animal->idAni }}">
                                    @if($animal->ideAni)
                                        {{ $animal->ideAni }} (ID: {{ $animal->idAni }})
                                    @else
                                        Animal #{{ $animal->idAni }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('idAniHis') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
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
                                    <div><span class="font-medium">Nombre:</span> {{ $animalSeleccionado->ideAni ?? 'Sin nombre' }}</div>
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

                <!-- Selector de Proveedor e Insumo -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Proveedor</label>
                        <select 
                            wire:model="idProveedor" 
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:border-green-500 focus:ring focus:ring-green-200"
                        >
                            <option value="">Seleccionar proveedor</option>
                            @foreach($proveedores as $proveedor)
                                <option value="{{ $proveedor->idProve }}">
                                    {{ $proveedor->nomProve }} ({{ $proveedor->tipSumProve ?? 'Sin tipo' }})
                                </option>
                            @endforeach
                        </select>
                        @error('idProveedor') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Selector de Insumo/Medicamento -->
                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Medicamento/Insumo</label>
                        <select 
                            wire:model="idIns" 
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:border-green-500 focus:ring focus:ring-green-200"
                        >
                            <option value="">Seleccionar medicamento/insumo</option>
                            @foreach($insumos as $insumo)
                                <option value="{{ $insumo->idIns }}">
                                    {{ $insumo->nomIns }} 
                                    @if($insumo->marIns) - {{ $insumo->marIns }} @endif
                                    ({{ $insumo->canIns ?? '0' }} {{ $insumo->uniIns }})
                                </option>
                            @endforeach
                        </select>
                        @error('idIns') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Campos del formulario principales -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Tipo de Registro <span class="text-red-500">*</span></label>
                        <select wire:model="tipHisMed" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-green-500 focus:ring focus:ring-green-200" required>
                            <option value="">Seleccionar tipo</option>
                            <option value="vacuna">Vacuna</option>
                            <option value="tratamiento">Tratamiento</option>
                            <option value="control">Control de Peso</option>
                        </select>
                        @error('tipHisMed') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Fecha <span class="text-red-500">*</span></label>
                        <input type="date" wire:model="fecHisMed" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-green-500 focus:ring focus:ring-green-200" required>
                        @error('fecHisMed') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Estado de Recuperación -->
                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Estado de Recuperación <span class="text-red-500">*</span></label>
                        <select wire:model="estRecHisMed" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-green-500 focus:ring focus:ring-green-200" required>
                            <option value="saludable">Saludable</option>
                            <option value="en tratamiento">En Tratamiento</option>
                            <option value="crónico">Crónico</option>
                        </select>
                        @error('estRecHisMed') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Campos de dosis y duración -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Dosis -->
                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Dosis</label>
                        <input type="text" wire:model="dosHisMed" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-green-500 focus:ring focus:ring-green-200" 
                               placeholder="Ej: 5ml, 2 pastillas, etc.">
                        @error('dosHisMed') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Duración -->
                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Duración</label>
                        <input type="text" wire:model="durHisMed" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-green-500 focus:ring focus:ring-green-200" 
                               placeholder="Ej: 7 días, 2 semanas, etc.">
                        @error('durHisMed') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Responsable <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="responHisMed" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-green-500 focus:ring focus:ring-green-200" required>
                        @error('responHisMed') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Resultado -->
                <div class="grid grid-cols-1 md:grid-cols-1 gap-6">
                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Resultado</label>
                        <input type="text" wire:model="resHisMed" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-green-500 focus:ring focus:ring-green-200" 
                               placeholder="Resultado del tratamiento o procedimiento">
                        @error('resHisMed') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label class="block mb-1 font-medium text-gray-700">Descripción <span class="text-red-500">*</span></label>
                    <textarea wire:model="desHisMed" rows="3" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-green-500 focus:ring focus:ring-green-200" required></textarea>
                    @error('desHisMed') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- Tratamiento -->
                <div>
                    <label class="block mb-1 font-medium text-gray-700">Tratamiento</label>
                    <textarea wire:model="traHisMed" rows="3" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-green-500 focus:ring focus:ring-green-200" 
                              placeholder="Descripción detallada del tratamiento aplicado"></textarea>
                    @error('traHisMed') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block mb-1 font-medium text-gray-700">Observaciones</label>
                    <textarea wire:model="obsHisMed" rows="2" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-green-500 focus:ring focus:ring-green-200"></textarea>
                    @error('obsHisMed') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- Observaciones Adicionales -->
                <div>
                    <label class="block mb-1 font-medium text-gray-700">Observaciones Adicionales</label>
                    <textarea wire:model="obsHisMed2" rows="2" class="w-full border border-gray-300 rounded px-3 py-2 focus:border-green-500 focus:ring focus:ring-green-200" 
                              placeholder="Información complementaria o notas adicionales"></textarea>
                    @error('obsHisMed2') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="flex justify-between items-center pt-4 border-t border-gray-200">
                    <a href="{{ route('pecuario.salud-peso.index') }}" wire:navigate
                       class="inline-flex items-center gap-2 px-4 py-2 border border-green-600 rounded text-green-700 hover:bg-green-100 transition-colors">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition-colors">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>