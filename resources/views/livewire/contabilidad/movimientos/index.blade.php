<?php
// resources/views/livewire/contabilidad/movimientos/index.blade.php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

new #[Layout('layouts.auth')] class extends Component {
    use WithPagination;

    // Propiedades para filtros
    public $tipo = '';
    public $fecha_desde = '';
    public $fecha_hasta = '';
    public $categoria = '';
    public $buscar = '';
    public $monto_min = '';
    public $monto_max = '';
    public $per_page = 50;

    // Propiedades para el modal de nuevo/editar movimiento
    public $modalAbierto = false;
    public $modalEdicion = false;
    public $movimientoId = null;
    public $tipoMovimiento = '';
    public $descripcion = '';
    public $monto = '';
    public $categoria_id = '';
    public $fecha = '';
    public $observaciones = '';

    // Propiedades calculadas
    public $totalesFiltrados = [];
    public $categorias = [];

    public function mount()
    {
        // Inicializar filtros desde parámetros URL
        $this->tipo = request('tipo', '');
        $this->fecha_desde = request('fecha_desde', '');
        $this->fecha_hasta = request('fecha_hasta', '');
        $this->categoria = request('categoria', '');
        $this->buscar = request('buscar', '');
        $this->monto_min = request('monto_min', '');
        $this->monto_max = request('monto_max', '');

        $this->fecha = date('Y-m-d');
        $this->cargarCategorias();
        $this->calcularTotalesIniciales();
    }

    public function cargarCategorias()
    {
        try {
            // Cargar categorías de movimientos existentes (método simple y funcional)
            $this->categorias = DB::table('movimientoscontables')
                ->select('catMovCont')
                ->whereNotNull('catMovCont')
                ->where('catMovCont', '!=', '')
                ->distinct()
                ->pluck('catMovCont')
                ->toArray();
                
            // Si no hay categorías, agregar algunas por defecto
            if (empty($this->categorias)) {
                $this->categorias = [
                    'Ventas',
                    'Servicios', 
                    'Materiales',
                    'Marketing',
                    'Oficina',
                    'Otros'
                ];
            }
        } catch (\Exception $e) {
            Log::error('Error cargando categorías: ' . $e->getMessage());
            $this->categorias = ['Sin categoría'];
        }
    }

    private function calcularTotalesIniciales()
    {
        try {
            $totales = DB::table('movimientoscontables')
                ->selectRaw('
                    SUM(CASE WHEN tipoMovCont = "ingreso" THEN montoMovCont ELSE 0 END) as total_ingresos,
                    SUM(CASE WHEN tipoMovCont = "egreso" THEN montoMovCont ELSE 0 END) as total_egresos,
                    COUNT(*) as cantidad_registros
                ')
                ->first();

            $this->totalesFiltrados = [
                'total_ingresos' => $totales->total_ingresos ?? 0,
                'total_egresos' => $totales->total_egresos ?? 0,
                'balance' => ($totales->total_ingresos ?? 0) - ($totales->total_egresos ?? 0),
                'cantidad_registros' => $totales->cantidad_registros ?? 0,
                'total_ingresos_formateado' => number_format($totales->total_ingresos ?? 0, 2),
                'total_egresos_formateado' => number_format($totales->total_egresos ?? 0, 2),
                'balance_formateado' => number_format(($totales->total_ingresos ?? 0) - ($totales->total_egresos ?? 0), 2)
            ];

        } catch (\Exception $e) {
            Log::error('Error calculando totales iniciales: ' . $e->getMessage());
            $this->totalesFiltrados = [
                'total_ingresos' => 0,
                'total_egresos' => 0,
                'balance' => 0,
                'cantidad_registros' => 0,
                'total_ingresos_formateado' => '0.00',
                'total_egresos_formateado' => '0.00',
                'balance_formateado' => '0.00'
            ];
        }
    }

    public function getMovimientosProperty()
{
    try {
        $query = DB::table('movimientoscontables');

        // AGREGAR ESTAS LÍNEAS AQUÍ
        Log::info('Ejecutando filtros:', [
            'fecha_desde' => $this->fecha_desde,
            'fecha_hasta' => $this->fecha_hasta,
        ]);
        
        // Aplicar filtros
        if ($this->tipo) {
            $query->where('tipoMovCont', $this->tipo);
        }

        if ($this->fecha_desde) {
            try {
                $fechaDesde = Carbon::parse($this->fecha_desde)->format('Y-m-d');
                $query->where('fecMovCont', '>=', $fechaDesde);
                Log::info('Aplicado fecha_desde: ' . $fechaDesde . ' - Registros: ' . $query->count());
            } catch (\Exception $e) {
                Log::error('Error parsing fecha_desde: ' . $e->getMessage());
            }
        }

        if ($this->fecha_hasta) {
            try {
                $fechaHasta = Carbon::parse($this->fecha_hasta)->format('Y-m-d');
                $query->where('fecMovCont', '<=', $fechaHasta);
                Log::info('Aplicado fecha_hasta: ' . $fechaHasta . ' - Registros: ' . $query->count());
            } catch (\Exception $e) {
                Log::error('Error parsing fecha_hasta: ' . $e->getMessage());
            }
        }

        if ($this->categoria) {
            $query->where('catMovCont', 'like', '%' . $this->categoria . '%');
        }

        if ($this->buscar) {
            $query->where('conceptoMovCont', 'like', '%' . $this->buscar . '%');
        }

        if ($this->monto_min) {
            $query->where('montoMovCont', '>=', $this->monto_min);
        }

        if ($this->monto_max) {
            $query->where('montoMovCont', '<=', $this->monto_max);
        }

        // Calcular totales si hay filtros activos
        if ($this->hayFiltrosActivos()) {
            $this->calcularTotales($query);
        }

        return $query->orderBy('fecMovCont', 'desc')
                    ->paginate($this->per_page);

    } catch (\Exception $e) {
        Log::error('Error al obtener movimientos: ' . $e->getMessage());
        return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->per_page);
    }
}

