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

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-6xl mx-auto px-4">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-green-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-plus text-white text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Nuevo Registro Médico</h1>
                        <p class="text-gray-600">Complete el formulario para agregar un nuevo registro médico</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-600 text-white">
                        <div class="w-2 h-2 bg-white rounded-full mr-2"></div>
                        Nuevo registro
                    </span>
                </div>
            </div>
        </div>

        <form wire:submit="save" class="space-y-8">
            <!-- Sección 1: Selección de Animal -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-paw text-gray-600 text-sm"></i>
                        </div>
                        <h2 class="text-lg font-semibold text-gray-900">Seleccionar Animal</h2>
                    </div>
                </div>
                
                <div class="p-8">
                    <!-- Información Básica -->
                    <div class="mb-8">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-info-circle text-gray-600 text-sm"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Información Básica</h3>
                        </div>
                        <p class="text-gray-600 mb-6">Datos principales del animal</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Selector de Especie -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Especie <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-dove text-gray-400"></i>
                                    </div>
                                    <select 
                                        wire:model="especieSeleccionada" 
                                        wire:change="$refresh"
                                        class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                                        required
                                    >
                                        <option value="">Seleccionar especie</option>
                                        @foreach($especies as $especie)
                                            <option value="{{ $especie }}">{{ $especie }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('especieSeleccionada') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                            </div>
                            
                            <!-- Selector de Raza -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Raza <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-paw text-gray-400"></i>
                                    </div>
                                    <select 
                                        wire:model="razaSeleccionada" 
                                        wire:change="$refresh"
                                        class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors disabled:bg-gray-100 disabled:cursor-not-allowed"
                                        @if(!$especieSeleccionada) disabled @endif
                                        required
                                    >
                                        <option value="">Seleccionar raza</option>
                                        @foreach($razas as $raza)
                                            <option value="{{ $raza }}">{{ $raza }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('razaSeleccionada') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                            </div>
                            
                            <!-- Selector de Animal -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Animal <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-search text-gray-400"></i>
                                    </div>
                                    <select 
                                        wire:model="idAniHis" 
                                        class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors disabled:bg-gray-100 disabled:cursor-not-allowed"
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
                                </div>
                                @error('idAniHis') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Información del animal seleccionado -->
                    @if($animalSeleccionado)
                        <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                            <h4 class="font-semibold text-green-900 mb-4 flex items-center gap-2">
                                <i class="fas fa-paw text-green-600"></i>
                                Información del Animal Seleccionado
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
                                <div>
                                    <span class="font-medium text-gray-700">ID:</span>
                                    <span class="text-gray-900 ml-1">{{ $animalSeleccionado->idAni }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Nombre:</span>
                                    <span class="text-gray-900 ml-1">{{ $animalSeleccionado->ideAni ?? 'Sin nombre' }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Especie:</span>
                                    <span class="text-gray-900 ml-1">{{ $animalSeleccionado->espAni }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Raza:</span>
                                    <span class="text-gray-900 ml-1">{{ $animalSeleccionado->razAni ?? 'No especificada' }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Sexo:</span>
                                    <span class="text-gray-900 ml-1">{{ $animalSeleccionado->sexAni }}</span>
                                </div>
                                @if($animalSeleccionado->fecNacAni)
                                    <div>
                                        <span class="font-medium text-gray-700">Nacimiento:</span>
                                        <span class="text-gray-900 ml-1">{{ date('d/m/Y', strtotime($animalSeleccionado->fecNacAni)) }}</span>
                                    </div>
                                @endif
                                <div>
                                    <span class="font-medium text-gray-700">Estado:</span>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 ml-1">
                                        {{ ucfirst($animalSeleccionado->estAni) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Sección 2: Información del Proveedor -->
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
                    <p class="text-gray-600 mb-6">Proveedor que suministra este animal</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Selector de Proveedor -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Procedencia/Proveedor</label>
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
                            @error('idProveedor') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Selector de Insumo/Medicamento -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Medicamento/Insumo</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-pills text-gray-400"></i>
                                </div>
                                <select 
                                    wire:model="idIns" 
                                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
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
                            </div>
                            @error('idIns') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    
                    <!-- Información del proveedor seleccionado -->
                    @if($proveedorSeleccionado)
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mt-6">
                            <h4 class="font-semibold text-blue-900 mb-4 flex items-center gap-2">
                                <i class="fas fa-info-circle text-blue-600"></i>
                                Información del Proveedor
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="font-medium text-gray-700">Nombre:</span>
                                    <span class="text-gray-900 ml-1">{{ $proveedorSeleccionado->nomProve }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Tipo:</span>
                                    <span class="text-gray-900 ml-1">{{ $proveedorSeleccionado->tipSumProve ?? 'No especificado' }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Contacto:</span>
                                    <span class="text-gray-900 ml-1">{{ $proveedorSeleccionado->conProve ?? 'No especificado' }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Teléfono:</span>
                                    <span class="text-gray-900 ml-1">{{ $proveedorSeleccionado->telProve ?? 'No especificado' }}</span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Sección 3: Fechas Importantes -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-calendar-alt text-gray-600 text-sm"></i>
                        </div>
                        <h2 class="text-lg font-semibold text-gray-900">Fechas Importantes</h2>
                    </div>
                </div>
                
                <div class="p-8">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Registro <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-tag text-gray-400"></i>
                                </div>
                                <select 
                                    wire:model="tipHisMed" 
                                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors" 
                                    required
                                >
                                    <option value="">Seleccionar tipo</option>
                                    <option value="vacuna">Vacuna</option>
                                    <option value="tratamiento">Tratamiento</option>
                                    <option value="control">Control de Peso</option>
                                </select>
                            </div>
                            @error('tipHisMed') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Fecha del Procedimiento <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-calendar text-gray-400"></i>
                                </div>
                                <input 
                                    type="date" 
                                    wire:model="fecHisMed" 
                                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors" 
                                    required
                                >
                            </div>
                            @error('fecHisMed') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Responsable <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-user text-gray-400"></i>
                                </div>
                                <input 
                                    type="text" 
                                    wire:model="responHisMed" 
                                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors" 
                                    required
                                >
                            </div>
                            @error('responHisMed') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección 4: Estados del Animal -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-heartbeat text-gray-600 text-sm"></i>
                        </div>
                        <h2 class="text-lg font-semibold text-gray-900">Estados del Animal</h2>
                    </div>
                </div>
                
                <div class="p-8">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Estado de Salud <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-heart text-gray-400"></i>
                                </div>
                                <select 
                                    wire:model="estRecHisMed" 
                                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors" 
                                    required
                                >
                                    <option value="saludable">Saludable</option>
                                    <option value="en tratamiento">En Tratamiento</option>
                                    <option value="crónico">Crónico</option>
                                </select>
                            </div>
                            @error('estRecHisMed') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Dosis</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-syringe text-gray-400"></i>
                                </div>
                                <input 
                                    type="text" 
                                    wire:model="dosHisMed" 
                                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors" 
                                    placeholder="Ej: 5ml, 2 pastillas, etc."
                                >
                            </div>
                            @error('dosHisMed') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Duración</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-clock text-gray-400"></i>
                                </div>
                                <input 
                                    type="text" 
                                    wire:model="durHisMed" 
                                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors" 
                                    placeholder="Ej: 7 días, 2 semanas, etc."
                                >
                            </div>
                            @error('durHisMed') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Resultado -->
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Resultado</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-check-circle text-gray-400"></i>
                            </div>
                            <input 
                                type="text" 
                                wire:model="resHisMed" 
                                class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors" 
                                placeholder="Resultado del tratamiento o procedimiento"
                            >
                        </div>
                        @error('resHisMed') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Sección 5: Información Adicional -->
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
                    <p class="text-gray-600 mb-6">Observaciones especiales, características específicas, cuidados requeridos, etc.</p>
                    
                    <div class="space-y-6">
                        <!-- Descripción -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Descripción <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <div class="absolute top-3 left-3">
                                    <i class="fas fa-align-left text-gray-400"></i>
                                </div>
                                <textarea 
                                    wire:model="desHisMed" 
                                    rows="4" 
                                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors resize-none"
                                    placeholder="Descripción detallada del procedimiento o tratamiento"
                                    required
                                ></textarea>
                            </div>
                            @error('desHisMed') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Tratamiento -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tratamiento</label>
                            <div class="relative">
                                <div class="absolute top-3 left-3">
                                    <i class="fas fa-prescription text-gray-400"></i>
                                </div>
                                <textarea 
                                    wire:model="traHisMed" 
                                    rows="4" 
                                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors resize-none" 
                                    placeholder="Descripción detallada del tratamiento aplicado"
                                ></textarea>
                            </div>
                            @error('traHisMed') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Observaciones -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Observaciones</label>
                            <div class="relative">
                                <div class="absolute top-3 left-3">
                                    <i class="fas fa-eye text-gray-400"></i>
                                </div>
                                <textarea 
                                    wire:model="obsHisMed" 
                                    rows="3" 
                                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors resize-none"
                                    placeholder="Escribe aquí cualquier observación importante sobre el animal..."
                                ></textarea>
                            </div>
                            @error('obsHisMed') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Observaciones Adicionales -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Observaciones Adicionales</label>
                            <div class="relative">
                                <div class="absolute top-3 left-3">
                                    <i class="fas fa-clipboard-list text-gray-400"></i>
                                </div>
                                <textarea 
                                    wire:model="obsHisMed2" 
                                    rows="3" 
                                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors resize-none" 
                                    placeholder="Información complementaria o notas adicionales"
                                ></textarea>
                            </div>
                            @error('obsHisMed2') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
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
                    Guardar Registro
                </button>
            </div>
        </form>
    </div>
</div>