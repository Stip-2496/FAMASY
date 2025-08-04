<?php
// resources/views/livewire/contabilidad/gastos/index.blade.php - CORREGIDO


use Illuminate\Support\Facades\DB; 
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

new #[Layout('layouts.auth')] class extends Component {
    use WithPagination;

    // Propiedades para filtros
    public $categoria = '';
    public $metodo_pago = '';
    public $proveedor_buscar = '';
    public $fecha = '';
    public $per_page = 15;

    // Propiedades para el modal de nuevo gasto
    public $modalAbierto = false;
    public $categoriaGasto = '';
    public $descripcion = '';
    public $montoGasto = '';
    public $fechaGasto = '';
    public $metodoPago = '';
    public $proveedorGasto = '';
    public $documento = '';
    public $observaciones = '';

    // Estadísticas
    public $estadisticas = [];
    public $proveedores = [];
    public $categorias = [];

    public function mount()
    {
        $this->fechaGasto = date('Y-m-d');
        $this->cargarProveedores();
        $this->cargarCategorias();
        $this->calcularEstadisticasIniciales();

        
    }

    private function calcularEstadisticasIniciales()
{
    try {
        // Calcular totales de TODOS los gastos (sin filtros de fecha)
        $totalGastos = DB::table('comprasgastos')
            ->where('tipComGas', 'gasto')
            ->sum('monComGas') ?? 0;
            
        $totalTransacciones = DB::table('comprasgastos')
            ->where('tipComGas', 'gasto')
            ->count();

        // Categorías únicas - CORREGIR ESTA CONSULTA
        $categoriasActivas = DB::table('comprasgastos')
            ->where('tipComGas', 'gasto')
            ->whereNotNull('catComGas')
            ->where('catComGas', '!=', '')  // Agregar esta línea
            ->where('catComGas', '!=', 'NULL')  // Agregar esta línea
            ->distinct()
            ->count('catComGas');

        // Obtener las categorías para mostrar en el top
        $gastosPorCategoria = DB::table('comprasgastos')
            ->where('tipComGas', 'gasto')
            ->whereNotNull('catComGas')
            ->where('catComGas', '!=', '')
            ->where('catComGas', '!=', 'NULL')
            ->select('catComGas', DB::raw('SUM(monComGas) as total'))
            ->groupBy('catComGas')
            ->orderBy('total', 'desc')
            ->limit(3)
            ->get();

        // Promedio por transacción
        $promedioTransaccion = $totalTransacciones > 0 ? $totalGastos / $totalTransacciones : 0;

        $this->estadisticas = [
            'gastos_mes' => $totalGastos,
            'transacciones_mes' => $totalTransacciones,
            'promedio_diario' => $promedioTransaccion,
            'gastos_por_categoria' => $gastosPorCategoria, // Usar la consulta corregida
            'categorias_activas' => $categoriasActivas
        ];

        // Log para debug
        Log::info('Estadísticas iniciales calculadas:', $this->estadisticas);

    } catch (\Exception $e) {
        Log::error('Error calculando estadísticas iniciales: ' . $e->getMessage());
        $this->estadisticas = [
            'gastos_mes' => 0,
            'transacciones_mes' => 0,
            'promedio_diario' => 0,
            'gastos_por_categoria' => collect(),
            'categorias_activas' => 0
        ];
    }
}




    public function cargarProveedores()
    {
        try {
            $this->proveedores = DB::table('proveedores')
                ->orderBy('nomProve')
                ->get()
                ->map(function($proveedor) {
                    return (object)[
                        'id' => $proveedor->idProve,
                        'nombre' => $proveedor->nomProve,
                        'nit' => $proveedor->nitProve
                    ];
                });
        } catch (\Exception $e) {
            Log::error('Error cargando proveedores: ' . $e->getMessage());
            $this->proveedores = collect();
        }
    }

    public function cargarCategorias()
    {
        try {
            // Obtener categorías únicas de gastos existentes desde la BD
            $categoriasExistentes = DB::table('comprasgastos')
                ->where('tipComGas', 'gasto')
                ->whereNotNull('catComGas')
                ->distinct()
                ->pluck('catComGas');

            // Combinar con categorías predefinidas
            $this->categorias = $categoriasExistentes->merge([
                'Servicios Públicos',
                'Mantenimiento', 
                'Transporte',
                'Suministros',
                'Alimentación Animal',
                'Veterinario',
                'Combustible',
                'Reparaciones',
                'Seguros',
                'Impuestos',
                'Marketing',
                'Otros'
            ])->unique()->sort()->values();

        } catch (\Exception $e) {
            Log::error('Error cargando categorías: ' . $e->getMessage());
            $this->categorias = collect(['Servicios Públicos', 'Mantenimiento', 'Otros']);
        }
    }

   public function calcularEstadisticas()
{
    try {
        $mesActual = now()->month;
        $anoActual = now()->year;

        // ✅ CONSULTAS CORREGIDAS - Verificar que la tabla existe
        $gastosMes = DB::table('comprasgastos')
            ->where('tipComGas', 'gasto')
            ->whereMonth('fecComGas', $mesActual)
            ->whereYear('fecComGas', $anoActual)
            ->sum('monComGas') ?? 0;

        $transaccionesMes = DB::table('comprasgastos')
            ->where('tipComGas', 'gasto')
            ->whereMonth('fecComGas', $mesActual)
            ->whereYear('fecComGas', $anoActual)
            ->count();

        // Gastos por categoría (top 3)
        $gastosPorCategoria = DB::table('comprasgastos')
            ->where('tipComGas', 'gasto')
            ->whereMonth('fecComGas', $mesActual)
            ->whereYear('fecComGas', $anoActual)
            ->whereNotNull('catComGas')
            ->select('catComGas', DB::raw('SUM(monComGas) as total'))
            ->groupBy('catComGas')
            ->orderBy('total', 'desc')
            ->limit(3)
            ->get();

        // Promedio diario del mes
        $diasTranscurridos = now()->day;
        $promedioDiario = $diasTranscurridos > 0 ? $gastosMes / $diasTranscurridos : 0;

        $this->estadisticas = [
            'gastos_mes' => $gastosMes,
            'transacciones_mes' => $transaccionesMes,
            'promedio_diario' => $promedioDiario,
            'gastos_por_categoria' => $gastosPorCategoria
        ];

        // Log para debug
        Log::info('Estadísticas calculadas:', $this->estadisticas);

    } catch (\Exception $e) {
        Log::error('Error calculando estadísticas de gastos: ' . $e->getMessage());
        $this->estadisticas = [
            'gastos_mes' => 0,
            'transacciones_mes' => 0,
            'promedio_diario' => 0,
            'gastos_por_categoria' => collect()
        ];
    }
}

    public function getGastosProperty()
    {
        try {
            $query = DB::table('comprasgastos')
                ->where('tipComGas', 'gasto');

            // Aplicar filtros
            if ($this->categoria) {
                $query->where('catComGas', $this->categoria);
            }

            if ($this->metodo_pago) {
                $query->where('metPagComGas', $this->metodo_pago);
            }

            if ($this->proveedor_buscar) {
                $query->where('provComGas', 'like', '%' . $this->proveedor_buscar . '%');
            }

            if ($this->fecha) {
                $query->whereDate('fecComGas', $this->fecha);
            }

            return $query->orderBy('fecComGas', 'desc')
                        ->paginate($this->per_page);

        } catch (\Exception $e) {
            Log::error('Error al obtener gastos: ' . $e->getMessage());
            return DB::table('comprasgastos')->whereRaw('1=0')->paginate($this->per_page);
        }
    }

    // ... resto de métodos igual (aplicarFiltros, limpiarFiltros, abrirModal, etc.)

    public function guardarGasto()
    {
        $this->validate([
            'categoriaGasto' => 'required|string|max:100',
            'descripcion' => 'required|string|max:500',
            'montoGasto' => 'required|numeric|min:0.01',
            'fechaGasto' => 'required|date',
            'metodoPago' => 'required|string|max:50',
            'proveedorGasto' => 'required|string|max:100',
        ]);

        try {
            DB::beginTransaction();

            // Crear el gasto
            $gastoId = DB::table('comprasgastos')->insertGetId([
                'tipComGas' => 'gasto',
                'catComGas' => $this->categoriaGasto,
                'desComGas' => $this->descripcion,
                'monComGas' => $this->montoGasto,
                'fecComGas' => $this->fechaGasto,
                'metPagComGas' => $this->metodoPago,
                'provComGas' => $this->proveedorGasto,
                'docComGas' => $this->documento,
                'obsComGas' => $this->observaciones,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Crear movimiento contable
            DB::table('movimientoscontables')->insert([
                'fecMovCont' => $this->fechaGasto,
                'tipoMovCont' => 'egreso',
                'catMovCont' => $this->categoriaGasto,
                'conceptoMovCont' => 'Gasto #' . $gastoId . ' - ' . $this->descripcion,
                'montoMovCont' => $this->montoGasto,
                'idComGasMovCont' => $gastoId,
                'obsMovCont' => 'Movimiento generado automáticamente por gasto registrado',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();

            $this->cerrarModal();
            $this->calcularEstadisticas(); // ✅ Recalcular estadísticas
            $this->cargarCategorias();
            
            session()->flash('success', 'Gasto registrado exitosamente');
            Log::info('Gasto guardado con ID: ' . $gastoId);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error al guardar gasto: ' . $e->getMessage());
            session()->flash('error', 'Error al registrar el gasto: ' . $e->getMessage());
        }
    }


    // Métodos auxiliares
    public function aplicarFiltros() { $this->resetPage(); }
    public function limpiarFiltros() { 
        $this->reset(['categoria', 'metodo_pago', 'proveedor_buscar', 'fecha']); 
        $this->resetPage(); 
    }
    public function abrirModal() { 
        $this->modalAbierto = true; 
        $this->resetearCamposModal(); 
    }
    private function resetearCamposModal() {
        $this->reset(['categoriaGasto', 'descripcion', 'montoGasto', 'metodoPago', 'proveedorGasto', 'documento', 'observaciones']);
        $this->fechaGasto = date('Y-m-d');
    }
    public function cerrarModal() { 
        $this->modalAbierto = false; 
        $this->resetValidation(); 
    }

    public function eliminarGasto($gastoId)
    {
        try {
            DB::table('comprasgastos')->where('idComGas', $gastoId)->delete();
            $this->calcularEstadisticas(); // ✅ Recalcular después de eliminar
            session()->flash('success', 'Gasto eliminado correctamente');
        } catch (\Exception $e) {
            Log::error('Error al eliminar gasto: ' . $e->getMessage());
            session()->flash('error', 'Error al eliminar el gasto');
        }
    }

    public function duplicarGasto($gastoId)
    {
        try {
            $gasto = DB::table('comprasgastos')->where('idComGas', $gastoId)->first();
            
            if ($gasto) {
                $this->categoriaGasto = $gasto->catComGas;
                $this->descripcion = $gasto->desComGas;
                $this->montoGasto = $gasto->monComGas;
                $this->metodoPago = $gasto->metPagComGas;
                $this->proveedorGasto = $gasto->provComGas;
                $this->documento = $gasto->docComGas;
                $this->observaciones = $gasto->obsComGas;
                $this->fechaGasto = date('Y-m-d');
                $this->abrirModal();
            }
        } catch (\Exception $e) {
            Log::error('Error al duplicar gasto: ' . $e->getMessage());
            session()->flash('error', 'Error al duplicar el gasto');
        }
    }

    public function exportarGastos() {
        session()->flash('info', 'Función de exportación en desarrollo');
    }

    public function getColorCategoria($categoria)
    {
        $colores = [
            'Servicios Públicos' => 'bg-blue-100 text-blue-800',
            'Mantenimiento' => 'bg-orange-100 text-orange-800',
            'Transporte' => 'bg-green-100 text-green-800',
            'Suministros' => 'bg-purple-100 text-purple-800',
            'Alimentación Animal' => 'bg-yellow-100 text-yellow-800',
            'Veterinario' => 'bg-red-100 text-red-800',
            'Combustible' => 'bg-gray-100 text-gray-800',
            'Marketing' => 'bg-pink-100 text-pink-800',
            'Otros' => 'bg-indigo-100 text-indigo-800'
        ];

        return $colores[$categoria] ?? 'bg-gray-100 text-gray-800';
    }
}; ?>

@section('title', 'Gastos')

<div>
    <div class="w-full px-6 py-6 mx-auto">
        <!-- Flash Messages -->
        @if (session()->has('success'))
            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
            </div>
        @endif

        @if (session()->has('info'))
            <div class="mb-4 p-4 bg-blue-100 border border-blue-400 text-blue-700 rounded-lg">
                <i class="fas fa-info-circle mr-2"></i>{{ session('info') }}
            </div>
        @endif

        <!-- Header -->
        <div class="flex flex-wrap -mx-3 mb-6">
            <div class="w-full px-3">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                    <div class="mb-4 md:mb-0">
                        <nav class="text-sm text-gray-600 mb-2">
                            <a href="{{ route('contabilidad.index') }}" wire:navigate class="hover:text-blue-600">Dashboard</a>
                            <span class="mx-2">/</span>
                            <span class="text-gray-900">Gastos</span>
                        </nav>
                        <h1 class="text-3xl font-bold text-gray-800 flex items-center">
                            <i class="fas fa-receipt mr-3 text-red-600"></i> 
                            Gestión de Gastos
                        </h1>
                        <p class="text-gray-600 mt-1">Control y registro de gastos operacionales</p>
                    </div>
                    <div class="flex space-x-3">
                        <button wire:click="abrirModal" 
                                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center transition duration-200">
                            <i class="fas fa-plus mr-2"></i> Nuevo Gasto
                        </button>
                        <a href="{{ route('contabilidad.reportes.index') }}" wire:navigate
                           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center transition duration-200">
                            <i class="fas fa-chart-bar mr-2"></i> Reportes
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resumen de Gastos -->
<!-- Resumen de Gastos -->
<div class="flex flex-wrap -mx-3 mb-6">
    <div class="w-full md:w-1/3 px-3 mb-6">
        <div class="bg-white shadow-lg rounded-lg p-6 border-l-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-red-600 uppercase tracking-wide mb-1">Gastos del Mes</p>
                    <p class="text-2xl font-bold text-gray-800">${{ number_format($estadisticas['gastos_mes'] ?? 0, 2) }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $estadisticas['transacciones_mes'] ?? 0 }} transacciones</p>
                </div>
                <div class="bg-red-100 p-3 rounded-full">
                    <i class="fas fa-money-bill-wave text-red-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="w-full md:w-1/3 px-3 mb-6">
        <div class="bg-white shadow-lg rounded-lg p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-blue-600 uppercase tracking-wide mb-1">Promedio Diario</p>
                    <p class="text-2xl font-bold text-gray-800">${{ number_format($estadisticas['promedio_diario'] ?? 0, 2) }}</p>
                    <p class="text-xs text-blue-500 mt-1">Este mes</p>
                </div>
                <div class="bg-blue-100 p-3 rounded-full">
                    <i class="fas fa-calculator text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="w-full md:w-1/3 px-3 mb-6">
        <div class="bg-white shadow-lg rounded-lg p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-green-600 uppercase tracking-wide mb-1">Categorías Activas</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $estadisticas['gastos_por_categoria']->count() ?? 0 }}</p>
                    <p class="text-xs text-green-500 mt-1">Con movimientos</p>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <i class="fas fa-tags text-green-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>
