<?php
// resources/views/livewire/contabilidad/pagos/index.blade.php

use App\Models\Pago;
use App\Models\Proveedor;
use App\Models\MovimientoContable;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Support\Facades\Route;

new #[Layout('layouts.auth')] class extends Component {
    use WithPagination;

    // Propiedades para filtros
    public $estado = '';
    public $metodo_pago = '';
    public $proveedor_buscar = '';
    public $fecha = '';
    public $per_page = 15;
    public $modalDetalles = false;
    public $pagoSeleccionado = null;

    // Propiedades para el modal de nuevo pago
    public $modalAbierto = false;
    public $proveedor = '';
    public $referencia = '';
    public $concepto = '';
    public $metodoPago = '';
    public $fechaPago = '';
    public $estadoPago = 'completado';
    public $monto = '';
    public $numeroTransaccion = '';
    public $observaciones = '';

    // Estadísticas
    public $estadisticas = [];
    public $proveedores = [];

    public function mount()
    {
        $this->fechaPago = date('Y-m-d');
        $this->referencia = $this->generarReferencia();
        $this->cargarProveedores();
        $this->calcularEstadisticas();
    }

    private function generarReferencia()
    {
        try {
            $ultimoPago = DB::table('pagos')->orderBy('id', 'desc')->first();
            $numero = $ultimoPago ? $ultimoPago->id + 1 : 1;
            return 'PAG-' . date('Ymd') . '-' . str_pad($numero, 3, '0', STR_PAD_LEFT);
        } catch (\Exception $e) {
            return 'PAG-' . date('Ymd') . '-001';
        }
    }

    public function cargarProveedores()
    {
        try {
            $this->proveedores = DB::table('proveedores')
                ->orderBy('nomProve')
                ->get()
                ->map(function ($proveedor) {
                    return (object)[
                        'id' => $proveedor->idProve,
                        'nombre' => $proveedor->nomProve
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
        $mesActual = now()->month;
        $anoActual = now()->year;

        // ✅ CAMBIAR: Usar comprasgastos en lugar de pagos
        $pagosMes = DB::table('comprasgastos')
            ->whereMonth('fecComGas', $mesActual)
            ->whereYear('fecComGas', $anoActual)
            ->sum('monComGas') ?? 0;

        $transaccionesMes = DB::table('comprasgastos')
            ->whereMonth('fecComGas', $mesActual)
            ->whereYear('fecComGas', $anoActual)
            ->count();

        // Usar cuentaspendientes para obtener estados reales
        $pagosCompletados = DB::table('comprasgastos as cg')
            ->leftJoin('cuentaspendientes as cp', 'cg.idComGas', '=', 'cp.idComGasCuePen')
            ->whereMonth('cg.fecComGas', $mesActual)
            ->whereYear('cg.fecComGas', $anoActual)
            ->where(function($query) {
                $query->where('cp.estCuePen', 'pagado')
                      ->orWhereNull('cp.idCuePen'); // Si no está en cuentaspendientes, se considera pagado
            })
            ->count();

        // Pagos pendientes
        $pagosPendientes = DB::table('cuentaspendientes')
            ->where('tipCuePen', 'por_pagar')
            ->whereIn('estCuePen', ['pendiente', 'parcial'])
            ->count();

        // Pagos vencidos (como rechazados)
        $pagosRechazados = DB::table('cuentaspendientes')
            ->where('tipCuePen', 'por_pagar')
            ->where('estCuePen', 'vencido')
            ->count();

        $this->estadisticas = [
            'pagos_mes' => $pagosMes,
            'transacciones_mes' => $transaccionesMes,
            'completados' => $pagosCompletados,
            'pendientes' => $pagosPendientes,
            'rechazados' => $pagosRechazados
        ];

    } catch (\Exception $e) {
        Log::error('Error calculando estadísticas de pagos: ' . $e->getMessage());
        $this->estadisticas = [
            'pagos_mes' => 0,
            'transacciones_mes' => 0,
            'completados' => 0,
            'pendientes' => 0,
            'rechazados' => 0
        ];
    }
}

    public function getPagosProperty()
    {
        try {
            $query = DB::table('comprasgastos as cg')
                ->leftJoin('cuentaspendientes as cp', 'cg.idComGas', '=', 'cp.idComGasCuePen')
                ->select(
                    'cg.*',
                    'cp.estCuePen as estado_pago',
                    'cp.fecVencimiento',
                    'cp.montoSaldo'
                );

            // Aplicar filtros
            if ($this->estado) {
                if ($this->estado == 'completado') {
                    $query->where(function ($q) {
                        $q->where('cp.estCuePen', 'pagado')
                            ->orWhereNull('cp.idCuePen');
                    });
                } else {
                    $query->where('cp.estCuePen', $this->estado);
                }
            }

            if ($this->metodo_pago) {
                $query->where('cg.metPagComGas', $this->metodo_pago);
            }

            if ($this->proveedor_buscar) {
                $query->where('cg.provComGas', 'like', '%' . $this->proveedor_buscar . '%');
            }

            if ($this->fecha) {
                $query->whereDate('cg.fecComGas', $this->fecha);
            }

            return $query->orderBy('cg.fecComGas', 'desc')
                ->paginate($this->per_page);
        } catch (\Exception $e) {
            Log::error('Error al obtener pagos: ' . $e->getMessage());
            return DB::table('comprasgastos')->whereRaw('1=0')->paginate($this->per_page);
        }
    }

    public function aplicarFiltros()
    {
        $this->resetPage();
    }

    public function limpiarFiltros()
    {
        $this->reset(['estado', 'metodo_pago', 'proveedor_buscar', 'fecha']);
        $this->resetPage();
    }

    public function abrirModal()
    {
        $this->modalAbierto = true;
        $this->resetearCamposModal();
    }

    private function resetearCamposModal()
    {
        $this->reset(['proveedor', 'concepto', 'metodoPago', 'monto', 'numeroTransaccion', 'observaciones']);
        $this->fechaPago = date('Y-m-d');
        $this->referencia = $this->generarReferencia();
        $this->estadoPago = 'completado';
    }

    public function cerrarModal()
    {
        $this->modalAbierto = false;
        $this->resetValidation();
    }

    public function rules(): array
    {
        return [
            'proveedor' => 'required|string|max:255',
            'concepto' => 'required|string|max:255',
            'metodoPago' => 'required|string',
            'fechaPago' => 'required|date',
            'estadoPago' => 'required|in:completado,pendiente,procesando',
            'monto' => 'required|numeric|min:0.01',
            'numeroTransaccion' => 'nullable|string|max:100',
            'observaciones' => 'nullable|string|max:500'
        ];
    }

    public function guardarPago()
    {
        $validated = $this->validate();

        try {
            DB::beginTransaction();

            // Crear el registro en comprasgastos
            $pagoId = DB::table('comprasgastos')->insertGetId([
                'tipComGas' => 'gasto',
                'catComGas' => 'Pagos a Proveedores',
                'desComGas' => $this->concepto,
                'monComGas' => $this->monto,
                'fecComGas' => $this->fechaPago,
                'metPagComGas' => $this->metodoPago,
                'provComGas' => $this->proveedor,
                'obsComGas' => $this->observaciones,
                'docComGas' => $this->numeroTransaccion,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Si está pendiente, crear registro en cuentaspendientes
            if ($this->estadoPago === 'pendiente') {
                DB::table('cuentaspendientes')->insert([
                    'tipCuePen' => 'por_pagar',
                    'idComGasCuePen' => $pagoId,
                    'montoOriginal' => $this->monto,
                    'montoPagado' => 0.00,
                    'montoSaldo' => $this->monto,
                    'estCuePen' => 'pendiente',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Crear movimiento contable si el pago está completado
            if ($this->estadoPago === 'completado') {
                MovimientoContable::create([
                    'fecMovCont' => $this->fechaPago,
                    'tipoMovCont' => 'egreso',
                    'catMovCont' => 'Pagos a Proveedores',
                    'conceptoMovCont' => 'Pago #' . $this->referencia . ' - ' . $this->proveedor,
                    'montoMovCont' => $this->monto,
                    'idComGasMovCont' => $pagoId,
                    'obsMovCont' => 'Movimiento generado automáticamente por pago registrado'
                ]);
            }

            DB::commit();

            $this->cerrarModal();
            $this->calcularEstadisticas();

            session()->flash('success', 'Pago registrado exitosamente');
            Log::info('Pago guardado con ID: ' . $pagoId);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error al guardar pago: ' . $e->getMessage());
            session()->flash('error', 'Error al registrar el pago: ' . $e->getMessage());
        }
    }

    public function cambiarEstadoPago($pagoId, $nuevoEstado)
    {
        try {
            DB::beginTransaction();

            if ($nuevoEstado == 'completado') {
                // Actualizar o crear en cuentaspendientes
                $cuentaPendiente = DB::table('cuentaspendientes')
                    ->where('idComGasCuePen', $pagoId)
                    ->first();

                if ($cuentaPendiente) {
                    DB::table('cuentaspendientes')
                        ->where('idComGasCuePen', $pagoId)
                        ->update([
                            'estCuePen' => 'pagado',
                            'montoPagado' => $cuentaPendiente->montoOriginal,
                            'montoSaldo' => 0,
                            'updated_at' => now()
                        ]);
                }

                // Crear movimiento contable
                $pago = DB::table('comprasgastos')->where('idComGas', $pagoId)->first();
                if ($pago) {
                    MovimientoContable::create([
                        'fecMovCont' => now()->format('Y-m-d'),
                        'tipoMovCont' => 'egreso',
                        'catMovCont' => 'Pagos a Proveedores',
                        'conceptoMovCont' => 'Pago procesado - ' . $pago->provComGas,
                        'montoMovCont' => $pago->monComGas,
                        'idComGasMovCont' => $pagoId,
                        'obsMovCont' => 'Pago procesado automáticamente'
                    ]);
                }
            }

            DB::commit();
            $this->calcularEstadisticas();
            session()->flash('success', 'Estado del pago actualizado');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error al cambiar estado del pago: ' . $e->getMessage());
            session()->flash('error', 'Error al actualizar el estado');
        }
    }

    public function eliminarPago($pagoId)
{
    try {
        DB::beginTransaction();
        
        // Verificar si el pago existe
        $pago = DB::table('comprasgastos')->where('idComGas', $pagoId)->first();
        if (!$pago) {
            throw new \Exception('Pago no encontrado');
        }
        
        // Eliminar movimientos contables relacionados
        DB::table('movimientoscontables')->where('idComGasMovCont', $pagoId)->delete();
        
        // Eliminar de cuentaspendientes si existe
        DB::table('cuentaspendientes')->where('idComGasCuePen', $pagoId)->delete();
        
        // Eliminar de comprasgastos
        DB::table('comprasgastos')->where('idComGas', $pagoId)->delete();
        
        DB::commit();
        $this->calcularEstadisticas();
        session()->flash('success', 'Pago eliminado correctamente');
        
    } catch (\Exception $e) {
        DB::rollback();
        Log::error('Error al eliminar pago: ' . $e->getMessage());
        session()->flash('error', 'Error al eliminar el pago: ' . $e->getMessage());
    }
}

    public function duplicarPago($pagoId)
{
    try {
        $pago = DB::table('comprasgastos')->where('idComGas', $pagoId)->first();
        
        if ($pago) {
            $this->proveedor = $pago->provComGas;
            $this->concepto = $pago->desComGas;
            $this->metodoPago = $pago->metPagComGas;
            $this->monto = $pago->monComGas;
            $this->observaciones = $pago->obsComGas;
            $this->fechaPago = date('Y-m-d');
            $this->referencia = $this->generarReferencia();
            $this->estadoPago = 'pendiente';
            
            $this->abrirModal();
        }
    } catch (\Exception $e) {
        Log::error('Error al duplicar pago: ' . $e->getMessage());
        session()->flash('error', 'Error al duplicar el pago');
    }
}

public function verDetalles($pagoId)
{
    try {
        $this->pagoSeleccionado = DB::table('comprasgastos as cg')
            ->leftJoin('cuentaspendientes as cp', 'cg.idComGas', '=', 'cp.idComGasCuePen')
            ->leftJoin('movimientoscontables as mc', 'cg.idComGas', '=', 'mc.idComGasMovCont')
            ->select(
                'cg.*',
                'cp.estCuePen as estado_cuenta',
                'cp.fecVencimiento',
                'cp.montoSaldo',
                'cp.montoPagado as monto_pagado_cuenta',
                'mc.idMovCont',
                'mc.fecMovCont as fecha_movimiento'
            )
            ->where('cg.idComGas', $pagoId)
            ->first();
        
        if ($this->pagoSeleccionado) {
            $this->modalDetalles = true;
        }
    } catch (\Exception $e) {
        Log::error('Error al ver detalles del pago: ' . $e->getMessage());
        session()->flash('error', 'Error al cargar los detalles del pago');
    }
}

public function cerrarDetalles()
{
    $this->modalDetalles = false;
    $this->pagoSeleccionado = null;
}

public function descargarPDF($pagoId)
{
    return $this->redirect(route('contabilidad.pagos.pdf', $pagoId));
}

    public function exportarPagos()
    {
        session()->flash('info', 'Función de exportación en desarrollo');
    }

}; ?>

@section('title', 'Pagos')
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
                            <span class="text-gray-900">Pagos</span>
                        </nav>
                        <h1 class="text-3xl font-bold text-gray-800 flex items-center">
                            <i class="fas fa-credit-card mr-3 text-purple-600"></i>
                            Gestión de Pagos
                        </h1>
                        <p class="text-gray-600 mt-1">Control de pagos a proveedores y servicios</p>
                    </div>
                    <div class="flex space-x-3">
                        <button wire:click="abrirModal"
                            class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg flex items-center transition duration-200">
                            <i class="fas fa-plus mr-2"></i> Nuevo Pago
                        </button>
                        <a href="{{ route('contabilidad.cuentas-pendientes.index') }}" wire:navigate
                            class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg flex items-center transition duration-200">
                            <i class="fas fa-clock mr-2"></i> Pendientes
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resumen de Pagos -->
        <div class="flex flex-wrap -mx-3 mb-6">
            <div class="w-full md:w-1/4 px-3 mb-6">
                <div class="bg-white shadow-lg rounded-lg p-6 border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-green-600 uppercase tracking-wide mb-1">Pagos del Mes</p>
                            <p class="text-2xl font-bold text-gray-800">${{ number_format($estadisticas['pagos_mes'] ?? 0, 2) }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $estadisticas['transacciones_mes'] ?? 0 }} transacciones</p>
                        </div>
                        <div class="bg-green-100 p-3 rounded-full">
                            <i class="fas fa-money-bill-wave text-green-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-full md:w-1/4 px-3 mb-6">
                <div class="bg-white shadow-lg rounded-lg p-6 border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-blue-600 uppercase tracking-wide mb-1">Pagos Completados</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $estadisticas['completados'] ?? 0 }}</p>
                            <p class="text-xs text-green-500 mt-1">Este mes</p>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-full">
                            <i class="fas fa-check-circle text-blue-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-full md:w-1/4 px-3 mb-6">
                <div class="bg-white shadow-lg rounded-lg p-6 border-l-4 border-yellow-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-yellow-600 uppercase tracking-wide mb-1">Pagos Pendientes</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $estadisticas['pendientes'] ?? 0 }}</p>
                            <p class="text-xs text-yellow-500 mt-1">Por procesar</p>
                        </div>
                        <div class="bg-yellow-100 p-3 rounded-full">
                            <i class="fas fa-hourglass-half text-yellow-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-full md:w-1/4 px-3 mb-6">
                <div class="bg-white shadow-lg rounded-lg p-6 border-l-4 border-red-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-red-600 uppercase tracking-wide mb-1">Pagos Rechazados</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $estadisticas['rechazados'] ?? 0 }}</p>
                            <p class="text-xs text-red-500 mt-1">Requieren atención</p>
                        </div>
                        <div class="bg-red-100 p-3 rounded-full">
                            <i class="fas fa-times-circle text-red-600 text-xl"></i>
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
                            <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                            <select wire:model="estado" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                                <option value="">Todos los estados</option>
                                <option value="completado">Completado</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="procesando">Procesando</option>
                                <option value="rechazado">Rechazado</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Método de Pago</label>
                            <select wire:model="metodo_pago" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                                <option value="">Todos los métodos</option>
                                <option value="transferencia">Transferencia</option>
                                <option value="cheque">Cheque</option>
                                <option value="efectivo">Efectivo</option>
                                <option value="tarjeta_credito">Tarjeta de Crédito</option>
                                <option value="tarjeta_debito">Tarjeta de Débito</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Proveedor</label>
                            <input type="text" wire:model="proveedor_buscar" placeholder="Buscar proveedor..."
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Fecha</label>
                            <input type="date" wire:model="fecha"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                        </div>
                        <div class="flex items-end space-x-2">
                            <button wire:click="aplicarFiltros" class="flex-1 bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition duration-200">
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

        <!-- Tabla de Pagos -->
        <div class="flex flex-wrap -mx-3">
            <div class="w-full px-3">
                <div class="bg-white shadow-lg rounded-lg">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <div>
                                <h6 class="text-lg font-semibold text-gray-800">Historial de Pagos</h6>
                                <p class="text-sm text-gray-600">{{ $this->pagos->total() }} pagos encontrados</p>
                            </div>
                            <div class="flex space-x-2">
                                <button wire:click="exportarPagos" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm transition duration-200">
                                    <i class="fas fa-download mr-1"></i> Exportar
                                </button>
                                <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition duration-200">
                                    <i class="fas fa-sync mr-1"></i> Sincronizar
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Referencia</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proveedor</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Concepto</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Método</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($this->pagos as $pago)
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ 'PAG-' . str_pad($pago->idComGas, 6, '0', STR_PAD_LEFT) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ $pago->provComGas ?? 'Sin proveedor' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <div class="max-w-xs truncate" title="{{ $pago->desComGas }}">
                                            {{ $pago->desComGas ?? 'Sin descripción' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                            {{ ucfirst($pago->metPagComGas ?? 'N/A') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ date('d/m/Y', strtotime($pago->fecComGas)) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        ${{ number_format($pago->monComGas, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                        $estado = $pago->estado_pago ?? 'completado';
                                        if (is_null($pago->estado_pago)) $estado = 'completado';
                                        @endphp
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                       @switch($estado)
                           @case('pagado') bg-green-100 text-green-800 @break
                           @case('pendiente') bg-yellow-100 text-yellow-800 @break
                           @case('parcial') bg-blue-100 text-blue-800 @break
                           @case('vencido') bg-red-100 text-red-800 @break
                           @case('completado') bg-green-100 text-green-800 @break
                           @default bg-gray-100 text-gray-800
                       @endswitch">
                                            @switch($estado)
                                            @case('pagado')
                                            @case('completado')
                                            <i class="fas fa-check mr-1"></i> Completado
                                            @break
                                            @case('pendiente')
                                            <i class="fas fa-clock mr-1"></i> Pendiente
                                            @break
                                            @case('parcial')
                                            <i class="fas fa-spinner mr-1"></i> Parcial
                                            @break
                                            @case('vencido')
                                            <i class="fas fa-times mr-1"></i> Vencido
                                            @break
                                            @default
                                            {{ ucfirst($estado) }}
                                            @endswitch
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
    <div class="flex space-x-2">
        <button wire:click="verDetalles({{ $pago->idComGas }})" 
                class="text-blue-600 hover:text-blue-900" title="Ver detalles">
            <i class="fas fa-eye"></i>
        </button>
        <button wire:click="descargarPDF({{ $pago->idComGas }})" 
                class="text-green-600 hover:text-green-900" title="Descargar comprobante">
            <i class="fas fa-download"></i>
        </button>
        @if($estado == 'pendiente')
        <button wire:click="cambiarEstadoPago({{ $pago->idComGas }}, 'completado')"
            class="text-yellow-600 hover:text-yellow-900" title="Procesar">
            <i class="fas fa-play"></i>
        </button>
        @endif
        <button wire:click="duplicarPago({{ $pago->idComGas }})"
            class="text-purple-600 hover:text-purple-900" title="Duplicar">
            <i class="fas fa-copy"></i>
        </button>
        <button wire:click="eliminarPago({{ $pago->idComGas }})"
            wire:confirm="¿Estás seguro de eliminar este pago? Esta acción no se puede deshacer."
            class="text-red-600 hover:text-red-900" title="Eliminar">
            <i class="fas fa-trash"></i>
        </button>
    </div>
</td>
                                @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                        <div class="py-8">
                                            <i class="fas fa-credit-card text-4xl text-gray-300 mb-4"></i>
                                            <p class="text-lg font-medium mb-2">No hay pagos registrados</p>
                                            <p class="text-sm text-gray-400 mb-4">Comienza registrando tu primer pago</p>
                                            <button wire:click="abrirModal"
                                                class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg transition duration-200">
                                                <i class="fas fa-plus mr-2"></i> Registrar Primer Pago
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    @if($this->pagos->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $this->pagos->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Nuevo Pago -->
    @if($modalAbierto)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-10 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-lg bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Registrar Nuevo Pago</h3>
                    <button wire:click="cerrarModal" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <form wire:submit="guardarPago" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Proveedor *</label>
                            <input type="text" wire:model="proveedor"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500"
                                placeholder="Nombre del proveedor">
                            @error('proveedor') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Referencia</label>
                            <input type="text" wire:model="referencia"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                            @error('referencia') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Concepto *</label>
                        <input type="text" wire:model="concepto"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500"
                            placeholder="Descripción del pago">
                        @error('concepto') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Método de Pago *</label>
                            <select wire:model="metodoPago"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                                <option value="">Seleccionar método</option>
                                <option value="transferencia">🏦 Transferencia Bancaria</option>
                                <option value="cheque">📝 Cheque</option>
                                <option value="efectivo">💵 Efectivo</option>
                                <option value="tarjeta_credito">💳 Tarjeta de Crédito</option>
                                <option value="tarjeta_debito">💳 Tarjeta de Débito</option>
                            </select>
                            @error('metodoPago') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Fecha de Pago *</label>
                            <input type="date" wire:model="fechaPago"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                            @error('fechaPago') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                            <select wire:model="estadoPago"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                                <option value="completado">✅ Completado</option>
                                <option value="pendiente">⏳ Pendiente</option>
                                <option value="procesando">🔄 Procesando</option>
                            </select>
                            @error('estadoPago') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Monto *</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-gray-500">$</span>
                                <input type="number" wire:model="monto" step="0.01"
                                    class="w-full border border-gray-300 rounded-lg pl-8 pr-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500"
                                    placeholder="0.00">
                            </div>
                            @error('monto') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Número de Transacción</label>
                            <input type="text" wire:model="numeroTransaccion"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500"
                                placeholder="Número de confirmación">
                            @error('numeroTransaccion') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Observaciones</label>
                        <textarea wire:model="observaciones" rows="3"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500"
                            placeholder="Notas adicionales sobre el pago..."></textarea>
                        @error('observaciones') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" wire:click="cerrarModal"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition duration-200">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition duration-200">
                            <i class="fas fa-save mr-2"></i> Registrar Pago
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal Detalles del Pago -->
@if($modalDetalles && $pagoSeleccionado)
<div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-lg bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-semibold text-gray-900">
                    Detalles del Pago #{{ str_pad($pagoSeleccionado->idComGas, 6, '0', STR_PAD_LEFT) }}
                </h3>
                <button wire:click="cerrarDetalles" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Información Principal -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                        <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                        Información Principal
                    </h4>
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm font-medium text-gray-600">Proveedor:</label>
                            <p class="text-gray-900">{{ $pagoSeleccionado->provComGas ?? 'Sin proveedor' }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">Concepto:</label>
                            <p class="text-gray-900">{{ $pagoSeleccionado->desComGas ?? 'Sin descripción' }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">Categoría:</label>
                            <p class="text-gray-900">{{ $pagoSeleccionado->catComGas ?? 'Sin categoría' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Información Financiera -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                        <i class="fas fa-dollar-sign text-green-600 mr-2"></i>
                        Información Financiera
                    </h4>
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm font-medium text-gray-600">Monto:</label>
                            <p class="text-lg font-semibold text-green-600">
                                ${{ number_format($pagoSeleccionado->monComGas, 2) }}
                            </p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">Método de Pago:</label>
                            <p class="text-gray-900">{{ ucfirst($pagoSeleccionado->metPagComGas ?? 'N/A') }}</p>
                        </div>
                        @if($pagoSeleccionado->montoSaldo)
                        <div>
                            <label class="text-sm font-medium text-gray-600">Saldo Pendiente:</label>
                            <p class="text-red-600 font-semibold">
                                ${{ number_format($pagoSeleccionado->montoSaldo, 2) }}
                            </p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Fechas -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                        <i class="fas fa-calendar text-purple-600 mr-2"></i>
                        Fechas
                    </h4>
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm font-medium text-gray-600">Fecha de Pago:</label>
                            <p class="text-gray-900">{{ date('d/m/Y', strtotime($pagoSeleccionado->fecComGas)) }}</p>
                        </div>
                        @if($pagoSeleccionado->fecVencimiento)
                        <div>
                            <label class="text-sm font-medium text-gray-600">Fecha de Vencimiento:</label>
                            <p class="text-gray-900">{{ date('d/m/Y', strtotime($pagoSeleccionado->fecVencimiento)) }}</p>
                        </div>
                        @endif
                        <div>
                            <label class="text-sm font-medium text-gray-600">Registrado:</label>
                            <p class="text-gray-900">{{ date('d/m/Y H:i', strtotime($pagoSeleccionado->created_at)) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Estado y Documentos -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                        <i class="fas fa-file-alt text-orange-600 mr-2"></i>
                        Estado y Documentos
                    </h4>
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm font-medium text-gray-600">Estado:</label>
                            @php
                                $estado = $pagoSeleccionado->estado_cuenta ?? 'completado';
                                if (is_null($pagoSeleccionado->estado_cuenta)) $estado = 'completado';
                            @endphp
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                @switch($estado)
                                    @case('pagado') bg-green-100 text-green-800 @break
                                    @case('pendiente') bg-yellow-100 text-yellow-800 @break
                                    @case('parcial') bg-blue-100 text-blue-800 @break
                                    @case('vencido') bg-red-100 text-red-800 @break
                                    @case('completado') bg-green-100 text-green-800 @break
                                    @default bg-gray-100 text-gray-800
                                @endswitch">
                                {{ ucfirst($estado) }}
                            </span>
                        </div>
                        @if($pagoSeleccionado->docComGas)
                        <div>
                            <label class="text-sm font-medium text-gray-600">Documento/Referencia:</label>
                            <p class="text-gray-900">{{ $pagoSeleccionado->docComGas }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            @if($pagoSeleccionado->obsComGas)
            <div class="mt-6 bg-yellow-50 p-4 rounded-lg">
                <h4 class="font-semibold text-gray-800 mb-2 flex items-center">
                    <i class="fas fa-sticky-note text-yellow-600 mr-2"></i>
                    Observaciones
                </h4>
                <p class="text-gray-700">{{ $pagoSeleccionado->obsComGas }}</p>
            </div>
            @endif

            <div class="flex justify-end space-x-3 mt-6 pt-4 border-t">
                <button wire:click="descargarPDF({{ $pagoSeleccionado->idComGas }})"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200">
                    <i class="fas fa-download mr-2"></i> Descargar PDF
                </button>
                <button wire:click="cerrarDetalles"
                    class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition duration-200">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>
@endif

    <!-- Loading Overlay -->
    <div wire:loading.flex wire:target="guardarPago"
        class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50 items-center justify-center">
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <div class="flex items-center space-x-3">
                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-purple-600"></div>
                <span class="text-gray-700">Guardando pago...</span>
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
        Livewire.on('pago-creado', () => {
            console.log('Pago creado exitosamente');
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