public function validarRangoFechas()
{
    if ($this->fecha_desde && $this->fecha_hasta) {
        try {
            $desde = Carbon::parse($this->fecha_desde);
            $hasta = Carbon::parse($this->fecha_hasta);
            
            if ($desde->gt($hasta)) {
                session()->flash('error', 'La fecha "Desde" no puede ser mayor que la fecha "Hasta"');
                return false;
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Formato de fecha inválido');
            return false;
        }
    }
    return true;
}

    private function calcularTotales($query)
    {
        try {
            $baseQuery = clone $query;
            
            $totales = $baseQuery->selectRaw('
                SUM(CASE WHEN tipoMovCont = "ingreso" THEN montoMovCont ELSE 0 END) as total_ingresos,
                SUM(CASE WHEN tipoMovCont = "egreso" THEN montoMovCont ELSE 0 END) as total_egresos,
                COUNT(*) as cantidad_registros
            ')->first();

            $this->totalesFiltrados = [
                'total_ingresos' => $totales->total_ingresos ?? 0,
                'total_egresos' => $totales->total_egresos ?? 0,
                'balance' => ($totales->total_ingresos ?? 0) - ($totales->total_egresos ?? 0),
                'cantidad_registros' => $totales->cantidad_registros ?? 0,
                'total_ingresos_formateado' => number_format($totales->total_ingresos ?? 0, 2),
                'total_egresos_formateado' => number_format($totales->total_egresos ?? 0, 2),
                'balance_formateado' => number_format(($totales->total_ingresos ?? 0) - ($totales->total_egresos ?? 0), 2)
            ];

        } catch (\Exception $e) {
            Log::error('Error calculando totales: ' . $e->getMessage());
            $this->totalesFiltrados = [
                'total_ingresos' => 0,
                'total_egresos' => 0,
                'balance' => 0,
                'cantidad_registros' => 0,
                'total_ingresos_formateado' => '0.00',
                'total_egresos_formateado' => '0.00',
                'balance_formateado' => '0.00'
            ];
        }
    }

    private function hayFiltrosActivos()
{
    return !empty($this->tipo) || 
           !empty($this->fecha_desde) || 
           !empty($this->fecha_hasta) || 
           !empty($this->categoria) || 
           !empty($this->buscar) || 
           (!empty($this->monto_min) && $this->monto_min > 0) || 
           (!empty($this->monto_max) && $this->monto_max > 0);
}

    public function aplicarFiltros()
    {
        $this->resetPage();
    }

    public function limpiarFiltros()
    {
        $this->reset(['tipo', 'fecha_desde', 'fecha_hasta', 'categoria', 'buscar', 'monto_min', 'monto_max']);
        $this->resetPage();
    }

    public function abrirModal()
    {
        $this->modalAbierto = true;
        $this->modalEdicion = false;
        $this->reset(['tipoMovimiento', 'descripcion', 'monto', 'categoria_id', 'observaciones', 'movimientoId']);
        $this->fecha = date('Y-m-d');
    }

    public function editarMovimiento($idMovimiento)
    {
        try {
            $movimiento = DB::table('movimientoscontables')
                ->where('idMovCont', $idMovimiento)
                ->first();

            if (!$movimiento) {
                session()->flash('error', 'Movimiento no encontrado');
                return;
            }

            $this->modalAbierto = true;
            $this->modalEdicion = true;
            $this->movimientoId = $movimiento->idMovCont;
            $this->tipoMovimiento = $movimiento->tipoMovCont;
            $this->descripcion = $movimiento->conceptoMovCont;
            $this->monto = $movimiento->montoMovCont;
            $this->categoria_id = $movimiento->catMovCont;
            $this->fecha = $movimiento->fecMovCont;
            $this->observaciones = $movimiento->obsMovCont ?? '';

        } catch (\Exception $e) {
            Log::error('Error al cargar movimiento para edición: ' . $e->getMessage());
            session()->flash('error', 'Error al cargar el movimiento');
        }
    }

    public function cerrarModal()
    {
        $this->modalAbierto = false;
        $this->resetValidation();
    }

    public function rules(): array
    {
        return [
            'tipoMovimiento' => 'required|in:ingreso,egreso',
            'descripcion' => 'required|string|max:200',
            'monto' => 'required|numeric|min:0.01|max:999999999.99',
            'fecha' => 'required|date|before_or_equal:today',
            'categoria_id' => 'nullable|string|max:100',
            'observaciones' => 'nullable|string|max:500'
        ];
    }

    public function messages(): array
    {
        return [
            'tipoMovimiento.required' => 'El tipo de movimiento es obligatorio',
            'tipoMovimiento.in' => 'El tipo de movimiento debe ser ingreso o egreso',
            'descripcion.required' => 'La descripción es obligatoria',
            'descripcion.max' => 'La descripción no puede exceder 200 caracteres',
            'monto.required' => 'El monto es obligatorio',
            'monto.numeric' => 'El monto debe ser un valor numérico',
            'monto.min' => 'El monto debe ser mayor a 0',
            'monto.max' => 'El monto no puede exceder 999,999,999.99',
            'fecha.required' => 'La fecha es obligatoria',
            'fecha.date' => 'La fecha debe tener un formato válido',
            'fecha.before_or_equal' => 'La fecha no puede ser futura',
        ];
    }

    public function guardarMovimiento()
    {
        $validated = $this->validate();

        DB::beginTransaction();
        try {
            $datos = [
                'fecMovCont' => $this->fecha,
                'tipoMovCont' => $this->tipoMovimiento,
                'catMovCont' => $this->categoria_id ?: 'Sin categoría',
                'conceptoMovCont' => $this->descripcion,
                'montoMovCont' => $this->monto,
                'obsMovCont' => $this->observaciones,
                'updated_at' => now()
            ];

            if ($this->modalEdicion && $this->movimientoId) {
                DB::table('movimientoscontables')
                    ->where('idMovCont', $this->movimientoId)
                    ->update($datos);

                session()->flash('success', 'Movimiento actualizado exitosamente');
            } else {
                $datos['created_at'] = now();
                DB::table('movimientoscontables')->insert($datos);
                session()->flash('success', 'Movimiento registrado exitosamente');
            }

            DB::commit();
            $this->cerrarModal();
            $this->cargarCategorias();
            $this->calcularTotalesIniciales();
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error al guardar movimiento: ' . $e->getMessage());
            session()->flash('error', 'Error al guardar el movimiento: ' . $e->getMessage());
        }
    }

    public function eliminarMovimiento($idMovimiento)
    {
        DB::beginTransaction();
        try {
            $movimiento = DB::table('movimientoscontables')
                ->where('idMovCont', $idMovimiento)
                ->first();

            if (!$movimiento) {
                session()->flash('error', 'Movimiento no encontrado');
                return;
            }

            $deleted = DB::table('movimientoscontables')
                ->where('idMovCont', $idMovimiento)
                ->delete();

            if ($deleted) {
                session()->flash('success', 'Movimiento eliminado correctamente');
                $this->calcularTotalesIniciales();
            } else {
                session()->flash('error', 'No se pudo eliminar el movimiento');
            }

            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error al eliminar movimiento: ' . $e->getMessage());
            session()->flash('error', 'Error al eliminar el movimiento');
        }
    }

    public function duplicarMovimiento($idMovimiento)
    {
        try {
            $movimiento = DB::table('movimientoscontables')
                ->where('idMovCont', $idMovimiento)
                ->first();

            if (!$movimiento) {
                session()->flash('error', 'Movimiento no encontrado');
                return;
            }

            $this->modalAbierto = true;
            $this->modalEdicion = false;
            $this->movimientoId = null;
            $this->tipoMovimiento = $movimiento->tipoMovCont;
            $this->descripcion = $movimiento->conceptoMovCont . ' (Copia)';
            $this->monto = $movimiento->montoMovCont;
            $this->categoria_id = $movimiento->catMovCont;
            $this->fecha = date('Y-m-d');
            $this->observaciones = $movimiento->obsMovCont ?? '';

        } catch (\Exception $e) {
            Log::error('Error al duplicar movimiento: ' . $e->getMessage());
            session()->flash('error', 'Error al duplicar el movimiento');
        }
    }

    public function exportarMovimientos()
    {
        session()->flash('info', 'Función de exportación en desarrollo');
    }
}; ?>

@section('title', 'Movimientos Contables')
<div>
<div class="w-full px-6 py-6 mx-auto">
    <!-- Header -->
    <div class="flex flex-wrap -mx-3 mb-6">
        <div class="w-full px-3">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                <div class="mb-4 md:mb-0">
                    <nav class="text-sm text-gray-600 mb-2">
                        <a href="{{ route('contabilidad.index') }}" wire:navigate class="hover:text-blue-600">Dashboard</a>
                        <span class="mx-2">/</span>
                        <span class="text-gray-900">Movimientos</span>
                    </nav>
                    <h1 class="text-3xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-exchange-alt mr-3 text-blue-600"></i> 
                        Movimientos Contables
                    </h1>
                    <p class="text-gray-600 mt-1">Gestión de ingresos y egresos</p>
                </div>
                <div class="flex space-x-3">
                    <button wire:click="abrirModal" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center transition duration-200">
                        <i class="fas fa-plus mr-2"></i> Nuevo Movimiento
                    </button>
                    <a href="{{ route('contabilidad.reportes.index') }}" wire:navigate
                       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center transition duration-200">
                        <i class="fas fa-chart-line mr-2"></i> Reportes
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros Funcionales -->
    <div class="flex flex-wrap -mx-3 mb-6">
        <div class="w-full px-3">
            <div class="bg-white shadow-lg rounded-lg p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Filtro por Tipo -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipo</label>
                        <select wire:model.live="tipo" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Todos</option>
                            <option value="ingreso">💰 Ingresos</option>
                            <option value="egreso">💸 Egresos</option>
                        </select>
                    </div>

                    <!-- Filtro Fecha Desde -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Desde</label>
                        <input type="date" wire:model.live="fecha_desde" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Filtro Fecha Hasta -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Hasta</label>
                        <input type="date" wire:model.live="fecha_hasta" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Botones de Acción -->
                    <div class="flex items-end space-x-2">
                        <button wire:click="aplicarFiltros" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-200 flex-1">
                            <i class="fas fa-search mr-2"></i> Filtrar
                        </button>
                        <button wire:click="limpiarFiltros" class="bg-gray-500 hover:bg-gray-600 text-white px-3 py-2 rounded-lg transition duration-200">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <!-- Filtros Adicionales -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4 pt-4 border-t border-gray-200">
                    <!-- Búsqueda -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
                        <input type="text" wire:model.live.debounce.500ms="buscar" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Buscar en descripción...">
                    </div>

                    <!-- Categoría -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Categoría</label>
                        <select wire:model.live="categoria" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Todas las categorías</option>
                            @foreach($categorias as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Monto Mínimo -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Monto Mínimo</label>
                        <input type="number" wire:model.live.debounce.500ms="monto_min" step="0.01"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="0.00">
                    </div>

                    <!-- Monto Máximo -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Monto Máximo</label>
                        <input type="number" wire:model.live.debounce.500ms="monto_max" step="0.01"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="0.00">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Indicadores de Filtros Activos -->
    @if($tipo || $fecha_desde || $fecha_hasta || $categoria || $buscar || $monto_min || $monto_max)
    <div class="flex flex-wrap -mx-3 mb-6">
        <div class="w-full px-3">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-filter text-blue-600 mr-2"></i>
                        <span class="text-blue-800 font-medium">Filtros Activos:</span>
                        <div class="ml-3 flex flex-wrap gap-2">
                            @if($tipo)
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs">
                                    Tipo: {{ ucfirst($tipo) }}
                                </span>
                            @endif
                            @if($fecha_desde)
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs">
                                    Desde: {{ $fecha_desde }}
                                </span>
                            @endif
                            @if($fecha_hasta)
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs">
                                    Hasta: {{ $fecha_hasta }}
                                </span>
                            @endif
                            @if($categoria)
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs">
                                    Categoría: {{ $categoria }}
                                </span>
                            @endif
                            @if($buscar)
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs">
                                    Búsqueda: {{ $buscar }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <button wire:click="limpiarFiltros" class="text-blue-600 hover:text-blue-800 text-sm">
                        <i class="fas fa-times mr-1"></i> Limpiar todos
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Resumen Rápido con Datos Dinámicos -->
    <div class="flex flex-wrap -mx-3 mb-6">
        <div class="w-full md:w-1/3 px-3 mb-6">
            <div class="bg-white shadow-lg rounded-lg p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-green-600 uppercase tracking-wide mb-1">Total Ingresos</p>
                        <p class="text-2xl font-bold text-gray-800">
                            ${{ $totalesFiltrados['total_ingresos_formateado'] ?? '0.00' }}
                        </p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-arrow-up text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/3 px-3 mb-6">
            <div class="bg-white shadow-lg rounded-lg p-6 border-l-4 border-red-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-red-600 uppercase tracking-wide mb-1">Total Egresos</p>
                        <p class="text-2xl font-bold text-gray-800">
                            ${{ $totalesFiltrados['total_egresos_formateado'] ?? '0.00' }}
                        </p>
                    </div>
                    <div class="bg-red-100 p-3 rounded-full">
                        <i class="fas fa-arrow-down text-red-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/3 px-3 mb-6">
            <div class="bg-white shadow-lg rounded-lg p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-blue-600 uppercase tracking-wide mb-1">Balance</p>
                        <p class="text-2xl font-bold text-gray-800">
                            ${{ $totalesFiltrados['balance_formateado'] ?? '0.00' }}
                        </p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-balance-scale text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Movimientos -->
    <div class="flex flex-wrap -mx-3">
        <div class="w-full px-3">
            <div class="bg-white shadow-lg rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <div>
                            <h6 class="text-lg font-semibold text-gray-800">Lista de Movimientos</h6>
                            <p class="text-sm text-gray-600">
                                {{ $totalesFiltrados['cantidad_registros'] ?? 0 }} registros encontrados
                            </p>
                        </div>
                        <div class="flex space-x-2">
                            <button wire:click="exportarMovimientos" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm transition duration-200">
                                <i class="fas fa-file-excel mr-1"></i> Exportar
                            </button>
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoría</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($this->movimientos as $movimiento)
                            <tr class="hover:bg-gray-50 transition duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ \Carbon\Carbon::parse($movimiento->fecMovCont)->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $movimiento->conceptoMovCont ?? 'Sin descripción' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                        {{ $movimiento->catMovCont ?? 'Sin categoría' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                               {{ $movimiento->tipoMovCont == 'ingreso' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        <i class="fas fa-{{ $movimiento->tipoMovCont == 'ingreso' ? 'arrow-up' : 'arrow-down' }} mr-1"></i>
                                        {{ ucfirst($movimiento->tipoMovCont) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium 
                                        {{ $movimiento->tipoMovCont == 'ingreso' ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $movimiento->tipoMovCont == 'ingreso' ? '+' : '-' }}${{ number_format($movimiento->montoMovCont ?? 0, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <button wire:click="editarMovimiento({{ $movimiento->idMovCont }})" 
                                                class="text-blue-600 hover:text-blue-900" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button wire:click="duplicarMovimiento({{ $movimiento->idMovCont }})" 
                                                class="text-green-600 hover:text-green-900" title="Duplicar">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                        <button wire:click="eliminarMovimiento({{ $movimiento->idMovCont }})" 
                                                wire:confirm="¿Estás seguro de eliminar este movimiento?"
                                                class="text-red-600 hover:text-red-900" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                    <div class="py-8">
                                        <i class="fas fa-inbox text-4xl text-gray-300 mb-4"></i>
                                        <p class="text-lg font-medium mb-2">No hay movimientos registrados</p>
                                        <p class="text-sm text-gray-400 mb-4">Comienza registrando tu primer movimiento contable</p>
                                        <button wire:click="abrirModal" 
                                                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition duration-200">
                                            <i class="fas fa-plus mr-2"></i> Crear Primer Movimiento
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                @if($this->movimientos->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $this->movimientos->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Nuevo/Editar Movimiento -->
@if($modalAbierto)
<div wire:ignore.self class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 lg:w-1/3 shadow-lg rounded-lg bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">
                    {{ $modalEdicion ? 'Editar' : 'Nuevo' }} Movimiento Contable
                </h3>
                <button wire:click="cerrarModal" type="button" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form wire:submit.prevent="guardarMovimiento" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Movimiento</label>
                    <select wire:model="tipoMovimiento" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="">Seleccionar...</option>
                        <option value="ingreso">💰 Ingreso</option>
                        <option value="egreso">💸 Egreso</option>
                    </select>
                    @error('tipoMovimiento') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
                    <input type="text" wire:model="descripcion" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" 
                           placeholder="Ej: Venta de producto, pago de servicios..." required>
                    @error('descripcion') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Monto</label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-500">$</span>
                        <input type="number" wire:model="monto" step="0.01" class="w-full border border-gray-300 rounded-lg pl-8 pr-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" 
                               placeholder="0.00" required>
                    </div>
                    @error('monto') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Categoría</label>
                    <select wire:model="categoria_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Sin categoría</option>
                        @foreach($categorias as $categoria)
                        <option value="{{ $categoria }}">{{ $categoria }}</option>
                        @endforeach
                    </select>
                    @error('categoria_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Observaciones</label>
                    <textarea wire:model="observaciones" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="Observaciones adicionales (opcional)"></textarea>
                    @error('observaciones') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Fecha</label>
                    <input type="date" wire:model="fecha" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    @error('fecha') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" wire:click="cerrarModal" 
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition duration-200">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200">
                        <i class="fas fa-save mr-2"></i> 
                        {{ $modalEdicion ? 'Actualizar' : 'Guardar' }} Movimiento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Notificaciones Flash -->
@if(session('success'))
<div class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 animate-pulse">
    <div class="flex items-center">
        <i class="fas fa-check-circle mr-2"></i>
        {{ session('success') }}
    </div>
</div>
@endif

@if(session('error'))
<div class="fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 animate-pulse">
    <div class="flex items-center">
        <i class="fas fa-exclamation-circle mr-2"></i>
        {{ session('error') }}
    </div>
</div>
@endif

@if(session('info'))
<div class="fixed top-4 right-4 bg-blue-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 animate-pulse">
    <div class="flex items-center">
        <i class="fas fa-info-circle mr-2"></i>
        {{ session('info') }}
    </div>
</div>
@endif

<script>
// Auto-cerrar notificaciones después de 5 segundos
setTimeout(function() {
    const notifications = document.querySelectorAll('.fixed.top-4.right-4');
    notifications.forEach(notification => {
        notification.style.transition = 'opacity 0.5s ease-out';
        notification.style.opacity = '0';
        setTimeout(() => {
            notification.style.display = 'none';
        }, 500);
    });
}, 5000);

// Validación de fechas en tiempo real
document.addEventListener('DOMContentLoaded', function() {
    const fechaDesde = document.querySelector('input[wire\\:model\\.live="fecha_desde"]');
    const fechaHasta = document.querySelector('input[wire\\:model\\.live="fecha_hasta"]');
    
    if (fechaDesde && fechaHasta) {
        fechaDesde.addEventListener('change', function() {
            if (fechaHasta.value && this.value > fechaHasta.value) {
                this.style.borderColor = '#ef4444';
                this.title = 'La fecha "Desde" no puede ser mayor que la fecha "Hasta"';
            } else {
                this.style.borderColor = '#d1d5db';
                this.title = '';
            }
        });

        fechaHasta.addEventListener('change', function() {
            if (fechaDesde.value && this.value < fechaDesde.value) {
                this.style.borderColor = '#ef4444';
                this.title = 'La fecha "Hasta" no puede ser menor que la fecha "Desde"';
            } else {
                this.style.borderColor = '#d1d5db';
                this.title = '';
            }
        });
    }
});

// Confirmación antes de eliminar
document.addEventListener('DOMContentLoaded', function() {
    document.addEventListener('click', function(e) {
        if (e.target.closest('[wire\\:confirm]')) {
            const message = e.target.closest('[wire\\:confirm]').getAttribute('wire:confirm');
            if (!confirm(message)) {
                e.preventDefault();
                e.stopPropagation();
            }
        }
    });
});

// Validación de montos en tiempo real
document.addEventListener('DOMContentLoaded', function() {
    const montoInput = document.querySelector('input[wire\\:model="monto"]');
    if (montoInput) {
        montoInput.addEventListener('input', function() {
            const valor = parseFloat(this.value);
            if (valor < 0.01) {
                this.style.borderColor = '#ef4444';
            } else {
                this.style.borderColor = '#d1d5db';
            }
        });
    }
});
</script>
</div>