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
use Barryvdh\DomPDF\Facade\Pdf as PDF;

new #[Layout('layouts.auth')] class extends Component {
    use WithPagination;

    // Propiedades para filtros
    public $tipo = '';
    public $estado = '';
    public $cliente_buscar = '';
    public $fecha_vencimiento = '';
    public $per_page = 15;

    // Propiedades para el modal de edición
    public $modalDetallesAbierto = false;
    public $cuentaDetalles = null;
    public $modalEdicionAbierto = false;
    public $cuentaEditando = null;
    public $editandoId = null;

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
                ->map(function ($cliente) {
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
                ->map(function ($proveedor) {
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

            // Aplicar filtros automáticamente
            if ($this->tipo) {
                $query->where('cp.tipCuePen', $this->tipo);
            }

            if ($this->estado) {
                $query->where('cp.estCuePen', $this->estado);
            }

            // Búsqueda mejorada en cliente/proveedor
            if ($this->cliente_buscar) {
                $busqueda = '%' . $this->cliente_buscar . '%';
                $query->where(function ($q) use ($busqueda) {
                    $q->where('c.nomCli', 'like', $busqueda)
                        ->orWhere('c.docCli', 'like', $busqueda)
                        ->orWhere('p.nomProve', 'like', $busqueda)
                        ->orWhere('p.nitProve', 'like', $busqueda);
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

    public function getConteoFiltradoProperty()
    {
        try {
            $query = DB::table('cuentaspendientes as cp')
                ->leftJoin('clientes as c', 'cp.idCliCuePen', '=', 'c.idCli')
                ->leftJoin('proveedores as p', 'cp.idProveCuePen', '=', 'p.idProve');

            if ($this->tipo) {
                $query->where('cp.tipCuePen', $this->tipo);
            }

            if ($this->estado) {
                $query->where('cp.estCuePen', $this->estado);
            }

            if ($this->cliente_buscar) {
                $busqueda = '%' . $this->cliente_buscar . '%';
                $query->where(function ($q) use ($busqueda) {
                    $q->where('c.nomCli', 'like', $busqueda)
                        ->orWhere('c.docCli', 'like', $busqueda)
                        ->orWhere('p.nomProve', 'like', $busqueda)
                        ->orWhere('p.nitProve', 'like', $busqueda);
                });
            }

            if ($this->fecha_vencimiento) {
                $query->whereDate('cp.fecVencimiento', $this->fecha_vencimiento);
            }

            return $query->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function getEstadisticasFiltradas()
    {
        try {
            $query = DB::table('cuentaspendientes as cp')
                ->leftJoin('clientes as c', 'cp.idCliCuePen', '=', 'c.idCli')
                ->leftJoin('proveedores as p', 'cp.idProveCuePen', '=', 'p.idProve');

            // Aplicar los mismos filtros
            if ($this->tipo) {
                $query->where('cp.tipCuePen', $this->tipo);
            }

            if ($this->estado) {
                $query->where('cp.estCuePen', $this->estado);
            }

            if ($this->cliente_buscar) {
                $busqueda = '%' . $this->cliente_buscar . '%';
                $query->where(function ($q) use ($busqueda) {
                    $q->where('c.nomCli', 'like', $busqueda)
                        ->orWhere('c.docCli', 'like', $busqueda)
                        ->orWhere('p.nomProve', 'like', $busqueda)
                        ->orWhere('p.nitProve', 'like', $busqueda);
                });
            }

            if ($this->fecha_vencimiento) {
                $query->whereDate('cp.fecVencimiento', $this->fecha_vencimiento);
            }

            // Calcular estadísticas de los resultados filtrados
            $resultados = $query->select(
                DB::raw('SUM(CASE WHEN cp.tipCuePen = "por_cobrar" AND cp.estCuePen != "pagado" THEN cp.montoSaldo ELSE 0 END) as por_cobrar'),
                DB::raw('SUM(CASE WHEN cp.tipCuePen = "por_pagar" AND cp.estCuePen != "pagado" THEN cp.montoSaldo ELSE 0 END) as por_pagar'),
                DB::raw('COUNT(CASE WHEN cp.tipCuePen = "por_cobrar" AND cp.estCuePen != "pagado" THEN 1 END) as cuentas_por_cobrar'),
                DB::raw('COUNT(CASE WHEN cp.tipCuePen = "por_pagar" AND cp.estCuePen != "pagado" THEN 1 END) as cuentas_por_pagar')
            )->first();

            return [
                'por_cobrar' => $resultados->por_cobrar ?? 0,
                'por_pagar' => $resultados->por_pagar ?? 0,
                'cuentas_por_cobrar' => $resultados->cuentas_por_cobrar ?? 0,
                'cuentas_por_pagar' => $resultados->cuentas_por_pagar ?? 0,
            ];
        } catch (\Exception $e) {
            return [
                'por_cobrar' => 0,
                'por_pagar' => 0,
                'cuentas_por_cobrar' => 0,
                'cuentas_por_pagar' => 0,
            ];
        }
    }

    public function limpiarFiltros()
    {
        $this->reset(['tipo', 'estado', 'cliente_buscar', 'fecha_vencimiento']);
        $this->resetPage();
        session()->flash('info', 'Filtros limpiados');
    }

    public function updatedTipo()
    {
        $this->resetPage();
    }

    public function updatedEstado()
    {
        $this->resetPage();
    }

    public function updatedClienteBuscar()
    {
        $this->resetPage();
    }

    public function updatedFechaVencimiento()
    {
        $this->resetPage();
    }

    public function abrirModal()
    {
        $this->editandoId = null;
        $this->modalAbierto = true;
        $this->resetearCamposModal();
    }

    // Función auxiliar para verificar si estamos editando
    public function esEdicion()
    {
        return !is_null($this->editandoId);
    }


    public function resetearCamposModal()
    {
        $this->reset(['cliente', 'proveedor', 'descripcion', 'montoOriginal', 'observaciones']);

        // Solo resetear fecha si no estamos editando
        if (!$this->editandoId) {
            $this->fechaVencimiento = date('Y-m-d', strtotime('+30 days'));
            $this->tipoCuenta = 'por_pagar';
            $this->estadoCuenta = 'pendiente';
        }

        $this->resetValidation();
    }

    public function cerrarModal()
    {
        $this->modalAbierto = false;
        $this->resetValidation();
    }

    public function rules(): array
{
    $rules = [
        'tipoCuenta' => 'required|in:por_cobrar,por_pagar',
        'descripcion' => 'required|string|max:500',
        'montoOriginal' => 'required|numeric|min:0.01',
        'estadoCuenta' => 'required|in:pendiente,pagado,vencido,parcial',
        'observaciones' => 'nullable|string|max:1000'
    ];

    // Validación condicional para fechaVencimiento
    if ($this->editandoId) {
        $rules['fechaVencimiento'] = 'required|date';
    } else {
        $rules['fechaVencimiento'] = 'required|date|after:today';
    }

    // Validación condicional para cliente/proveedor
    if ($this->tipoCuenta === 'por_cobrar') {
        $rules['cliente'] = 'required|string|max:255';
    } else {
        $rules['proveedor'] = 'required|string|max:255';
    }

    return $rules;
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

    // Función para ver detalles de una cuenta
   public function verDetalles($cuentaId)
{
    try {
        if (!$cuentaId || !is_numeric($cuentaId)) {
            session()->flash('error', 'ID de cuenta inválido');
            return;
        }

        $this->cuentaDetalles = null;
        
        $this->cuentaDetalles = DB::table('cuentaspendientes as cp')
            ->leftJoin('clientes as c', 'cp.idCliCuePen', '=', 'c.idCli')
            ->leftJoin('proveedores as p', 'cp.idProveCuePen', '=', 'p.idProve')
            ->select(
                'cp.*',
                'c.nomCli as cliente_nombre',
                'c.docCli as cliente_documento', 
                'p.nomProve as proveedor_nombre',
                'p.nitProve as proveedor_documento'
            )
            ->where('cp.idCuePen', $cuentaId)
            ->first();

        if (!$this->cuentaDetalles) {
            session()->flash('error', 'No se encontró la cuenta pendiente');
            return;
        }

        $this->modalDetallesAbierto = true;
        
    } catch (\Exception $e) {
        Log::error('Error al obtener detalles: ' . $e->getMessage());
        session()->flash('error', 'Error al cargar los detalles');
    }
}

    // Función para cerrar modal de detalles
    public function cerrarModalDetalles()
{
    $this->modalDetallesAbierto = false;
    $this->cuentaDetalles = null;
    
    // Limpiar cualquier mensaje de error
    session()->forget(['error', 'success', 'info']);
}

public function verificarCuenta($cuentaId)
{
    try {
        $cuenta = DB::table('cuentaspendientes')->where('idCuePen', $cuentaId)->first();
        
        if ($cuenta) {
            Log::info("Cuenta verificada:", (array)$cuenta);
            session()->flash('success', "Cuenta {$cuentaId} verificada correctamente");
        } else {
            Log::warning("Cuenta {$cuentaId} no existe");
            session()->flash('error', "Cuenta {$cuentaId} no encontrada en base de datos");
        }
    } catch (\Exception $e) {
        Log::error("Error verificando cuenta {$cuentaId}: " . $e->getMessage());
        session()->flash('error', 'Error en verificación');
    }
}
    // Función para abrir modal de edición
    public function editarCuenta($cuentaId)
{
    try {
        $cuenta = DB::table('cuentaspendientes as cp')
            ->leftJoin('clientes as c', 'cp.idCliCuePen', '=', 'c.idCli')
            ->leftJoin('proveedores as p', 'cp.idProveCuePen', '=', 'p.idProve')
            ->select('cp.*', 'c.nomCli as cliente_nombre', 'p.nomProve as proveedor_nombre')
            ->where('cp.idCuePen', $cuentaId)
            ->first();

        if ($cuenta) {
            $this->editandoId = $cuentaId;
            $this->tipoCuenta = $cuenta->tipCuePen;
            $this->cliente = $cuenta->cliente_nombre ?? '';
            $this->proveedor = $cuenta->proveedor_nombre ?? '';
            $this->descripcion = 'Cuenta pendiente #' . $cuentaId;
            $this->montoOriginal = $cuenta->montoOriginal;
            $this->fechaVencimiento = $cuenta->fecVencimiento;
            $this->estadoCuenta = $cuenta->estCuePen;
            $this->observaciones = '';
            
            // Cerrar modal de detalles si está abierto
            $this->modalDetallesAbierto = false;
            $this->modalEdicionAbierto = true;
        } else {
            session()->flash('error', 'No se encontró la cuenta para editar');
        }
    } catch (\Exception $e) {
        Log::error('Error al cargar cuenta para edición: ' . $e->getMessage());
        session()->flash('error', 'Error al cargar la cuenta para edición');
    }
}


    // Función para cerrar modal de edición
    public function cerrarModalEdicion()
    {
        $this->modalEdicionAbierto = false;
        $this->editandoId = null;
        $this->resetearCamposModal();
    }

    // Función para actualizar cuenta
    public function actualizarCuenta()
{
    $this->validate();

    try {
        DB::beginTransaction();

        DB::table('cuentaspendientes')
            ->where('idCuePen', $this->editandoId)
            ->update([
                'tipCuePen' => $this->tipoCuenta,
                'idCliCuePen' => $this->tipoCuenta === 'por_cobrar' ? $this->obtenerIdCliente() : null,
                'idProveCuePen' => $this->tipoCuenta === 'por_pagar' ? $this->obtenerIdProveedor() : null,
                'montoOriginal' => $this->montoOriginal,
                'fecVencimiento' => $this->fechaVencimiento,
                'estCuePen' => $this->estadoCuenta,
                'updated_at' => now()
            ]);

        // Recalcular saldo
        $cuentaActual = DB::table('cuentaspendientes')->where('idCuePen', $this->editandoId)->first();
        $nuevoSaldo = $this->montoOriginal - $cuentaActual->montoPagado;
        
        DB::table('cuentaspendientes')
            ->where('idCuePen', $this->editandoId)
            ->update(['montoSaldo' => $nuevoSaldo]);

        DB::commit();

        $this->cerrarModalEdicion();
        $this->calcularEstadisticas();
        
        session()->flash('success', 'Cuenta actualizada exitosamente');
        
    } catch (\Exception $e) {
        DB::rollback();
        Log::error('Error al actualizar cuenta: ' . $e->getMessage());
        session()->flash('error', 'Error al actualizar la cuenta');
    }
}


public function descargarPDF($cuentaId) 
{
    // Solo hacer redirect a la ruta PDF
    return redirect()->route('contabilidad.cuentas-pendientes.pdf', $cuentaId);
}

// ===== FUNCIÓN PARA EXPORTAR EXCEL =====
public function exportarExcel()
{
    try {
        session()->flash('success', 'Exportación Excel iniciada');
        Log::info('Exportación Excel solicitada');
        
        // TODO: Implementar exportación real a Excel/CSV
        
    } catch (\Exception $e) {
        Log::error('Error exportando Excel: ' . $e->getMessage());
        session()->flash('error', 'Error al exportar Excel');
    }
}

// ===== FUNCIÓN PARA DESCARGAR REPORTE PDF =====
public function descargarReportePDF()
{
    try {
        // Obtener las cuentas filtradas actuales
        $cuentas = $this->cuentasPendientes->items();
        
        if (empty($cuentas)) {
            session()->flash('error', 'No hay cuentas para generar el reporte');
            return;
        }

        // Datos de la granja
        $datosGranja = [
            'nombre' => 'FAMASY',
            'nombre_completo' => 'Finca Agropecuaria Familiar Sostenible',
            'nit' => '900.123.456-7',
            'direccion' => 'Vereda La Esperanza, Pitalito, Huila, Colombia',
            'telefono' => '+57 318 123 4567',
            'email' => 'contacto@famasy.com'
        ];

        // Estadísticas del reporte
        $estadisticasReporte = [
            'total_cuentas' => count($cuentas),
            'total_por_cobrar' => collect($cuentas)->where('tipCuePen', 'por_cobrar')->sum('montoSaldo'),
            'total_por_pagar' => collect($cuentas)->where('tipCuePen', 'por_pagar')->sum('montoSaldo'),
            'cuentas_vencidas' => collect($cuentas)->filter(function($cuenta) {
                return \Carbon\Carbon::parse($cuenta->fecVencimiento)->isPast() && $cuenta->estCuePen !== 'pagado';
            })->count(),
            'fecha_generacion' => now()->format('d/m/Y H:i:s'),
            'usuario_generador' => auth()->user()->name ?? 'Sistema',
            'filtros_aplicados' => [
                'tipo' => $this->tipo,
                'estado' => $this->estado,
                'cliente_buscar' => $this->cliente_buscar,
                'fecha_vencimiento' => $this->fecha_vencimiento
            ]
        ];

        // Generar PDF
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('pdfs.reporte-cuentas-pendientes', compact('cuentas', 'datosGranja', 'estadisticasReporte'));
        $pdf->setPaper('A4', 'portrait');

        $nombreArchivo = "FAMASY_Reporte_CuentasPendientes_" . date('Ymd_His') . ".pdf";

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $nombreArchivo, ['Content-Type' => 'application/pdf']);
        
    } catch (\Exception $e) {
        Log::error('Error generando reporte PDF: ' . $e->getMessage());
        session()->flash('error', 'Error al generar el reporte PDF: ' . $e->getMessage());
    }
}



    // FUNCIÓN AUXILIAR: Verificar qué campos existen en tu tabla
    public function verificarEstructuraTabla()
    {
        try {
            $columns = DB::select("DESCRIBE cuentaspendientes");
            Log::info('Columnas de la tabla cuentaspendientes:', array_map(function ($col) {
                return $col->Field;
            }, $columns));
        } catch (\Exception $e) {
            Log::error('Error al verificar estructura: ' . $e->getMessage());
        }
    }

    // Función para calcular porcentaje de pago
    public function calcularPorcentajePago($montoOriginal, $montoPagado)
    {
        if ($montoOriginal > 0) {
            return round(($montoPagado / $montoOriginal) * 100, 1);
        }
        return 0;
    }

    // Función para obtener días restantes o vencidos
    public function obtenerDiasVencimiento($fechaVencimiento)
    {
        try {
            $vencimiento = Carbon::parse($fechaVencimiento);
            $hoy = Carbon::now();

            if ($vencimiento->isPast()) {
                return [
                    'tipo' => 'vencido',
                    'dias' => $hoy->diffInDays($vencimiento),
                    'texto' => $hoy->diffInDays($vencimiento) . ' días vencido'
                ];
            } elseif ($vencimiento->diffInDays($hoy) <= 7) {
                return [
                    'tipo' => 'proximo',
                    'dias' => $vencimiento->diffInDays($hoy),
                    'texto' => 'Vence en ' . $vencimiento->diffInDays($hoy) . ' días'
                ];
            } else {
                return [
                    'tipo' => 'normal',
                    'dias' => $vencimiento->diffInDays($hoy),
                    'texto' => 'Vence en ' . $vencimiento->diffInDays($hoy) . ' días'
                ];
            }
        } catch (\Exception $e) {
            return [
                'tipo' => 'error',
                'dias' => 0,
                'texto' => 'Fecha inválida'
            ];
        }
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
        <div class="bg-white shadow-lg rounded-lg">
    <div class="p-6 border-b border-gray-200">
        <div class="flex justify-between items-center">
            <div class="mb-4 md:mb-0">
                        <nav class="text-sm text-gray-600 mb-2">
                            <a href="{{ route('contabilidad.index') }}" wire:navigate class="hover:text-blue-600">Dashboard</a>
                            <span class="mx-2">/</span>
                            <span class="text-gray-900">Cuentas Pendientes</span>
                        </nav>
                        <h1 class="text-3xl font-bold text-gray-800 flex items-center">
                            <i class="fas fa-receipt mr-3 text-red-600"></i>
                            Gestión de Cuentas Pendientes
                        </h1>
                        <p class="text-gray-600 mt-1">Control y registro de cuentas pendientes</p>
                    </div>
            <div class="flex space-x-2">
                <!-- Dropdown para opciones de exportación -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" 
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm transition duration-200 flex items-center">
                        <i class="fas fa-download mr-1"></i> Exportar
                        <i class="fas fa-chevron-down ml-1 text-xs"></i>
                    </button>
                    
                    <div x-show="open" 
                         @click.away="open = false"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 transform scale-95"
                         x-transition:enter-end="opacity-100 transform scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 transform scale-100"
                         x-transition:leave-end="opacity-0 transform scale-95"
                         class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 border border-gray-200">
                        <div class="py-1">
                            <button wire:click="descargarReportePDF" 
                                    @click="open = false"
                                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                <i class="fas fa-file-pdf mr-2 text-red-600"></i>
                                Reporte PDF
                            </button>
                            <div class="border-t border-gray-100"></div>
                            <div class="px-4 py-2 text-xs text-gray-500">
                                Se exportarán {{ $this->cuentasPendientes->total() }} registros
                            </div>
                        </div>
                    </div>
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

        <!-- Filtros con búsqueda automática -->
        <div class="flex flex-wrap -mx-3 mb-6">
            <div class="w-full px-3">
                <div class="bg-white shadow-lg rounded-lg p-6">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo</label>
                            <select wire:model.live="tipo" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                                <option value="">Todos los tipos</option>
                                <option value="por_cobrar">Por Cobrar</option>
                                <option value="por_pagar">Por Pagar</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                            <select wire:model.live="estado" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                                <option value="">Todos los estados</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="pagado">Pagado</option>
                                <option value="vencido">Vencido</option>
                                <option value="parcial">Parcial</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Cliente/Proveedor</label>
                            <div class="relative">
                                <input type="text"
                                    wire:model.live.debounce.300ms="cliente_buscar"
                                    placeholder="Buscar por nombre..."
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <div wire:loading wire:target="cliente_buscar" class="animate-spin rounded-full h-4 w-4 border-b-2 border-yellow-600"></div>
                                    <i wire:loading.remove wire:target="cliente_buscar" class="fas fa-search text-gray-400"></i>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Vencimiento</label>
                            <input type="date"
                                wire:model.live="fecha_vencimiento"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                        </div>
                        <div class="flex items-end">
                            <button wire:click="limpiarFiltros"
                                class="w-full bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition duration-200 flex items-center justify-center">
                                <i class="fas fa-times mr-2"></i> Limpiar
                            </button>
                        </div>
                    </div>

                    <!-- Indicadores de filtros activos -->
                    @if($tipo || $estado || $cliente_buscar || $fecha_vencimiento)
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-sm text-gray-600">Filtros activos:</span>

                            @if($tipo)
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                Tipo: {{ $tipo === 'por_cobrar' ? 'Por Cobrar' : 'Por Pagar' }}
                                <button wire:click="$set('tipo', '')" class="ml-1 text-yellow-600 hover:text-yellow-800">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            </span>
                            @endif

                            @if($estado)
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                Estado: {{ ucfirst($estado) }}
                                <button wire:click="$set('estado', '')" class="ml-1 text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            </span>
                            @endif

                            @if($cliente_buscar)
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Buscar: "{{ $cliente_buscar }}"
                                <button wire:click="$set('cliente_buscar', '')" class="ml-1 text-green-600 hover:text-green-800">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            </span>
                            @endif

                            @if($fecha_vencimiento)
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                Vencimiento: {{ date('d/m/Y', strtotime($fecha_vencimiento)) }}
                                <button wire:click="$set('fecha_vencimiento', '')" class="ml-1 text-purple-600 hover:text-purple-800">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            </span>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!--Sección de la tabla -->
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
        <button wire:click="verDetalles({{ $cuenta->idCuePen }})" 
                class="text-blue-600 hover:text-blue-900" title="Ver detalles">
            <i class="fas fa-eye"></i>
        </button>
        
        <!-- TEMPORAL: Botón de verificación para debug -->
        <button wire:click="verificarCuenta({{ $cuenta->idCuePen }})" 
                class="text-yellow-600 hover:text-yellow-900" title="Verificar cuenta">
            <i class="fas fa-search"></i>
        </button>
        
        <button wire:click="descargarPDF({{ $cuenta->idCuePen }})" 
                class="text-red-600 hover:text-red-900" title="Descargar PDF">
            <i class="fas fa-file-pdf"></i>
        </button>
        
        @if($cuenta->estCuePen !== 'pagado')
            <button wire:click="marcarComoPagado({{ $cuenta->idCuePen }})" 
                    class="text-green-600 hover:text-green-900" title="Marcar como pagado">
                <i class="fas fa-check-circle"></i>
            </button>
        @endif
        <button wire:click="editarCuenta({{ $cuenta->idCuePen }})" 
                class="text-purple-600 hover:text-purple-900" title="Editar">
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
        @if($modalDetallesAbierto && $cuentaDetalles && isset($cuentaDetalles->idCuePen))
<div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-lg bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Detalles de la Cuenta Pendiente</h3>
                <button wire:click="cerrarModalDetalles" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-semibold text-gray-700 mb-2">Información General</h4>
                        <div class="space-y-2">
                            <div>
                                <span class="text-sm text-gray-600">ID:</span>
                                <span class="ml-2 font-medium">#{{ $cuentaDetalles->idCuePen ?? 'N/A' }}</span>
                            </div>
                            <div>
                                <span class="text-sm text-gray-600">Tipo:</span>
                                <span class="ml-2 inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ ($cuentaDetalles->tipCuePen ?? '') === 'por_cobrar' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ ($cuentaDetalles->tipCuePen ?? '') === 'por_cobrar' ? 'Por Cobrar' : 'Por Pagar' }}
                                </span>
                            </div>
                            <div>
                                <span class="text-sm text-gray-600">Estado:</span>
                                <span class="ml-2 inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $this->getEstadoClase($cuentaDetalles->estCuePen ?? 'pendiente', $cuentaDetalles->fecVencimiento ?? date('Y-m-d')) }}">
                                    {{ ucfirst($cuentaDetalles->estCuePen ?? 'N/A') }}
                                </span>
                            </div>
                            <div>
                                <span class="text-sm text-gray-600">Fecha de Creación:</span>
                                <span class="ml-2 font-medium">{{ isset($cuentaDetalles->created_at) ? date('d/m/Y H:i', strtotime($cuentaDetalles->created_at)) : 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-semibold text-gray-700 mb-2">
                            {{ ($cuentaDetalles->tipCuePen ?? '') === 'por_cobrar' ? 'Cliente' : 'Proveedor' }}
                        </h4>
                        <div class="space-y-2">
                            <div>
                                <span class="text-sm text-gray-600">Nombre:</span>
                                <span class="ml-2 font-medium">
                                    {{ $cuentaDetalles->cliente_nombre ?? $cuentaDetalles->proveedor_nombre ?? 'N/A' }}
                                </span>
                            </div>
                            <div>
                                <span class="text-sm text-gray-600">Documento:</span>
                                <span class="ml-2 font-medium">
                                    {{ $cuentaDetalles->cliente_documento ?? $cuentaDetalles->proveedor_documento ?? 'N/A' }}
                                </span>
                            </div>
                            @if(isset($cuentaDetalles->cliente_telefono) || isset($cuentaDetalles->proveedor_telefono))
                            <div>
                                <span class="text-sm text-gray-600">Teléfono:</span>
                                <span class="ml-2 font-medium">
                                    {{ $cuentaDetalles->cliente_telefono ?? $cuentaDetalles->proveedor_telefono ?? 'N/A' }}
                                </span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-blue-50 p-4 rounded-lg text-center">
                        <p class="text-sm text-blue-600 font-medium">Monto Original</p>
                        <p class="text-2xl font-bold text-blue-800">${{ number_format($cuentaDetalles->montoOriginal ?? 0, 2) }}</p>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg text-center">
                        <p class="text-sm text-green-600 font-medium">Monto Pagado</p>
                        <p class="text-2xl font-bold text-green-800">${{ number_format($cuentaDetalles->montoPagado ?? 0, 2) }}</p>
                        <p class="text-xs text-green-600">{{ $this->calcularPorcentajePago($cuentaDetalles->montoOriginal ?? 0, $cuentaDetalles->montoPagado ?? 0) }}%</p>
                    </div>
                    <div class="bg-orange-50 p-4 rounded-lg text-center">
                        <p class="text-sm text-orange-600 font-medium">Saldo Pendiente</p>
                        <p class="text-2xl font-bold text-orange-800">${{ number_format($cuentaDetalles->montoSaldo ?? 0, 2) }}</p>
                    </div>
                </div>
                
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="font-semibold text-gray-700 mb-2">Fechas y Vencimiento</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <span class="text-sm text-gray-600">Fecha de Vencimiento:</span>
                            <span class="ml-2 font-medium">{{ isset($cuentaDetalles->fecVencimiento) ? date('d/m/Y', strtotime($cuentaDetalles->fecVencimiento)) : 'N/A' }}</span>
                        </div>
                        <div>
                            @if(isset($cuentaDetalles->fecVencimiento))
                            @php $diasVencimiento = $this->obtenerDiasVencimiento($cuentaDetalles->fecVencimiento); @endphp
                            <span class="text-sm text-gray-600">Estado del Vencimiento:</span>
                            <span class="ml-2 text-sm {{ $diasVencimiento['tipo'] === 'vencido' ? 'text-red-600 font-semibold' : ($diasVencimiento['tipo'] === 'proximo' ? 'text-orange-600 font-semibold' : 'text-green-600') }}">
                                {{ $diasVencimiento['texto'] }}
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Solo mostrar si los campos existen -->
                @if((isset($cuentaDetalles->desCuePen) && $cuentaDetalles->desCuePen) || (isset($cuentaDetalles->obsCuePen) && $cuentaDetalles->obsCuePen))
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="font-semibold text-gray-700 mb-2">Descripción y Observaciones</h4>
                    @if(isset($cuentaDetalles->desCuePen) && $cuentaDetalles->desCuePen)
                    <div class="mb-2">
                        <span class="text-sm text-gray-600">Descripción:</span>
                        <p class="mt-1 text-gray-800">{{ $cuentaDetalles->desCuePen }}</p>
                    </div>
                    @endif
                    @if(isset($cuentaDetalles->obsCuePen) && $cuentaDetalles->obsCuePen)
                    <div>
                        <span class="text-sm text-gray-600">Observaciones:</span>
                        <p class="mt-1 text-gray-800">{{ $cuentaDetalles->obsCuePen }}</p>
                    </div>
                    @endif
                </div>
                @endif
                
                @if(isset($cuentaDetalles->factura_numero) || isset($cuentaDetalles->compra_descripcion))
                <div class="bg-yellow-50 p-4 rounded-lg">
                    <h4 class="font-semibold text-gray-700 mb-2">Documentos Relacionados</h4>
                    @if(isset($cuentaDetalles->factura_numero))
                    <div>
                        <span class="text-sm text-gray-600">Factura:</span>
                        <span class="ml-2 font-medium">#{{ $cuentaDetalles->factura_numero }}</span>
                    </div>
                    @endif
                    @if(isset($cuentaDetalles->compra_descripcion))
                    <div>
                        <span class="text-sm text-gray-600">Compra/Gasto:</span>
                        <span class="ml-2 font-medium">{{ $cuentaDetalles->compra_descripcion }}</span>
                    </div>
                    @endif
                </div>
                @endif
            </div>
            
            <div class="flex justify-end space-x-3 pt-4 border-t">
                <!-- Botón de descarga PDF con verificación -->
                @if(isset($cuentaDetalles->idCuePen))
                <button wire:click="descargarPDF({{ $cuentaDetalles->idCuePen }})" 
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-200">
                    <i class="fas fa-file-pdf mr-2"></i> Descargar PDF
                </button>
                @endif
                
                @if(($cuentaDetalles->estCuePen ?? '') !== 'pagado' && isset($cuentaDetalles->idCuePen))
                <button wire:click="marcarComoPagado({{ $cuentaDetalles->idCuePen }})" 
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200">
                    <i class="fas fa-check mr-2"></i> Marcar como Pagado
                </button>
                <button wire:click="editarCuenta({{ $cuentaDetalles->idCuePen }})" 
                        class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition duration-200">
                    <i class="fas fa-edit mr-2"></i> Editar
                </button>
                @endif
                <button wire:click="cerrarModalDetalles" 
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition duration-200">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>
@endif

        <!-- Modal Nueva Cuenta Pendiente -->
        @if($modalEdicionAbierto)
<div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-lg bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">
                    Editar Cuenta Pendiente #{{ $editandoId }}
                </h3>
                <button wire:click="cerrarModalEdicion" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form wire:submit="actualizarCuenta" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Cuenta *</label>
                        <select wire:model="tipoCuenta" 
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                            <option value="por_pagar">💰 Por Pagar</option>
                            <option value="por_cobrar">💸 Por Cobrar</option>
                        </select>
                        @error('tipoCuenta') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    
                    @if($tipoCuenta === 'por_cobrar')
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Cliente *</label>
                        <input type="text" wire:model="cliente" list="clientes-list-edit"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-500" 
                               placeholder="Nombre del cliente">
                        <datalist id="clientes-list-edit">
                            @foreach($clientes as $cli)
                                <option value="{{ $cli->nombre }}">{{ $cli->documento }}</option>
                            @endforeach
                        </datalist>
                        @error('cliente') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    @else
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Proveedor *</label>
                        <input type="text" wire:model="proveedor" list="proveedores-list-edit"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-yellow-500" 
                               placeholder="Nombre del proveedor">
                        <datalist id="proveedores-list-edit">
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
                            <option value="pagado">✅ Pagado</option>
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
                    <button type="button" wire:click="cerrarModalEdicion" 
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition duration-200">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition duration-200">
                        <i class="fas fa-save mr-2"></i> Actualizar Cuenta
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

        <!-- Agregar estos loading overlays antes del cierre del div principal -->

        <!-- Loading para actualizar cuenta -->
        <div wire:loading.flex wire:target="actualizarCuenta"
            class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50 items-center justify-center">
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <div class="flex items-center space-x-3">
                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-purple-600"></div>
                    <span class="text-gray-700">Actualizando cuenta...</span>
                </div>
            </div>
        </div>

        <!-- Loading para cargar detalles -->
        <div wire:loading.flex wire:target="verDetalles"
            class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50 items-center justify-center">
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <div class="flex items-center space-x-3">
                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                    <span class="text-gray-700">Cargando detalles...</span>
                </div>
            </div>
        </div>

        <!-- Loading para marcar como pagado -->
        <div wire:loading.flex wire:target="marcarComoPagado"
            class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50 items-center justify-center">
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <div class="flex items-center space-x-3">
                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-green-600"></div>
                    <span class="text-gray-700">Marcando como pagado...</span>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/3.13.3/cdn.min.js" defer></script>
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

// Declarar variable global
let formModified = false;

// Event listeners optimizados
document.addEventListener('livewire:init', () => {
    Livewire.on('cuenta-creada', () => {
        console.log('Cuenta pendiente creada exitosamente');
        formModified = false;
    });

    Livewire.on('modal-detalles-abierto', () => {
        document.body.style.overflow = 'hidden';
    });

    Livewire.on('modal-detalles-cerrado', () => {
        document.body.style.overflow = 'auto';
    });

    Livewire.on('cuenta-actualizada', () => {
        console.log('Cuenta actualizada exitosamente');
        formModified = false;
    });

    Livewire.on('cuenta-pagada', () => {
        console.log('Cuenta marcada como pagada');
    });
});

// Prevenir envío accidental de formularios y cerrar modales con ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
        e.preventDefault();
    }

    if (e.key === 'Escape') {
        const closeButtons = document.querySelectorAll('[wire\\:click*="cerrarModal"]');
        if (closeButtons.length > 0) {
            closeButtons[0].click();
        }
    }
});

// Auto-focus en campos de entrada cuando se abren modales
document.addEventListener('livewire:updated', () => {
    // Focus en primer input visible de modales abiertos
    const modals = document.querySelectorAll('.fixed.inset-0');
    modals.forEach(modal => {
        if (modal.style.display !== 'none') {
            const firstInput = modal.querySelector('input:not([type="hidden"]), select, textarea');
            if (firstInput) {
                setTimeout(() => firstInput.focus(), 100);
            }
        }
    });

    // Reset flag cuando se guarda
    if (window.location.search.includes('success')) {
        formModified = false;
    }
});

// Detectar cambios en formularios
document.addEventListener('input', function(e) {
    if (e.target.closest('.fixed.inset-0')) {
        formModified = true;
    }
});

// Confirmar antes de cerrar modal con cambios sin guardar
document.addEventListener('click', function(e) {
    if (e.target.matches('[wire\\:click*="cerrarModal"]') && formModified) {
        if (!confirm('¿Estás seguro de cerrar? Los cambios no guardados se perderán.')) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
        formModified = false;
    }
});

// Event listener para reset de formularios cuando se abren modales
document.addEventListener('livewire:updated', () => {
    // Reset formModified cuando se abre un nuevo modal
    const modals = document.querySelectorAll('.fixed.inset-0');
    const openModals = Array.from(modals).filter(modal => modal.style.display !== 'none');
    if (openModals.length > 0) {
        setTimeout(() => {
            formModified = false;
        }, 100);
    }
});
</script>
@endpush