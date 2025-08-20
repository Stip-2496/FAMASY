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
                                               ->orderBy('nomAni')
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
                                           ->orderBy('nomAni')
                                           ->get()
                                           ->toArray();
        } elseif ($this->especieSeleccionada && empty($value)) {
            // Si se deselecciona la raza pero hay especie, mostrar todos los animales de esa especie
            $this->animalesFiltrados = Animal::where('estAni', 'vivo')
                                           ->where('espAni', $this->especieSeleccionada)
                                           ->orderBy('nomAni')
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
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Registro de producción creado exitosamente!'
            ]);
            
            $this->redirect(route('pecuario.produccion.index'), navigate: true);
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

<!-- VISTA -->
<div class="max-w-4xl mx-auto px-4 py-6">
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="bg-green-600 text-white px-6 py-4">
            <h2 class="text-lg font-semibold">
                <i class="fas fa-plus-circle mr-2"></i> Nuevo Registro de Producción
            </h2>
        </div>

        <div class="px-6 py-4">
            <form wire:submit="save">
                <!-- Selectores jerárquicos para animales -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <!-- Selector de Especie -->
                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Especie <span class="text-red-500">*</span></label>
                        <select 
                            wire:model.live="especieSeleccionada" 
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                            required
                        >
                            <option value="">Seleccionar especie</option>
                            @foreach($especies as $especie)
                                <option value="{{ $especie }}">{{ ucfirst($especie) }}</option>
                            @endforeach
                        </select>
                        <div wire:loading wire:target="especieSeleccionada" class="text-sm text-blue-500 mt-1">
                            <i class="fas fa-spinner fa-spin"></i> Cargando razas...
                        </div>
                    </div>
                    
                    <!-- Selector de Raza -->
                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Raza (opcional)</label>
                        <select 
                            wire:model.live="razaSeleccionada" 
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                            @if(!$especieSeleccionada) disabled @endif
                        >
                            <option value="">Todas las razas</option>
                            @if(is_array($razas))
                                @foreach($razas as $raza)
                                    <option value="{{ $raza }}">{{ ucfirst($raza) }}</option>
                                @endforeach
                            @endif
                        </select>
                        @if(!$especieSeleccionada)
                            <p class="text-sm text-gray-500 mt-1">Seleccione una especie primero</p>
                        @elseif(empty($razas))
                            <p class="text-sm text-gray-500 mt-1">Sin razas específicas para esta especie</p>
                        @endif
                        <div wire:loading wire:target="razaSeleccionada" class="text-sm text-blue-500 mt-1">
                            <i class="fas fa-spinner fa-spin"></i> Cargando animales...
                        </div>
                    </div>
                    
                    <!-- Selector de Animal -->
                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Animal <span class="text-red-500">*</span></label>
                        <select 
                            wire:model.live="idAniPro" 
                            class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                            @if(!$especieSeleccionada) disabled @endif
                            required
                        >
                            <option value="">Seleccionar animal</option>
                            @if(is_array($animalesFiltrados))
                                @foreach($animalesFiltrados as $animal)
                                    <option value="{{ $animal['idAni'] }}">
                                        @if($animal['nomAni'])
                                            {{ $animal['nomAni'] }} (ID: {{ $animal['idAni'] }})
                                        @else
                                            Animal #{{ $animal['idAni'] }}
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
                        @if(!$especieSeleccionada)
                            <p class="text-sm text-gray-500 mt-1">Seleccione una especie primero</p>
                        @elseif(empty($animalesFiltrados))
                            <p class="text-sm text-gray-500 mt-1">No hay animales disponibles</p>
                        @endif
                        <div wire:loading wire:target="idAniPro" class="text-sm text-blue-500 mt-1">
                            <i class="fas fa-spinner fa-spin"></i> Cargando información...
                        </div>
                    </div>
                </div>
                
                <!-- Categoría de Producción -->
                <div class="mb-6">
                    <label class="block mb-1 font-medium text-gray-700">Tipo de Producción <span class="text-red-500">*</span></label>
                    <select 
                        wire:model.live="categoriaProduccion" 
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                        required
                    >
                        <option value="">Seleccione una categoría</option>
                        @foreach($opcionesCategoria as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('categoriaProduccion')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Subcategoría (solo para ciertas producciones como huevos) -->
                @if($mostrarSubcategoria)
                <div class="mb-6">
                    <label class="block mb-1 font-medium text-gray-700">Tipo específico <span class="text-red-500">*</span></label>
                    <select 
                        wire:model="subcategoriaProduccion" 
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                        required
                    >
                        <option value="">Seleccione un tipo</option>
                        @foreach($opcionesSubcategoria as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('subcategoriaProduccion')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                @endif

                <!-- Cantidad y Unidad -->
                <div class="grid md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Cantidad <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" wire:model="canProAni"
                               class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                               min="0.01" max="9999.99" required>
                        @error('canProAni')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Unidad</label>
                        <input type="text" wire:model="uniProAni"
                               class="w-full border border-gray-300 rounded px-3 py-2 bg-gray-50 focus:outline-none"
                               placeholder="Unidad de medida" readonly>
                        @error('uniProAni')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Cantidad Total -->
                <div class="mb-6">
                    <label class="block mb-1 font-medium text-gray-700">Cantidad Total (opcional)</label>
                    <input type="number" step="0.01" wire:model="canTotProAni"
                           class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                           placeholder="Cantidad acumulada">
                    @error('canTotProAni')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Fecha -->
                <div class="mb-6">
                    <label class="block mb-1 font-medium text-gray-700">Fecha <span class="text-red-500">*</span></label>
                    <input type="date" wire:model="fecProAni"
                           class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                           max="{{ now()->format('Y-m-d') }}" required>
                    @error('fecProAni')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Observaciones -->
                <div class="mb-6">
                    <label class="block mb-1 font-medium text-gray-700">Observaciones</label>
                    <textarea wire:model="obsProAni" rows="3"
                              class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                              placeholder="Observaciones adicionales..."></textarea>
                    @error('obsProAni')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Botones -->
                <div class="flex justify-between mt-6">
                    <a href="{{ route('pecuario.produccion.index') }}" wire:navigate
                       class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded shadow transition duration-200">
                        <i class="fas fa-times mr-2"></i> Cancelar
                    </a>
                    <button type="submit"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow transition duration-200"
                            wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="save">
                            <i class="fas fa-save mr-2"></i> Guardar Registro
                        </span>
                        <span wire:loading wire:target="save">
                            <i class="fas fa-spinner fa-spin mr-2"></i> Guardando...
                        </span>
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>