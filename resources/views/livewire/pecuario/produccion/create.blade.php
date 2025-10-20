<?php
use App\Models\Animal;
use App\Models\ProduccionAnimal;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component {
    // Colección de animales vivos
    public $animales = [];
    public $especies = [];
    public $razas = [];
    public $animalesFiltrados = [];
    
    // Selecciones del formulario
    public $especieSeleccionada = '';
    public $razaSeleccionada = '';
    public $idAniPro = '';
    public $animalSeleccionado = null;
    
    // Campos de producción
    public $categoriaProduccion = '';
    public $subcategoriaProduccion = '';
    public $canProAni = '';
    public $canTotProAni = '';
    public $uniProAni = '';
    public $fecProAni = '';
    public $obsProAni = '';

    // Opciones dinámicas
    public $opcionesCategoria = [];
    public $opcionesSubcategoria = [];
    public $mostrarSubcategoria = false;

    // Mapeo de opciones por especie (CORREGIDO para coincidir con ENUM de BD)
    protected $opcionesPorEspecie = [
        'bovino' => [
            'categorias' => [
                'leche_bovina' => 'Leche Bovina',
                'venta_pie_bovino' => 'Venta en pie Bovino'
            ],
            'subcategorias' => [
                'leche_bovina' => [],
                'venta_pie_bovino' => []
            ],
            'unidades' => [
                'leche_bovina' => 'litros',
                'venta_pie_bovino' => 'unidad'
            ]
        ],
        'ovino' => [
            'categorias' => [
                'leche_ovina' => 'Leche Ovina',
                'lana_ovina' => 'Lana Ovina',
                'venta_pie_ovino' => 'Venta en pie Ovino'
            ],
            'subcategorias' => [
                'leche_ovina' => [],
                'lana_ovina' => [],
                'venta_pie_ovino' => []
            ],
            'unidades' => [
                'leche_ovina' => 'litros',
                'lana_ovina' => 'kg',
                'venta_pie_ovino' => 'unidad'
            ]
        ],
        'avicola' => [
            'categorias' => [
                'huevos' => 'Huevos',
                'venta_gallinas_pie' => 'Venta Gallinas en pie'
            ],
            'subcategorias' => [
                'huevos' => [
                    'huevo A' => 'Huevo A',
                    'huevo AA' => 'Huevo AA',
                    'huevo AAA' => 'Huevo AAA',
                    'huevo Jumbo' => 'Huevo Jumbo',
                    'huevo B' => 'Huevo B',
                    'huevo C' => 'Huevo C'
                ],
                'venta_gallinas_pie' => []
            ],
            'unidades' => [
                'huevos' => 'unidad',
                'venta_gallinas_pie' => 'unidad'
            ]
        ],
        'pollo' => [
            'categorias' => [
                'venta_pollo_engorde' => 'Venta Pollo Engorde'
            ],
            'subcategorias' => [
                'venta_pollo_engorde' => []
            ],
            'unidades' => [
                'venta_pollo_engorde' => 'kg'
            ]
        ]
    ];

    public function mount()
    {
        // Cargar especies únicas de animales vivos
        $this->especies = Animal::where('estAni', 'vivo')
                               ->select('espAni')
                               ->distinct()
                               ->orderBy('espAni')
                               ->pluck('espAni')
                               ->toArray();
                               
        $this->fecProAni = now()->format('Y-m-d');
    }

    public function updatedEspecieSeleccionada($value)
    {
        // Resetear campos dependientes
        $this->razaSeleccionada = '';
        $this->idAniPro = '';
        $this->animalSeleccionado = null;
        $this->categoriaProduccion = '';
        $this->subcategoriaProduccion = '';
        $this->razas = [];
        $this->animalesFiltrados = [];
        $this->opcionesCategoria = [];
        $this->opcionesSubcategoria = [];
        $this->mostrarSubcategoria = false;
        $this->uniProAni = '';
        
        if ($value) {
            // Obtener razas para la especie seleccionada (solo razas no nulas)
            $this->razas = Animal::where('estAni', 'vivo')
                               ->where('espAni', $value)
                               ->whereNotNull('razAni')
                               ->where('razAni', '!=', '')
                               ->select('razAni')
                               ->distinct()
                               ->orderBy('razAni')
                               ->pluck('razAni')
                               ->toArray();
            
            // Si no hay razas específicas, cargar todos los animales de esa especie
            if (empty($this->razas)) {
                $this->animalesFiltrados = Animal::where('estAni', 'vivo')
                                               ->where('espAni', $value)
                                               ->orderBy('espAni')
                                               ->get()
                                               ->toArray();
            }
            
            // Configurar opciones de producción según especie
            $this->configurarOpcionesProduccion($value);
        }
    }

    public function updatedRazaSeleccionada($value)
    {
        // Resetear animal seleccionado
        $this->idAniPro = '';
        $this->animalSeleccionado = null;
        $this->animalesFiltrados = [];
        
        if ($value && $this->especieSeleccionada) {
            // Cargar animales de la especie y raza seleccionada
            $this->animalesFiltrados = Animal::where('estAni', 'vivo')
                                           ->where('espAni', $this->especieSeleccionada)
                                           ->where('razAni', $value)
                                           ->orderBy('espAni')
                                           ->get()
                                           ->toArray();
        } elseif ($this->especieSeleccionada && empty($value)) {
            // Si se deselecciona la raza pero hay especie, mostrar todos los animales de esa especie
            $this->animalesFiltrados = Animal::where('estAni', 'vivo')
                                           ->where('espAni', $this->especieSeleccionada)
                                           ->orderBy('espAni')
                                           ->get()
                                           ->toArray();
        }
    }

    public function updatedIdAniPro($value)
    {
        if ($value) {
            $this->animalSeleccionado = Animal::find($value);
        } else {
            $this->animalSeleccionado = null;
        }
    }

    public function updatedCategoriaProduccion($value)
    {
        $this->subcategoriaProduccion = '';
        $this->opcionesSubcategoria = [];
        $this->mostrarSubcategoria = false;
        $this->uniProAni = '';
        
        if (!$this->especieSeleccionada || !$value) return;
        
        $especieLower = strtolower($this->especieSeleccionada);
        $especieKey = $this->determinarEspecieKey($especieLower);
        
        if (isset($this->opcionesPorEspecie[$especieKey])) {
            $this->opcionesSubcategoria = $this->opcionesPorEspecie[$especieKey]['subcategorias'][$value] ?? [];
            $this->mostrarSubcategoria = !empty($this->opcionesSubcategoria);
            $this->uniProAni = $this->opcionesPorEspecie[$especieKey]['unidades'][$value] ?? '';
        }
    }

    private function configurarOpcionesProduccion($especie)
    {
        $especieLower = strtolower($especie);
        $especieKey = $this->determinarEspecieKey($especieLower);
        
        if (isset($this->opcionesPorEspecie[$especieKey])) {
            $this->opcionesCategoria = $this->opcionesPorEspecie[$especieKey]['categorias'];
        } else {
            $this->opcionesCategoria = [
                'otros' => 'Otros productos'
            ];
        }
    }

    private function determinarEspecieKey($especieLower)
    {
        // Especies bovinas
        if (str_contains($especieLower, 'bovino') || str_contains($especieLower, 'vaca') || 
            str_contains($especieLower, 'toro') || str_contains($especieLower, 'res') || 
            str_contains($especieLower, 'ganado bovino')) {
            return 'bovino';
        }
        
        // Especies ovinas
        elseif (str_contains($especieLower, 'ovino') || str_contains($especieLower, 'oveja') || 
                str_contains($especieLower, 'carnero') || str_contains($especieLower, 'cordero')) {
            return 'ovino';
        }
        
        // Especies avícolas - gallinas ponedoras
        elseif (str_contains($especieLower, 'gallina') || str_contains($especieLower, 'gallin') ||
                str_contains($especieLower, 'aves') || str_contains($especieLower, 'avicola') ||
                str_contains($especieLower, 'avícola') || str_contains($especieLower, 'ponedora') ||
                str_contains($especieLower, 'ave de corral')) {
            return 'avicola';
        }
        
        // Pollos específicamente (separado de avícola general)
        elseif (str_contains($especieLower, 'pollo') || str_contains($especieLower, 'broiler') ||
                str_contains($especieLower, 'engorde')) {
            return 'pollo';
        }
        
        return 'otros';
    }

    public function rules()
    {
        return [
            'idAniPro' => 'required|exists:animales,idAni',
            'categoriaProduccion' => 'required|string',
            'subcategoriaProduccion' => 'nullable|required_if:mostrarSubcategoria,true|string',
            'canProAni' => 'required|numeric|min:0.01|max:9999.99',
            'canTotProAni' => 'nullable|numeric|min:0|max:999999.99',
            'uniProAni' => 'nullable|string|max:20',
            'fecProAni' => 'required|date|before_or_equal:today',
            'obsProAni' => 'nullable|string|max:500'
        ];
    }

    // MÉTODO CORREGIDO - Mapea correctamente a los valores ENUM de la BD
    protected function obtenerTipoProduccionFinal()
    {
        // Si hay subcategoría específica (como tipos de huevos), usar esa
        if ($this->mostrarSubcategoria && $this->subcategoriaProduccion) {
            return $this->subcategoriaProduccion;
        }

        // Mapear categorías a valores exactos del ENUM
        $mapeoTipos = [
            'leche_bovina' => 'leche bovina',
            'venta_pie_bovino' => 'venta en pie bovino',
            'leche_ovina' => 'leche ovina',
            'lana_ovina' => 'lana ovina',
            'venta_pie_ovino' => 'venta en pie ovino',
            'venta_gallinas_pie' => 'venta gallinas en pie',
            'venta_pollo_engorde' => 'venta pollo engorde'
        ];

        return $mapeoTipos[$this->categoriaProduccion] ?? 'otros';
    }

    // Método para limpiar todos los campos
    public function limpiarFormulario(): void
    {
        $this->especieSeleccionada = '';
        $this->razaSeleccionada = '';
        $this->idAniPro = '';
        $this->animalSeleccionado = null;
        $this->categoriaProduccion = '';
        $this->subcategoriaProduccion = '';
        $this->canProAni = '';
        $this->canTotProAni = '';
        $this->uniProAni = '';
        $this->fecProAni = now()->format('Y-m-d');
        $this->obsProAni = '';
        
        // Limpiar errores de validación
        $this->resetErrorBag();
    }

    // Método para cancelar registro (limpia el formulario)
    public function cancelarRegistro(): void
    {
        $this->limpiarFormulario();
        
        // Disparar evento para mostrar mensaje
        $this->dispatch('registro-cancelado');
    }

    public function save()
    {
        $this->validate();
        
        try {
            $tipoProduccion = $this->obtenerTipoProduccionFinal();
            
            // Debug para verificar el valor que se está enviando
            \Log::info('Guardando producción', [
                'idAniPro' => $this->idAniPro,
                'tipProAni' => $tipoProduccion,
                'categoriaProduccion' => $this->categoriaProduccion,
                'subcategoriaProduccion' => $this->subcategoriaProduccion,
                'mostrarSubcategoria' => $this->mostrarSubcategoria
            ]);
            
            ProduccionAnimal::create([
                'idAniPro' => $this->idAniPro,
                'tipProAni' => $tipoProduccion,
                'canProAni' => $this->canProAni,
                'canTotProAni' => $this->canTotProAni ?: null,
                'uniProAni' => $this->uniProAni ?: null,
                'fecProAni' => $this->fecProAni,
                'obsProAni' => $this->obsProAni ?: null
            ]);
            
            // Limpiar el formulario después del registro exitoso
            $this->limpiarFormulario();

            // Disparar evento para mostrar mensaje de éxito
            $this->dispatch('registro-exitoso');
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Registro de producción creado exitosamente!'
            ]);
            
            // ELIMINADO: $this->redirect(route('pecuario.produccion.index'), navigate: true);
            // Ahora permanece en la misma página después del registro
        } catch (\Exception $e) {
            \Log::error('Error al guardar producción: ' . $e->getMessage());
            
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al guardar: ' . $e->getMessage()
            ]);
        }
    }

    // Método para debugging (opcional - remover en producción)
    public function debug()
    {
        $especieKey = $this->especieSeleccionada ? $this->determinarEspecieKey(strtolower($this->especieSeleccionada)) : 'ninguna';
        $tipoProduccionFinal = $this->obtenerTipoProduccionFinal();
        
        dd([
            'especies_en_bd' => $this->especies,
            'especieSeleccionada' => $this->especieSeleccionada,
            'especieKey_detectada' => $especieKey,
            'opcionesCategoria' => $this->opcionesCategoria,
            'categoriaProduccion' => $this->categoriaProduccion,
            'subcategoriaProduccion' => $this->subcategoriaProduccion,
            'mostrarSubcategoria' => $this->mostrarSubcategoria,
            'opcionesSubcategoria' => $this->opcionesSubcategoria,
            'tipoProduccionFinal' => $tipoProduccionFinal,
            'valores_enum_permitidos' => [
                'leche bovina', 'venta en pie bovino', 'lana ovina', 'venta en pie ovino', 
                'leche ovina', 'venta gallinas en pie', 'huevo A', 'huevo AA', 'huevo AAA', 
                'huevo Jumbo', 'huevo B', 'huevo C', 'venta pollo engorde', 'otros'
            ]
        ]);
    }
};
?>

