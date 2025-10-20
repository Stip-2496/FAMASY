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
        $this->reset(['razaSeleccionada', 'idAniHis', 'animalSeleccionado', 'animalesFiltrados']);
        
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
                                          ->orderBy('nitAni')
                                          ->get();
        } else {
            $this->animalesFiltrados = [];
        }
    }

    public function updatedIdAniHis($value)
    {
        if ($value) {
            $this->animalSeleccionado = Animal::find($value);
        } else {
            $this->animalSeleccionado = null;
        }
    }

    public function updatedIdProveedor($value)
    {
        if ($value) {
            $this->proveedorSeleccionado = Proveedor::find($value);
        } else {
            $this->proveedorSeleccionado = null;
        }
    }

    public function updatedIdIns($value)
    {
        if ($value) {
            $this->insumoSeleccionado = Insumo::find($value);
        } else {
            $this->insumoSeleccionado = null;
        }
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
            'obsHisMed' => 'nullable|string|max:65535'
        ];
    }

    public function limpiarFormulario(): void
    {
        $this->reset([
            'especieSeleccionada', 
            'razaSeleccionada', 
            'idAniHis', 
            'idProveedor', 
            'idIns', 
            'animalSeleccionado', 
            'proveedorSeleccionado', 
            'insumoSeleccionado',
            'tipHisMed', 
            'desHisMed', 
            'traHisMed', 
            'dosHisMed', 
            'durHisMed', 
            'resHisMed', 
            'obsHisMed'
        ]);
        $this->estRecHisMed = 'en tratamiento';
        $this->fecHisMed = date('Y-m-d');
        $this->responHisMed = auth()->user()->name;
        $this->resetErrorBag();
        $this->dispatch('registro-cancelado');
    }

    public function cancelarRegistro(): void
    {
        $this->limpiarFormulario();
    }

    public function save()
    {
        // Validar antes de proceder
        $this->validate();
        
        try {
            // Preparar los datos para crear el registro
            $data = [
                'idAni' => $this->idAniHis,
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
                'idProve' => $this->idProveedor,
            ];

            // Filtrar valores nulos/vacíos para campos opcionales pero mantener campos requeridos
            $filteredData = array_filter($data, function($value, $key) {
                if (in_array($key, ['idAni', 'tipHisMed', 'fecHisMed', 'desHisMed', 'responHisMed', 'estRecHisMed'])) {
                    return true;
                }
                return $value !== null && $value !== '';
            }, ARRAY_FILTER_USE_BOTH);

            HistorialMedico::create($filteredData);
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Registro médico creado exitosamente'
            ]);
            
            // Limpiar formulario después del registro exitoso
            $this->limpiarFormulario();
            $this->dispatch('registro-exitoso');
            
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

