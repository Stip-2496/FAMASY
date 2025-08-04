<?php
// resources/views/livewire/contabilidad/cuentas-pendientes/index.blade.php

use App\Models\CuentaPendiente;
use App\Models\Cliente;
use App\Models\Proveedor;
use App\Models\MovimientoContable;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

new #[Layout('layouts.auth')] class extends Component {
    use WithPagination;

    // Propiedades para filtros
    public $tipo = '';
    public $estado = '';
    public $cliente_buscar = '';
    public $fecha_vencimiento = '';
    public $per_page = 15;

    // Propiedades para el modal de nueva cuenta pendiente
    public $modalAbierto = false;
    public $tipoCuenta = 'por_pagar';
    public $cliente = '';
    public $proveedor = '';
    public $descripcion = '';
    public $montoOriginal = '';
    public $fechaVencimiento = '';
    public $estadoCuenta = 'pendiente';
    public $observaciones = '';

    // Estadísticas
    public $estadisticas = [];
    public $clientes = [];
    public $proveedores = [];

    public function mount()
    {
        $this->fechaVencimiento = date('Y-m-d', strtotime('+30 days'));
        $this->cargarClientes();
        $this->cargarProveedores();
        $this->calcularEstadisticas();
    }

    public function cargarClientes()
    {
        try {
            $this->clientes = DB::table('clientes')
                ->where('estCli', 'activo')
                ->orderBy('nomCli')
                ->get()
                ->map(function($cliente) {
                    return (object)[
                        'id' => $cliente->idCli,
                        'nombre' => $cliente->nomCli,
                        'documento' => $cliente->docCli
                    ];
                });
        } catch (\Exception $e) {
            Log::error('Error cargando clientes: ' . $e->getMessage());
            $this->clientes = collect();
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

    public function calcularEstadisticas()
{
    try {
        // ✅ MEJORAR: Usar fechas sin hora para comparaciones precisas
        $hoy = now()->format('Y-m-d');
        $en7Dias = now()->addDays(7)->format('Y-m-d');

        // Cuentas por cobrar
        $porCobrar = DB::table('cuentaspendientes')
            ->where('tipCuePen', 'por_cobrar')
            ->where('estCuePen', '!=', 'pagado')
            ->sum('montoSaldo') ?? 0;

        $cuentasPorCobrar = DB::table('cuentaspendientes')
            ->where('tipCuePen', 'por_cobrar')
            ->where('estCuePen', '!=', 'pagado')
            ->count();

        // Cuentas por pagar
        $porPagar = DB::table('cuentaspendientes')
            ->where('tipCuePen', 'por_pagar')
            ->where('estCuePen', '!=', 'pagado')
            ->sum('montoSaldo') ?? 0;

        $cuentasPorPagar = DB::table('cuentaspendientes')
            ->where('tipCuePen', 'por_pagar')
            ->where('estCuePen', '!=', 'pagado')
            ->count();

        // ✅ MEJORAR: Cuentas vencidas (usando solo fecha)
        $vencidas = DB::table('cuentaspendientes')
            ->where('fecVencimiento', '<', $hoy)
            ->where('estCuePen', '!=', 'pagado')
            ->count();

        // ✅ MEJORAR: Próximas a vencer (próximos 7 días, usando solo fechas)
        $proximasVencer = DB::table('cuentaspendientes')
            ->where('fecVencimiento', '>=', $hoy)
            ->where('fecVencimiento', '<=', $en7Dias)
            ->where('estCuePen', '!=', 'pagado')
            ->count();

        // ✅ AGREGAR: Debug para verificar si hay datos
        $totalCuentas = DB::table('cuentaspendientes')->count();
        Log::info("Debug cuentas pendientes - Total: $totalCuentas, Por cobrar: $cuentasPorCobrar, Por pagar: $cuentasPorPagar, Vencidas: $vencidas, Por vencer: $proximasVencer");

        $this->estadisticas = [
            'por_cobrar' => $porCobrar,
            'cuentas_por_cobrar' => $cuentasPorCobrar,
            'por_pagar' => $porPagar,
            'cuentas_por_pagar' => $cuentasPorPagar,
            'vencidas' => $vencidas,
            'proximas_vencer' => $proximasVencer
        ];

    } catch (\Exception $e) {
        Log::error('Error calculando estadísticas de cuentas pendientes: ' . $e->getMessage());
        $this->estadisticas = [
            'por_cobrar' => 0,
            'cuentas_por_cobrar' => 0,
            'por_pagar' => 0,
            'cuentas_por_pagar' => 0,
            'vencidas' => 0,
            'proximas_vencer' => 0
        ];
    }
}

public function verificarDatos()
{
    try {
        // Verificar datos en la tabla
        $totalRegistros = DB::table('cuentaspendientes')->count();
        $porCobrar = DB::table('cuentaspendientes')->where('tipCuePen', 'por_cobrar')->count();
        $porPagar = DB::table('cuentaspendientes')->where('tipCuePen', 'por_pagar')->count();
        
        Log::info("Verificación cuentas pendientes:");
        Log::info("- Total registros: $totalRegistros");
        Log::info("- Por cobrar: $porCobrar");
        Log::info("- Por pagar: $porPagar");
        
        // Ver algunos registros de ejemplo
        $ejemplos = DB::table('cuentaspendientes')->limit(3)->get();
        Log::info("Ejemplos:", $ejemplos->toArray());
        
        session()->flash('info', "Total registros: $totalRegistros. Ver logs para más detalles.");
        
    } catch (\Exception $e) {
        Log::error('Error verificando datos: ' . $e->getMessage());
        session()->flash('error', 'Error al verificar datos');
    }
}

public function crearDatosPrueba()
{
    try {
        DB::beginTransaction();

        // Crear algunas cuentas de prueba
        DB::table('cuentaspendientes')->insert([
            [
                'tipCuePen' => 'por_cobrar',
                'montoOriginal' => 1500.00,
                'montoPagado' => 0.00,
                'montoSaldo' => 1500.00,
                'fecVencimiento' => now()->addDays(15)->format('Y-m-d'),
                'estCuePen' => 'pendiente',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'tipCuePen' => 'por_pagar',
                'montoOriginal' => 2500.00,
                'montoPagado' => 0.00,
                'montoSaldo' => 2500.00,
                'fecVencimiento' => now()->addDays(5)->format('Y-m-d'),
                'estCuePen' => 'pendiente',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'tipCuePen' => 'por_pagar',
                'montoOriginal' => 800.00,
                'montoPagado' => 0.00,
                'montoSaldo' => 800.00,
                'fecVencimiento' => now()->subDays(3)->format('Y-m-d'), // Vencida
                'estCuePen' => 'vencido',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
        
        DB::commit();
        $this->calcularEstadisticas();
        session()->flash('success', 'Datos de prueba creados exitosamente');
        
    } catch (\Exception $e) {
        DB::rollback();
        Log::error('Error creando datos de prueba: ' . $e->getMessage());
        session()->flash('error', 'Error al crear datos de prueba');
    }
}

    public function getCuentasPendientesProperty()
    {
        try {
            $query = DB::table('cuentaspendientes as cp')
                ->leftJoin('clientes as c', 'cp.idCliCuePen', '=', 'c.idCli')
                ->leftJoin('proveedores as p', 'cp.idProveCuePen', '=', 'p.idProve')
                ->leftJoin('facturas as f', 'cp.idFacCuePen', '=', 'f.idFac')
                ->leftJoin('comprasgastos as cg', 'cp.idComGasCuePen', '=', 'cg.idComGas')
                ->select(
                    'cp.*',
                    'c.nomCli as cliente_nombre',
                    'c.docCli as cliente_documento',
                    'p.nomProve as proveedor_nombre',
                    'p.nitProve as proveedor_documento',
                    'f.idFac as factura_numero',
                    'cg.desComGas as compra_descripcion'
                );

            // Aplicar filtros
            if ($this->tipo) {
                $query->where('cp.tipCuePen', $this->tipo);
            }

            if ($this->estado) {
                $query->where('cp.estCuePen', $this->estado);
            }

            if ($this->cliente_buscar) {
                $query->where(function($q) {
                    $q->where('c.nomCli', 'like', '%' . $this->cliente_buscar . '%')
                      ->orWhere('p.nomProve', 'like', '%' . $this->cliente_buscar . '%');
                });
            }

            if ($this->fecha_vencimiento) {
                $query->whereDate('cp.fecVencimiento', $this->fecha_vencimiento);
            }

            return $query->orderBy('cp.fecVencimiento', 'asc')
                        ->paginate($this->per_page);

        } catch (\Exception $e) {
            Log::error('Error al obtener cuentas pendientes: ' . $e->getMessage());
            return DB::table('cuentaspendientes')->whereRaw('1=0')->paginate($this->per_page);
        }
    }

    public function aplicarFiltros()
    {
        $this->resetPage();
    }

    public function limpiarFiltros()
    {
        $this->reset(['tipo', 'estado', 'cliente_buscar', 'fecha_vencimiento']);
        $this->resetPage();
    }

    public function abrirModal()
    {
        $this->modalAbierto = true;
        $this->resetearCamposModal();
    }

    public function resetearCamposModal()
{
    $this->reset(['cliente', 'proveedor', 'descripcion', 'montoOriginal', 'observaciones']);
    $this->fechaVencimiento = date('Y-m-d', strtotime('+30 days'));
    $this->tipoCuenta = 'por_pagar';
    $this->estadoCuenta = 'pendiente';
    $this->resetValidation();
}   

    public function cerrarModal()
    {
        $this->modalAbierto = false;
        $this->resetValidation();
    }

    public function rules(): array
    {
        return [
            'tipoCuenta' => 'required|in:por_cobrar,por_pagar',
            'cliente' => 'required_if:tipoCuenta,por_cobrar|string|max:255',
            'proveedor' => 'required_if:tipoCuenta,por_pagar|string|max:255',
            'descripcion' => 'required|string|max:500',
            'montoOriginal' => 'required|numeric|min:0.01',
            'fechaVencimiento' => 'required|date|after:today',
            'estadoCuenta' => 'required|in:pendiente,pagado,vencido,parcial',
            'observaciones' => 'nullable|string|max:1000'
        ];
    }

    public function guardarCuentaPendiente()
    {
        $validated = $this->validate();

        try {
            DB::beginTransaction();

            // Crear la cuenta pendiente
            $cuentaId = DB::table('cuentaspendientes')->insertGetId([
                'tipCuePen' => $this->tipoCuenta,
                'idCliCuePen' => $this->tipoCuenta === 'por_cobrar' ? $this->obtenerIdCliente() : null,
                'idProveCuePen' => $this->tipoCuenta === 'por_pagar' ? $this->obtenerIdProveedor() : null,
                'montoOriginal' => $this->montoOriginal,
                'montoPagado' => 0.00,
                'montoSaldo' => $this->montoOriginal,
                'fecVencimiento' => $this->fechaVencimiento,
                'diasVencido' => 0,
                'estCuePen' => $this->estadoCuenta,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Crear movimiento contable
            MovimientoContable::create([
                'fecMovCont' => now()->format('Y-m-d'),
                'tipoMovCont' => $this->tipoCuenta === 'por_cobrar' ? 'ingreso' : 'egreso',
                'catMovCont' => $this->tipoCuenta === 'por_cobrar' ? 'Cuentas por Cobrar' : 'Cuentas por Pagar',
                'conceptoMovCont' => 'Cuenta pendiente #' . $cuentaId . ' - ' . $this->descripcion,
                'montoMovCont' => $this->montoOriginal,
                'obsMovCont' => 'Movimiento generado automáticamente por cuenta pendiente registrada'
            ]);

            DB::commit();

            $this->cerrarModal();
            $this->calcularEstadisticas();
            
            session()->flash('success', 'Cuenta pendiente registrada exitosamente');
            Log::info('Cuenta pendiente guardada con ID: ' . $cuentaId);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error al guardar cuenta pendiente: ' . $e->getMessage());
            session()->flash('error', 'Error al registrar la cuenta pendiente: ' . $e->getMessage());
        }
    }

    private function obtenerIdCliente()
    {
        try {
            $cliente = DB::table('clientes')->where('nomCli', $this->cliente)->first();
            return $cliente ? $cliente->idCli : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function obtenerIdProveedor()
    {
        try {
            $proveedor = DB::table('proveedores')->where('nomProve', $this->proveedor)->first();
            return $proveedor ? $proveedor->idProve : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function marcarComoPagado($cuentaId)
    {
        try {
            $cuenta = DB::table('cuentaspendientes')->where('idCuePen', $cuentaId)->first();
            
            if ($cuenta) {
                DB::table('cuentaspendientes')
                    ->where('idCuePen', $cuentaId)
                    ->update([
                        'montoPagado' => $cuenta->montoOriginal,
                        'montoSaldo' => 0,
                        'estCuePen' => 'pagado',
                        'updated_at' => now()
                    ]);

                $this->calcularEstadisticas();
                session()->flash('success', 'Cuenta marcada como pagada');
            }
        } catch (\Exception $e) {
            Log::error('Error al marcar cuenta como pagada: ' . $e->getMessage());
            session()->flash('error', 'Error al actualizar la cuenta');
        }
    }

    public function eliminarCuenta($cuentaId)
    {
        try {
            DB::table('cuentaspendientes')->where('idCuePen', $cuentaId)->delete();
            
            $this->calcularEstadisticas();
            session()->flash('success', 'Cuenta eliminada correctamente');
        } catch (\Exception $e) {
            Log::error('Error al eliminar cuenta: ' . $e->getMessage());
            session()->flash('error', 'Error al eliminar la cuenta');
        }
    }

    public function exportarCuentas()
    {
        session()->flash('info', 'Función de exportación en desarrollo');
    }

    public function calcularDiasVencido($fechaVencimiento)
    {
        try {
            $vencimiento = Carbon::parse($fechaVencimiento);
            $hoy = Carbon::now();
            
            if ($vencimiento->isPast()) {
                return $hoy->diffInDays($vencimiento);
            }
            
            return 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function getEstadoClase($estado, $fechaVencimiento)
    {
        if ($estado === 'pagado') {
            return 'bg-green-100 text-green-800';
        }
        
        if ($estado === 'vencido' || Carbon::parse($fechaVencimiento)->isPast()) {
            return 'bg-red-100 text-red-800';
        }
        
        if ($estado === 'parcial') {
            return 'bg-yellow-100 text-yellow-800';
        }
        
        // Próximo a vencer (menos de 7 días)
        if (Carbon::parse($fechaVencimiento)->diffInDays(Carbon::now()) <= 7) {
            return 'bg-orange-100 text-orange-800';
        }
        
        return 'bg-blue-100 text-blue-800';
    }
}; ?>

@section('title', 'Cuentas Pendientes')

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
                            <span class="text-gray-900">Cuentas Pendientes</span>
                        </nav>
                        <h1 class="text-3xl font-bold text-gray-800 flex items-center">
                            <i class="fas fa-clock mr-3 text-yellow-600"></i> 
                            Cuentas Pendientes
                        </h1>
                        <p class="text-gray-600 mt-1">Control de cuentas por cobrar y por pagar</p>
                    </div>
                    <div class="flex space-x-3">
                        <button wire:click="abrirModal" 
                                class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg flex items-center transition duration-200">
                            <i class="fas fa-plus mr-2"></i> Nueva Cuenta
                        </button>
                        <a href="{{ route('contabilidad.pagos.index') }}" wire:navigate
                           class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg flex items-center transition duration-200">
                            <i class="fas fa-credit-card mr-2"></i> Pagos
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resumen de Cuentas -->
        <div class="flex flex-wrap -mx-3 mb-6">
            <div class="w-full md:w-1/4 px-3 mb-6">
                <div class="bg-white shadow-lg rounded-lg p-6 border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-green-600 uppercase tracking-wide mb-1">Por Cobrar</p>
                            <p class="text-2xl font-bold text-gray-800">${{ number_format($estadisticas['por_cobrar'] ?? 0, 2) }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $estadisticas['cuentas_por_cobrar'] ?? 0 }} cuentas</p>
                        </div>
                        <div class="bg-green-100 p-3 rounded-full">
                            <i class="fas fa-arrow-down text-green-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-full md:w-1/4 px-3 mb-6">
                <div class="bg-white shadow-lg rounded-lg p-6 border-l-4 border-red-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-red-600 uppercase tracking-wide mb-1">Por Pagar</p>
                            <p class="text-2xl font-bold text-gray-800">${{ number_format($estadisticas['por_pagar'] ?? 0, 2) }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $estadisticas['cuentas_por_pagar'] ?? 0 }} cuentas</p>
                        </div>
                        <div class="bg-red-100 p-3 rounded-full">
                            <i class="fas fa-arrow-up text-red-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-full md:w-1/4 px-3 mb-6">
                <div class="bg-white shadow-lg rounded-lg p-6 border-l-4 border-orange-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-orange-600 uppercase tracking-wide mb-1">Vencidas</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $estadisticas['vencidas'] ?? 0 }}</p>
                            <p class="text-xs text-orange-500 mt-1">Requieren atención</p>
                        </div>
                        <div class="bg-orange-100 p-3 rounded-full">
                            <i class="fas fa-exclamation-triangle text-orange-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-full md:w-1/4 px-3 mb-6">
                <div class="bg-white shadow-lg rounded-lg p-6 border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-blue-600 uppercase tracking-wide mb-1">Por Vencer</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $estadisticas['proximas_vencer'] ?? 0 }}</p>
                            <p class="text-xs text-blue-500 mt-1">Próximos 7 días</p>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-full">
                            <i class="fas fa-calendar-alt text-blue-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="flex flex-wrap -mx-3 mb-6">
            <div class="w-full px-3">
                <div class="bg-white shadow-lg rounded-lg p-6">
                    <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo</label>
                            <select wire:model="tipo" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                                <option value="">Todos los tipos</option>
                                <option value="por_cobrar">Por Cobrar</option>
                                <option value="por_pagar">Por Pagar</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                            <select wire:model="estado" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                                <option value="">Todos los estados</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="pagado">Pagado</option>
                                <option value="vencido">Vencido</option>
                                <option value="parcial">Parcial</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Cliente/Proveedor</label>
                            <input type="text" wire:model="cliente_buscar" placeholder="Buscar..." 
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Vencimiento</label>
                            <input type="date" wire:model="fecha_vencimiento" 
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                        </div>
                        <div class="flex items-end space-x-2">
                            <button wire:click="aplicarFiltros" class="flex-1 bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg transition duration-200">
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

        <!-- Tabla de Cuentas Pendientes -->
        <div class="flex flex-wrap -mx-3">
            <div class="w-full px-3">
                <div class="bg-white shadow-lg rounded-lg">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <div>
                                <h6 class="text-lg font-semibold text-gray-800">Cuentas Pendientes</h6>
                                <p class="text-sm text-gray-600">{{ $this->cuentasPendientes->total() }} cuentas encontradas</p>
                            </div>
                            <div class="flex space-x-2">
                                <button wire:click="exportarCuentas" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm transition duration-200">
                                    <i class="fas fa-download mr-1"></i> Exportar
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente/Proveedor</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto Original</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto Pagado</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Saldo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vencimiento</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($this->cuentasPendientes as $cuenta)
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $cuenta->tipCuePen === 'por_cobrar' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            @if($cuenta->tipCuePen === 'por_cobrar')
                                                <i class="fas fa-arrow-down mr-1"></i> Por Cobrar
                                            @else
                                                <i class="fas fa-arrow-up mr-1"></i> Por Pagar
                                            @endif
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <div>
                                            <div class="font-medium">
                                                {{ $cuenta->cliente_nombre ?? $cuenta->proveedor_nombre ?? 'N/A' }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ $cuenta->cliente_documento ?? $cuenta->proveedor_documento ?? '' }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        ${{ number_format($cuenta->montoOriginal, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        ${{ number_format($cuenta->montoPagado, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        ${{ number_format($cuenta->montoSaldo, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div>
                                            {{ date('d/m/Y', strtotime($cuenta->fecVencimiento)) }}
                                            @if($this->calcularDiasVencido($cuenta->fecVencimiento) > 0)
                                                <div class="text-xs text-red-500">
                                                    {{ $this->calcularDiasVencido($cuenta->fecVencimiento) }} días vencido
                                                </div>
                                            @elseif(\Carbon\Carbon::parse($cuenta->fecVencimiento)->diffInDays(\Carbon\Carbon::now()) <= 7)
                                                <div class="text-xs text-orange-500">
                                                    Vence en {{ \Carbon\Carbon::parse($cuenta->fecVencimiento)->diffInDays(\Carbon\Carbon::now()) }} días
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $this->getEstadoClase($cuenta->estCuePen, $cuenta->fecVencimiento) }}">
                                            @switch($cuenta->estCuePen)
                                                @case('pendiente')
                                                    <i class="fas fa-clock mr-1"></i> Pendiente
                                                    @break
                                                @case('pagado')
                                                    <i class="fas fa-check mr-1"></i> Pagado
                                                    @break
                                                @case('vencido')
                                                    <i class="fas fa-exclamation-triangle mr-1"></i> Vencido
                                                    @break
                                                @case('parcial')
                                                    <i class="fas fa-minus-circle mr-1"></i> Parcial
                                                    @break
                                                @default
                                                    {{ ucfirst($cuenta->estCuePen) }}
                                            @endswitch
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <button class="text-blue-600 hover:text-blue-900" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            @if($cuenta->estCuePen !== 'pagado')
                                                <button wire:click="marcarComoPagado({{ $cuenta->idCuePen }})" 
                                                        class="text-green-600 hover:text-green-900" title="Marcar como pagado">
                                                    <i class="fas fa-check-circle"></i>
                                                </button>
                                            @endif
                                            <button class="text-purple-600 hover:text-purple-900" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button wire:click="eliminarCuenta({{ $cuenta->idCuePen }})" 
                                                    wire:confirm="¿Estás seguro de eliminar esta cuenta?"
                                                    class="text-red-600 hover:text-red-900" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                        <div class="py-8">
                                            <i class="fas fa-clock text-4xl text-gray-300 mb-4"></i>
                                            <p class="text-lg font-medium mb-2">No hay cuentas pendientes</p>
                                            <p class="text-sm text-gray-400 mb-4">Comienza registrando tu primera cuenta</p>
                                            <button wire:click="abrirModal" 
                                                    class="bg-yellow-600 hover:bg-yellow-700 text-white px-6 py-2 rounded-lg transition duration-200">
                                                <i class="fas fa-plus mr-2"></i> Registrar Primera Cuenta
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    @if($this->cuentasPendientes->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $this->cuentasPendientes->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Nueva Cuenta Pendiente -->
    @if($modalAbierto)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-10 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-lg bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Registrar Nueva Cuenta Pendiente</h3>
                    <button wire:click="cerrarModal" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <form wire:submit="guardarCuentaPendiente" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Cuenta *</label>
                            <select wire:model="tipoCuenta" wire:change="resetearCamposModal" 
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                                <option value="por_pagar">💰 Por Pagar</option>
                                <option value="por_cobrar">💸 Por Cobrar</option>
                            </select>
                            @error('tipoCuenta') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        @if($tipoCuenta === 'por_cobrar')
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Cliente *</label>
                            <input type="text" wire:model="cliente" list="clientes-list"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-500" 
                                   placeholder="Nombre del cliente">
                            <datalist id="clientes-list">
                                @foreach($clientes as $cli)
                                    <option value="{{ $cli->nombre }}">{{ $cli->documento }}</option>
                                @endforeach
                            </datalist>
                            @error('cliente') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        @else
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Proveedor *</label>
                            <input type="text" wire:model="proveedor" list="proveedores-list"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-500" 
                                   placeholder="Nombre del proveedor">
                            <datalist id="proveedores-list">
                                @foreach($proveedores as $prov)
                                    <option value="{{ $prov->nombre }}">{{ $prov->nit }}</option>
                                @endforeach
                            </datalist>
                            @error('proveedor') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        @endif
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Descripción *</label>
                        <input type="text" wire:model="descripcion" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-500" 
                               placeholder="Descripción de la cuenta pendiente">
                        @error('descripcion') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Monto Original *</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-gray-500">$</span>
                                <input type="number" wire:model="montoOriginal" step="0.01" 
                                       class="w-full border border-gray-300 rounded-lg pl-8 pr-3 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-500" 
                                       placeholder="0.00">
                            </div>
                            @error('montoOriginal') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Fecha de Vencimiento *</label>
                            <input type="date" wire:model="fechaVencimiento" 
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                            @error('fechaVencimiento') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                            <select wire:model="estadoCuenta" 
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                                <option value="pendiente">⏳ Pendiente</option>
                                <option value="vencido">❌ Vencido</option>
                                <option value="parcial">⚠️ Parcial</option>
                            </select>
                            @error('estadoCuenta') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Observaciones</label>
                        <textarea wire:model="observaciones" rows="3" 
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-500" 
                                  placeholder="Notas adicionales sobre la cuenta..."></textarea>
                        @error('observaciones') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" wire:click="cerrarModal" 
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition duration-200">
                            Cancelar
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition duration-200">
                            <i class="fas fa-save mr-2"></i> Registrar Cuenta
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Loading Overlay -->
    <div wire:loading.flex wire:target="guardarCuentaPendiente" 
         class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50 items-center justify-center">
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <div class="flex items-center space-x-3">
                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-yellow-600"></div>
                <span class="text-gray-700">Guardando cuenta...</span>
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
    Livewire.on('cuenta-creada', () => {
        console.log('Cuenta pendiente creada exitosamente');
    });
});

// Prevenir envío accidental de formularios
document.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
        e.preventDefault();
    }
});
</script>
@endpush