@section('title', 'Registrar Producción')

<div class="flex items-center justify-center min-h-screen py-3">
    <div class="w-full max-w-7xl mx-auto bg-white/80 backdrop-blur-xl shadow rounded-3xl p-3 relative border border-white/20">
        <!-- Botón Volver -->
        <div class="absolute top-2 right-2">
            <a href="{{ route('pecuario.produccion.index') }}" wire:navigate
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
                        <i class="fas fa-chart-line text-base"></i>
                    </div>
                </div>
            </div>
            <h1 class="text-base font-black bg-gradient-to-r from-gray-900 via-gray-800 to-green-800 bg-clip-text text-transparent mb-1">
                Registrar Nueva Producción
            </h1>
            
            <template x-if="showSuccess">
                <div class="rounded bg-green-100 px-2 py-1 text-green-800 border border-green-400 text-xs mb-1 font-semibold">
                    ¡Producción registrada exitosamente!
                </div>
            </template>

            <template x-if="showCancel">
                <div class="rounded bg-yellow-100 px-2 py-1 text-yellow-800 border border-yellow-400 text-xs mb-1 font-semibold">
                    Formulario limpiado. Puede registrar una nueva producción.
                </div>
            </template>

            <template x-if="!showSuccess && !showCancel">
                <p class="text-gray-600 text-xs">Complete los datos de la nueva producción</p>
            </template>
        </div>

        <form wire:submit.prevent="save" class="space-y-2" enctype="multipart/form-data">
            <!-- Sección: Información del Animal -->
            <div class="border border-gray-300 rounded-3xl overflow-hidden">
                <div class="h-1.5 bg-gradient-to-r from-[#000000] to-[#39A900]"></div>
                <div class="p-2">
                    <div class="flex items-center space-x-2 mb-2">
                        <div class="p-1.5 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full shadow-lg w-6 h-6 flex items-center justify-center">
                            <i class="fas fa-cow text-white text-xs"></i>
                        </div>
                        <div>
                            <h2 class="text-xs font-bold text-gray-900">Selección del Animal</h2>
                            <p class="text-gray-600 text-[10px]">Seleccione el animal para registrar producción</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                        <!-- Selector de Especie -->
                        <div>
                            <label class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                Especie <span class="text-red-500">*</span>
                            </label>
                            <div class="relative group">
                                <select 
                                    wire:model.live="especieSeleccionada" 
                                    class="cursor-pointer w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl appearance-none text-xs @error('especieSeleccionada') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                                    required
                                >
                                    <option value="">Seleccionar especie</option>
                                    @foreach($especies as $especie)
                                        <option value="{{ $especie }}">{{ ucfirst($especie) }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-1.5 pointer-events-none">
                                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Selector de Raza -->
                        <div>
                            <label class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                Raza (opcional)
                            </label>
                            <div class="relative group">
                                <select 
                                    wire:model.live="razaSeleccionada" 
                                    class="cursor-pointer w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl appearance-none text-xs @error('razaSeleccionada') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                                    @if(!$especieSeleccionada) disabled @endif
                                >
                                    <option value="">Todas las razas</option>
                                    @if(is_array($razas))
                                        @foreach($razas as $raza)
                                            <option value="{{ $raza }}">{{ ucfirst($raza) }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-1.5 pointer-events-none">
                                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                            </div>
                            @if(!$especieSeleccionada)
                                <p class="text-xs text-gray-500 mt-0.5">Seleccione una especie primero</p>
                            @elseif(empty($razas))
                                <p class="text-xs text-gray-500 mt-0.5">Sin razas específicas para esta especie</p>
                            @endif
                        </div>
                        
                        <!-- Selector de Animal -->
                        <div>
                            <label class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                Animal <span class="text-red-500">*</span>
                            </label>
                            <div class="relative group">
                                <select 
                                    wire:model.live="idAniPro" 
                                    class="cursor-pointer w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl appearance-none text-xs @error('idAniPro') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                                    @if(!$especieSeleccionada) disabled @endif
                                    required
                                >
                                    <option value="">Seleccionar animal</option>
                                    @if(is_array($animalesFiltrados))
                                        @foreach($animalesFiltrados as $animal)
                                            <option value="{{ $animal['idAni'] }}">
                                                @if($animal['nitAni'])
                                                    {{ $animal['nitAni'] }}
                                                @else
                                                    {{ $animal['nitAni'] }}
                                                @endif
                                                @if($animal['razAni'])
                                                    - {{ $animal['razAni'] }}
                                                @endif
                                                @if($animal['sexAni'])
                                                    - {{ $animal['sexAni'] }}
                                                @endif
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-1.5 pointer-events-none">
                                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                            </div>
                            @if(!$especieSeleccionada)
                                <p class="text-xs text-gray-500 mt-0.5">Seleccione una especie primero</p>
                            @elseif(empty($animalesFiltrados))
                                <p class="text-xs text-gray-500 mt-0.5">No hay animales disponibles</p>
                            @endif
                        </div>
                    </div>

                    <!-- Información del Animal Seleccionado -->
                    @if($animalSeleccionado)
                    <div class="mt-2 p-2 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl border border-blue-200">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-2 text-xs">
                            <div class="text-center">
                                <p class="text-[10px] text-gray-500">NIT Animal</p>
                                <p class="font-bold text-gray-800">{{ $animalSeleccionado->nitAni }}</p>
                            </div>
                            <div class="text-center">
                                <p class="text-[10px] text-gray-500">Especie</p>
                                <p class="font-bold text-gray-800">{{ $animalSeleccionado->espAni }}</p>
                            </div>
                            <div class="text-center">
                                <p class="text-[10px] text-gray-500">Raza</p>
                                <p class="font-bold text-gray-800">{{ $animalSeleccionado->razAni ?? 'No especificada' }}</p>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="mt-2 p-2 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl border border-blue-200">
                        <p class="text-blue-700 text-xs flex items-center justify-center">
                            Seleccione un animal para ver su información
                        </p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Sección Unificada: Tipo y Detalles de Producción -->
            <div class="flex flex-col md:flex-row gap-2">
                <!-- Tipo de Producción -->
                <div class="flex-1 border border-gray-300 rounded-3xl overflow-hidden">
                    <div class="h-1.5 bg-gradient-to-r from-[#39A900] to-[#000000]"></div>
                    <div class="p-2">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-purple-500 to-pink-600 rounded-full shadow-lg w-6 h-6 flex items-center justify-center">
                                <i class="fas fa-list text-white text-xs"></i>
                            </div>
                            <div>
                                <h2 class="text-xs font-bold text-gray-900">Tipo de Producción</h2>
                                <p class="text-gray-600 text-[10px]">Seleccione la categoría y tipo de producción</p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <!-- Categoría de Producción -->
                            <div>
                                <label class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Categoría <span class="text-red-500">*</span>
                                </label>
                                <div class="relative group">
                                    <select 
                                        wire:model.live="categoriaProduccion" 
                                        class="cursor-pointer w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl appearance-none text-xs @error('categoriaProduccion') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                                        required
                                    >
                                        <option value="">Seleccione una categoría</option>
                                        @foreach($opcionesCategoria as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-1.5 pointer-events-none">
                                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </div>
                                @error('categoriaProduccion')
                                    <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                        <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Subcategoría (solo para ciertas producciones como huevos) -->
                            @if($mostrarSubcategoria)
                            <div>
                                <label class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Tipo específico <span class="text-red-500">*</span>
                                </label>
                                <div class="relative group">
                                    <select 
                                        wire:model="subcategoriaProduccion" 
                                        class="cursor-pointer w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl appearance-none text-xs @error('subcategoriaProduccion') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                                        required
                                    >
                                        <option value="">Seleccione un tipo</option>
                                        @foreach($opcionesSubcategoria as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-1.5 pointer-events-none">
                                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </div>
                                @error('subcategoriaProduccion')
                                    <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                        <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Detalles de Producción -->
                <div class="flex-1 border border-gray-300 rounded-3xl overflow-hidden">
                    <div class="h-1.5 bg-gradient-to-r from-[#000000] to-[#39A900]"></div>
                    <div class="p-2">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-orange-500 to-red-600 rounded-full shadow-lg w-6 h-6 flex items-center justify-center">
                                <i class="fas fa-calculator text-white text-xs"></i>
                            </div>
                            <div>
                                <h2 class="text-xs font-bold text-gray-900">Detalles de Producción</h2>
                                <p class="text-gray-600 text-[10px]">Información cuantitativa de la producción</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            <!-- Cantidad -->
                            <div>
                                <label class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Cantidad <span class="text-red-500">*</span>
                                </label>
                                <div class="relative group">
                                    <input type="number" step="0.01" wire:model="canProAni"
                                           class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('canProAni') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                                           min="0.01" max="9999.99" required>
                                </div>
                                @error('canProAni')
                                    <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                        <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                            
                            <!-- Unidad -->
                            <div>
                                <label class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Unidad
                                </label>
                                <div class="relative group">
                                    <input type="text" wire:model="uniProAni"
                                           class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs bg-gray-50 @error('uniProAni') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                                           placeholder="Unidad de medida">
                                </div>
                                @error('uniProAni')
                                    <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                        <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Cantidad Total -->
                            <div>
                                <label class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Cantidad Total (opcional)
                                </label>
                                <div class="relative group">
                                    <input type="number" step="0.01" wire:model="canTotProAni"
                                           class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('canTotProAni') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                                           placeholder="Cantidad acumulada">
                                </div>
                                @error('canTotProAni')
                                    <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                        <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Fecha -->
                            <div>
                                <label class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Fecha <span class="text-red-500">*</span>
                                </label>
                                <div class="relative group">
                                    <input type="date" wire:model="fecProAni"
                                           class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('fecProAni') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                                           max="{{ now()->format('Y-m-d') }}" required>
                                </div>
                                @error('fecProAni')
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

            <!-- Sección: Observaciones -->
            <div class="border border-gray-300 rounded-3xl overflow-hidden">
                <div class="h-1.5 bg-gradient-to-r from-[#39A900] to-[#000000]"></div>
                <div class="p-2">
                    <div class="flex items-center space-x-2 mb-2">
                        <div class="p-1.5 bg-gradient-to-br from-yellow-500 to-orange-600 rounded-full shadow-lg w-6 h-6 flex items-center justify-center">
                            <i class="fas fa-sticky-note text-white text-xs"></i>
                        </div>
                        <div>
                            <h2 class="text-xs font-bold text-gray-900">Información Adicional</h2>
                            <p class="text-gray-600 text-[10px]">Observaciones adicionales sobre la producción</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-gray-700 mb-0.5">
                            Observaciones
                        </label>
                        <div class="relative group">
                            <textarea wire:model="obsProAni" rows="3"
                                      placeholder="Observaciones adicionales sobre la producción..."
                                      class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('obsProAni') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"></textarea>
                        </div>
                        @error('obsProAni')
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
                    <span class="relative z-10 text-xs">Guardar Producción</span>
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