<div class="flex items-center justify-center min-h-screen py-3">
    <div class="w-full max-w-7xl mx-auto bg-white/80 backdrop-blur-xl shadow rounded-3xl p-3 relative border border-white/20">
        <!-- Botón Volver -->
        <div class="absolute top-2 right-2">
            <a href="{{ route('pecuario.salud-peso.index') }}" wire:navigate
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
             x-on:registro-exitoso.window="showSuccess = true; showCancel = false; setTimeout(() => showSuccess = false, 3000)"
             x-on:registro-cancelado.window="showCancel = true; showSuccess = false; setTimeout(() => showCancel = false, 3000)">
            <div class="flex justify-center mb-1">
                <div class="p-2 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl shadow-xl transform rotate-3 hover:rotate-0 transition-transform duration-300">
                    <div class="w-4 h-4 text-white flex items-center justify-center">
                        <i class="fas fa-heartbeat text-base"></i>
                    </div>
                </div>
            </div>
            <h1 class="text-base font-black bg-gradient-to-r from-gray-900 via-gray-800 to-green-800 bg-clip-text text-transparent mb-1">
                Nuevo Registro Médico
            </h1>
            
            <template x-if="showSuccess">
                <div class="rounded bg-green-100 px-2 py-1 text-green-800 border border-green-400 text-xs mb-1 font-semibold">
                    ¡Registro médico creado exitosamente!
                </div>
            </template>

            <template x-if="showCancel">
                <div class="rounded bg-yellow-100 px-2 py-1 text-yellow-800 border border-yellow-400 text-xs mb-1 font-semibold">
                    Formulario limpiado. Puede registrar un nuevo historial médico.
                </div>
            </template>

            <template x-if="!showSuccess && !showCancel">
                <p class="text-gray-600 text-xs">Complete los datos del nuevo registro médico</p>
            </template>
        </div>

        <form wire:submit.prevent="save" class="space-y-2">
            <!-- Sección 1: Selección de Animal e Información del Proveedor -->
            <div class="flex flex-col md:flex-row gap-2">
                <!-- Selección de Animal -->
                <div class="flex-1 border border-gray-300 rounded-3xl overflow-hidden">
                    <div class="h-1.5 bg-gradient-to-r from-[#000000] to-[#39A900]"></div>
                    <div class="p-2">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl shadow-lg">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xs font-bold text-gray-900">Seleccionar Animal</h2>
                                <p class="text-gray-600 text-[10px]">Datos principales del animal</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                            <!-- Selector de Especie -->
                            <div>
                                <label class="block text-[10px] font-bold text-gray-700 mb-0.5">Especie <span class="text-red-500">*</span></label>
                                <div class="relative group">
                                    <select 
                                        wire:model.live="especieSeleccionada" 
                                        class="cursor-pointer w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl appearance-none text-xs @error('especieSeleccionada') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                                        required
                                    >
                                        <option value="">Seleccionar especie</option>
                                        @foreach($especies as $especie)
                                            <option value="{{ $especie }}">{{ $especie }}</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-1.5 pointer-events-none">
                                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-indigo-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('especieSeleccionada')
                                    <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                        <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                            
                            <!-- Selector de Raza -->
                            <div>
                                <label class="block text-[10px] font-bold text-gray-700 mb-0.5">Raza <span class="text-red-500">*</span></label>
                                <div class="relative group">
                                    <select 
                                        wire:model.live="razaSeleccionada" 
                                        class="cursor-pointer w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl appearance-none text-xs disabled:bg-gray-100 disabled:cursor-not-allowed @error('razaSeleccionada') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                                        @if(!$especieSeleccionada) disabled @endif
                                        required
                                    >
                                        <option value="">Seleccionar raza</option>
                                        @foreach($razas as $raza)
                                            <option value="{{ $raza }}">{{ $raza }}</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-1.5 pointer-events-none">
                                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-indigo-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('razaSeleccionada')
                                    <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                        <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                            
                            <!-- Selector de Animal -->
                            <div>
                                <label class="block text-[10px] font-bold text-gray-700 mb-0.5">Animal <span class="text-red-500">*</span></label>
                                <div class="relative group">
                                    <select 
                                        wire:model.live="idAniHis" 
                                        class="cursor-pointer w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl appearance-none text-xs disabled:bg-gray-100 disabled:cursor-not-allowed @error('idAniHis') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                                        @if(!$razaSeleccionada) disabled @endif
                                        required
                                    >
                                        <option value="">Seleccionar animal</option>
                                        @foreach($animalesFiltrados as $animal)
                                            <option value="{{ $animal->idAni }}">
                                                @if($animal->nitAni)
                                                    {{ $animal->nitAni }}
                                                @else
                                                    Animal #{{ $animal->idAni }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-1.5 pointer-events-none">
                                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-indigo-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('idAniHis')
                                    <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                        <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>

                        <!-- Información del animal seleccionado -->
                        @if($animalSeleccionado)
                            <div class="bg-green-50/50 border border-green-200 rounded-2xl p-2 mt-2">
                                <h4 class="text-[10px] font-bold text-green-900 mb-1 flex items-center gap-1">
                                    <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Información del Animal Seleccionado
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-1 text-[10px]">
                                    <div>
                                        <span class="font-medium text-gray-700">Nombre:</span>
                                        <span class="text-gray-900">{{ $animalSeleccionado->nitAni ?? 'Sin nombre' }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-700">Especie:</span>
                                        <span class="text-gray-900">{{ $animalSeleccionado->espAni }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-700">Raza:</span>
                                        <span class="text-gray-900">{{ $animalSeleccionado->razAni ?? 'No especificada' }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-700">Sexo:</span>
                                        <span class="text-gray-900">{{ $animalSeleccionado->sexAni }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-700">Peso:</span>
                                        <span class="text-gray-900">{{ $animalSeleccionado->pesAni }}</span>
                                    </div>
                                    @if($animalSeleccionado->fecNacAni)
                                        <div>
                                            <span class="font-medium text-gray-700">Nacimiento:</span>
                                            <span class="text-gray-900">{{ date('d/m/Y', strtotime($animalSeleccionado->fecNacAni)) }}</span>
                                        </div>
                                    @endif
                                    <div>
                                        <span class="font-medium text-gray-700">Estado:</span>
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-medium bg-green-100 text-green-800">
                                            {{ ucfirst($animalSeleccionado->estAni) }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-700">Estado Reproductivo:</span>
                                        <span class="text-gray-900">{{ $animalSeleccionado->estReproAni }}</span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Información del Proveedor -->
                <div class="flex-1 border border-gray-300 rounded-3xl overflow-hidden">
                    <div class="h-1.5 bg-gradient-to-r from-[#000000] to-[#39A900]"></div>
                    <div class="p-2">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-orange-500 to-red-600 rounded-xl shadow-lg">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xs font-bold text-gray-900">Información del Proveedor</h2>
                                <p class="text-gray-600 text-[10px]">Proveedor y medicamento/insumo</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            <!-- Selector de Proveedor -->
                            <div>
                                <label class="block text-[10px] font-bold text-gray-700 mb-0.5">Procedencia/Proveedor</label>
                                <div class="relative group">
                                    <select 
                                        wire:model.live="idProveedor" 
                                        class="cursor-pointer w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl appearance-none text-xs @error('idProveedor') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                                    >
                                        <option value="">Seleccionar proveedor</option>
                                        @foreach($proveedores as $proveedor)
                                            <option value="{{ $proveedor->idProve }}">
                                                {{ $proveedor->nomProve }} ({{ $proveedor->tipSumProve ?? 'Sin tipo' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-1.5 pointer-events-none">
                                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                    <div class="absolute inset-0 bg-gradient-to-r from-orange-500/5 to-red-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('idProveedor')
                                    <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                        <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Selector de Insumo/Medicamento -->
                            <div>
                                <label class="block text-[10px] font-bold text-gray-700 mb-0.5">Medicamento/Insumo</label>
                                <div class="relative group">
                                    <select 
                                        wire:model.live="idIns" 
                                        class="cursor-pointer w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl appearance-none text-xs @error('idIns') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
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
                                    <div class="absolute inset-y-0 right-0 flex items-center px-1.5 pointer-events-none">
                                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                    <div class="absolute inset-0 bg-gradient-to-r from-orange-500/5 to-red-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('idIns')
                                    <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                        <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Información del proveedor seleccionado -->
                        @if($proveedorSeleccionado)
                            <div class="bg-blue-50/50 border border-blue-200 rounded-2xl p-2 mt-2">
                                <h4 class="text-[10px] font-bold text-blue-900 mb-1 flex items-center gap-1">
                                    <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Información del Proveedor
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-[10px]">
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
            </div>

            <!-- Sección 2: Fechas Importantes y Estados del Animal -->
            <div class="flex flex-col md:flex-row gap-2">
                <!-- Fechas Importantes -->
                <div class="flex-1 border border-gray-300 rounded-3xl overflow-hidden">
                    <div class="h-1.5 bg-gradient-to-r from-[#000000] to-[#39A900]"></div>
                    <div class="p-2">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-orange-500 to-red-600 rounded-xl shadow-lg">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xs font-bold text-gray-900">Fechas Importantes</h2>
                                <p class="text-gray-600 text-[10px]">Fechas clave del procedimiento</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                            <div>
                                <label class="block text-[10px] font-bold text-gray-700 mb-0.5">Tipo de Registro <span class="text-red-500">*</span></label>
                                <div class="relative group">
                                    <select 
                                        wire:model.live="tipHisMed" 
                                        class="cursor-pointer w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl appearance-none text-xs @error('tipHisMed') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                                        required
                                    >
                                        <option value="">Seleccionar tipo</option>
                                        <option value="vacuna">Vacuna</option>
                                        <option value="tratamiento">Tratamiento</option>
                                        <option value="control">Control de Peso</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-1.5 pointer-events-none">
                                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                    <div class="absolute inset-0 bg-gradient-to-r from-orange-500/5 to-red-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('tipHisMed')
                                    <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                        <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                            
                            <div>
                                <label class="block text-[10px] font-bold text-gray-700 mb-0.5">Fecha del Procedimiento <span class="text-red-500">*</span></label>
                                <div class="relative group">
                                    <input 
                                        type="date" 
                                        wire:model.live="fecHisMed" 
                                        class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('fecHisMed') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                                        required
                                    >
                                    <div class="absolute inset-0 bg-gradient-to-r from-orange-500/5 to-red-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('fecHisMed')
                                    <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                        <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-gray-700 mb-0.5">Responsable <span class="text-red-500">*</span></label>
                                <div class="relative group">
                                    <input 
                                        type="text" 
                                        wire:model.live="responHisMed" 
                                        class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('responHisMed') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                                        required
                                    >
                                    <div class="absolute inset-0 bg-gradient-to-r from-orange-500/5 to-red-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('responHisMed')
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

                <!-- Estados del Animal -->
                <div class="flex-1 border border-gray-300 rounded-3xl overflow-hidden">
                    <div class="h-1.5 bg-gradient-to-r from-[#000000] to-[#39A900]"></div>
                    <div class="p-2">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl shadow-lg">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2M9 19"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xs font-bold text-gray-900">Estados del Animal</h2>
                                <p class="text-gray-600 text-[10px]">Estado actual del animal</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                            <div>
                                <label class="block text-[10px] font-bold text-gray-700 mb-0.5">Estado de Salud <span class="text-red-500">*</span></label>
                                <div class="relative group">
                                    <select 
                                        wire:model.live="estRecHisMed" 
                                        class="cursor-pointer w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl appearance-none text-xs @error('estRecHisMed') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                                        required
                                    >
                                        <option value="saludable">Saludable</option>
                                        <option value="en tratamiento">En Tratamiento</option>
                                        <option value="crónico">Crónico</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-1.5 pointer-events-none">
                                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                    <div class="absolute inset-0 bg-gradient-to-r from-green-500/5 to-emerald-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('estRecHisMed')
                                    <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                        <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-gray-700 mb-0.5">Dosis</label>
                                <div class="relative group">
                                    <input 
                                        type="text" 
                                        wire:model.live="dosHisMed" 
                                        class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('dosHisMed') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                                        placeholder="Ej: 5ml, 2 pastillas, etc."
                                    >
                                    <div class="absolute inset-0 bg-gradient-to-r from-green-500/5 to-emerald-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('dosHisMed')
                                    <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                        <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-gray-700 mb-0.5">Duración</label>
                                <div class="relative group">
                                    <input 
                                        type="text" 
                                        wire:model.live="durHisMed" 
                                        class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('durHisMed') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                                        placeholder="Ej: 7 días, 2 semanas, etc."
                                    >
                                    <div class="absolute inset-0 bg-gradient-to-r from-green-500/5 to-emerald-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('durHisMed')
                                    <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                        <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>

                        <!-- Resultado -->
                        <div class="mt-2">
                            <label class="block text-[10px] font-bold text-gray-700 mb-0.5">Resultado</label>
                            <div class="relative group">
                                <input 
                                    type="text" 
                                    wire:model.live="resHisMed" 
                                    class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('resHisMed') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                                    placeholder="Resultado del tratamiento o procedimiento"
                                >
                                <div class="absolute inset-0 bg-gradient-to-r from-green-500/5 to-emerald-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                            </div>
                            @error('resHisMed')
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

            <!-- Sección 3: Información Médica -->
<div class="flex flex-col md:flex-row gap-2">
    <div class="flex-1 border border-gray-300 rounded-3xl overflow-hidden">
        <div class="h-1.5 bg-gradient-to-r from-[#39A900] to-[#000000]"></div>
        <div class="p-2">
            <div class="flex items-center space-x-2 mb-2">
                <div class="p-1.5 bg-gradient-to-br from-yellow-500 to-orange-600 rounded-xl shadow-lg">
                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-xs font-bold text-gray-900">Información Médica</h2>
                    <p class="text-gray-600 text-[10px]">Detalles del padecimiento y tratamiento aplicado</p>
                </div>
            </div>

            <!-- Tres columnas para los campos -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                <!-- Columna 1: Padecimiento -->
                <div>
                    <label class="block text-[10px] font-bold text-gray-700 mb-0.5">
                        Padecimiento <span class="text-red-500">*</span>
                    </label>
                    <div class="relative group">
                        <textarea 
                            wire:model.live="desHisMed" 
                            rows="6" 
                            class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('desHisMed') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                            placeholder="Describa los síntomas, padecimiento o problema de salud del animal..."
                            required
                        ></textarea>
                        <div class="absolute inset-0 bg-gradient-to-r from-yellow-500/5 to-orange-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                    </div>
                    @error('desHisMed')
                        <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                            <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Columna 2: Tratamiento -->
                <div>
                    <label class="block text-[10px] font-bold text-gray-700 mb-0.5">
                        Tratamiento
                    </label>
                    <div class="relative group">
                        <textarea 
                            wire:model.live="traHisMed" 
                            rows="6" 
                            class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('traHisMed') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                            placeholder="Describa el tratamiento, procedimiento o medicación aplicada..."
                        ></textarea>
                        <div class="absolute inset-0 bg-gradient-to-r from-yellow-500/5 to-orange-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                    </div>
                    @error('traHisMed')
                        <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                            <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Columna 3: Observaciones -->
                <div>
                    <label class="block text-[10px] font-bold text-gray-700 mb-0.5">
                        Observaciones
                    </label>
                    <div class="relative group">
                        <textarea 
                            wire:model.live="obsHisMed" 
                            rows="6" 
                            class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('obsHisMed') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                            placeholder="Observaciones, reacciones, evoluciones durante el tratamiento..."
                        ></textarea>
                        <div class="absolute inset-0 bg-gradient-to-r from-yellow-500/5 to-orange-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                    </div>
                    @error('obsHisMed')
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
                <button type="button" wire:click="cancelarRegistro"
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
                    <span class="relative z-10 text-xs">Guardar Registro</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('livewire:initialized', () => {
    Livewire.on('notify', (event) => {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: event.type,
            title: event.message,
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            customClass: {
                popup: 'text-xs'
            },
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });
    });
});
</script>