</div>


        <!-- Top Categorías -->
        @if($estadisticas['gastos_por_categoria']->isNotEmpty())
        <div class="flex flex-wrap -mx-3 mb-6">
            <div class="w-full px-3">
                <div class="bg-white shadow-lg rounded-lg p-6">
                    <h6 class="text-lg font-semibold text-gray-800 mb-4">Top Categorías del Mes</h6>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach($estadisticas['gastos_por_categoria'] as $index => $categoria)
                        <div class="flex items-center p-3 rounded-lg {{ $index === 0 ? 'bg-red-50' : ($index === 1 ? 'bg-orange-50' : 'bg-yellow-50') }}">
                            <div class="mr-3">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center {{ $index === 0 ? 'bg-red-500' : ($index === 1 ? 'bg-orange-500' : 'bg-yellow-500') }} text-white font-bold">
                                    {{ $index + 1 }}
                                </div>
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-900">{{ $categoria->catComGas }}</p>
                                <p class="text-sm font-bold text-gray-700">${{ number_format($categoria->total, 2) }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Filtros -->
        <div class="flex flex-wrap -mx-3 mb-6">
            <div class="w-full px-3">
                <div class="bg-white shadow-lg rounded-lg p-6">
                    <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Categoría</label>
                            <select wire:model="categoria" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500">
                                <option value="">Todas las categorías</option>
                                @foreach($categorias as $cat)
                                    <option value="{{ $cat }}">{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Método de Pago</label>
                            <select wire:model="metodo_pago" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500">
                                <option value="">Todos los métodos</option>
                                <option value="efectivo">Efectivo</option>
                                <option value="transferencia">Transferencia</option>
                                <option value="cheque">Cheque</option>
                                <option value="tarjeta_credito">Tarjeta de Crédito</option>
                                <option value="tarjeta_debito">Tarjeta de Débito</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Proveedor</label>
                            <input type="text" wire:model="proveedor_buscar" placeholder="Buscar proveedor..." 
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Fecha</label>
                            <input type="date" wire:model="fecha" 
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500">
                        </div>
                        <div class="flex items-end space-x-2">
                            <button wire:click="aplicarFiltros" class="flex-1 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition duration-200">
                                <i class="fas fa-search mr-2"></i> Filtrar
                            </button>
                            <button wire:click="limpiarFiltros" class="bg-gray-500 hover:bg-gray-600 text-white px-3 py-2 rounded-lg transition duration-200">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Gastos -->
        <div class="flex flex-wrap -mx-3">
            <div class="w-full px-3">
                <div class="bg-white shadow-lg rounded-lg">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <div>
                                <h6 class="text-lg font-semibold text-gray-800">Historial de Gastos</h6>
                                <p class="text-sm text-gray-600">{{ $this->gastos->total() }} gastos encontrados</p>
                            </div>
                            <div class="flex space-x-2">
                                <button wire:click="exportarGastos" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm transition duration-200">
                                    <i class="fas fa-download mr-1"></i> Exportar
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoría</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proveedor</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Método</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($this->gastos as $gasto)
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ date('d/m/Y', strtotime($gasto->fecComGas)) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $this->getColorCategoria($gasto->catComGas) }}">
                                            {{ $gasto->catComGas }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <div class="max-w-xs truncate" title="{{ $gasto->desComGas }}">
                                            {{ $gasto->desComGas }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ $gasto->provComGas }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                            {{ ucfirst($gasto->metPagComGas) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-red-600">
                                        ${{ number_format($gasto->monComGas, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <button class="text-blue-600 hover:text-blue-900" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            @if($gasto->docComGas)
                                                <button class="text-green-600 hover:text-green-900" title="Ver documento">
                                                    <i class="fas fa-file-alt"></i>
                                                </button>
                                            @endif
                                            <button wire:click="duplicarGasto({{ $gasto->idComGas }})" 
                                                    class="text-purple-600 hover:text-purple-900" title="Duplicar">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                            <button class="text-yellow-600 hover:text-yellow-900" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button wire:click="eliminarGasto({{ $gasto->idComGas }})" 
                                                    wire:confirm="¿Estás seguro de eliminar este gasto?"
                                                    class="text-red-600 hover:text-red-900" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                        <div class="py-8">
                                            <i class="fas fa-receipt text-4xl text-gray-300 mb-4"></i>
                                            <p class="text-lg font-medium mb-2">No hay gastos registrados</p>
                                            <p class="text-sm text-gray-400 mb-4">Comienza registrando tu primer gasto</p>
                                            <button wire:click="abrirModal" 
                                                    class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg transition duration-200">
                                                <i class="fas fa-plus mr-2"></i> Registrar Primer Gasto
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    @if($this->gastos->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $this->gastos->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Nuevo Gasto -->
    @if($modalAbierto)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-10 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-lg bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Registrar Nuevo Gasto</h3>
                    <button wire:click="cerrarModal" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <form wire:submit="guardarGasto" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Categoría *</label>
                            <select wire:model="categoriaGasto" 
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500">
                                <option value="">Seleccionar categoría</option>
                                @foreach($categorias as $categoria)
                                    <option value="{{ $categoria }}">{{ $categoria }}</option>
                                @endforeach
                            </select>
                            @error('categoriaGasto') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Proveedor *</label>
                            <input type="text" wire:model="proveedorGasto" list="proveedores-list"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500" 
                                   placeholder="Nombre del proveedor">
                            <datalist id="proveedores-list">
                                @foreach($proveedores as $proveedor)
                                    <option value="{{ $proveedor->nombre }}">{{ $proveedor->nit }}</option>
                                @endforeach
                            </datalist>
                            @error('proveedorGasto') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Descripción *</label>
                        <input type="text" wire:model="descripcion" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500" 
                               placeholder="Descripción detallada del gasto">
                        @error('descripcion') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Monto *</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-gray-500">$</span>
                                <input type="number" wire:model="montoGasto" step="0.01" 
                                       class="w-full border border-gray-300 rounded-lg pl-8 pr-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500" 
                                       placeholder="0.00">
                            </div>
                            @error('montoGasto') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Fecha del Gasto *</label>
                            <input type="date" wire:model="fechaGasto" 
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500">
                            @error('fechaGasto') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Método de Pago *</label>
                            <select wire:model="metodoPago" 
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500">
                                <option value="">Seleccionar método</option>
                                <option value="efectivo">💵 Efectivo</option>
                                <option value="transferencia">🏦 Transferencia</option>
                                <option value="cheque">📝 Cheque</option>
                                <option value="tarjeta_credito">💳 Tarjeta de Crédito</option>
                                <option value="tarjeta_debito">💳 Tarjeta de Débito</option>
                            </select>
                            @error('metodoPago') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Documento de Respaldo</label>
                        <input type="text" wire:model="documento" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500" 
                               placeholder="Número de factura, recibo, etc.">
                        @error('documento') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Observaciones</label>
                        <textarea wire:model="observaciones" rows="3" 
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500" 
                                  placeholder="Notas adicionales sobre el gasto..."></textarea>
                        @error('observaciones') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" wire:click="cerrarModal" 
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition duration-200">
                            Cancelar
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-200">
                            <i class="fas fa-save mr-2"></i> Registrar Gasto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Loading Overlay -->
    <div wire:loading.flex wire:target="guardarGasto" 
         class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50 items-center justify-center">
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <div class="flex items-center space-x-3">
                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-red-600"></div>
                <span class="text-gray-700">Guardando gasto...</span>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Auto-cerrar notificaciones
setTimeout(function() {
    const notifications = document.querySelectorAll('.bg-green-100, .bg-red-100, .bg-blue-100');
    notifications.forEach(notification => {
        if (notification.classList.contains('mb-4')) {
            notification.style.transition = 'opacity 0.5s ease';
            notification.style.opacity = '0';
            setTimeout(() => notification.remove(), 500);
        }
    });
}, 5000);

// Event listeners optimizados
document.addEventListener('livewire:init', () => {
    Livewire.on('gasto-creado', () => {
        console.log('Gasto creado exitosamente');
    });
});

// Prevenir envío accidental de formularios
document.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
        e.preventDefault();
    }
});

// Auto-completar categoría personalizada
document.addEventListener('change', function(e) {
    if (e.target.id === 'categoria-select' && e.target.value === 'custom') {
        const customInput = document.getElementById('categoria-custom');
        if (customInput) {
            customInput.style.display = 'block';
            customInput.focus();
        }
    }
});
</script>
@endpush