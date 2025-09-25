<?php
// ===============================================
// FAMASY SISTEMA DE FACTURACIÓN - COMPONENTE VOLT
// PARTE 1: IMPORTS, LAYOUT Y PROPIEDADES PRINCIPALES
// ===============================================

use App\Models\Factura;
use App\Models\MovimientoContable;
use App\Models\CompraGasto;
use App\Models\Cliente;
use App\Models\Animal;
use App\Models\ProduccionAnimal;
use App\Models\Inventario;
use App\Models\CuentaPendiente;
use App\Models\Proveedor;
use App\Models\CategoriaContable;
use App\Models\ConfigContable;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Kwn\NumberToWords\NumberToWords;

new #[Layout('layouts.auth')] class extends Component {
    use WithPagination, WithFileUploads;

    // ===============================================
    // PROPIEDADES DE CONTROL DEL SISTEMA
    // ===============================================
    
    public $modalAbierto = false;
    public $modalVerAbierto = false;
    public $vistaActiva = 'ventas';
    public $tipoFactura = null; // 'granja' o 'proveedor'
    public $guardandoFactura = false;
    public $cargandoCliente = false;
    public $cargandoProveedor = false;
    public $cargandoAnimales = false;

    // ===============================================
    // PROPIEDADES DE FILTROS Y PAGINACIÓN
    // ===============================================
    
    public $estado = '';
    public $cliente_buscar = '';
    public $fecha_desde = '';
    public $fecha_hasta = '';
    public $per_page = 15;

    // ===============================================
    // PROPIEDADES DE FACTURA PRINCIPAL
    // ===============================================
    
    public $numero = '';
    public $fecFac = '';
    public $subtotalFac = 0;
    public $ivaFac = 19;
    public $descuentoFac = 0;
    public $totFac = 0;
    public $metPagFac = '';
    public $obsFac = '';
    public $nomCliFac = '';
    public $tipDocCliFac = '';
    public $docCliFac = '';

    // ===============================================
    // PROPIEDADES DE CLIENTE
    // ===============================================
    
    public $tipoCliente = 'existente'; // 'existente', 'nuevo', 'ocasional'
    public $idCliFac = '';
    public $clienteSeleccionado = null;
    public $clientes = [];
    public $limiteCredito = 0;
    public $diasCredito = 30;

    // Cliente nuevo
    public $nomCliNuevo = '';
    public $tipDocCliNuevo = 'CC';
    public $docCliNuevo = '';
    public $telCliNuevo = '';
    public $emailCliNuevo = '';
    public $dirCliNuevo = '';
    public $tipCliNuevo = 'particular';

    // Cliente ocasional
    public $nomComprador = '';
    public $docComprador = '';

    // ===============================================
    // PROPIEDADES DE PRODUCTOS DE GRANJA
    // ===============================================
    
    public $tipoProductoGranja = 'productos'; // 'productos' o 'animales_pie'
    public $productosGranjaDisponibles = [];
    public $productoSeleccionado = '';
    public $cantidadProducto = 1;
    public $precioUnitario = 0;
    public $productosSeleccionados = [];

    // Específicos para huevos
    public $categoriaHuevosSeleccionada = '';
    public $unidadVentaHuevos = 'panal';

    // Específicos para lana
    public $unidadLanaSeleccionada = 'kilos';

    // Animales en pie
    public $animalesEnPieDisponibles = [];
    public $animalEnPieSeleccionado = '';

    // ===============================================
    // PROPIEDADES DE PROVEEDORES
    // ===============================================
    
    public $idProveFac = '';
    public $proveedorSeleccionado = null;
    public $proveedoresDisponibles = [];
    public $servicioSuministrado = '';
    public $categoriaGasto = '';
    public $archivoFacturaProveedor = null;
    public $nombreArchivoOriginal = '';
    public $numeroComprobanteProveedor = '';

    // ===============================================
    // PROPIEDADES DE CONTROL AVANZADO
    // ===============================================
    
    public $verificarStock = true;
    public $alertasStock = [];
    public $aplicarDescuento = false;
    public $porcentajeDescuento = 0;
    public $motivoDescuento = '';
    public $requiereAprobacion = false;
    public $estadoAprobacion = 'pendiente';
    public $mostrarModal = false;

    // ===============================================
    // PROPIEDADES DE CONFIGURACIÓN Y SISTEMA
    // ===============================================
    
    public $configuraciones = [];
    public $permisos = [];
    public $auditoria = true;
    public $notificaciones = true;
    public $estadisticas = [];

    // ===============================================
    // PROPIEDADES DE VISUALIZACIÓN
    // ===============================================
    
    public $facturaVer = null;
    public $detallesFactura = [];
    public $facturasProveedores = [];

    // ===============================================
    // ARRAYS DE CONFIGURACIÓN DEL SISTEMA
    // ===============================================
    
    public $tiposProductosGranja = [
        'leche_bovina' => 'Leche Bovina',
        'bovino_pie' => 'Bovino en Pie', 
        'leche_ovina' => 'Leche Ovina',
        'lana' => 'Lana',
        'ovino_pie' => 'Ovino en Pie',
        'huevos' => 'Huevos',
        'gallina_pie' => 'Gallina en Pie',
        'pollo_pie' => 'Pollo en Pie'
    ];

    public $categoriasHuevos = [
        'A' => 'Categoría A',
        'AA' => 'Categoría AA',
        'AAA' => 'Categoría AAA', 
        'JUMBO' => 'Categoría JUMBO'
    ];

    public $unidadesVentaHuevos = [
        'panal' => 'Panal (30 unidades)',
        'cubeta' => 'Cubeta (10 unidades)',
        'unidad' => 'Unidad individual'
    ];

    public $unidadesLana = [
        'kilos' => 'Kilos',
        'libras' => 'Libras',
        'gramos' => 'Gramos'
    ];

    public $tiposServiciosProveedores = [
        'medicamentos_veterinarios' => 'Medicamentos Veterinarios',
        'concentrado_alimento' => 'Concentrado y Alimento Animal',
        'servicios_veterinarios' => 'Servicios Veterinarios',
        'mantenimiento_equipos' => 'Mantenimiento de Equipos',
        'insumos_agricolas' => 'Insumos Agrícolas',
        'servicios_construccion' => 'Servicios de Construcción',
        'transporte_logistica' => 'Transporte y Logística',
        'servicios_profesionales' => 'Servicios Profesionales',
        'combustible_energia' => 'Combustible y Energía',
        'otros_servicios' => 'Otros Servicios'
    ];

    public $metodosPago = [
        'efectivo' => 'Efectivo',
        'transferencia' => 'Transferencia Bancaria',
        'tarjeta_credito' => 'Tarjeta de Crédito',
        'tarjeta_debito' => 'Tarjeta de Débito',
        'cheque' => 'Cheque',
        'credito' => 'Crédito (30 días)',
        'credito_extendido' => 'Crédito Extendido (60 días)'
    ];

    public $tiposProduccionPecuaria = [
        'leche' => 'Leche',
        'huevos' => 'Huevos',
        'carne' => 'Carne',
        'lana' => 'Lana'
    ];

    public $categoriasProductos = [
        'bovinos' => 'Productos Bovinos',
        'ovinos' => 'Productos Ovinos',
        'aves' => 'Productos Avícolas',
        'cunículos' => 'Productos Cunícolas',
        'porcinos' => 'Productos Porcinos'
    ];

    // ===============================================
    // MÉTODOS DE INICIALIZACIÓN DEL SISTEMA
    // ===============================================

    /**
     * Inicialización principal del componente
     */
    public function mount()
{
    // ✅ VERIFICAR QUE ESTAS LÍNEAS SE EJECUTEN
    $this->fecha_desde = '';
    $this->fecha_hasta = '';
    
    // ✅ AGREGAR LOG PARA VERIFICAR
    \Log::info('Mount ejecutado', [
        'fecha_desde' => $this->fecha_desde,
        'fecha_hasta' => $this->fecha_hasta,
        'vista_activa' => $this->vistaActiva
    ]);
    
    $this->cargarConfiguraciones();
    $this->cargarPermisos();
    $this->cargarDatosIniciales();
    $this->calcularEstadisticas();
}

public function getFacturasProveedoresProperty()
{
    try {
        // ✅ QUERY SIMPLIFICADA
        $query = DB::table('comprasgastos')
            ->select([
                'idComGas',
                'tipComGas',
                'catComGas',
                'desComGas',
                'monComGas',
                'fecComGas',
                'metPagComGas',
                'provComGas',
                'docComGas',
                'obsComGas',
                'created_at',
                'updated_at'
            ]);

        // ✅ DEBUG
        $totalSinFiltros = $query->count();
        \Log::info('Total compras/gastos sin filtros: ' . $totalSinFiltros);

        // Aplicar filtros de fecha
        if ($this->fecha_desde) {
            $query->where('fecComGas', '>=', $this->fecha_desde);
        }

        if ($this->fecha_hasta) {
            $query->where('fecComGas', '<=', $this->fecha_hasta);
        }

        $resultado = $query->orderBy('fecComGas', 'desc')
            ->orderBy('idComGas', 'desc')
            ->paginate($this->per_page, ['*'], 'page', request()->get('page', 1));

        \Log::info('Resultado proveedores', [
            'total' => $resultado->total(),
            'items' => $resultado->count()
        ]);

        return $resultado;

    } catch (\Exception $e) {
        \Log::error('Error en facturas proveedores: ' . $e->getMessage());
        return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->per_page);
    }
}

    // ===============================================
    // MÉTODOS DE CARGA DE CONFIGURACIONES
    // ===============================================

    /**
     * Cargar configuraciones del sistema desde base de datos
     */
    private function cargarConfiguraciones()
    {
        try {
            $configs = DB::table('config_contable')
                ->pluck('valorConfig', 'conceptoConfig');

            $this->configuraciones = [
                'iva_predeterminado' => floatval($configs['iva_predeterminado'] ?? 19),
                'descuento_maximo' => floatval($configs['descuento_maximo'] ?? 20),
                'limite_aprobacion' => floatval($configs['limite_aprobacion'] ?? 1000000),
                'dias_credito_default' => intval($configs['dias_credito_default'] ?? 30),
                'auto_generar_pdf' => boolval($configs['auto_generar_pdf'] ?? true),
                'enviar_email_cliente' => boolval($configs['enviar_email_cliente'] ?? false)
            ];

            $this->ivaFac = $this->configuraciones['iva_predeterminado'];
            $this->diasCredito = $this->configuraciones['dias_credito_default'];
            
        } catch (\Exception $e) {
            Log::error('Error cargando configuraciones: ' . $e->getMessage());
            
            // Configuraciones por defecto
            $this->configuraciones = [
                'iva_predeterminado' => 19,
                'descuento_maximo' => 20,
                'limite_aprobacion' => 1000000,
                'dias_credito_default' => 30,
                'auto_generar_pdf' => true,
                'enviar_email_cliente' => false
            ];
            
            $this->ivaFac = 19;
            $this->diasCredito = 30;
        }
    }

    /**
     * Cargar permisos del usuario según su rol
     */
    private function cargarPermisos()
    {
        $usuario = Auth::user();
        $rol = $usuario->rol ?? null;

        $this->permisos = [
            'crear_facturas' => true,
            'eliminar_facturas' => $rol && in_array($rol->nomRol, ['Administrador', 'Supervisor']),
            'aprobar_facturas' => $rol && $rol->nomRol === 'Administrador',
            'descuentos_especiales' => $rol && in_array($rol->nomRol, ['Administrador', 'Supervisor']),
            'credito_extendido' => $rol && $rol->nomRol === 'Administrador',
            'ver_todas_facturas' => $rol && in_array($rol->nomRol, ['Administrador', 'Supervisor']),
            'exportar_datos' => true
        ];
    }

    // ===============================================
    // MÉTODOS DE CARGA DE DATOS INICIALES
    // ===============================================

    /**
     * Cargar datos iniciales del sistema
     */
    private function cargarDatosIniciales()
    {
        try {
            // Clientes activos
            $this->clientes = Cliente::where('estCli', 'activo')
                ->orderBy('nomCli')
                ->get();

            Log::info('Datos iniciales cargados correctamente');
            
        } catch (\Exception $e) {
            Log::error('Error cargando datos iniciales: ' . $e->getMessage());
            session()->flash('error', 'Error al cargar datos del sistema');
        }
    }

    /**
     * Cargar productos de granja disponibles
     */
    private function cargarProductosGranjaDisponibles()
    {
        try {
            $this->productosGranjaDisponibles = DB::table('insumos as i')
                ->leftJoin('inventario as inv', 'i.idIns', '=', 'inv.idIns')
                ->select([
                    'i.idIns',
                    'i.nomIns',
                    'i.tipIns',
                    'i.uniIns',
                    'i.categoria',
                    DB::raw('SUM(CASE WHEN inv.tipMovInv = "entrada" THEN inv.cantMovInv ELSE -inv.cantMovInv END) as stockActual'),
                    DB::raw('CONCAT(i.nomIns, " - ", COALESCE(i.categoria, "General")) as nombre_completo'),
                    'inv.loteInv as loteProduccion',
                    DB::raw('CASE 
                        WHEN i.tipIns LIKE "%huevo%" THEN 800
                        WHEN i.tipIns LIKE "%leche%" THEN 3500
                        WHEN i.tipIns LIKE "%lana%" THEN 15000
                        WHEN i.tipIns LIKE "%carne%" THEN 25000
                        ELSE 2000
                    END as precio_sugerido')
                ])
                ->where(function($query) {
                    $query->where('i.tipIns', 'LIKE', '%huevo%')
                          ->orWhere('i.tipIns', 'LIKE', '%leche%')
                          ->orWhere('i.tipIns', 'LIKE', '%lana%')
                          ->orWhere('i.tipIns', 'LIKE', '%carne%')
                          ->orWhereIn('i.tipIns', ['huevos', 'leche_bovina', 'leche_ovina', 'lana', 'carne']);
                })
                ->where('i.estIns', 'disponible')
                ->whereNotNull('inv.idIns')
                ->groupBy('i.idIns', 'i.nomIns', 'i.tipIns', 'i.uniIns', 'i.categoria', 'inv.loteInv')
                ->having('stockActual', '>', 0)
                ->orderBy('i.nomIns')
                ->get();

            Log::info('Productos de granja cargados: ' . $this->productosGranjaDisponibles->count());

            // Si no encuentra productos, crear algunos básicos
            if ($this->productosGranjaDisponibles->isEmpty()) {
                $this->crearProductosGranjaBasicos();
            }

        } catch (\Exception $e) {
            Log::error('Error cargando productos de granja: ' . $e->getMessage());
            $this->productosGranjaDisponibles = collect();
            session()->flash('warning', 'Error al cargar productos: ' . $e->getMessage());
        }
    }

    /**
     * Cargar animales en pie disponibles para venta
     */
    private function cargarAnimalesEnPie()
    {
        try {
            $this->animalesEnPieDisponibles = DB::table('animales as a')
                ->select([
                    'a.idAni',
                    'a.nomAni',
                    'a.espAni',
                    'a.razAni',
                    'a.sexAni',
                    'a.pesAni',
                    'a.fecNacAni',
                    DB::raw('CONCAT(a.nomAni, " (", a.espAni, " - ", a.razAni, " - ", a.pesAni, "kg)") as nombre_completo'),
                    DB::raw('CASE 
                        WHEN a.espAni = "Bovino" THEN a.pesAni * 8000
                        WHEN a.espAni = "Ovino" THEN a.pesAni * 6000
                        WHEN a.espAni = "Pollo" THEN a.pesAni * 12000
                        WHEN a.espAni = "Gallina" THEN a.pesAni * 15000
                        ELSE 5000
                    END as precio_sugerido')
                ])
                ->whereIn('a.espAni', ['Bovino', 'Ovino', 'Pollo', 'Gallina'])
                ->where('a.estAni', 'vivo')
                ->orderBy('a.espAni')
                ->orderBy('a.nomAni')
                ->get();

            Log::info('Animales en pie cargados: ' . $this->animalesEnPieDisponibles->count());

        } catch (\Exception $e) {
            Log::error('Error cargando animales en pie: ' . $e->getMessage());
            $this->animalesEnPieDisponibles = collect();
        }
    }

    /**
     * Cargar proveedores disponibles
     */
    private function cargarProveedoresDisponibles()
    {
        try {
            $this->proveedoresDisponibles = DB::table('proveedores')
                ->select('idProve', 'nomProve', 'tipSumProve', 'telProve', 'emailProve')
                ->orderBy('nomProve', 'asc')
                ->get();

            Log::info('Proveedores cargados: ' . $this->proveedoresDisponibles->count());

        } catch (\Exception $e) {
            Log::error('Error cargando proveedores: ' . $e->getMessage());
            $this->proveedoresDisponibles = collect();
            session()->flash('warning', 'No se pudieron cargar los proveedores.');
        }
    }

    /**
     * Cargar facturas de proveedores para la vista de compras
     */
    private function cargarFacturasProveedores()
{
    try {
        $query = DB::table('comprasgastos as cg')
            ->leftJoin('proveedores as p', 'cg.provComGas', '=', 'p.nomProve')
            ->select([
                'cg.*',
                'p.tipSumProve'
            ])
            ->where('cg.tipComGas', 'compra');

        // Aplicar filtros también a facturas de proveedores
        if ($this->fecha_desde) {
            $query->where('cg.fecComGas', '>=', $this->fecha_desde);
        }

        if ($this->fecha_hasta) {
            $query->where('cg.fecComGas', '<=', $this->fecha_hasta);
        }

        $this->facturasProveedores = $query->orderBy('cg.created_at', 'desc')
            ->paginate($this->per_page);

        Log::info('Facturas de proveedores cargadas: ' . $this->facturasProveedores->count());

    } catch (\Exception $e) {
        Log::error('Error cargando facturas de proveedores: ' . $e->getMessage());
        $this->facturasProveedores = collect()->paginate(1);
    }
}

    // ===============================================
    // MÉTODOS DE ESTADÍSTICAS
    // ===============================================

    /**
     * Calcular estadísticas principales del sistema
     */
    public function calcularEstadisticas()
    {
        try {
            $usuario = Auth::user();
            $esAdmin = $this->permisos['ver_todas_facturas'];

            // Base query según permisos
            $baseQuery = $esAdmin ? Factura::query() : Factura::where('idUsuFac', $usuario->id);

            $totalFacturado = $baseQuery->sum('totFac') ?? 0;
            $totalCompras = CompraGasto::where('tipComGas', 'compra')->sum('monComGas') ?? 0;

            $facturasPorEstado = $baseQuery->selectRaw('estFac, COUNT(*) as cantidad')
                ->groupBy('estFac')
                ->pluck('cantidad', 'estFac')
                ->toArray();

            $porCobrar = CuentaPendiente::where('tipCuePen', 'por_cobrar')
                ->where('estCuePen', 'pendiente')
                ->sum('montoSaldo') ?? 0;

            $porPagar = CuentaPendiente::where('tipCuePen', 'por_pagar')
                ->where('estCuePen', 'pendiente')
                ->sum('montoSaldo') ?? 0;

            $clientesActivos = Cliente::where('estCli', 'activo')->count();
            $ventasHoy = $baseQuery->whereDate('fecFac', today())->sum('totFac') ?? 0;
            $ventasMes = $baseQuery->whereMonth('fecFac', now()->month)
                ->whereYear('fecFac', now()->year)
                ->sum('totFac') ?? 0;

            // Factura más alta del día
            $facturaMaxHoy = $baseQuery->whereDate('fecFac', today())
                ->orderBy('totFac', 'desc')
                ->first();

            // Promedio de venta
            $promedioVenta = $baseQuery->whereMonth('fecFac', now()->month)
                ->avg('totFac') ?? 0;

            $this->estadisticas = [
                'total_facturado' => $totalFacturado,
                'total_compras' => $totalCompras,
                'utilidad_bruta' => $totalFacturado - $totalCompras,
                'emitidas' => $facturasPorEstado['emitida'] ?? 0,
                'pagadas' => $facturasPorEstado['pagada'] ?? 0,
                'pendientes' => $facturasPorEstado['pendiente'] ?? 0,
                'anuladas' => $facturasPorEstado['anulada'] ?? 0,
                'por_cobrar' => $porCobrar,
                'por_pagar' => $porPagar,
                'clientes_activos' => $clientesActivos,
                'ventas_hoy' => $ventasHoy,
                'ventas_mes' => $ventasMes,
                'factura_max_hoy' => $facturaMaxHoy?->totFac ?? 0,
                'promedio_venta' => $promedioVenta,
                'flujo_neto' => $totalFacturado - $totalCompras,
                'facturas_totales' => array_sum($facturasPorEstado),
                'facturas_venta' => array_sum($facturasPorEstado),
                'facturas_compra' => CompraGasto::count()
            ];

        } catch (\Exception $e) {
            Log::error('Error calculando estadísticas: ' . $e->getMessage());
            
            // Estadísticas por defecto en caso de error
            $this->estadisticas = array_fill_keys([
                'total_facturado', 'total_compras', 'utilidad_bruta', 'emitidas',
                'pagadas', 'pendientes', 'anuladas', 'por_cobrar', 'por_pagar',
                'clientes_activos', 'ventas_hoy', 'ventas_mes', 'factura_max_hoy',
                'promedio_venta', 'flujo_neto', 'facturas_totales', 'facturas_venta',
                'facturas_compra'
            ], 0);
        }
    }

    // ===============================================
    // MÉTODOS DE UTILIDAD PARA INICIALIZACIÓN
    // ===============================================

    /**
     * Crear productos de granja básicos si no existen
     */
    private function crearProductosGranjaBasicos()
    {
        try {
            Log::info('Creando productos de granja básicos...');
            
            // Aquí podrías crear productos básicos si la base de datos está vacía
            // Por ahora solo logueamos que no hay productos
            
            session()->flash('info', 'No se encontraron productos de granja en el inventario.');
            
        } catch (\Exception $e) {
            Log::error('Error creando productos básicos: ' . $e->getMessage());
        }
    }

    /**
     * Generar número de factura automático
     */
    private function generarNumeroFactura()
    {
        try {
            $prefijo = $this->tipoFactura === 'proveedor' ? 'COMP-' : 'FAC-';
            $fecha = date('Ymd');

            $tabla = $this->tipoFactura === 'proveedor' ? 'compragastos' : 'facturas';
            $campo = $this->tipoFactura === 'proveedor' ? 'idComGas' : 'idFac';

            $ultimo = DB::table($tabla)->orderBy($campo, 'desc')->first();
            $numero = $ultimo ? (($this->tipoFactura === 'proveedor' ? $ultimo->idComGas : $ultimo->idFac) + 1) : 1;

            return $prefijo . $fecha . '-' . str_pad($numero, 4, '0', STR_PAD_LEFT);
            
        } catch (\Exception $e) {
            Log::error('Error generando número de factura: ' . $e->getMessage());
            $prefijo = $this->tipoFactura === 'proveedor' ? 'COMP-' : 'FAC-';
            return $prefijo . date('Ymd') . '-0001';
        }
    }

    // ===============================================
    // MÉTODOS DE CAMBIO DE VISTA
    // ===============================================

    /**
     * Cambiar entre vista de ventas y compras
     */
    public function cambiarVista($vista)
{
    $this->vistaActiva = $vista;
    
    if ($vista === 'compras') {
        $this->cargarFacturasProveedores();
    }
    
    $this->calcularEstadisticas();
    $this->resetPage(); // Agregar esta línea
    
    Log::info('Vista cambiada a: ' . $vista);
}

    // ===============================================
    // MÉTODOS DE GESTIÓN DE PRODUCTOS DE GRANJA
    // ===============================================

    /**
     * Seleccionar producto de granja desde el dropdown
     */
    public function seleccionarProductoGranja($productoId)
    {
        if (empty($productoId)) {
            $this->productoSeleccionado = '';
            $this->precioUnitario = 0;
            $this->categoriaHuevosSeleccionada = '';
            return;
        }

        $this->productoSeleccionado = $productoId;
        
        $producto = $this->productosGranjaDisponibles->firstWhere('idIns', $productoId);
        if ($producto) {
            $this->precioUnitario = $producto->precio_sugerido;
            
            // Si es huevo, preseleccionar la categoría del producto
            if ($producto->tipIns === 'huevos') {
                $this->categoriaHuevosSeleccionada = $producto->categoria ?: '';
            }
        }

        Log::info('Producto de granja seleccionado', [
            'producto_id' => $productoId,
            'precio_sugerido' => $this->precioUnitario
        ]);
    }

    /**
     * Agregar producto de granja al carrito
     */
    public function agregarProductoGranja()
    {
        try {
            $this->validate([
                'productoSeleccionado' => 'required',
                'cantidadProducto' => 'required|numeric|min:0.01',
                'precioUnitario' => 'required|numeric|min:0'
            ]);

            $producto = $this->productosGranjaDisponibles->firstWhere('idIns', $this->productoSeleccionado);
            
            if (!$producto) {
                session()->flash('error', 'Producto no encontrado');
                return;
            }

            // Validar stock disponible
            if ($this->verificarStock && $this->cantidadProducto > $producto->stockActual) {
                session()->flash('error', 'Stock insuficiente. Disponible: ' . $producto->stockActual . ' ' . $producto->uniIns);
                return;
            }

            // Determinar unidad y descripción según tipo
            $unidad = $producto->uniIns;
            $descripcion = $producto->nombre_completo;
            
            // Configuraciones específicas por tipo de producto
            if ($producto->tipIns === 'huevos' && $this->categoriaHuevosSeleccionada) {
                $descripcion .= ' - Categoría ' . $this->categoriaHuevosSeleccionada;
                if ($this->unidadVentaHuevos === 'panal') {
                    $unidad = 'Panal (30 unidades)';
                } elseif ($this->unidadVentaHuevos === 'cubeta') {
                    $unidad = 'Cubeta (30 unidades)';
                }
            } elseif ($producto->tipIns === 'lana') {
                $unidad = ucfirst($this->unidadLanaSeleccionada);
            } elseif (in_array($producto->tipIns, ['leche_bovina', 'leche_ovina'])) {
                $unidad = 'Litros';
            }

            $subtotal = $this->cantidadProducto * $this->precioUnitario;

            $this->productosSeleccionados[] = [
                'id' => $producto->idIns,
                'tipo' => 'granja',
                'nombre' => $producto->nomIns,
                'descripcion' => $descripcion,
                'cantidad' => $this->cantidadProducto,
                'unidad' => $unidad,
                'precio_unitario' => $this->precioUnitario,
                'subtotal' => $subtotal,
                'categoria' => $this->categoriaHuevosSeleccionada ?? null,
                'tipo_producto' => $producto->tipIns,
                'stock_original' => $producto->stockActual,
                'lote_produccion' => $producto->loteProduccion ?? null
            ];

            $this->recalcularTotales();
            $this->limpiarSeleccionProductoGranja();

            session()->flash('success', 'Producto agregado correctamente');

            Log::info('Producto de granja agregado', [
                'producto' => $producto->nomIns,
                'cantidad' => $this->cantidadProducto,
                'subtotal' => $subtotal
            ]);

        } catch (\Exception $e) {
            Log::error('Error agregando producto: ' . $e->getMessage());
            session()->flash('error', 'Error al agregar producto: ' . $e->getMessage());
        }
    }

    /**
     * Limpiar selección de productos de granja
     */
    private function limpiarSeleccionProductoGranja()
    {
        $this->reset([
            'productoSeleccionado', 'cantidadProducto', 'precioUnitario',
            'categoriaHuevosSeleccionada', 'unidadVentaHuevos'
        ]);
        $this->cantidadProducto = 1;
        $this->unidadVentaHuevos = 'panal';
        $this->unidadLanaSeleccionada = 'kilos';
    }

    // ===============================================
    // MÉTODOS DE GESTIÓN DE ANIMALES EN PIE
    // ===============================================

    /**
     * Seleccionar animal en pie desde el dropdown
     */
    public function seleccionarAnimalEnPie($animalId)
    {
        $this->animalEnPieSeleccionado = $animalId;
        $this->cantidadProducto = 1; // Solo 1 animal
        
        if ($animalId) {
            $animal = $this->animalesEnPieDisponibles->firstWhere('idAni', $animalId);
            if ($animal) {
                $this->precioUnitario = $animal->precio_sugerido;
                
                Log::info('Animal en pie seleccionado', [
                    'animal_id' => $animalId,
                    'nombre' => $animal->nomAni,
                    'precio_sugerido' => $animal->precio_sugerido
                ]);
            }
        }
    }

    /**
     * Agregar animal en pie al carrito
     */
    public function agregarAnimalEnPie()
    {
        try {
            $this->validate([
                'animalEnPieSeleccionado' => 'required',
                'precioUnitario' => 'required|numeric|min:0'
            ]);

            $animal = $this->animalesEnPieDisponibles->firstWhere('idAni', $this->animalEnPieSeleccionado);
            
            if (!$animal) {
                session()->flash('error', 'Animal no encontrado');
                return;
            }

            // Verificar que el animal no esté ya en el carrito
            $yaEnCarrito = collect($this->productosSeleccionados)->contains(function ($item) use ($animal) {
                return $item['tipo'] === 'animal_pie' && $item['id'] === $animal->idAni;
            });

            if ($yaEnCarrito) {
                session()->flash('warning', 'Este animal ya está en el carrito');
                return;
            }

            $subtotal = 1 * $this->precioUnitario; // Solo 1 animal

            $this->productosSeleccionados[] = [
                'id' => $animal->idAni,
                'tipo' => 'animal_pie',
                'nombre' => $animal->nomAni,
                'descripcion' => $animal->espAni . ' - ' . $animal->razAni . ' (' . $animal->pesAni . 'kg)',
                'cantidad' => 1,
                'unidad' => 'Animal',
                'precio_unitario' => $this->precioUnitario,
                'subtotal' => $subtotal,
                'peso' => $animal->pesAni,
                'especie' => $animal->espAni,
                'raza' => $animal->razAni,
                'sexo' => $animal->sexAni
            ];

            $this->recalcularTotales();
            $this->limpiarSeleccionAnimalEnPie();

            session()->flash('success', 'Animal en pie agregado correctamente');

            Log::info('Animal en pie agregado', [
                'animal' => $animal->nomAni,
                'especie' => $animal->espAni,
                'peso' => $animal->pesAni,
                'precio' => $this->precioUnitario
            ]);

        } catch (\Exception $e) {
            Log::error('Error agregando animal: ' . $e->getMessage());
            session()->flash('error', 'Error al agregar animal: ' . $e->getMessage());
        }
    }

    /**
     * Limpiar selección de animal en pie
     */
    private function limpiarSeleccionAnimalEnPie()
    {
        $this->reset(['animalEnPieSeleccionado', 'precioUnitario']);
        $this->cantidadProducto = 1;
    }

    // ===============================================
    // MÉTODOS DE GESTIÓN DEL CARRITO
    // ===============================================

    /**
     * Eliminar producto del carrito por índice
     */
    public function eliminarProducto($index)
    {
        if (isset($this->productosSeleccionados[$index])) {
            $producto = $this->productosSeleccionados[$index];
            
            unset($this->productosSeleccionados[$index]);
            $this->productosSeleccionados = array_values($this->productosSeleccionados);
            
            $this->recalcularTotales();
            
            session()->flash('info', 'Producto eliminado: ' . $producto['nombre']);
            
            Log::info('Producto eliminado del carrito', [
                'producto' => $producto['nombre'],
                'tipo' => $producto['tipo']
            ]);
        }
    }

    /**
     * Limpiar todos los productos seleccionados
     */
    public function limpiarProductosSeleccionados()
    {
        $cantidad = count($this->productosSeleccionados);
        $this->productosSeleccionados = [];
        $this->recalcularTotales();
        
        session()->flash('info', "Se eliminaron {$cantidad} productos del carrito");
        
        Log::info('Carrito limpiado', ['productos_eliminados' => $cantidad]);
    }

    /**
     * Recalcular totales de la factura
     */
    public function recalcularTotales()
    {
        try {
            $this->subtotalFac = collect($this->productosSeleccionados)->sum('subtotal');
            
            // Aplicar descuento si está activo
            $descuentoCalculado = 0;
            if ($this->aplicarDescuento && $this->porcentajeDescuento > 0) {
                $descuentoCalculado = ($this->subtotalFac * $this->porcentajeDescuento) / 100;
                $this->descuentoFac = $descuentoCalculado;
            } else {
                $this->descuentoFac = 0;
            }
            
            $subtotalConDescuento = $this->subtotalFac - $this->descuentoFac;
            
            // Calcular IVA sobre el subtotal con descuento
            $ivaCalculado = ($subtotalConDescuento * $this->ivaFac) / 100;
            
            // Total final
            $this->totFac = $subtotalConDescuento + $ivaCalculado;
            
            // Verificar si requiere aprobación
            $this->verificarRequiereAprobacion();
            
        } catch (\Exception $e) {
            Log::error('Error calculando totales: ' . $e->getMessage());
            $this->subtotalFac = 0;
            $this->totFac = 0;
            $this->descuentoFac = 0;
        }
    }

    // ===============================================
    // MÉTODOS DE VALIDACIÓN Y VERIFICACIÓN
    // ===============================================

    /**
     * Verificar si la factura requiere aprobación por monto
     */
    private function verificarRequiereAprobacion()
    {
        $this->requiereAprobacion = $this->totFac > $this->configuraciones['limite_aprobacion'];

        if ($this->requiereAprobacion && !$this->permisos['aprobar_facturas']) {
            session()->flash('warning', 'Esta factura requerirá aprobación por el monto: $' . number_format($this->totFac, 2));
        }
    }

    /**
     * Validar stock disponible para un producto
     */
    private function validarStockDisponible($productoId, $cantidadSolicitada)
    {
        $producto = $this->productosGranjaDisponibles->firstWhere('idIns', $productoId);
        
        if (!$producto) {
            throw new \Exception('Producto no encontrado');
        }
        
        if ($cantidadSolicitada > $producto->stockActual) {
            throw new \Exception("Stock insuficiente. Disponible: {$producto->stockActual} {$producto->uniIns}");
        }
        
        return true;
    }

    /**
     * Validar que un animal esté disponible para venta
     */
    private function validarAnimalDisponible($animalId)
    {
        $animal = $this->animalesEnPieDisponibles->firstWhere('idAni', $animalId);
        
        if (!$animal) {
            throw new \Exception('Animal no encontrado');
        }
        
        // Verificar que no esté ya vendido hoy
        $ventaHoy = DB::table('facturadetalles as fd')
            ->join('facturas as f', 'fd.idFacDet', '=', 'f.idFac')
            ->where('fd.idAniDet', $animalId)
            ->where('f.fecFac', $this->fecFac)
            ->where('f.estFac', '!=', 'anulada')
            ->exists();
            
        if ($ventaHoy) {
            throw new \Exception('Este animal ya fue vendido el día de hoy');
        }
        
        return true;
    }

    // ===============================================
    // MÉTODOS DE UTILIDAD PARA PRODUCTOS
    // ===============================================

    /**
     * Determinar unidad de venta según el producto
     */
    private function determinarUnidadVenta($producto)
    {
        if ($producto->tipIns === 'huevos') {
            return match($this->unidadVentaHuevos) {
                'panal' => 'Panal(es)',
                'cubeta' => 'Cubeta(s)', 
                default => 'Unidades'
            };
        }
        
        return $producto->uniIns;
    }

    /**
     * Formatear nombre completo del producto para visualización
     */
    private function formatearNombreProductoGranja($producto)
    {
        $nombre = $this->tiposProductosGranja[$producto->tipIns] ?? $producto->nomIns;
        
        // Agregar información del animal origen si está disponible
        if (isset($producto->nomAni) && $producto->nomAni) {
            $nombre .= " de {$producto->nomAni} ({$producto->espAni})";
        }
        
        // Agregar categoría para huevos
        if ($producto->tipIns === 'huevos' && isset($producto->categoria) && $producto->categoria) {
            $nombre .= " - Cat. {$producto->categoria}";
        }
        
        // Agregar información de stock
        $nombre .= " - Stock: {$producto->stockActual}";
        
        // Agregar unidad
        if ($producto->tipIns === 'huevos') {
            $nombre .= " unidades";
        } else {
            $nombre .= " {$producto->uniIns}";
        }
        
        // Agregar lote si existe
        if (isset($producto->loteProduccion) && $producto->loteProduccion) {
            $nombre .= " (Lote: {$producto->loteProduccion})";
        }
        
        return $nombre;
    }

    /**
     * Construir nombre del producto para la venta
     */
    private function construirNombreProductoVenta($producto)
    {
        $nombre = $this->tiposProductosGranja[$producto->tipIns] ?? $producto->nomIns;
        
        // Para huevos, agregar categoría
        if ($producto->tipIns === 'huevos' && $this->categoriaHuevosSeleccionada) {
            $nombre .= " Categoría {$this->categoriaHuevosSeleccionada}";
            
            // Agregar tipo de venta
            if (in_array($this->unidadVentaHuevos, ['panal', 'cubeta'])) {
                $nombre .= " por " . ucfirst($this->unidadVentaHuevos);
            }
        }
        
        // Agregar origen animal si está disponible
        if (isset($producto->nomAni) && $producto->nomAni) {
            $nombre .= " - {$producto->nomAni}";
        }
        
        return $nombre;
    }

    /**
     * Obtener precio sugerido para un producto
     */
    private function obtenerPrecioSugerido($productoId)
    {
        $producto = $this->productosGranjaDisponibles->firstWhere('idIns', $productoId);
        
        if (!$producto) {
            return 0;
        }
        
        // Si tiene precio sugerido, usarlo
        if (isset($producto->precio_sugerido) && $producto->precio_sugerido > 0) {
            return $producto->precio_sugerido;
        }
        
        // Calcular precio basado en ventas anteriores
        $precioPromedio = DB::table('facturadetalles as fd')
            ->join('facturas as f', 'fd.idFacDet', '=', 'f.idFac')
            ->where('fd.idInsDet', $productoId)
            ->where('f.fecFac', '>=', now()->subDays(30))
            ->where('f.estFac', '!=', 'anulada')
            ->avg('fd.precioUnitDet');
            
        return $precioPromedio ?: $this->obtenerPrecioBasePorTipo($producto->tipIns);
    }

    /**
     * Obtener precio base por tipo de producto
     */
    private function obtenerPrecioBasePorTipo($tipoProducto)
    {
        $preciosBase = [
            'huevos' => 800,
            'leche_bovina' => 3500,
            'leche_ovina' => 4000,
            'lana' => 15000,
            'carne' => 25000
        ];
        
        return $preciosBase[$tipoProducto] ?? 2000;
    }

    // ===============================================
    // MÉTODOS DE GESTIÓN DE CLIENTES
    // ===============================================

    /**
     * Seleccionar cliente existente desde dropdown
     */
    public function seleccionarCliente($clienteId)
    {
        if (empty($clienteId)) {
            $this->reset(['clienteSeleccionado', 'idCliFac', 'limiteCredito']);
            return;
        }

        $this->cargandoCliente = true;

        try {
            $this->clienteSeleccionado = Cliente::find($clienteId);
            
            if ($this->clienteSeleccionado) {
                $this->idCliFac = $this->clienteSeleccionado->idCli;
                $this->calcularLimiteCredito();

                Log::info('Cliente seleccionado', [
                    'cliente_id' => $clienteId,
                    'nombre' => $this->clienteSeleccionado->nomCli,
                    'limite_credito' => $this->limiteCredito
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Error seleccionando cliente: ' . $e->getMessage());
            session()->flash('error', 'Error al cargar información del cliente');
            
        } finally {
            $this->cargandoCliente = false;
        }
    }

    /**
     * Crear nuevo cliente desde el formulario
     */
    public function crearClienteNuevo()
    {
        $this->validate([
            'nomCliNuevo' => 'required|string|max:100|min:2',
            'tipDocCliNuevo' => 'required|in:NIT,CC,CE,Pasaporte',
            'docCliNuevo' => 'required|string|max:20|unique:clientes,docCli',
            'telCliNuevo' => 'nullable|string|max:20|regex:/^[0-9+\-\s]+$/',
            'emailCliNuevo' => 'nullable|email|max:100|unique:clientes,emailCli',
            'dirCliNuevo' => 'nullable|string|max:255',
            'tipCliNuevo' => 'required|in:particular,empresa'
        ]);

        try {
            DB::beginTransaction();

            $cliente = Cliente::create([
                'nomCli' => trim($this->nomCliNuevo),
                'tipDocCli' => $this->tipDocCliNuevo,
                'docCli' => $this->docCliNuevo,
                'telCli' => $this->telCliNuevo,
                'emailCli' => $this->emailCliNuevo,
                'dirCli' => $this->dirCliNuevo,
                'tipCli' => $this->tipCliNuevo,
                'estCli' => 'activo',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $this->idCliFac = $cliente->idCli;
            $this->clienteSeleccionado = $cliente;
            
            // Recargar lista de clientes
            $this->cargarDatosIniciales();
            
            // Calcular límite de crédito inicial
            $this->calcularLimiteCredito();

            // Registrar auditoría
            $this->registrarAuditoria(
                'CREATE', 
                'clientes', 
                $cliente->idCli, 
                'Cliente creado desde facturación: ' . $cliente->nomCli
            );

            DB::commit();
            
            session()->flash('success', 'Cliente creado exitosamente: ' . $cliente->nomCli);
            $this->tipoCliente = 'existente';
            
            Log::info('Cliente nuevo creado', [
                'cliente_id' => $cliente->idCli,
                'nombre' => $cliente->nomCli,
                'documento' => $cliente->tipDocCli . ': ' . $cliente->docCli
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creando cliente: ' . $e->getMessage());
            session()->flash('error', 'Error al crear el cliente: ' . $e->getMessage());
        }
    }

    /**
     * Calcular límite de crédito disponible para el cliente
     */
    private function calcularLimiteCredito()
    {
        if (!$this->clienteSeleccionado) {
            $this->limiteCredito = 0;
            return;
        }

        try {
            // Obtener deuda actual pendiente
            $deudaActual = CuentaPendiente::where('idCliCuePen', $this->clienteSeleccionado->idCli)
                ->where('tipCuePen', 'por_cobrar')
                ->where('estCuePen', 'pendiente')
                ->sum('montoSaldo') ?? 0;

            // Obtener historial de pagos en los últimos 12 meses
            $historialPagos = DB::table('cuentaspendientes')
                ->where('idCliCuePen', $this->clienteSeleccionado->idCli)
                ->where('estCuePen', 'pagado')
                ->where('created_at', '>=', now()->subMonths(12))
                ->count();

            // Obtener total de compras históricas
            $totalComprasHistoricas = DB::table('facturas')
                ->where('idCliFac', $this->clienteSeleccionado->idCli)
                ->where('estFac', '!=', 'anulada')
                ->sum('totFac') ?? 0;

            // Calcular límite base según historial
            $limiteBase = match (true) {
                $totalComprasHistoricas >= 5000000 => 3000000, // Cliente VIP: $3M
                $historialPagos >= 10 => 2000000,             // Cliente frecuente: $2M
                $historialPagos >= 5 => 1000000,              // Cliente regular: $1M
                $historialPagos >= 1 => 500000,               // Cliente nuevo: $500K
                default => 200000                              // Cliente sin historial: $200K
            };

            // Ajustar según comportamiento de pago
            $factorComportamiento = $this->calcularFactorComportamientoPago();
            $limiteAjustado = $limiteBase * $factorComportamiento;

            // Límite disponible = Límite ajustado - Deuda actual
            $this->limiteCredito = max(0, $limiteAjustado - $deudaActual);

            Log::info('Límite de crédito calculado', [
                'cliente' => $this->clienteSeleccionado->nomCli,
                'limite_base' => $limiteBase,
                'factor_comportamiento' => $factorComportamiento,
                'deuda_actual' => $deudaActual,
                'limite_disponible' => $this->limiteCredito
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error calculando límite de crédito: ' . $e->getMessage());
            $this->limiteCredito = 0;
        }
    }

    /**
     * Calcular factor de comportamiento de pago (0.5 a 1.5)
     */
    private function calcularFactorComportamientoPago()
    {
        try {
            // Obtener pagos en los últimos 6 meses
            $pagosRecientes = DB::table('cuentaspendientes')
                ->where('idCliCuePen', $this->clienteSeleccionado->idCli)
                ->where('estCuePen', 'pagado')
                ->where('fecVencimiento', '>=', now()->subMonths(6))
                ->get(['fecVencimiento', 'fecPago']);

            if ($pagosRecientes->isEmpty()) {
                return 1.0; // Factor neutro para clientes sin historial reciente
            }

            $pagosPuntuales = 0;
            $totalPagos = $pagosRecientes->count();

            foreach ($pagosRecientes as $pago) {
                $diasRetraso = Carbon::parse($pago->fecPago)->diffInDays(Carbon::parse($pago->fecVencimiento), false);
                
                if ($diasRetraso <= 3) { // Hasta 3 días de gracia
                    $pagosPuntuales++;
                }
            }

            $porcentajePuntualidad = $pagosPuntuales / $totalPagos;

            // Factor basado en puntualidad
            return match (true) {
                $porcentajePuntualidad >= 0.9 => 1.3,  // Excelente: +30%
                $porcentajePuntualidad >= 0.8 => 1.1,  // Bueno: +10%
                $porcentajePuntualidad >= 0.6 => 1.0,  // Regular: sin cambio
                $porcentajePuntualidad >= 0.4 => 0.8,  // Malo: -20%
                default => 0.5                         // Muy malo: -50%
            };
            
        } catch (\Exception $e) {
            Log::error('Error calculando factor de comportamiento: ' . $e->getMessage());
            return 1.0;
        }
    }

    /**
     * Cambiar tipo de cliente (existente, nuevo, ocasional)
     */
    public function cambiarTipoCliente($tipo)
    {
        $this->tipoCliente = $tipo;
        
        // Limpiar campos según el tipo
        $this->reset([
            'idCliFac', 'clienteSeleccionado', 'limiteCredito',
            'nomCliNuevo', 'tipDocCliNuevo', 'docCliNuevo', 'telCliNuevo',
            'emailCliNuevo', 'dirCliNuevo', 'tipCliNuevo',
            'nomComprador', 'docComprador'
        ]);
        
        Log::info('Tipo de cliente cambiado', ['nuevo_tipo' => $tipo]);
    }

    // ===============================================
    // MÉTODOS DE GESTIÓN DE PROVEEDORES
    // ===============================================

    /**
     * Seleccionar proveedor desde dropdown
     */
    public function seleccionarProveedor($proveedorId)
    {
        if (empty($proveedorId)) {
            $this->proveedorSeleccionado = null;
            $this->idProveFac = '';
            return;
        }

        $this->cargandoProveedor = true;

        try {
            $this->proveedorSeleccionado = $this->proveedoresDisponibles->firstWhere('idProve', $proveedorId);
            $this->idProveFac = $proveedorId;
            
            if ($this->proveedorSeleccionado) {
                // Auto-completar categoría si el proveedor tiene especialidad
                if ($this->proveedorSeleccionado->tipSumProve) {
                    $this->categoriaGasto = $this->mapearEspecialidadACategoria($this->proveedorSeleccionado->tipSumProve);
                }
                
                Log::info('Proveedor seleccionado', [
                    'proveedor_id' => $proveedorId,
                    'nombre' => $this->proveedorSeleccionado->nomProve,
                    'especialidad' => $this->proveedorSeleccionado->tipSumProve
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Error seleccionando proveedor: ' . $e->getMessage());
            session()->flash('error', 'Error al cargar información del proveedor');
            
        } finally {
            $this->cargandoProveedor = false;
        }
    }

    /**
     * Mapear especialidad del proveedor a categoría de gasto
     */
    private function mapearEspecialidadACategoria($especialidad)
    {
        $mapeo = [
            'veterinario' => 'Servicios Veterinarios',
            'concentrados' => 'Insumos Agropecuarios',
            'medicamentos' => 'Insumos Agropecuarios',
            'construccion' => 'Servicios de Construcción',
            'transporte' => 'Transporte y Logística',
            'profesional' => 'Servicios Profesionales',
            'mantenimiento' => 'Mantenimiento y Reparaciones'
        ];

        return $mapeo[strtolower($especialidad)] ?? 'Otros Servicios';
    }

    // ===============================================
    // MÉTODOS DE VALIDACIÓN DE CLIENTES Y PROVEEDORES
    // ===============================================

    /**
     * Validar que el cliente sea único (documento)
     */
    private function validarClienteUnico($tipoDoc, $documento, $excludeId = null)
    {
        $query = Cliente::where('tipDocCli', $tipoDoc)
            ->where('docCli', $documento);

        if ($excludeId) {
            $query->where('idCli', '!=', $excludeId);
        }

        if ($query->exists()) {
            throw new \Exception('Ya existe un cliente con este tipo y número de documento');
        }

        return true;
    }

    /**
     * Validar límite de crédito para clientes
     */
    private function validarLimiteCredito($montoFactura)
    {
        if (!$this->clienteSeleccionado) {
            return true; // Cliente ocasional, no aplica límite
        }

        if (!in_array($this->metPagFac, ['credito', 'credito_extendido'])) {
            return true; // No es venta a crédito
        }

        if ($montoFactura > $this->limiteCredito) {
            if (!$this->permisos['credito_extendido']) {
                throw new \Exception(
                    "El monto ($" . number_format($montoFactura, 2) . ") excede el límite de crédito disponible ($" . 
                    number_format($this->limiteCredito, 2) . ")"
                );
            } else {
                // Admin puede aprobar, pero se registra la excepción
                $this->requiereAprobacion = true;
                Log::warning('Límite de crédito excedido aprobado por administrador', [
                    'cliente' => $this->clienteSeleccionado->nomCli,
                    'limite' => $this->limiteCredito,
                    'monto_factura' => $montoFactura,
                    'usuario_aprobador' => Auth::user()->nomUsu
                ]);
            }
        }

        return true;
    }

    // ===============================================
    // MÉTODOS DE UTILIDAD PARA CLIENTES
    // ===============================================

    /**
     * Obtener nombre del cliente según el tipo seleccionado
     */
    private function obtenerNombreCliente()
    {
        return match ($this->tipoCliente) {
            'existente' => $this->clienteSeleccionado?->nomCli ?? '',
            'nuevo' => $this->nomCliNuevo,
            'ocasional' => $this->nomComprador,
            default => 'Cliente no especificado'
        };
    }

    /**
     * Obtener tipo de documento del cliente
     */
    private function obtenerTipoDocumento()
    {
        return match ($this->tipoCliente) {
            'existente' => $this->clienteSeleccionado?->tipDocCli ?? 'CC',
            'nuevo' => $this->tipDocCliNuevo,
            'ocasional' => 'CC',
            default => 'CC'
        };
    }

    /**
     * Obtener documento del cliente
     */
    private function obtenerDocumento()
    {
        return match ($this->tipoCliente) {
            'existente' => $this->clienteSeleccionado?->docCli ?? '',
            'nuevo' => $this->docCliNuevo,
            'ocasional' => $this->docComprador,
            default => ''
        };
    }

    /**
     * Gestionar cliente según el tipo (retorna ID del cliente o null)
     */
    private function gestionarCliente()
    {
        switch ($this->tipoCliente) {
            case 'existente':
                if (!$this->idCliFac) {
                    throw new \Exception('Debe seleccionar un cliente');
                }
                return $this->idCliFac;

            case 'nuevo':
                $this->crearClienteNuevo();
                return $this->idCliFac;

            case 'ocasional':
                if (!$this->nomComprador || !$this->docComprador) {
                    throw new \Exception('Debe completar los datos del comprador ocasional');
                }
                return null; // Cliente ocasional no se guarda en BD

            default:
                throw new \Exception('Tipo de cliente no válido');
        }
    }

    /**
     * Obtener historial de compras del cliente
     */
    public function getHistorialClienteProperty()
    {
        if (!$this->clienteSeleccionado) {
            return collect();
        }

        try {
            return DB::table('facturas')
                ->where('idCliFac', $this->clienteSeleccionado->idCli)
                ->where('estFac', '!=', 'anulada')
                ->select('fecFac', 'totFac', 'estFac', 'metPagFac')
                ->orderBy('fecFac', 'desc')
                ->limit(10)
                ->get();
                
        } catch (\Exception $e) {
            Log::error('Error obteniendo historial de cliente: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Obtener resumen del cliente seleccionado
     */
    public function getResumenClienteProperty()
    {
        if (!$this->clienteSeleccionado) {
            return null;
        }

        try {
            $totalCompras = DB::table('facturas')
                ->where('idCliFac', $this->clienteSeleccionado->idCli)
                ->where('estFac', '!=', 'anulada')
                ->sum('totFac') ?? 0;

            $ultimaCompra = DB::table('facturas')
                ->where('idCliFac', $this->clienteSeleccionado->idCli)
                ->where('estFac', '!=', 'anulada')
                ->orderBy('fecFac', 'desc')
                ->first();

            $deudaPendiente = CuentaPendiente::where('idCliCuePen', $this->clienteSeleccionado->idCli)
                ->where('tipCuePen', 'por_cobrar')
                ->where('estCuePen', 'pendiente')
                ->sum('montoSaldo') ?? 0;

            return [
                'total_compras' => $totalCompras,
                'ultima_compra' => $ultimaCompra?->fecFac,
                'monto_ultima_compra' => $ultimaCompra?->totFac ?? 0,
                'deuda_pendiente' => $deudaPendiente,
                'limite_credito' => $this->limiteCredito,
                'antiguedad_meses' => $this->clienteSeleccionado->created_at ? 
                    Carbon::parse($this->clienteSeleccionado->created_at)->diffInMonths(now()) : 0
            ];
            
        } catch (\Exception $e) {
            Log::error('Error obteniendo resumen de cliente: ' . $e->getMessage());
            return null;
        }
    }

    // ===============================================
    // MÉTODOS PRINCIPALES DE PROCESAMIENTO
    // ===============================================

    /**
     * Método principal para guardar facturas
     */
    public function guardarFactura()
    {
        try {
            $this->guardandoFactura = true;
            
            // Validar datos de la factura
            $this->validate();
            $this->validarFactura();

            DB::beginTransaction();

            if ($this->tipoFactura === 'proveedor') {
                $resultado = $this->procesarFacturaProveedor();
                
                session()->flash('success', 'Factura de proveedor registrada correctamente. ID: ' . $resultado->idComGas);
                
                $this->dispatch('factura-guardada', [
                    'tipo' => 'proveedor',
                    'mensaje' => 'Factura de proveedor registrada exitosamente',
                    'id' => $resultado->idComGas
                ]);
                
            } else {
                // Gestionar cliente antes de procesar factura
                $clienteId = $this->gestionarCliente();
                $factura = $this->procesarFacturaGranja($clienteId);
                
                session()->flash('success', 'Factura de venta emitida correctamente. Número: FAC-' . $factura->idFac);
                
                $this->dispatch('factura-guardada', [
                    'tipo' => 'granja',
                    'mensaje' => 'Factura de venta generada exitosamente',
                    'numero' => 'FAC-' . $factura->idFac
                ]);
            }

            DB::commit();
            
            $this->cerrarModal();
            $this->calcularEstadisticas();
            
            Log::info('Factura guardada exitosamente', [
                'tipo' => $this->tipoFactura,
                'monto' => $this->totFac,
                'usuario' => Auth::id()
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            session()->flash('error', 'Por favor, corrija los errores en el formulario');
            Log::warning('Errores de validación en factura', ['errores' => $e->errors()]);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Error guardando factura: ' . $e->getMessage(), [
                'tipo' => $this->tipoFactura,
                'usuario' => Auth::id(),
                'linea' => $e->getLine(),
                'archivo' => $e->getFile()
            ]);
            
            session()->flash('error', 'Error al guardar la factura: ' . $e->getMessage());
            
        } finally {
            $this->guardandoFactura = false;
        }
    }

    // ===============================================
    // PROCESAMIENTO DE FACTURAS DE PROVEEDOR
    // ===============================================

    /**
     * Procesar factura de proveedor completa
     */
    private function procesarFacturaProveedor()
    {
        if (!$this->proveedorSeleccionado) {
            throw new \Exception('Debe seleccionar un proveedor');
        }
        
        if (!$this->archivoFacturaProveedor) {
            throw new \Exception('Debe adjuntar la foto de la factura del proveedor');
        }

        // Guardar archivo de factura
        $rutaArchivo = $this->guardarArchivoFacturaProveedor();

        // Crear registro en compras/gastos
        $compraGasto = CompraGasto::create([
            'tipComGas' => 'compra',
            'catComGas' => $this->categoriaGasto ?: 'Servicios Externos',
            'desComGas' => $this->servicioSuministrado,
            'monComGas' => $this->totFac,
            'fecComGas' => $this->fecFac,
            'metPagComGas' => $this->metPagFac,
            'provComGas' => $this->proveedorSeleccionado->nomProve,
            'docComGas' => $rutaArchivo,
            'nombreArchivoOriginal' => $this->nombreArchivoOriginal ?: $this->archivoFacturaProveedor->getClientOriginalName(),
            'numeroComprobante' => $this->numeroComprobanteProveedor,
            'obsComGas' => $this->obsFac,
            'idUsuReg' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Crear movimiento contable de egreso
        $this->crearMovimientoContableEgreso($compraGasto);

        // Crear cuenta por pagar si es a crédito
        if (in_array($this->metPagFac, ['credito', 'credito_extendido'])) {
            $this->crearCuentaPorPagar($compraGasto);
        }

        // Actualizar estadísticas del proveedor
        $this->actualizarEstadisticasProveedor($this->proveedorSeleccionado->idProve, $this->totFac);

        // Registrar auditoría
        $this->registrarAuditoria(
            'CREATE', 
            'compragastos', 
            $compraGasto->idComGas, 
            'Factura de proveedor creada: ' . $this->proveedorSeleccionado->nomProve . 
            ' - Archivo: ' . ($this->nombreArchivoOriginal ?: 'Factura del proveedor')
        );

        return $compraGasto;
    }

    /**
     * Guardar archivo de factura de proveedor
     */
    private function guardarArchivoFacturaProveedor()
    {
        try {
            $extension = $this->archivoFacturaProveedor->getClientOriginalExtension();
            $nombreArchivo = 'factura_prov_' . $this->proveedorSeleccionado->idProve . '_' . date('YmdHis') . '.' . $extension;
            $rutaArchivo = $this->archivoFacturaProveedor->storeAs('facturas_proveedores', $nombreArchivo, 'public');
            
            Log::info('Archivo de factura guardado', [
                'ruta' => $rutaArchivo,
                'nombre_original' => $this->archivoFacturaProveedor->getClientOriginalName(),
                'tamaño' => $this->archivoFacturaProveedor->getSize()
            ]);
            
            return $rutaArchivo;
            
        } catch (\Exception $e) {
            Log::error('Error guardando archivo: ' . $e->getMessage());
            throw new \Exception('Error al guardar el archivo de la factura');
        }
    }

    /**
     * Crear movimiento contable de egreso
     */
    private function crearMovimientoContableEgreso($compraGasto)
    {
        MovimientoContable::create([
            'fecMovCont' => $this->fecFac,
            'tipoMovCont' => 'egreso',
            'catMovCont' => $this->categoriaGasto ?: 'Servicios Externos',
            'conceptoMovCont' => 'Factura Proveedor: ' . $this->proveedorSeleccionado->nomProve,
            'montoMovCont' => $this->totFac,
            'idComGasMovCont' => $compraGasto->idComGas,
            'obsMovCont' => 'Pago a proveedor: ' . $this->servicioSuministrado,
            'idUsuReg' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Crear cuenta por pagar para proveedor
     */
    private function crearCuentaPorPagar($compraGasto)
    {
        $diasVencimiento = $this->metPagFac === 'credito_extendido' ? 60 : 30;
        
        CuentaPendiente::create([
            'tipCuePen' => 'por_pagar',
            'idComGasCuePen' => $compraGasto->idComGas,
            'idProveCuePen' => $this->proveedorSeleccionado->idProve,
            'montoOriginal' => $this->totFac,
            'montoPagado' => 0,
            'montoSaldo' => $this->totFac,
            'fecVencimiento' => now()->addDays($diasVencimiento),
            'estCuePen' => 'pendiente',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    // ===============================================
    // PROCESAMIENTO DE FACTURAS DE GRANJA
    // ===============================================

    /**
     * Procesar factura de granja completa
     */
    private function procesarFacturaGranja($clienteId)
    {
        $usuario = Auth::user();

        // Validar que hay productos seleccionados
        if (empty($this->productosSeleccionados)) {
            throw new \Exception('Debe agregar al menos un producto a la factura');
        }

        // Validar límite de crédito si aplica
        $this->validarLimiteCredito($this->totFac);

        // Determinar estado inicial
        $estadoInicial = $this->requiereAprobacion && !$this->permisos['aprobar_facturas']
            ? 'pendiente' : 'emitida';

        // Crear factura principal
        $factura = Factura::create([
            'idUsuFac' => $usuario->id,
            'idCliFac' => $clienteId,
            'nomCliFac' => $this->obtenerNombreCliente(),
            'tipDocCliFac' => $this->obtenerTipoDocumento(),
            'docCliFac' => $this->obtenerDocumento(),
            'fecFac' => $this->fecFac,
            'subtotalFac' => $this->subtotalFac,
            'ivaFac' => ($this->subtotalFac - $this->descuentoFac) * ($this->ivaFac / 100),
            'descuentoFac' => $this->descuentoFac,
            'totFac' => $this->totFac,
            'metPagFac' => $this->metPagFac,
            'estFac' => $estadoInicial,
            'obsFac' => $this->generarObservaciones(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Procesar productos y servicios asociados
        $this->guardarDetallesFactura($factura->idFac);
        $this->registrarProduccionAnimal($factura->idFac);
        $this->actualizarInventario($factura->idFac);

        // Crear movimiento contable solo si no requiere aprobación
        if ($estadoInicial === 'emitida') {
            $this->crearMovimientoContableIngreso($factura);
        }

        // Crear cuenta pendiente si es a crédito
        if (in_array($this->metPagFac, ['credito', 'credito_extendido']) && $clienteId) {
            $this->crearCuentaPorCobrar($factura->idFac, $clienteId);
        }

        // Actualizar estadísticas del cliente si existe
        if ($clienteId) {
            $this->actualizarEstadisticasCliente($clienteId, $this->totFac);
        }

        // Registrar auditoría
        $this->registrarAuditoria(
            'CREATE',
            'facturas',
            $factura->idFac,
            'Factura de granja creada para: ' . $this->obtenerNombreCliente() .
            ' - Estado: ' . $estadoInicial
        );

        Log::info('Factura de granja procesada', [
            'factura_id' => $factura->idFac,
            'cliente' => $this->obtenerNombreCliente(),
            'monto' => $this->totFac,
            'estado' => $estadoInicial,
            'productos_count' => count($this->productosSeleccionados)
        ]);

        return $factura;
    }

    /**
     * Guardar detalles de la factura
     */
    private function guardarDetallesFactura($facturaId)
    {
        foreach ($this->productosSeleccionados as $producto) {
            DB::table('facturadetalles')->insert([
                'idFacDet' => $facturaId,
                'conceptoDet' => $producto['nombre'],
                'cantidadDet' => $producto['cantidad'],
                'precioUnitDet' => $producto['precio_unitario'],
                'subtotalDet' => $producto['subtotal'],
                'idAniDet' => $producto['tipo'] === 'animal_pie' ? $producto['id'] : null,
                'idProAniDet' => null, // Se establecerá después de crear producción animal
                'idInsDet' => $producto['tipo'] === 'granja' ? $producto['id'] : null,
                'tipoProducto' => $producto['tipo'],
                'categoria' => $producto['categoria'] ?? null,
                'unidadVenta' => $producto['unidad'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    /**
     * Registrar producción animal para productos derivados
     */
    private function registrarProduccionAnimal($facturaId)
    {
        foreach ($this->productosSeleccionados as $producto) {
            if ($producto['tipo'] === 'granja' && isset($producto['tipo_producto'])) {
                // Solo registrar si es un producto que requiere registro de producción
                if (in_array($producto['tipo_producto'], ['huevos', 'leche_bovina', 'leche_ovina', 'lana'])) {
                    
                    $tipoProduccion = $this->mapearTipoProduccion($producto['tipo_producto']);
                    
                    $produccion = ProduccionAnimal::create([
                        'idAniPro' => $producto['animal_origen'] ?? null, // Si está disponible
                        'tipProAni' => $tipoProduccion,
                        'canProAni' => $producto['cantidad'],
                        'uniProAni' => $producto['unidad'],
                        'fecProAni' => $this->fecFac,
                        'obsProAni' => 'Producción vendida - Factura: FAC-' . $facturaId . 
                                      ' - Categoría: ' . ($producto['categoria'] ?? 'General'),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    // Actualizar detalle de factura con ID de producción
                    DB::table('facturadetalles')
                        ->where('idFacDet', $facturaId)
                        ->where('idInsDet', $producto['id'])
                        ->update(['idProAniDet' => $produccion->idProAni]);
                }
            }
        }
    }

    /**
     * Actualizar inventario con las ventas
     */
    private function actualizarInventario($facturaId)
    {
        foreach ($this->productosSeleccionados as $producto) {
            if ($producto['tipo'] === 'granja') {
                // Registrar salida de inventario
                DB::table('inventario')->insert([
                    'idIns' => $producto['id'],
                    'tipMovInv' => 'venta',
                    'cantMovInv' => $producto['cantidad'],
                    'uniMovInv' => $producto['unidad'],
                    'costoUnitInv' => $producto['precio_unitario'],
                    'costoTotInv' => $producto['subtotal'],
                    'fecMovInv' => now(),
                    'loteInv' => $producto['lote_produccion'] ?? null,
                    'idFac' => $facturaId,
                    'idProduccionAnimal' => null,
                    'idUsuReg' => Auth::id(),
                    'obsInv' => 'Venta productos granja - Cliente: ' . $this->obtenerNombreCliente() .
                               ($producto['categoria'] ? ' - Cat: ' . $producto['categoria'] : ''),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } elseif ($producto['tipo'] === 'animal_pie') {
                // Marcar animal como vendido
                DB::table('animales')
                    ->where('idAni', $producto['id'])
                    ->update([
                        'estAni' => 'vendido',
                        'fecVenta' => $this->fecFac,
                        'precioVenta' => $producto['precio_unitario'],
                        'obsVenta' => 'Vendido en factura FAC-' . $facturaId,
                        'updated_at' => now()
                    ]);
            }
        }
    }

    /**
     * Crear movimiento contable de ingreso
     */
    private function crearMovimientoContableIngreso($factura)
    {
        MovimientoContable::create([
            'fecMovCont' => $this->fecFac,
            'tipoMovCont' => 'ingreso',
            'catMovCont' => 'Venta Productos Granja',
            'conceptoMovCont' => 'Factura FAC-' . $factura->idFac . ' - ' . $this->obtenerNombreCliente(),
            'montoMovCont' => $this->totFac,
            'idFacMovCont' => $factura->idFac,
            'obsMovCont' => $this->aplicarDescuento
                ? 'Venta con descuento del ' . $this->porcentajeDescuento . '% - ' . $this->motivoDescuento
                : 'Venta de productos agropecuarios',
            'idUsuReg' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Crear cuenta por cobrar para cliente
     */
    private function crearCuentaPorCobrar($facturaId, $clienteId)
    {
        $diasVencimiento = match ($this->metPagFac) {
            'credito_extendido' => 60,
            'credito' => $this->diasCredito,
            default => 30
        };

        CuentaPendiente::create([
            'tipCuePen' => 'por_cobrar',
            'idFacCuePen' => $facturaId,
            'idCliCuePen' => $clienteId,
            'montoOriginal' => $this->totFac,
            'montoPagado' => 0,
            'montoSaldo' => $this->totFac,
            'fecVencimiento' => now()->addDays($diasVencimiento),
            'estCuePen' => 'pendiente',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    // ===============================================
    // MÉTODOS DE UTILIDAD PARA PROCESAMIENTO
    // ===============================================

    /**
     * Mapear tipo de producto a tipo de producción animal
     */
    private function mapearTipoProduccion($tipoProducto)
    {
        $mapeo = [
            'huevos' => 'huevos',
            'leche_bovina' => 'leche',
            'leche_ovina' => 'leche',
            'lana' => 'lana'
        ];

        return $mapeo[$tipoProducto] ?? 'otros';
    }

    /**
     * Generar observaciones completas de la factura
     */
    private function generarObservaciones()
    {
        $obs = $this->obsFac ?: '';
        $usuario = Auth::user();

        $obs .= ' | Vendedor: ' . $usuario->nomUsu . ' ' . $usuario->apeUsu;

        if ($this->tipoCliente === 'ocasional') {
            $obs .= ' | Cliente Ocasional';
        }

        if ($this->aplicarDescuento) {
            $obs .= ' | Descuento: ' . $this->porcentajeDescuento . '% - ' . $this->motivoDescuento;
        }

        if ($this->requiereAprobacion) {
            $obs .= ' | REQUIERE APROBACIÓN por monto: $' . number_format($this->totFac, 2);
        }

        // Agregar resumen de productos
        $resumenProductos = collect($this->productosSeleccionados)->groupBy('tipo')->map(function ($items, $tipo) {
            return count($items) . ' ' . ($tipo === 'animal_pie' ? 'animales' : 'productos');
        })->implode(', ');

        $obs .= ' | Productos: ' . $resumenProductos;

        return trim($obs, ' |');
    }

    /**
     * Actualizar estadísticas del proveedor
     */
    private function actualizarEstadisticasProveedor($proveedorId, $monto)
    {
        try {
            // Aquí podrías actualizar una tabla de estadísticas de proveedores
            // Por ahora solo logueamos
            Log::info('Estadísticas de proveedor actualizadas', [
                'proveedor_id' => $proveedorId,
                'monto_compra' => $monto
            ]);
        } catch (\Exception $e) {
            Log::error('Error actualizando estadísticas de proveedor: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar estadísticas del cliente
     */
    private function actualizarEstadisticasCliente($clienteId, $monto)
    {
        try {
            // Aquí podrías actualizar una tabla de estadísticas de clientes
            // Por ahora solo logueamos
            Log::info('Estadísticas de cliente actualizadas', [
                'cliente_id' => $clienteId,
                'monto_venta' => $monto
            ]);
        } catch (\Exception $e) {
            Log::error('Error actualizando estadísticas de cliente: ' . $e->getMessage());
        }
    }

    // ===============================================
    // MÉTODOS DE VALIDACIÓN PRINCIPAL
    // ===============================================

    /**
     * Validar factura de proveedor específicamente
     */
    private function validarFacturaProveedor()
    {
        if (!$this->proveedorSeleccionado) {
            throw new \Exception('Debe seleccionar un proveedor');
        }

        if (empty($this->servicioSuministrado) || strlen($this->servicioSuministrado) < 5) {
            throw new \Exception('La descripción del servicio debe tener al menos 5 caracteres');
        }

        if (!$this->archivoFacturaProveedor) {
            throw new \Exception('Debe adjuntar la foto de la factura del proveedor');
        }

        if ($this->totFac <= 0) {
            throw new \Exception('El valor total debe ser mayor a cero');
        }

        // Validar tamaño y tipo de archivo
        $this->validarArchivoFactura();
    }

    /**
     * Validar factura de granja específicamente
     */
    private function validarFacturaGranja()
    {
        // Validar que hay productos
        if (empty($this->productosSeleccionados)) {
            throw new \Exception('Debe agregar al menos un producto a la factura');
        }

        // Validar cliente según tipo
        $this->validarClienteSegunTipo();

        // Validar productos específicamente
        foreach ($this->productosSeleccionados as $index => $producto) {
            $this->validarProductoIndividual($producto, $index);
        }

        // Validar límite de crédito si aplica
        if (in_array($this->metPagFac, ['credito', 'credito_extendido'])) {
            $this->validarLimiteCredito($this->totFac);
        }

        // Validar descuentos si están aplicados
        if ($this->aplicarDescuento) {
            $this->validarDescuento();
        }
    }

    /**
     * Validar cliente según el tipo seleccionado
     */
    private function validarClienteSegunTipo()
    {
        switch ($this->tipoCliente) {
            case 'existente':
                if (!$this->idCliFac) {
                    throw new \Exception('Debe seleccionar un cliente');
                }
                break;

            case 'nuevo':
                if (!$this->nomCliNuevo || !$this->docCliNuevo) {
                    throw new \Exception('Debe completar los datos del cliente nuevo');
                }
                // Validar unicidad del documento
                $this->validarClienteUnico($this->tipDocCliNuevo, $this->docCliNuevo);
                break;

            case 'ocasional':
                if (!$this->nomComprador || !$this->docComprador) {
                    throw new \Exception('Debe completar los datos del comprador ocasional');
                }
                break;

            default:
                throw new \Exception('Tipo de cliente no válido');
        }
    }

    /**
     * Validar producto individual en el carrito
     */
    private function validarProductoIndividual($producto, $index)
    {
        if ($producto['tipo'] === 'granja') {
            $this->validarProductoGranja($producto, $index);
        } elseif ($producto['tipo'] === 'animal_pie') {
            $this->validarAnimalEnPie($producto, $index);
        }

        // Validaciones generales del producto
        if ($producto['cantidad'] <= 0) {
            throw new \Exception("La cantidad del producto #" . ($index + 1) . " debe ser mayor a cero");
        }

        if ($producto['precio_unitario'] <= 0) {
            throw new \Exception("El precio unitario del producto #" . ($index + 1) . " debe ser mayor a cero");
        }

        if ($producto['subtotal'] <= 0) {
            throw new \Exception("El subtotal del producto # . ($index + 1) debe ser mayor a cero");
        }
    }

    /**
     * Validar producto de granja específico
     */
    private function validarProductoGranja($producto, $index)
    {
        // Verificar que el producto existe en inventario
        $productoInventario = $this->productosGranjaDisponibles->firstWhere('idIns', $producto['id']);
        if (!$productoInventario) {
            throw new \Exception("El producto # . ($index + 1) ya no está disponible en inventario");
        }

        // Verificar stock disponible
        if ($this->verificarStock && $producto['cantidad'] > $productoInventario->stockActual) {
            throw new \Exception("Stock insuficiente para producto # . ($index + 1). Disponible: {$productoInventario->stockActual}");
        }

        // Validaciones específicas por tipo de producto
        if ($producto['tipo_producto'] === 'huevos') {
            if (empty($producto['categoria'])) {
                throw new \Exception("Debe especificar la categoría para los huevos en producto # . ($index + 1)");
            }
            if (!in_array($producto['categoria'], ['A', 'AA', 'AAA', 'JUMBO'])) {
                throw new \Exception("Categoría de huevos no válida en producto # . ($index + 1)");
            }
        }
    }

    /**
     * Validar animal en pie específico
     */
    private function validarAnimalEnPie($producto, $index)
    {
        // Verificar que el animal existe y está disponible
        $animal = $this->animalesEnPieDisponibles->firstWhere('idAni', $producto['id']);
        if (!$animal) {
            throw new \Exception("El animal # . ($index + 1) ya no está disponible para venta");
        }

        // Verificar que no esté ya vendido
        $yaVendido = DB::table('facturas as f')
            ->join('facturadetalles as fd', 'f.idFac', '=', 'fd.idFacDet')
            ->where('fd.idAniDet', $producto['id'])
            ->where('f.estFac', '!=', 'anulada')
            ->exists();

        if ($yaVendido) {
            throw new \Exception("El animal # . ($index + 1) ya fue vendido anteriormente");
        }

        // Validar que solo se vende 1 animal
        if ($producto['cantidad'] != 1) {
            throw new \Exception("Solo se puede vender un animal a la vez");
        }
    }

    /**
     * Validar descuento aplicado
     */
    private function validarDescuento()
    {
        if (!$this->permisos['descuentos_especiales']) {
            throw new \Exception('No tiene permisos para aplicar descuentos especiales');
        }

        if ($this->porcentajeDescuento <= 0 || $this->porcentajeDescuento > $this->configuraciones['descuento_maximo']) {
            throw new \Exception('El porcentaje de descuento debe estar entre 0.01% y ' . $this->configuraciones['descuento_maximo'] . '%');
        }

        if (empty($this->motivoDescuento) || strlen($this->motivoDescuento) < 10) {
            throw new \Exception('Debe especificar un motivo válido para el descuento (mínimo 10 caracteres)');
        }
    }

    /**
     * Validar que los totales son coherentes
     */
    private function validarTotalesCoherentes()
    {
        if ($this->tipoFactura === 'granja') {
            $subtotalCalculado = collect($this->productosSeleccionados)->sum('subtotal');
            
            if (abs($subtotalCalculado - $this->subtotalFac) > 0.01) {
                throw new \Exception('El subtotal no coincide con la suma de productos');
            }

            $descuentoCalculado = $this->aplicarDescuento ? ($this->subtotalFac * $this->porcentajeDescuento) / 100 : 0;
            if (abs($descuentoCalculado - $this->descuentoFac) > 0.01) {
                throw new \Exception('El descuento calculado no coincide');
            }

            $subtotalConDescuento = $this->subtotalFac - $this->descuentoFac;
            $ivaCalculado = ($subtotalConDescuento * $this->ivaFac) / 100;
            $totalCalculado = $subtotalConDescuento + $ivaCalculado;

            if (abs($totalCalculado - $this->totFac) > 0.01) {
                throw new \Exception('El total calculado no coincide. Verifique los cálculos.');
            }
        }
    }

    /**
     * Validar fecha de la factura
     */
    private function validarFecha()
    {
        $fechaFactura = Carbon::parse($this->fecFac);
        $hoy = Carbon::today();
        $hace30Dias = $hoy->copy()->subDays(30);

        if ($fechaFactura->isAfter($hoy)) {
            throw new \Exception('La fecha de la factura no puede ser futura');
        }

        if ($fechaFactura->isBefore($hace30Dias)) {
            throw new \Exception('No se pueden crear facturas con fecha mayor a 30 días en el pasado');
        }
    }

    /**
     * Validar montos mínimos
     */
    private function validarMontosMinimos()
    {
        if ($this->totFac < 100) {
            throw new \Exception('El monto mínimo de facturación es $100');
        }

        if ($this->totFac > 50000000) { // 50 millones
            if (!$this->permisos['aprobar_facturas']) {
                throw new \Exception('Facturas superiores a $50,000,000 requieren aprobación de administrador');
            }
        }
    }

    /**
     * Validar archivo de factura de proveedor
     */
    private function validarArchivoFactura()
    {
        $archivo = $this->archivoFacturaProveedor;
        
        // Validar tamaño (máximo 5MB)
        if ($archivo->getSize() > 5 * 1024 * 1024) {
            throw new \Exception('El archivo no puede superar los 5MB');
        }

        // Validar tipo de archivo
        $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'pdf'];
        $extension = strtolower($archivo->getClientOriginalExtension());
        
        if (!in_array($extension, $extensionesPermitidas)) {
            throw new \Exception('Solo se permiten archivos JPG, PNG o PDF');
        }

        // Validar que el archivo no esté corrupto
        if ($archivo->getError() !== UPLOAD_ERR_OK) {
            throw new \Exception('Error en la subida del archivo');
        }
    }

    // ===============================================
    // REGLAS DE VALIDACIÓN PARA LIVEWIRE
    // ===============================================

    /**
     * Reglas de validación dinámicas
     */
    public function rules(): array
    {
        $rules = [
            'fecFac' => 'required|date|before_or_equal:today|after:' . now()->subDays(30)->format('Y-m-d'),
            'metPagFac' => 'required|string|in:' . implode(',', array_keys($this->metodosPago)),
            'obsFac' => 'nullable|string|max:1000'
        ];

        if ($this->tipoFactura === 'proveedor') {
            $rules = array_merge($rules, [
                'idProveFac' => 'required|exists:proveedores,idProve',
                'servicioSuministrado' => 'required|string|max:500|min:5',
                'categoriaGasto' => 'nullable|string|max:100',
                'totFac' => 'required|numeric|min:100|max:50000000',
                'archivoFacturaProveedor' => 'required|file|max:5120|mimes:jpeg,png,jpg,pdf',
                'numeroComprobanteProveedor' => 'nullable|string|max:50'
            ]);
        } else {
            $rules = array_merge($rules, [
                'subtotalFac' => 'required|numeric|min:0.01|max:999999999.99',
                'ivaFac' => 'required|numeric|min:0|max:100',
                'totFac' => 'required|numeric|min:100|max:50000000',
                'productosSeleccionados' => 'required|array|min:1'
            ]);

            // Validaciones según tipo de cliente
            if ($this->tipoCliente === 'existente') {
                $rules['idCliFac'] = 'required|exists:clientes,idCli';
            } elseif ($this->tipoCliente === 'nuevo') {
                $rules = array_merge($rules, [
                    'nomCliNuevo' => 'required|string|max:100|min:2',
                    'tipDocCliNuevo' => 'required|in:NIT,CC,CE,Pasaporte',
                    'docCliNuevo' => 'required|string|max:20|unique:clientes,docCli',
                    'telCliNuevo' => 'nullable|string|max:20|regex:/^[0-9+\-\s]+$/',
                    'emailCliNuevo' => 'nullable|email|max:100|unique:clientes,emailCli'
                ]);
            } else {
                $rules = array_merge($rules, [
                    'nomComprador' => 'required|string|max:100|min:2',
                    'docComprador' => 'required|string|max:20|min:5'
                ]);
            }

            // Validaciones para descuentos
            if ($this->aplicarDescuento) {
                $rules = array_merge($rules, [
                    'porcentajeDescuento' => 'required|numeric|min:0.01|max:' . $this->configuraciones['descuento_maximo'],
                    'motivoDescuento' => 'required|string|min:10|max:200'
                ]);
            }
        }

        return $rules;
    }

    /**
     * Mensajes de validación personalizados
     */
    public function messages(): array
    {
        return [
            'fecFac.required' => 'La fecha de la factura es obligatoria',
            'fecFac.before_or_equal' => 'La fecha no puede ser futura',
            'fecFac.after' => 'No se pueden crear facturas con más de 30 días de antigüedad',
            'metPagFac.required' => 'Debe seleccionar un método de pago',
            'idProveFac.required' => 'Debe seleccionar un proveedor',
            'servicioSuministrado.required' => 'Debe describir el servicio o producto',
            'servicioSuministrado.min' => 'La descripción debe tener al menos 5 caracteres',
            'archivoFacturaProveedor.required' => 'Debe adjuntar la foto de la factura',
            'archivoFacturaProveedor.max' => 'El archivo no puede superar los 5MB',
            'archivoFacturaProveedor.mimes' => 'Solo se permiten archivos JPG, PNG o PDF',
            'totFac.min' => 'El monto mínimo es $100',
            'totFac.max' => 'El monto máximo es $50,000,000',
            'productosSeleccionados.required' => 'Debe agregar al menos un producto',
            'idCliFac.required' => 'Debe seleccionar un cliente',
            'nomCliNuevo.required' => 'El nombre del cliente es obligatorio',
            'docCliNuevo.unique' => 'Ya existe un cliente con este documento',
            'emailCliNuevo.unique' => 'Ya existe un cliente con este email',
            'nomComprador.required' => 'El nombre del comprador es obligatorio',
            'porcentajeDescuento.max' => 'El descuento máximo permitido es ' . $this->configuraciones['descuento_maximo'] . '%',
            'motivoDescuento.min' => 'El motivo del descuento debe tener al menos 10 caracteres'
        ];
    }

    // ===============================================
    // MÉTODOS DE GESTIÓN DE FACTURAS
    // ===============================================

    /**
     * Ver detalles de una factura
     */
    public function verFactura($facturaId)
{
    try {
        $this->facturaVer = DB::table('facturas as f')
            ->leftJoin('clientes as c', 'f.idCliFac', '=', 'c.idCli')
            ->leftJoin('users as u', 'f.idUsuFac', '=', 'u.id')
            ->select(
                'f.*',
                'c.nomCli', 'c.tipDocCli', 'c.docCli', 'c.dirCli', 'c.telCli', 'c.emailCli',
                'u.nomUsu', 'u.apeUsu'
            )
            ->where('f.idFac', $facturaId)
            ->first();

        if (!$this->facturaVer) {
            session()->flash('error', 'Factura no encontrada');
            return;
        }

        // Verificar permisos para ver esta factura
        if (!($this->permisos['ver_todas_facturas'] ?? false) && $this->facturaVer->idUsuFac !== Auth::id()) {
            session()->flash('error', 'No tiene permisos para ver esta factura');
            return;
        }

        // Cargar detalles con información relacionada
        $this->detallesFactura = DB::table('facturadetalles as fd')
            ->leftJoin('animales as a', 'fd.idAniDet', '=', 'a.idAni')
            ->leftJoin('insumos as i', 'fd.idInsDet', '=', 'i.idIns')
            ->where('fd.idFacDet', $facturaId)
            ->select(
                'fd.*',
                'a.nitAni',
                'a.espAni',
                'a.razAni',
                'i.nomIns',
                'i.tipIns'
            )
            ->get();

        $this->modalVerAbierto = true;

        Log::info('Factura visualizada', [
            'factura_id' => $facturaId,
            'usuario' => Auth::id()
        ]);

    } catch (\Exception $e) {
        Log::error('Error cargando factura: ' . $e->getMessage());
        session()->flash('error', 'Error al cargar la factura');
    }
}

    /**
     * Eliminar factura con validaciones
     */
    public function eliminarFactura($facturaId)
{
    if (!($this->permisos['eliminar_facturas'] ?? false)) {
        session()->flash('error', 'No tiene permisos para eliminar facturas');
        return;
    }

    try {
        DB::beginTransaction();

        $factura = DB::table('facturas')->where('idFac', $facturaId)->first();
        if (!$factura) {
            throw new \Exception('Factura no encontrada');
        }

        // Validaciones de eliminación
        if ($factura->estFac === 'pagada') {
            throw new \Exception('No se puede eliminar una factura que ya está pagada');
        }

        $tiempoTranscurrido = \Carbon\Carbon::parse($factura->created_at)->diffInHours(now());
        if ($tiempoTranscurrido > 24 && !($this->permisos['aprobar_facturas'] ?? false)) {
            throw new \Exception('Solo se pueden eliminar facturas dentro de las primeras 24 horas');
        }

        // Revertir movimientos de inventario
        $this->revertirMovimientosInventario($facturaId);

        // Revertir estado de animales si aplica
        $this->revertirEstadoAnimales($facturaId);

        // Eliminar movimientos contables relacionados
        DB::table('movimientoscontables')->where('idFacMovCont', $facturaId)->delete();

        // Eliminar cuentas pendientes
        DB::table('cuentaspendientes')->where('idFacCuePen', $facturaId)->delete();

        // Eliminar detalles
        DB::table('facturadetalles')->where('idFacDet', $facturaId)->delete();

        // Eliminar factura
        $nombreCliente = $factura->nomCliFac;
        DB::table('facturas')->where('idFac', $facturaId)->delete();

        // Registrar auditoría
        $this->registrarAuditoria(
            'DELETE',
            'facturas',
            $facturaId,
            'Factura eliminada - Cliente: ' . $nombreCliente
        );

        DB::commit();

        $this->calcularEstadisticas();
        $this->resetPage();
        session()->flash('success', 'Factura eliminada correctamente');

        Log::info('Factura eliminada', [
            'factura_id' => $facturaId,
            'cliente' => $nombreCliente,
            'usuario' => Auth::id()
        ]);

    } catch (\Exception $e) {
        DB::rollback();
        Log::error('Error eliminando factura: ' . $e->getMessage());
        session()->flash('error', 'Error al eliminar la factura: ' . $e->getMessage());
    }
}

    /**
     * Revertir movimientos de inventario
     */
    private function revertirMovimientosInventario($facturaId)
{
    $movimientos = DB::table('inventario')
        ->where('idFac', $facturaId)
        ->where('tipMovInv', 'venta')
        ->get();

    foreach ($movimientos as $movimiento) {
        // Crear movimiento de ajuste para revertir
        DB::table('inventario')->insert([
            'idIns' => $movimiento->idIns,
            'tipMovInv' => 'ajuste_pos',
            'cantMovInv' => $movimiento->cantMovInv,
            'uniMovInv' => $movimiento->uniMovInv,
            'costoUnitInv' => $movimiento->costoUnitInv,
            'costoTotInv' => $movimiento->costoTotInv,
            'fecMovInv' => now(),
            'idUsuReg' => Auth::id(),
            'obsInv' => 'Reversión por eliminación de factura #' . $facturaId,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}


    /**
     * Revertir estado de animales vendidos
     */
    private function revertirEstadoAnimales($facturaId)
{
    $animalesVendidos = DB::table('facturadetalles')
        ->where('idFacDet', $facturaId)
        ->whereNotNull('idAniDet')
        ->pluck('idAniDet');

    foreach ($animalesVendidos as $animalId) {
        DB::table('animales')
            ->where('idAni', $animalId)
            ->update([
                'estAni' => 'vivo',
                'updated_at' => now()
                // ✅ Solo actualizar campos que existen en la tabla
            ]);
    }
}
    /**
     * Cambiar estado de una factura
     */
    public function cambiarEstadoFactura($facturaId, $nuevoEstado)
    {
        try {
            $factura = Factura::find($facturaId);

            if (!$factura) {
                session()->flash('error', 'Factura no encontrada');
                return;
            }

            // Validar permisos según el cambio de estado
            if ($nuevoEstado === 'anulada' && !$this->permisos['eliminar_facturas']) {
                session()->flash('error', 'No tiene permisos para anular facturas');
                return;
            }

            if ($nuevoEstado === 'emitida' && $factura->estFac === 'pendiente' && !$this->permisos['aprobar_facturas']) {
                session()->flash('error', 'No tiene permisos para aprobar facturas');
                return;
            }

            DB::beginTransaction();

            $estadoAnterior = $factura->estFac;
            $factura->update(['estFac' => $nuevoEstado]);

            // Procesar cambios según el nuevo estado
            $this->procesarCambioEstado($factura, $estadoAnterior, $nuevoEstado);

            // Registrar auditoría
            $this->registrarAuditoria(
                'UPDATE',
                'facturas',
                $facturaId,
                "Estado cambiado de {$estadoAnterior} a {$nuevoEstado}"
            );

            DB::commit();

            $this->calcularEstadisticas();
            session()->flash('success', 'Estado de factura actualizado correctamente');

            Log::info('Estado de factura cambiado', [
                'factura_id' => $facturaId,
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => $nuevoEstado,
                'usuario' => Auth::id()
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error cambiando estado de factura: ' . $e->getMessage());
            session()->flash('error', 'Error al cambiar el estado de la factura');
        }
    }

    /**
     * Procesar cambios específicos según el nuevo estado
     */
    private function procesarCambioEstado($factura, $estadoAnterior, $nuevoEstado)
    {
        if ($nuevoEstado === 'pagada' && $estadoAnterior === 'emitida') {
            // Crear movimiento contable si no existe
            $movimientoExiste = MovimientoContable::where('idFacMovCont', $factura->idFac)->exists();

            if (!$movimientoExiste) {
                MovimientoContable::create([
                    'fecMovCont' => now()->format('Y-m-d'),
                    'tipoMovCont' => 'ingreso',
                    'catMovCont' => 'Venta Productos Granja',
                    'conceptoMovCont' => 'Pago Factura FAC-' . $factura->idFac,
                    'montoMovCont' => $factura->totFac,
                    'idFacMovCont' => $factura->idFac,
                    'obsMovCont' => 'Factura marcada como pagada por: ' . Auth::user()->nomUsu,
                    'idUsuReg' => Auth::id(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Actualizar cuenta pendiente si existe
            DB::table('cuentaspendientes')
                ->where('idFacCuePen', $factura->idFac)
                ->where('tipCuePen', 'por_cobrar')
                ->update([
                    'estCuePen' => 'pagado',
                    'montoPagado' => $factura->totFac,
                    'montoSaldo' => 0,
                    'fecPago' => now(),
                    'updated_at' => now()
                ]);

        } elseif ($nuevoEstado === 'emitida' && $estadoAnterior === 'pendiente') {
            // Crear movimiento contable para facturas aprobadas
            MovimientoContable::create([
                'fecMovCont' => now()->format('Y-m-d'),
                'tipoMovCont' => 'ingreso',
                'catMovCont' => 'Venta Productos Granja',
                'conceptoMovCont' => 'Factura FAC-' . $factura->idFac . ' - Aprobada',
                'montoMovCont' => $factura->totFac,
                'idFacMovCont' => $factura->idFac,
                'obsMovCont' => 'Factura aprobada por: ' . Auth::user()->nomUsu,
                'idUsuReg' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    // ===============================================
    // PROPIEDADES COMPUTADAS (GETTERS)
    // ===============================================

    /**
     * Obtener facturas con filtros aplicados
     */
    public function getFacturasProperty()
{
    try {
        $usuario = Auth::user();
        $query = Factura::query();

        // ✅ DEBUG: Log inicial
        \Log::info('=== DEBUG FACTURAS ===', [
            'filtros_aplicados' => [
                'estado' => $this->estado,
                'cliente_buscar' => $this->cliente_buscar,
                'fecha_desde' => $this->fecha_desde,
                'fecha_hasta' => $this->fecha_hasta
            ],
            'usuario_id' => $usuario->id,
            'permisos_ver_todas' => $this->permisos['ver_todas_facturas'] ?? false
        ]);

        // Filtrar por permisos
        if (!($this->permisos['ver_todas_facturas'] ?? false)) {
            $query->where('idUsuFac', $usuario->id);
            \Log::info('Filtro aplicado: solo facturas del usuario ' . $usuario->id);
        }

        // Contar antes de aplicar filtros de fecha
        $totalAntesFiltros = $query->count();
        \Log::info('Total facturas antes filtros fecha: ' . $totalAntesFiltros);

        // Aplicar filtros
        if ($this->estado) {
            $query->where('estFac', $this->estado);
        }

        if ($this->cliente_buscar) {
            $query->where(function ($q) {
                $q->where('nomCliFac', 'like', '%' . $this->cliente_buscar . '%')
                  ->orWhere('docCliFac', 'like', '%' . $this->cliente_buscar . '%');
            });
        }

        if ($this->fecha_desde) {
            $query->where('fecFac', '>=', $this->fecha_desde);
            \Log::info('Filtro fecha_desde aplicado: ' . $this->fecha_desde);
        }

        if ($this->fecha_hasta) {
            $query->where('fecFac', '<=', $this->fecha_hasta);
            \Log::info('Filtro fecha_hasta aplicado: ' . $this->fecha_hasta);
        }

        // Contar después de filtros
        $totalDespuesFiltros = $query->count();
        \Log::info('Total facturas después filtros: ' . $totalDespuesFiltros);

        // Obtener resultado
        $resultado = $query->orderBy('fecFac', 'desc')
            ->orderBy('idFac', 'desc')
            ->paginate($this->per_page);

        \Log::info('Resultado paginación', [
            'total' => $resultado->total(),
            'por_pagina' => $this->per_page,
            'pagina_actual' => $resultado->currentPage()
        ]);

        return $resultado;

    } catch (\Exception $e) {
        \Log::error('Error en getFacturasProperty: ' . $e->getMessage());
        \Log::error('Stack trace: ' . $e->getTraceAsString());
        return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->per_page);
    }
}

    /**
     * Obtener estadísticas del sistema
     */
    public function getEstadisticasProperty()
    {
        return [
            'total_facturado' => $this->estadisticas['total_facturado'] ?? 0,
            'por_cobrar' => $this->estadisticas['por_cobrar'] ?? 0,
            'ventas_hoy' => $this->estadisticas['ventas_hoy'] ?? 0,
            'clientes_activos' => $this->estadisticas['clientes_activos'] ?? 0,
            'facturas_venta' => $this->estadisticas['facturas_venta'] ?? 0,
            'facturas_compra' => $this->estadisticas['facturas_compra'] ?? 0,
        ];
    }

    /**
     * Obtener alertas inteligentes del sistema
     */
    public function getAlertasInteligenteProperty()
    {
        try {
            $alertas = [];

            // Stock crítico
            $stockCritico = DB::table('insumos as i')
                ->leftJoin('inventario as inv', 'i.idIns', '=', 'inv.idIns')
                ->select([
                    'i.idIns',
                    'i.nomIns',
                    DB::raw('SUM(CASE WHEN inv.tipMovInv = "entrada" THEN inv.cantMovInv ELSE -inv.cantMovInv END) as stockActual')
                ])
                ->where('i.estIns', 'disponible')
                ->groupBy('i.idIns', 'i.nomIns')
                ->having('stockActual', '<', 10)
                ->count();

            if ($stockCritico > 0) {
                $alertas[] = [
                    'tipo' => 'stock',
                    'nivel' => 'crítico',
                    'mensaje' => "$stockCritico productos con stock crítico",
                    'icono' => 'fas fa-exclamation-triangle',
                    'color' => 'red'
                ];
            }

            // Facturas pendientes de aprobación
            if ($this->permisos['aprobar_facturas'] ?? false) {
                $pendientesAprobacion = Factura::where('estFac', 'pendiente')->count();

                if ($pendientesAprobacion > 0) {
                    $alertas[] = [
                        'tipo' => 'aprobacion',
                        'nivel' => 'importante',
                        'mensaje' => "$pendientesAprobacion facturas pendientes de aprobación",
                        'icono' => 'fas fa-clock',
                        'color' => 'orange'
                    ];
                }
            }

            // Cuentas vencidas
            $cuentasVencidas = CuentaPendiente::where('tipCuePen', 'por_cobrar')
                ->where('estCuePen', 'pendiente')
                ->where('fecVencimiento', '<', now())
                ->count();

            if ($cuentasVencidas > 0) {
                $alertas[] = [
                    'tipo' => 'cobranza',
                    'nivel' => 'importante',
                    'mensaje' => "$cuentasVencidas cuentas vencidas",
                    'icono' => 'fas fa-money-bill-wave',
                    'color' => 'red'
                ];
            }

            return collect($alertas);

        } catch (\Exception $e) {
            Log::error('Error obteniendo alertas: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Obtener resumen del día actual
     */
    public function getResumenDiaProperty()
    {
        try {
            return [
                'ventas_count' => Factura::whereDate('fecFac', today())->count(),
                'ventas_total' => Factura::whereDate('fecFac', today())->sum('totFac'),
                'compras_count' => CompraGasto::whereDate('fecComGas', today())->count(),
                'compras_total' => CompraGasto::whereDate('fecComGas', today())->sum('monComGas'),
                'clientes_nuevos' => Cliente::whereDate('created_at', today())->count()
            ];
        } catch (\Exception $e) {
            Log::error('Error obteniendo resumen del día: ' . $e->getMessage());
            return [
                'ventas_count' => 0,
                'ventas_total' => 0,
                'compras_count' => 0,
                'compras_total' => 0,
                'clientes_nuevos' => 0
            ];
        }
    }

    /**
     * Obtener productos más vendidos
     */
    public function getProductosMasVendidosProperty()
    {
        try {
            return DB::table('facturadetalles as fd')
                ->join('facturas as f', 'fd.idFacDet', '=', 'f.idFac')
                ->where('f.estFac', '!=', 'anulada')
                ->where('f.fecFac', '>=', now()->subDays(30))
                ->selectRaw('fd.conceptoDet, SUM(fd.cantidadDet) as total_vendido, SUM(fd.subtotalDet) as ingresos')
                ->groupBy('fd.conceptoDet')
                ->orderBy('total_vendido', 'desc')
                ->limit(5)
                ->get();
        } catch (\Exception $e) {
            Log::error('Error obteniendo productos más vendidos: ' . $e->getMessage());
            return collect();
        }
    }

    // ===============================================
    // MÉTODOS DE EXPORTACIÓN
    // ===============================================

    /**
     * Exportar facturas en diferentes formatos
     */
    public function exportarFacturas($formato = 'csv')
    {
        try {
            $facturas = $this->facturas;

            if ($formato === 'excel') {
                return $this->exportarExcel($facturas);
            }

            // CSV por defecto
            $filename = 'facturas_famasy_' . date('Y-m-d_H-i-s') . '.csv';

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            $output = fopen('php://output', 'w');

            // BOM para UTF-8
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Encabezados
            fputcsv($output, [
                'ID',
                'Número',
                'Cliente',
                'Tipo Documento',
                'Documento',
                'Fecha',
                'Subtotal',
                'IVA',
                'Descuento',
                'Total',
                'Estado',
                'Método Pago',
                'Vendedor',
                'Observaciones',
                'Fecha Creación'
            ]);

            // Datos
            foreach ($facturas as $factura) {
                fputcsv($output, [
                    $factura->idFac,
                    'FAC-' . $factura->idFac,
                    $factura->nomCliFac,
                    $factura->tipDocCliFac,
                    $factura->docCliFac,
                    date('d/m/Y', strtotime($factura->fecFac)),
                    number_format($factura->subtotalFac, 2),
                    number_format($factura->ivaFac, 2),
                    number_format($factura->descuentoFac ?? 0, 2),
                    number_format($factura->totFac, 2),
                    ucfirst($factura->estFac),
                    $factura->metPagFac ?: 'No especificado',
                    $factura->usuario ? $factura->usuario->nomUsu . ' ' . $factura->usuario->apeUsu : 'N/A',
                    $factura->obsFac ?: '',
                    date('d/m/Y H:i', strtotime($factura->created_at))
                ]);
            }

            fclose($output);
            exit;

        } catch (\Exception $e) {
            Log::error('Error exportando facturas: ' . $e->getMessage());
            session()->flash('error', 'Error al exportar facturas');
        }
    }

    // ===============================================
    // MÉTODOS DE CONTROL DE MODAL
    // ===============================================

    /**
     * Abrir modal principal
     */
    public function abrirModal()
    {
        $this->modalAbierto = true;
        $this->mostrarModal = true;
        $this->resetearCamposModal();
        $this->cargarDatosIniciales();
        $this->cargarProductosGranjaDisponibles();
        $this->cargarAnimalesEnPie(); 
        $this->cargarProveedoresDisponibles();  
        $this->dispatch('modal-opened');

        Log::info('Modal de facturación abierto', ['usuario' => Auth::id()]);
    }

    /**
     * Cerrar modal principal
     */
    public function cerrarModal()
    {
        $this->modalAbierto = false;
        $this->mostrarModal = false;
        $this->resetValidation();
        $this->resetearCamposModal();
        $this->dispatch('modal-closed');

        Log::info('Modal de facturación cerrado', ['usuario' => Auth::id()]);
    }

    /**
     * Cerrar modal de visualización
     */
    public function cerrarModalVer()
    {
        $this->modalVerAbierto = false;
        $this->facturaVer = null;
        $this->detallesFactura = [];
    }

    /**
     * Cambiar tipo de factura
     */
    public function cambiarTipoFactura($tipo)
    {
        $this->tipoFactura = $tipo;
        
        // Limpiar campos específicos según el tipo
        if ($tipo === 'granja') {
            $this->reset([
                'idProveFac', 'proveedorSeleccionado', 'servicioSuministrado',
                'categoriaGasto', 'archivoFacturaProveedor', 'numeroComprobanteProveedor'
            ]);
        } else {
            $this->reset([
                'productoSeleccionado', 'cantidadProducto', 'precioUnitario',
                'categoriaHuevosSeleccionada', 'productosSeleccionados', 'subtotalFac'
            ]);
        }
        
        // Regenerar número de factura
        $this->numero = $this->generarNumeroFactura();
        
        Log::info('Tipo de factura cambiado', ['nuevo_tipo' => $tipo]);
    }

    public function seleccionarTipoFactura($tipo)
    {
        $this->tipoFactura = $tipo;
        
        // Limpiar campos específicos según el tipo
        if ($tipo === 'granja') {
            $this->reset([
                'idProveFac', 'proveedorSeleccionado', 'servicioSuministrado',
                'categoriaGasto', 'archivoFacturaProveedor', 'numeroComprobanteProveedor'
            ]);
        } else {
            $this->reset([
                'productoSeleccionado', 'cantidadProducto', 'precioUnitario',
                'categoriaHuevosSeleccionada', 'productosSeleccionados', 'subtotalFac'
            ]);
        }
        
        // Regenerar número de factura
        $this->numero = $this->generarNumeroFactura();
        
        Log::info('Tipo de factura seleccionado', ['tipo' => $tipo]);
    }

      public function volverSeleccionTipo()
    {
        $this->tipoFactura = null;
        
        // Limpiar todos los campos
        $this->reset([
            'productoSeleccionado', 'cantidadProducto', 'precioUnitario',
            'categoriaHuevosSeleccionada', 'productosSeleccionados', 'subtotalFac',
            'idProveFac', 'proveedorSeleccionado', 'servicioSuministrado',
            'categoriaGasto', 'archivoFacturaProveedor', 'numeroComprobanteProveedor'
        ]);
        
        Log::info('Volviendo a selección de tipo de factura');
    }

     public function validarFactura()
    {
        // Validar que se haya seleccionado un tipo de factura
        if (!$this->tipoFactura) {
            throw new \Exception('Debe seleccionar un tipo de factura');
        }

        // Validaciones específicas por tipo de factura
        if ($this->tipoFactura === 'proveedor') {
            $this->validarFacturaProveedor();
        } else {
            $this->validarFacturaGranja();
        }

        // Validaciones generales
        $this->validarTotalesCoherentes();
        $this->validarFecha();
        $this->validarMontosMinimos();
    }

    /**
     * Resetear campos del modal
     */
    private function resetearCamposModal()
    {
        $this->reset([
            'subtotalFac', 'totFac', 'metPagFac', 'obsFac', 'productosSeleccionados',
            'idCliFac', 'clienteSeleccionado', 'nomCliNuevo', 'tipDocCliNuevo',
            'docCliNuevo', 'telCliNuevo', 'emailCliNuevo', 'dirCliNuevo',
            'nomComprador', 'docComprador', 
            'nomCliFac', 'tipDocCliFac', 'docCliFac', // ← Agregar estas líneas
            'productoSeleccionado', 'cantidadProducto', 'precioUnitario',
            'categoriaHuevosSeleccionada', 'unidadVentaHuevos', 'animalEnPieSeleccionado',
            'idProveFac', 'proveedorSeleccionado', 'servicioSuministrado', 
            'categoriaGasto', 'archivoFacturaProveedor', 'nombreArchivoOriginal',
            'numeroComprobanteProveedor', 
            'aplicarDescuento', 'porcentajeDescuento', 'motivoDescuento', 
            'descuentoFac', 'requiereAprobacion'
        ]);

        $this->ivaFac = $this->configuraciones['iva_predeterminado'] ?? 19;
        $this->subtotalFac = 0;
        $this->totFac = 0;
        $this->fecFac = date('Y-m-d');
        $this->numero = $this->generarNumeroFactura();
        $this->tipoCliente = 'existente';
        $this->tipoFactura = null;
        $this->cantidadProducto = 1;
        $this->unidadVentaHuevos = 'panal';
        $this->unidadLanaSeleccionada = 'kilos';
        $this->productosSeleccionados = [];
    }

    // ===============================================
    // MÉTODOS DE FILTROS Y NAVEGACIÓN
    // ===============================================

    /**
     * Aplicar filtros de búsqueda
     */
    public function aplicarFiltros()
    {
        $this->resetPage();
        $this->calcularEstadisticas();

        Log::info('Filtros aplicados', [
            'estado' => $this->estado,
            'cliente' => $this->cliente_buscar,
            'fecha_desde' => $this->fecha_desde,
            'fecha_hasta' => $this->fecha_hasta
        ]);
    }

    /**
     * Limpiar todos los filtros
     */
    public function limpiarFiltros()
    {
        $this->reset(['estado', 'cliente_buscar', 'fecha_desde', 'fecha_hasta']);
        $this->fecha_desde = now()->startOfMonth()->format('Y-m-d');
        $this->fecha_hasta = now()->endOfMonth()->format('Y-m-d');
        $this->resetPage();
        $this->calcularEstadisticas();

        session()->flash('info', 'Filtros limpiados');
    }

    /**
     * Actualizar cuando cambia items por página
     */
    public function updatedPerPage()
    {
        $this->resetPage();
    }

    // ===============================================
    // MÉTODOS DE AUDITORÍA Y LOGGING
    // ===============================================

    /**
     * Registrar evento en auditoría
     */
    private function registrarAuditoria($operacion, $tabla, $registro, $descripcion)
    {
        if (!$this->auditoria) return;

        try {
            DB::table('auditoria')->insert([
                'idUsuAud' => Auth::id(),
                'usuAud' => Auth::user()->nomUsu . ' ' . Auth::user()->apeUsu,
                'rolAud' => Auth::user()->rol?->nomRol ?? 'Usuario',
                'opeAud' => $operacion,
                'tablaAud' => $tabla,
                'regAud' => $registro,
                'desAud' => $descripcion,
                'ipAud' => request()->ip(),
                'fecAud' => now(),
                'created_at' => now()
            ]);
        } catch (\Exception $e) {
            Log::error('Error registrando auditoría: ' . $e->getMessage());
        }
    }

    // ===============================================
    // MÉTODOS DE DESCUENTOS Y PROMOCIONES
    // ===============================================

    /**
     * Aplicar descuento especial
     */
    public function aplicarDescuentoEspecial()
    {
        if (!$this->permisos['descuentos_especiales']) {
            session()->flash('error', 'No tiene permisos para aplicar descuentos especiales');
            return;
        }

        $this->validate([
            'porcentajeDescuento' => 'required|numeric|min:0.01|max:' . $this->configuraciones['descuento_maximo'],
            'motivoDescuento' => 'required|string|min:10|max:200'
        ]);

        $this->aplicarDescuento = true;
        $this->recalcularTotales();

        session()->flash('success', 'Descuento del ' . $this->porcentajeDescuento . '% aplicado correctamente');

        Log::info('Descuento especial aplicado', [
            'porcentaje' => $this->porcentajeDescuento,
            'motivo' => $this->motivoDescuento,
            'usuario' => Auth::id()
        ]);
    }

    /**
     * Remover descuento aplicado
     */
    public function removerDescuento()
    {
        $this->aplicarDescuento = false;
        $this->porcentajeDescuento = 0;
        $this->motivoDescuento = '';
        $this->descuentoFac = 0;
        
        $this->recalcularTotales();
        
        session()->flash('info', 'Descuento removido');
    }

    // ===============================================
    // MÉTODOS AVANZADOS DE GESTIÓN
    // ===============================================

    /**
     * Duplicar una factura existente
     */
    public function duplicarFactura($facturaId)
    {
        try {
            $factura = Factura::find($facturaId);
            if (!$factura) {
                throw new \Exception('Factura no encontrada');
            }

            // Abrir modal y cargar datos de la factura
            $this->abrirModal();

            // Cargar datos del cliente
            if ($factura->idCliFac) {
                $this->tipoCliente = 'existente';
                $this->seleccionarCliente($factura->idCliFac);
            } else {
                $this->tipoCliente = 'ocasional';
                $this->nomComprador = $factura->nomCliFac;
                $this->docComprador = $factura->docCliFac;
            }

            $this->metPagFac = $factura->metPagFac;
            $this->obsFac = 'DUPLICADA DE FAC-' . $facturaId . ' | ' . $factura->obsFac;

            session()->flash('info', 'Factura cargada para duplicar. Revise los datos antes de guardar.');

            Log::info('Factura duplicada', [
                'factura_original' => $facturaId,
                'usuario' => Auth::id()
            ]);

        } catch (\Exception $e) {
            Log::error('Error duplicando factura: ' . $e->getMessage());
            session()->flash('error', 'Error al duplicar la factura');
        }
    }

    public function descargarFacturaPDF($facturaId)
{
    return $this->redirect(route('contabilidad.facturas.pdf', $facturaId));
}

    // ===============================================
    // MÉTODOS FINALES Y DESTRUCTOR
    // ===============================================

    /**
     * Método de limpieza cuando se actualiza un valor
     */
 public function updated($propertyName)
    {
        // Recalcular totales automáticamente cuando cambian valores importantes
        if (in_array($propertyName, ['ivaFac', 'porcentajeDescuento'])) {
            $this->recalcularTotales();
        }

        // Limpiar validaciones específicas
        if (str_starts_with($propertyName, 'productosSeleccionados')) {
            $this->recalcularTotales();
        }
    }

}; // ✅ FIN DEL COMPONENTE VOLT
?>


@section('title', 'Gestión Integral de Facturas FAMASY')

<div class="w-full px-6 py-6 mx-auto">
    
    <!-- =============================================== -->
    <!-- SECCIÓN 1: FLASH MESSAGES MEJORADOS            -->
    <!-- =============================================== -->
    @if (session()->has('success'))
    <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg flex items-center">
        <i class="fas fa-check-circle mr-3 text-xl"></i>
        <div>
            <strong>¡Éxito!</strong>
            <p class="mt-1">{{ session('success') }}</p>
        </div>
    </div>
    @endif

    @if (session()->has('error'))
    <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg flex items-center">
        <i class="fas fa-exclamation-circle mr-3 text-xl"></i>
        <div>
            <strong>Error</strong>
            <p class="mt-1">{{ session('error') }}</p>
        </div>
    </div>
    @endif

    @if (session()->has('warning'))
    <div class="mb-4 p-4 bg-yellow-100 border border-yellow-400 text-yellow-700 rounded-lg flex items-center">
        <i class="fas fa-exclamation-triangle mr-3 text-xl"></i>
        <div>
            <strong>Advertencia</strong>
            <p class="mt-1">{{ session('warning') }}</p>
        </div>
    </div>
    @endif

    @if (session()->has('info'))
    <div class="mb-4 p-4 bg-blue-100 border border-blue-400 text-blue-700 rounded-lg flex items-center">
        <i class="fas fa-info-circle mr-3 text-xl"></i>
        <div>
            <strong>Información</strong>
            <p class="mt-1">{{ session('info') }}</p>
        </div>
    </div>
    @endif

    <!-- =============================================== -->
    <!-- SECCIÓN 2: HEADER PRINCIPAL CON NAVEGACIÓN     -->
    <!-- =============================================== -->
    <div class="flex flex-wrap -mx-3 mb-6">
        <div class="w-full px-3">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center">
                
                <!-- Título y navegación -->
                <div class="mb-4 lg:mb-0">
                    <nav class="text-sm text-gray-600 mb-2">
                        <a href="{{ route('contabilidad.index') }}" wire:navigate class="hover:text-blue-600">Dashboard</a>
                        <span class="mx-2">/</span>
                        <span class="text-gray-900">Facturas y Ventas</span>
                    </nav>
                    <h1 class="text-3xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-file-invoice mr-3 text-green-600"></i>
                        Sistema FAMASY de Facturación
                    </h1>
                    <p class="text-gray-600">Gestión integral agropecuaria con trazabilidad completa</p>
                </div>
                
                <!-- Botones de acción principales -->
                <div class="flex flex-wrap gap-3">
                    <button wire:click="abrirModal"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center transition duration-200 shadow-lg">
                        <i class="fas fa-plus mr-2"></i> Nueva Factura
                    </button>
                    <button wire:click="exportarFacturas('csv')"
                        class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg flex items-center transition duration-200 shadow-lg">
                        <i class="fas fa-download mr-2"></i> Exportar
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- =============================================== -->
    <!-- SECCIÓN 3: DASHBOARD DE ESTADÍSTICAS           -->
    <!-- =============================================== -->
    <div class="flex flex-wrap -mx-3 mb-6">
        
        <!-- Card 1: Total Facturado -->
        <div class="w-full md:w-1/4 px-3 mb-6">
            <div class="bg-white shadow-lg rounded-lg p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-green-600 uppercase tracking-wide mb-1">Total Facturado</p>
                        <p class="text-2xl font-bold text-gray-800">${{ number_format($estadisticas['total_facturado'] ?? 0, 2) }}</p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-dollar-sign text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Card 2: Por Cobrar -->
        <div class="w-full md:w-1/4 px-3 mb-6">
            <div class="bg-white shadow-lg rounded-lg p-6 border-l-4 border-red-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-red-600 uppercase tracking-wide mb-1">Por Cobrar</p>
                        <p class="text-2xl font-bold text-gray-800">${{ number_format($estadisticas['por_cobrar'] ?? 0, 2) }}</p>
                    </div>
                    <div class="bg-red-100 p-3 rounded-full">
                        <i class="fas fa-clock text-red-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Card 3: Ventas Hoy -->
        <div class="w-full md:w-1/4 px-3 mb-6">
            <div class="bg-white shadow-lg rounded-lg p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-blue-600 uppercase tracking-wide mb-1">Ventas Hoy</p>
                        <p class="text-2xl font-bold text-gray-800">${{ number_format($estadisticas['ventas_hoy'] ?? 0, 2) }}</p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-calendar-day text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Card 4: Clientes Activos -->
        <div class="w-full md:w-1/4 px-3 mb-6">
            <div class="bg-white shadow-lg rounded-lg p-6 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-purple-600 uppercase tracking-wide mb-1">Clientes Activos</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $estadisticas['clientes_activos'] ?? 0 }}</p>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-full">
                        <i class="fas fa-users text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- =============================================== -->
    <!-- SECCIÓN 4: SISTEMA DE PESTAÑAS                 -->
    <!-- =============================================== -->
    <div class="mb-6">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8">
                
                <!-- Pestaña: Facturas de Venta -->
                <button wire:click="$set('vistaActiva', 'ventas')"
                    class="py-2 px-1 border-b-2 font-medium text-sm transition-colors duration-200 
                           {{ $vistaActiva === 'ventas' ? 'border-green-500 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <i class="fas fa-store mr-2"></i>
                    Facturas de Venta ({{ $estadisticas['facturas_venta'] ?? 0 }})
                </button>
                
                <!-- Pestaña: Facturas de Proveedores -->
                <button wire:click="$set('vistaActiva', 'compras')"
                    class="py-2 px-1 border-b-2 font-medium text-sm transition-colors duration-200
                           {{ $vistaActiva === 'compras' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <i class="fas fa-truck mr-2"></i>
                    Facturas de Proveedores ({{ $estadisticas['facturas_compra'] ?? 0 }})
                </button>
            </nav>
        </div>
    </div>

    <!-- =============================================== -->
    <!-- SECCIÓN 5: FILTROS DE BÚSQUEDA                 -->
    <!-- =============================================== -->
    <div class="flex flex-wrap -mx-3 mb-6">
        <div class="w-full px-3">
            <div class="bg-white shadow-lg rounded-lg p-6">
                
                <!-- Título de filtros -->
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-filter mr-2 text-green-600"></i>Filtros de Búsqueda
                </h3>
                
                <!-- Grid de filtros -->
                <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                    
                    <!-- Filtro: Estado -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                        <select wire:model="estado" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500 transition-colors">
                            <option value="">Todos</option>
                            <option value="emitida">Emitidas</option>
                            <option value="pagada">Pagadas</option>
                            <option value="pendiente">Pendientes</option>
                            <option value="anulada">Anuladas</option>
                        </select>
                    </div>
                    
                    <!-- Filtro: Cliente -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Cliente</label>
                        <input type="text" wire:model="cliente_buscar" placeholder="Buscar cliente..."
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500 transition-colors">
                    </div>
                    
                    <!-- Filtro: Fecha Desde -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Desde</label>
                        <input type="date" wire:model="fecha_desde"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500 transition-colors">
                    </div>
                    
                    <!-- Filtro: Fecha Hasta -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Hasta</label>
                        <input type="date" wire:model="fecha_hasta"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500 transition-colors">
                    </div>
                    
                    <!-- Filtro: Por página -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Por página</label>
                        <select wire:model="per_page" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500 transition-colors">
                            <option value="15">15</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                    
                    <!-- Botones de acción -->
                    <div class="flex items-end space-x-2">
                        <button wire:click="aplicarFiltros" 
                            class="flex-1 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition duration-200 flex items-center justify-center">
                            <i class="fas fa-search mr-2"></i> Filtrar
                        </button>
                        <button wire:click="limpiarFiltros" 
                            class="bg-gray-500 hover:bg-gray-600 text-white px-3 py-2 rounded-lg transition duration-200">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- =============================================== -->
    <!-- SECCIÓN 6: CONTENIDO CONDICIONAL POR PESTAÑAS  -->
    <!-- =============================================== -->
    
    <!-- ✅ PESTAÑA: FACTURAS DE VENTA -->
    @if($vistaActiva === 'ventas')
        <div class="flex flex-wrap -mx-3">
            <div class="w-full px-3">
                <div class="bg-white shadow-lg rounded-lg">
                    
                    <!-- Header de la tabla de ventas -->
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <div>
                                <h6 class="text-lg font-semibold text-gray-800">
                                    <i class="fas fa-receipt mr-2 text-green-600"></i>
                                    Facturas de Venta - Productos de Granja
                                </h6>
                                <p class="text-sm text-gray-600">{{ $this->facturas->total() }} facturas encontradas</p>
                            </div>
                            <div class="flex space-x-2">
                                <button wire:click="exportarFacturas" 
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition duration-200">
                                    <i class="fas fa-file-excel mr-1"></i> Exportar Excel
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tabla de facturas de venta -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Número</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($this->facturas as $factura)
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <!-- Número de factura -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        FAC-{{ $factura->idFac }}
                                    </td>
                                    
                                    <!-- Información del cliente -->
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <div>
                                            <div class="font-medium">{{ $factura->nomCliFac }}</div>
                                            <div class="text-gray-500">{{ $factura->tipDocCliFac }}: {{ $factura->docCliFac }}</div>
                                        </div>
                                    </td>
                                    
                                    <!-- Fecha -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ date('d/m/Y', strtotime($factura->fecFac)) }}
                                    </td>
                                    
                                    <!-- Monto -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        ${{ number_format($factura->totFac, 2) }}
                                    </td>
                                    
                                    <!-- Estado con badge dinámico -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                                   {{ $factura->estFac == 'pagada' ? 'bg-green-100 text-green-800' : 
                                                      ($factura->estFac == 'anulada' ? 'bg-red-100 text-red-800' : 
                                                      ($factura->estFac == 'pendiente' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800')) }}">
                                            {{ ucfirst($factura->estFac) }}
                                        </span>
                                    </td>
                                    
                                    <!-- Acciones -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
    <div class="flex space-x-1">
        <button wire:click="verFactura({{ $factura->idFac }})"
            class="text-blue-600 hover:text-blue-900 p-1 rounded hover:bg-blue-100 transition duration-200"
            title="Ver detalles">
            <i class="fas fa-eye"></i>
        </button>
        
        <!-- NUEVO BOTÓN PDF -->
        <button wire:click="descargarFacturaPDF({{ $factura->idFac }})"
            class="text-green-600 hover:text-green-900 p-1 rounded hover:bg-green-100 transition duration-200"
            title="Descargar PDF">
            <i class="fas fa-file-pdf"></i>
        </button>
        
        <button wire:click="eliminarFactura({{ $factura->idFac }})"
            wire:confirm="¿Estás seguro de eliminar esta factura?"
            class="text-red-600 hover:text-red-900 p-1 rounded hover:bg-red-100 transition duration-200"
            title="Eliminar">
            <i class="fas fa-trash"></i>
        </button>
    </div>
</td>
                                
                                <!-- Estado vacío para facturas de venta -->
                                @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                        <div class="py-8">
                                            <i class="fas fa-file-invoice text-4xl text-gray-300 mb-4"></i>
                                            <p class="text-lg font-medium mb-2">No hay facturas de venta registradas</p>
                                            <p class="text-sm text-gray-400 mb-4">Comienza creando tu primera factura de productos de granja</p>
                                            <button wire:click="abrirModal"
                                                class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg transition duration-200">
                                                <i class="fas fa-plus mr-2"></i> Crear Primera Factura
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Paginación para facturas de venta -->
                    @if($this->facturas->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $this->facturas->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>

    <!-- ✅ PESTAÑA: FACTURAS DE PROVEEDORES -->
    @else
        <div class="flex flex-wrap -mx-3">
            <div class="w-full px-3">
                <div class="bg-white shadow-lg rounded-lg">
                    
                    <!-- Header de la tabla de proveedores -->
                    <div class="p-6 border-b border-gray-200">
                        <h6 class="text-lg font-semibold text-gray-800">
                            <i class="fas fa-file-invoice mr-2 text-blue-600"></i>
                            Facturas de Proveedores - Compras y Servicios
                        </h6>
                    </div>
                    
                    <!-- Tabla de facturas de proveedores -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proveedor</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Servicio</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($facturasProveedores as $factura)
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <!-- ID -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        CG-{{ $factura->idComGas }}
                                    </td>
                                    
                                    <!-- Proveedor -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $factura->provComGas }}</div>
                                        <div class="text-sm text-gray-500">{{ $factura->catComGas }}</div>
                                    </td>
                                    
                                    <!-- Servicio -->
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900 max-w-xs truncate">{{ $factura->desComGas }}</div>
                                    </td>
                                    
                                    <!-- Fecha -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ date('d/m/Y', strtotime($factura->fecComGas)) }}
                                    </td>
                                    
                                    <!-- Monto -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        ${{ number_format($factura->monComGas, 2) }}
                                    </td>
                                    
                                    <!-- Estado -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            {{ ucfirst($factura->tipComGas) }}
                                        </span>
                                    </td>
                                    
                                    <!-- Acciones -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        @if($factura->docComGas)
                                        <a href="{{ Storage::url($factura->docComGas) }}" target="_blank" 
                                           class="text-blue-600 hover:text-blue-900 mr-3" title="Ver documento">
                                            <i class="fas fa-file-image"></i>
                                        </a>
                                        @endif
                                        <button class="text-green-600 hover:text-green-900 mr-3" title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                
                                <!-- Estado vacío para facturas de proveedores -->
                                @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                        <div class="py-8">
                                            <i class="fas fa-truck text-4xl text-gray-300 mb-4"></i>
                                            <p class="text-lg font-medium mb-2">No hay facturas de proveedores registradas</p>
                                            <p class="text-sm text-gray-400 mb-4">Registra tu primera factura de proveedor</p>
                                            <button wire:click="abrirModal"
                                                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition duration-200">
                                                <i class="fas fa-plus mr-2"></i> Registrar Factura Proveedor
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <!-- =============================================== -->
    <!-- SECCIÓN 7: MODAL PRINCIPAL DEL SISTEMA         -->
    <!-- =============================================== -->
    
    @if($modalAbierto)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        
        <!-- Overlay del modal -->
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            
            <!-- Centrado del modal -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            
            <!-- Contenido del modal -->
            <div class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-6xl sm:w-full">
                
                <!-- Header del modal -->
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fas fa-file-invoice text-green-600"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    {{ $tipoFactura === 'proveedor' ? 'Registrar Factura de Proveedor' : 'Nueva Factura de Venta' }}
                                </h3>
                                <p class="text-sm text-gray-500">
                                    {{ $tipoFactura === 'proveedor' ? 'Registra facturas de proveedores y servicios externos' : 'Crea facturas para productos derivados de la granja' }}
                                </p>
                            </div>
                        </div>
                        
                        <!-- Botón cerrar -->
                        <button type="button" wire:click="cerrarModal"
                            class="bg-white rounded-md text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <span class="sr-only">Cerrar</span>
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>

                    <!-- =============================================== -->
                    <!-- SELECTOR DE TIPO DE FACTURA                    -->
                    <!-- =============================================== -->
                    
                    @if(!$tipoFactura)
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4 text-center">
                            <i class="fas fa-route mr-2"></i>Selecciona el Tipo de Factura
                        </h4>
                        <p class="text-sm text-gray-600 text-center mb-6">Elige qué tipo de factura deseas crear en el sistema FAMASY</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <!-- Opción: Productos de Granja -->
                            <div wire:click="seleccionarTipoFactura('granja')"
                                class="cursor-pointer bg-white border-2 border-gray-200 rounded-lg p-6 hover:border-green-400 hover:shadow-lg transition-all duration-200 group">
                                <div class="text-center">
                                    <div class="mx-auto w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4 group-hover:bg-green-200 transition-colors">
                                        <i class="fas fa-leaf text-green-600 text-2xl"></i>
                                    </div>
                                    <h5 class="text-lg font-semibold text-gray-800 mb-2">Productos de Granja</h5>
                                    <p class="text-sm text-gray-600 mb-4">Venta de productos derivados de la producción animal</p>
                                    
                                    <!-- Lista de productos incluidos -->
                                    <div class="text-xs text-gray-500 space-y-1">
                                        <div class="flex items-center justify-center">
                                            <i class="fas fa-egg mr-2 text-amber-500"></i>
                                            <span>Huevos (A, AA, AAA, YUMBO)</span>
                                        </div>
                                        <div class="flex items-center justify-center">
                                            <i class="fas fa-glass-whiskey mr-2 text-blue-500"></i>
                                            <span>Leche (Bovina y Ovina)</span>
                                        </div>
                                        <div class="flex items-center justify-center">
                                            <i class="fas fa-cut mr-2 text-gray-500"></i>
                                            <span>Lana (Kilos, Libras, Gramos)</span>
                                        </div>
                                        <div class="flex items-center justify-center">
                                            <i class="fas fa-cow mr-2 text-brown-500"></i>
                                            <span>Animales en Pie</span>
                                        </div>
                                    </div>
                                    
                                    <button wire:click="seleccionarTipoFactura('granja')"  class="mt-4 w-full bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg transition duration-200 flex items-center justify-center">
                                        <i class="fas fa-arrow-right mr-2"></i>
                                        Crear Factura de Venta
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Opción: Factura de Proveedor -->
                            <div wire:click="seleccionarTipoFactura('proveedor')"
                                class="cursor-pointer bg-white border-2 border-gray-200 rounded-lg p-6 hover:border-blue-400 hover:shadow-lg transition-all duration-200 group">
                                <div class="text-center">
                                    <div class="mx-auto w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-4 group-hover:bg-blue-200 transition-colors">
                                        <i class="fas fa-truck text-blue-600 text-2xl"></i>
                                    </div>
                                    <h5 class="text-lg font-semibold text-gray-800 mb-2">Factura de Proveedor</h5>
                                    <p class="text-sm text-gray-600 mb-4">Registro de compras y servicios externos</p>
                                    
                                    <!-- Lista de servicios incluidos -->
                                    <div class="text-xs text-gray-500 space-y-1">
                                        <div class="flex items-center justify-center">
                                            <i class="fas fa-seedling mr-2 text-green-500"></i>
                                            <span>Insumos Agropecuarios</span>
                                        </div>
                                        <div class="flex items-center justify-center">
                                            <i class="fas fa-stethoscope mr-2 text-red-500"></i>
                                            <span>Servicios Veterinarios</span>
                                        </div>
                                        <div class="flex items-center justify-center">
                                            <i class="fas fa-tools mr-2 text-orange-500"></i>
                                            <span>Mantenimiento y Reparaciones</span>
                                        </div>
                                        <div class="flex items-center justify-center">
                                            <i class="fas fa-shipping-fast mr-2 text-purple-500"></i>
                                            <span>Transporte y Logística</span>
                                        </div>
                                    </div>
                                    
                                    <button wire:click="seleccionarTipoFactura('proveedor')" class="mt-4 w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg transition duration-200 flex items-center justify-center">
                                        <i class="fas fa-arrow-right mr-2"></i>
                                        Registrar Factura Proveedor
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Botón de cancelar -->
                        <div class="mt-6 text-center">
                            <button type="button" wire:click="cerrarModal"
                                class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition duration-200">
                                <i class="fas fa-times mr-2"></i>Cancelar
                            </button>
                        </div>
                    </div>
                    @endif

                    <!-- =============================================== -->
                    <!-- FORMULARIO PRINCIPAL (CUANDO YA SE SELECCIONÓ) -->
                    <!-- =============================================== -->
                    
                    @if($tipoFactura)
                    <form wire:submit.prevent="guardarFactura" class="space-y-6">
                        
                        <!-- Información del cliente (para facturas de granja) -->
                        @if($tipoFactura === 'granja')
                        <div class="bg-white border border-gray-200 rounded-lg p-4">
                            <h4 class="text-md font-semibold text-gray-800 mb-3">
                                <i class="fas fa-user mr-2 text-green-600"></i>Información del Cliente
                            </h4>
                            
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Nombre Completo *</label>
                                    <input type="text" wire:model="nomCliFac" 
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                                        placeholder="Nombre del cliente">
                                    @error('nomCliFac') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipo Documento *</label>
                                    <select wire:model="tipDocCliFac" 
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                                        <option value="">Seleccionar...</option>
                                        <option value="CC">Cédula de Ciudadanía</option>
                                        <option value="NIT">NIT</option>
                                        <option value="CE">Cédula de Extranjería</option>
                                        <option value="PAS">Pasaporte</option>
                                    </select>
                                    @error('tipDocCliFac') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Número Documento *</label>
                                    <input type="text" wire:model="docCliFac" 
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                                        placeholder="Número de documento">
                                    @error('docCliFac') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        @endif
                        <!-- =============================================== -->
                        <!-- SECCIÓN 8: PRODUCTOS DE GRANJA                 -->
                        <!-- =============================================== -->
                        
                        @if($tipoFactura === 'granja')
                        <div class="bg-green-50 p-4 rounded-lg">
                            <h4 class="text-md font-semibold text-gray-800 mb-3">
                                <i class="fas fa-leaf mr-2 text-green-600"></i>Productos Derivados de Producción Animal
                            </h4>

                            <!-- ✅ SELECTOR DE TIPO DE PRODUCTO -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Producto</label>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <label class="inline-flex items-center p-3 border rounded-lg cursor-pointer transition-all duration-200 {{ $tipoProductoGranja === 'productos' ? 'bg-green-100 border-green-500 shadow-md' : 'border-gray-200 hover:border-green-300' }}">
                                        <input type="radio" 
                                               wire:click="$set('tipoProductoGranja', 'productos')" 
                                               @if($tipoProductoGranja === 'productos') checked @endif 
                                               class="form-radio text-green-600">
                                        <div class="ml-3">
                                            <div class="flex items-center">
                                                <i class="fas fa-egg mr-2 text-green-600"></i>
                                                <span class="text-sm font-medium">Productos Derivados</span>
                                            </div>
                                            <p class="text-xs text-gray-500">Huevos, Leche, Lana</p>
                                        </div>
                                    </label>
                                    
                                    <label class="inline-flex items-center p-3 border rounded-lg cursor-pointer transition-all duration-200 {{ $tipoProductoGranja === 'animales_pie' ? 'bg-blue-100 border-blue-500 shadow-md' : 'border-gray-200 hover:border-blue-300' }}">
                                        <input type="radio" 
                                               wire:click="$set('tipoProductoGranja', 'animales_pie')" 
                                               @if($tipoProductoGranja === 'animales_pie') checked @endif 
                                               class="form-radio text-blue-600">
                                        <div class="ml-3">
                                            <div class="flex items-center">
                                                <i class="fas fa-cow mr-2 text-blue-600"></i>
                                                <span class="text-sm font-medium">Animales en Pie</span>
                                            </div>
                                            <p class="text-xs text-gray-500">Bovinos, Ovinos, Aves</p>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- =============================================== -->
                            <!-- PRODUCTOS DERIVADOS (HUEVOS, LECHE, LANA)      -->
                            <!-- =============================================== -->
                            
                            @if($tipoProductoGranja === 'productos')
                            <div class="grid grid-cols-1 md:grid-cols-6 gap-4 mb-4">
                                
                                <!-- Selector de producto -->
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Producto de Granja</label>
                                    <select wire:change="seleccionarProductoGranja($event.target.value)"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                                        <option value="">Seleccionar producto...</option>
                                        @foreach($productosGranjaDisponibles as $producto)
                                        <option value="{{ $producto->idIns }}" @if($productoSeleccionado == $producto->idIns) selected @endif>
                                            {{ $producto->nombre_completo }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- ✅ CAMPOS ESPECÍFICOS PARA HUEVOS -->
                                @if($productoSeleccionado && $productosGranjaDisponibles->firstWhere('idIns', $productoSeleccionado)?->tipIns === 'huevos')
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Categoría * 
                                        <i class="fas fa-info-circle text-gray-400 ml-1" title="Clasificación por tamaño y calidad"></i>
                                    </label>
                                    <select wire:model="categoriaHuevosSeleccionada"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                                        <option value="">Seleccionar...</option>
                                        <option value="A">Categoría A - Estándar</option>
                                        <option value="AA">Categoría AA - Premium</option>
                                        <option value="AAA">Categoría AAA - Extra</option>
                                        <option value="YUMBO">Categoría YUMBO - Jumbo</option>
                                    </select>
                                    @error('categoriaHuevosSeleccionada') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Unidad Venta</label>
                                    <select wire:model="unidadVentaHuevos"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                                        <option value="panal">Panal (30 unidades)</option>
                                        <option value="cubeta">Cubeta (30 unidades)</option>
                                        <option value="unidad">Por Unidad</option>
                                    </select>
                                </div>
                                
                                <!-- ✅ CAMPOS ESPECÍFICOS PARA LANA -->
                                @elseif($productoSeleccionado && $productosGranjaDisponibles->firstWhere('idIns', $productoSeleccionado)?->tipIns === 'lana')
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Unidad de Venta *
                                        <i class="fas fa-info-circle text-gray-400 ml-1" title="Peso de la lana"></i>
                                    </label>
                                    <select wire:model="unidadLanaSeleccionada"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                                        <option value="kilos">Kilogramos (Kg)</option>
                                        <option value="libras">Libras (Lb)</option>
                                        <option value="gramos">Gramos (g)</option>
                                    </select>
                                </div>
                                <div class="flex items-center justify-center">
                                    <div class="bg-gray-100 rounded-lg p-2">
                                        <i class="fas fa-cut text-gray-500 text-lg"></i>
                                    </div>
                                </div>
                                
                                <!-- ✅ CAMPOS ESPECÍFICOS PARA LECHE -->
                                @elseif($productoSeleccionado && ($productosGranjaDisponibles->firstWhere('idIns', $productoSeleccionado)?->tipIns === 'leche_bovina' || $productosGranjaDisponibles->firstWhere('idIns', $productoSeleccionado)?->tipIns === 'leche_ovina'))
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Unidad</label>
                                    <input type="text" value="Litros" readonly
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-100 text-gray-600">
                                </div>
                                <div class="flex items-center justify-center">
                                    <div class="bg-blue-100 rounded-lg p-2">
                                        <i class="fas fa-glass-whiskey text-blue-500 text-lg"></i>
                                    </div>
                                </div>
                                
                                <!-- ESTADO PARA OTROS PRODUCTOS -->
                                @else
                                <div class="col-span-2 flex items-center justify-center">
                                    @if($productoSeleccionado)
                                    <div class="text-center text-gray-500">
                                        <i class="fas fa-box text-2xl mb-2"></i>
                                        <p class="text-sm">Producto estándar seleccionado</p>
                                    </div>
                                    @else
                                    <div class="text-center text-gray-400">
                                        <i class="fas fa-arrow-left text-2xl mb-2"></i>
                                        <p class="text-sm">Selecciona un producto</p>
                                    </div>
                                    @endif
                                </div>
                                @endif
                                
                                <!-- Cantidad -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Cantidad *
                                        @if($productoSeleccionado)
                                        <span class="text-xs text-gray-500">
                                            (Stock: {{ $productosGranjaDisponibles->firstWhere('idIns', $productoSeleccionado)?->stock_actual ?? 0 }})
                                        </span>
                                        @endif
                                    </label>
                                    <input type="number" wire:model="cantidadProducto" min="0.01" step="0.01"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                                        placeholder="Cantidad">
                                    @error('cantidadProducto') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <!-- Precio unitario -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Precio Unitario *</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-2 text-gray-500">$</span>
                                        <input type="number" wire:model="precioUnitario" min="0" step="0.01"
                                            class="w-full border border-gray-300 rounded-lg pl-8 pr-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                                            placeholder="0.00">
                                    </div>
                                    @error('precioUnitario') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <!-- Botón agregar producto -->
                                <div class="flex items-end">
                                    <button type="button" wire:click="agregarProductoGranja"
                                        class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition duration-200 flex items-center justify-center">
                                        <i class="fas fa-plus mr-2"></i>Agregar
                                    </button>
                                </div>
                            </div>
                            @endif

                            <!-- =============================================== -->
                            <!-- ANIMALES EN PIE                                -->
                            <!-- =============================================== -->
                            
                            @if($tipoProductoGranja === 'animales_pie')
                            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4">
                                
                                <!-- Selector de animal -->
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Animal en Pie</label>
                                    <select wire:change="seleccionarAnimalEnPie($event.target.value)"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">Seleccionar animal...</option>
                                        @foreach($animalesEnPieDisponibles as $animal)
                                        <option value="{{ $animal->idAni }}">
                                            {{ $animal->nombre_completo }}
                                        </option>
                                        @endforeach
                                    </select>
                                    
                                    @if($animalesEnPieDisponibles->isEmpty())
                                    <div class="mt-2 p-2 bg-orange-100 border border-orange-200 rounded-lg">
                                        <p class="text-sm text-orange-600 flex items-center">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            No hay animales disponibles para venta
                                        </p>
                                    </div>
                                    @endif
                                </div>
                                
                                <!-- Cantidad (fija en 1) -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Cantidad *
                                        <i class="fas fa-info-circle text-gray-400 ml-1" title="Solo se puede vender un animal a la vez"></i>
                                    </label>
                                    <input type="number" wire:model="cantidadProducto" value="1" min="1" max="1" readonly
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-100 text-center font-medium"
                                        title="Solo se puede vender un animal a la vez">
                                </div>
                                
                                <!-- Precio total del animal -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Precio Total *</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-2 text-gray-500">$</span>
                                        <input type="number" wire:model="precioUnitario" min="0" step="1000"
                                            class="w-full border border-gray-300 rounded-lg pl-8 pr-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            placeholder="Precio del animal">
                                    </div>
                                    @error('precioUnitario') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <!-- Botón agregar animal -->
                                <div class="flex items-end">
                                    <button type="button" wire:click="agregarAnimalEnPie"
                                        class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-200 flex items-center justify-center">
                                        <i class="fas fa-plus mr-2"></i>Agregar Animal
                                    </button>
                                </div>
                            </div>
                            @endif
                            <!-- =============================================== -->
                            <!-- LISTA DE PRODUCTOS SELECCIONADOS               -->
                            <!-- =============================================== -->
                            
                            @if(!empty($productosSeleccionados))
                            <div class="mt-6">
                                <div class="flex items-center justify-between mb-3">
                                    <h5 class="text-sm font-medium text-gray-700 flex items-center">
                                        <i class="fas fa-list mr-2 text-green-600"></i>
                                        Productos Seleccionados ({{ count($productosSeleccionados) }})
                                    </h5>
                                    <button type="button" wire:click="limpiarProductosSeleccionados" 
                                        class="text-red-600 hover:text-red-800 text-xs">
                                        <i class="fas fa-trash mr-1"></i>Limpiar todo
                                    </button>
                                </div>
                                
                                <div class="overflow-x-auto bg-white rounded-lg border border-gray-200">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio Unit.</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($productosSeleccionados as $index => $producto)
                                            <tr class="hover:bg-gray-50 transition duration-150">
                                                <!-- Nombre del producto -->
                                                <td class="px-4 py-3 text-sm">
                                                    <div class="font-medium text-gray-900">{{ $producto['nombre'] }}</div>
                                                    @if(!empty($producto['descripcion']))
                                                    <div class="text-gray-500 text-xs">{{ $producto['descripcion'] }}</div>
                                                    @endif
                                                </td>
                                                
                                                <!-- Tipo con badge -->
                                                <td class="px-4 py-3 text-xs">
                                                    <span class="px-2 py-1 rounded-full text-xs font-medium
                                                           {{ $producto['tipo'] === 'animal_pie' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                                        @if($producto['tipo'] === 'animal_pie')
                                                            <i class="fas fa-cow mr-1"></i>Animal en Pie
                                                        @else
                                                            <i class="fas fa-leaf mr-1"></i>Producto
                                                        @endif
                                                    </span>
                                                </td>
                                                
                                                <!-- Cantidad con unidad -->
                                                <td class="px-4 py-3 text-sm">
                                                    <span class="font-medium">{{ $producto['cantidad'] }}</span>
                                                    <span class="text-gray-500 ml-1">{{ $producto['unidad'] }}</span>
                                                </td>
                                                
                                                <!-- Precio unitario -->
                                                <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                                    ${{ number_format($producto['precio_unitario'], 0) }}
                                                </td>
                                                
                                                <!-- Subtotal -->
                                                <td class="px-4 py-3 text-sm font-bold text-green-600">
                                                    ${{ number_format($producto['subtotal'], 0) }}
                                                </td>
                                                
                                                <!-- Botón eliminar -->
                                                <td class="px-4 py-3 text-sm">
                                                    <button type="button" wire:click="eliminarProducto({{ $index }})"
                                                        class="text-red-600 hover:text-red-900 hover:bg-red-100 p-1 rounded transition duration-200"
                                                        title="Eliminar producto">
                                                        <i class="fas fa-trash text-xs"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @endif

                            <!-- =============================================== -->
                            <!-- CÁLCULO DE TOTALES CON IVA                     -->
                            <!-- =============================================== -->
                            
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mt-6">
                                <h4 class="text-md font-semibold text-gray-800 mb-3 flex items-center">
                                    <i class="fas fa-calculator mr-2 text-green-600"></i>
                                    Totales de la Factura
                                </h4>
                                
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <!-- Subtotal (calculado automáticamente) -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Subtotal</label>
                                        <div class="relative">
                                            <span class="absolute left-3 top-2 text-gray-500 font-bold">$</span>
                                            <input type="text" value="{{ number_format($subtotalFac, 0) }}" readonly
                                                class="w-full border border-gray-300 rounded-lg pl-8 pr-3 py-2 bg-gray-100 text-lg font-bold text-green-600">
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1">Suma de todos los productos</p>
                                    </div>
                                    
                                    <!-- IVA (editable) -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">IVA (%)</label>
                                        <input type="number" step="0.01" wire:model="ivaFac" wire:blur="recalcularTotales"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                                            placeholder="0.00">
                                        <p class="text-xs text-gray-500 mt-1">Porcentaje de IVA aplicable</p>
                                    </div>
                                    
                                    <!-- Total final -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Total Final</label>
                                        <div class="relative">
                                            <span class="absolute left-3 top-2 text-gray-500 font-bold">$</span>
                                            <input type="text" value="{{ number_format($totFac, 0) }}" readonly
                                                class="w-full border border-gray-300 rounded-lg pl-8 pr-3 py-2 bg-green-100 text-xl font-bold text-green-600">
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1">Subtotal + IVA</p>
                                    </div>
                                </div>
                            </div>

                            <!-- =============================================== -->
                            <!-- MÉTODO DE PAGO PARA GRANJA                     -->
                            <!-- =============================================== -->
                            
                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Método de Pago *</label>
                                <select wire:model="metPagFac"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                                    <option value="">Seleccionar método...</option>
                                    <option value="efectivo">💵 Efectivo</option>
                                    <option value="transferencia">🏦 Transferencia Bancaria</option>
                                    <option value="tarjeta_credito">💳 Tarjeta de Crédito</option>
                                    <option value="credito">📝 Crédito (30 días)</option>
                                </select>
                                @error('metPagFac') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        @endif

                        <!-- =============================================== -->
                        <!-- FORMULARIO PARA FACTURAS DE PROVEEDORES        -->
                        <!-- =============================================== -->
                        
                        @if($tipoFactura === 'proveedor')
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h4 class="text-md font-semibold text-gray-800 mb-3">
                                <i class="fas fa-file-invoice mr-2 text-blue-600"></i>Factura de Proveedor/Servicio
                            </h4>

                            <!-- Información del proveedor -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Proveedor *</label>
                                    <select wire:model="idProveFac" wire:change="seleccionarProveedor($event.target.value)"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">Seleccionar proveedor...</option>
                                        @foreach($proveedoresDisponibles as $proveedor)
                                        <option value="{{ $proveedor->idProve }}">
                                            {{ $proveedor->nomProve }}
                                            @if($proveedor->tipSumProve) - {{ $proveedor->tipSumProve }} @endif
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('idProveFac') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Categoría de Gasto</label>
                                    <select wire:model="categoriaGasto"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">Seleccionar categoría...</option>
                                        <option value="Insumos Agropecuarios">🌱 Insumos Agropecuarios</option>
                                        <option value="Servicios Veterinarios">🩺 Servicios Veterinarios</option>
                                        <option value="Mantenimiento y Reparaciones">🔧 Mantenimiento y Reparaciones</option>
                                        <option value="Servicios Profesionales">👨‍💼 Servicios Profesionales</option>
                                        <option value="Transporte y Logística">🚛 Transporte y Logística</option>
                                        <option value="Servicios Públicos">⚡ Servicios Públicos</option>
                                        <option value="Otros Gastos">📦 Otros Gastos</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Descripción del servicio/producto -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Descripción del Servicio/Producto *</label>
                                <textarea wire:model="servicioSuministrado" rows="3" 
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="Describa detalladamente el servicio prestado o producto suministrado..."></textarea>
                                @error('servicioSuministrado') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <!-- Información financiera -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Valor Total *</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-2 text-gray-500">$</span>
                                        <input type="number" wire:model="totFac" min="0" step="0.01"
                                            class="w-full border border-gray-300 rounded-lg pl-8 pr-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            placeholder="Valor de la factura">
                                    </div>
                                    @error('totFac') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Método de Pago *</label>
                                    <select wire:model="metPagFac"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">Seleccionar...</option>
                                        <option value="efectivo">💵 Efectivo</option>
                                        <option value="transferencia">🏦 Transferencia</option>
                                        <option value="cheque">📄 Cheque</option>
                                        <option value="credito">📝 Crédito (30 días)</option>
                                    </select>
                                    @error('metPagFac') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Número de Comprobante</label>
                                    <input type="text" wire:model="numeroComprobanteProveedor"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        placeholder="Número de factura/recibo">
                                </div>
                            </div>

                            <!-- =============================================== -->
                            <!-- SUBIDA DE ARCHIVO DE FACTURA                   -->
                            <!-- =============================================== -->
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Foto/Archivo de la Factura *
                                    <span class="text-xs text-gray-500">(Máximo 5MB - JPG, PNG, PDF)</span>
                                </label>
                                
                                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-400 transition-colors">
                                    <input type="file" wire:model="archivoFacturaProveedor" accept="image/*,.pdf" class="hidden" id="archivoFactura">
                                    <label for="archivoFactura" class="cursor-pointer">
                                        <div class="flex flex-col items-center">
                                            <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-3"></i>
                                            <p class="text-sm text-gray-600 mb-2">
                                                <span class="font-medium text-blue-600 hover:text-blue-700">Haga clic para subir</span>
                                                o arrastre el archivo aquí
                                            </p>
                                            <p class="text-xs text-gray-500">JPG, PNG o PDF hasta 5MB</p>
                                        </div>
                                    </label>
                                </div>
                                
                                @if($archivoFacturaProveedor)
                                <div class="mt-3 p-3 bg-green-50 border border-green-200 rounded-lg">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <i class="fas fa-file-check text-green-600 mr-2"></i>
                                            <span class="text-sm text-green-700 font-medium">Archivo seleccionado:</span>
                                            <span class="text-sm text-gray-700 ml-2">{{ $archivoFacturaProveedor->getClientOriginalName() }}</span>
                                        </div>
                                        <button type="button" wire:click="$set('archivoFacturaProveedor', null)"
                                            class="text-red-500 hover:text-red-700">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                @endif
                                
                                @error('archivoFacturaProveedor') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <!-- Observaciones -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Observaciones</label>
                                <textarea wire:model="obsFac" rows="2"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="Observaciones adicionales sobre la factura o servicio..."></textarea>
                            </div>
                        </div>
                        @endif
                        <!-- =============================================== -->
                        <!-- BOTONES DE ACCIÓN DEL MODAL                    -->
                        <!-- =============================================== -->
                        
                        <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200 mt-6">
                            
                            <!-- Botón Cancelar -->
                            <button type="button" wire:click="cerrarModal"
                                class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition duration-200 flex items-center">
                                <i class="fas fa-times mr-2"></i>Cancelar
                            </button>
                            
                            <!-- Botón Volver (para cambiar tipo de factura) -->
                            @if($tipoFactura)
                            <button wire:click="volverSeleccionTipo" type="button" wire:click="volverSeleccionTipo"
                                class="bg-yellow-500 hover:bg-yellow-600 text-white px-6 py-2 rounded-lg transition duration-200 flex items-center">
                                <i class="fas fa-arrow-left mr-2"></i>Volver
                            </button>
                            @endif
                            
                            <!-- Botón Guardar (dinámico según tipo) -->
                            <button type="submit"
                                class="px-6 py-2 rounded-lg transition duration-200 text-white flex items-center
                                       {{ $tipoFactura === 'proveedor' ? 'bg-blue-600 hover:bg-blue-700' : 'bg-green-600 hover:bg-green-700' }}"
                                @if($tipoFactura === 'granja' && empty($productosSeleccionados)) disabled @endif>
                                
                                <!-- Loading spinner -->
                                <div wire:loading wire:target="guardarFactura" class="mr-2">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </div>
                                
                                <!-- Icono normal -->
                                <div wire:loading.remove wire:target="guardarFactura">
                                    <i class="fas fa-save mr-2"></i>
                                </div>
                                
                                <!-- Texto del botón -->
                                <span wire:loading.remove wire:target="guardarFactura">
                                    @if($tipoFactura === 'proveedor')
                                        Registrar Factura de Proveedor
                                    @else
                                        Guardar Factura de Productos
                                    @endif
                                </span>
                                
                                <span wire:loading wire:target="guardarFactura">
                                    Guardando...
                                </span>
                            </button>
                        </div>
                        
                        <!-- =============================================== -->
                        <!-- RESUMEN DE VALIDACIÓN (PIE DEL MODAL)          -->
                        <!-- =============================================== -->
                        
                        @if($tipoFactura === 'granja')
                        <div class="mt-4 p-3 bg-green-50 border border-green-200 rounded-lg">
                            <div class="flex items-start">
                                <i class="fas fa-info-circle text-green-600 mr-2 mt-0.5"></i>
                                <div class="text-sm text-green-700">
                                    <p class="font-medium">Resumen de la factura:</p>
                                    <ul class="mt-1 space-y-1 text-xs">
                                        <li>• Cliente: <span class="font-medium">{{ $nomCliFac ?: 'Por definir' }}</span></li>
                                        <li>• Productos: <span class="font-medium">{{ count($productosSeleccionados) }} item(s)</span></li>
                                        <li>• Total: <span class="font-medium">${{ number_format($totFac, 0) }}</span></li>
                                        <li>• Método de pago: <span class="font-medium">{{ $metPagFac ?: 'Por seleccionar' }}</span></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        @elseif($tipoFactura === 'proveedor')
                        <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                            <div class="flex items-start">
                                <i class="fas fa-info-circle text-blue-600 mr-2 mt-0.5"></i>
                                <div class="text-sm text-blue-700">
                                    <p class="font-medium">Resumen del registro:</p>
                                    <ul class="mt-1 space-y-1 text-xs">
                                        <li>• Proveedor: <span class="font-medium">{{ $proveedoresDisponibles->firstWhere('idProve', $idProveFac)?->nomProve ?? 'Por seleccionar' }}</span></li>
                                        <li>• Categoría: <span class="font-medium">{{ $categoriaGasto ?: 'Por definir' }}</span></li>
                                        <li>• Valor: <span class="font-medium">${{ number_format($totFac, 0) }}</span></li>
                                        <li>• Archivo: <span class="font-medium">{{ $archivoFacturaProveedor ? '✓ Adjunto' : 'Sin archivo' }}</span></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        @endif
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
    

    <!-- =============================================== -->
<!-- MODAL VER DETALLES DE FACTURA                  -->
<!-- =============================================== -->


    <!-- Overlay del modal -->
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        
        <!-- Centrado del modal -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <!-- Contenido del modal -->
        <div class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            
            <!-- Header del modal -->
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-file-invoice text-blue-600"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-ver-title">
                                Detalles de Factura FAC-{{ $facturaVer->idFac }}
                            </h3>
                            <p class="text-sm text-gray-500">
                                Fecha: {{ date('d/m/Y', strtotime($facturaVer->fecFac)) }}
                            </p>
                        </div>
                    </div>
                    
                    <!-- Botón cerrar -->
                    <button type="button" wire:click="cerrarModalVer"
                        class="bg-white rounded-md text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <span class="sr-only">Cerrar</span>
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Información de la factura -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    
                    <!-- Información del cliente -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="text-md font-semibold text-gray-800 mb-3">
                            <i class="fas fa-user mr-2 text-blue-600"></i>Cliente
                        </h4>
                        <div class="space-y-1">
                            <p><strong>Nombre:</strong> {{ $facturaVer->nomCliFac }}</p>
                            <p><strong>Documento:</strong> {{ $facturaVer->tipDocCliFac }}: {{ $facturaVer->docCliFac }}</p>
                            @if($facturaVer->dirCli)
                                <p><strong>Dirección:</strong> {{ $facturaVer->dirCli }}</p>
                            @endif
                            @if($facturaVer->telCli)
                                <p><strong>Teléfono:</strong> {{ $facturaVer->telCli }}</p>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Información de la factura -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="text-md font-semibold text-gray-800 mb-3">
                            <i class="fas fa-info-circle mr-2 text-blue-600"></i>Detalles
                        </h4>
                        <div class="space-y-1">
                            <p><strong>Estado:</strong> 
                                <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                           {{ $facturaVer->estFac == 'pagada' ? 'bg-green-100 text-green-800' : 
                                              ($facturaVer->estFac == 'anulada' ? 'bg-red-100 text-red-800' : 
                                              ($facturaVer->estFac == 'pendiente' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800')) }}">
                                    {{ ucfirst($facturaVer->estFac) }}
                                </span>
                            </p>
                            <p><strong>Método de Pago:</strong> {{ $facturaVer->metPagFac }}</p>
                            @if($facturaVer->nomUsu)
                                <p><strong>Vendedor:</strong> {{ $facturaVer->nomUsu }} {{ $facturaVer->apeUsu }}</p>
                            @endif
                            <p><strong>Creado:</strong> {{ date('d/m/Y H:i', strtotime($facturaVer->created_at)) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Productos/Detalles -->
                @if($detallesFactura && $detallesFactura->count() > 0)
                <div class="mb-6">
                    <h4 class="text-md font-semibold text-gray-800 mb-3">
                        <i class="fas fa-list mr-2 text-green-600"></i>Productos
                    </h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Precio Unit.</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($detallesFactura as $detalle)
                                <tr>
                                    <td class="px-4 py-3 text-sm">
                                        <div class="font-medium text-gray-900">{{ $detalle->conceptoDet }}</div>
                                        @if($detalle->nomIns)
                                            <div class="text-xs text-gray-500">{{ $detalle->nomIns }}</div>
                                        @endif
                                        @if($detalle->nitAni)
                                            <div class="text-xs text-gray-500">Animal: {{ $detalle->nitAni }} ({{ $detalle->espAni }})</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-center">{{ $detalle->cantidadDet }}</td>
                                    <td class="px-4 py-3 text-sm text-right">${{ number_format($detalle->precioUnitDet, 0) }}</td>
                                    <td class="px-4 py-3 text-sm text-right font-medium">${{ number_format($detalle->subtotalDet, 0) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                <!-- Totales -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="bg-green-50 p-4 rounded-lg">
                        <h4 class="text-md font-semibold text-gray-800 mb-3">
                            <i class="fas fa-calculator mr-2 text-green-600"></i>Totales
                        </h4>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span>Subtotal:</span>
                                <span>${{ number_format($facturaVer->subtotalFac, 0) }}</span>
                            </div>
                            @if($facturaVer->descuentoFac > 0)
                            <div class="flex justify-between">
                                <span>Descuento:</span>
                                <span class="text-red-600">-${{ number_format($facturaVer->descuentoFac, 0) }}</span>
                            </div>
                            @endif
                            @if($facturaVer->ivaFac > 0)
                            <div class="flex justify-between">
                                <span>IVA:</span>
                                <span>${{ number_format($facturaVer->ivaFac, 0) }}</span>
                            </div>
                            @endif
                            <div class="flex justify-between font-bold text-lg border-t border-gray-300 pt-2">
                                <span>TOTAL:</span>
                                <span class="text-green-600">${{ number_format($facturaVer->totFac, 0) }}</span>
                            </div>
                        </div>
                    </div>
                    
                    @if($facturaVer->obsFac)
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <h4 class="text-md font-semibold text-gray-800 mb-3">
                            <i class="fas fa-comment mr-2 text-blue-600"></i>Observaciones
                        </h4>
                        <p class="text-gray-700">{{ $facturaVer->obsFac }}</p>
                    </div>
                    @endif
                </div>

                <!-- Botones de acción -->
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                    <button wire:click="descargarFacturaPDF({{ $facturaVer->idFac }})"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition duration-200 flex items-center">
                        <i class="fas fa-file-pdf mr-2"></i>Descargar PDF
                    </button>
                    <button type="button" wire:click="cerrarModalVer"
                        class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-times mr-2"></i>Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

    <!-- =============================================== -->
    <!-- SCRIPTS ADICIONALES Y FUNCIONALIDADES          -->
    <!-- =============================================== -->
    
    @push('scripts')
    <script>
        // Cerrar modal con tecla ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                @this.call('cerrarModal');
            }
        });
        
        // Confirmación antes de cerrar si hay datos
        window.addEventListener('beforeunload', function(event) {
            if (@this.productosSeleccionados && @this.productosSeleccionados.length > 0) {
                event.preventDefault();
                event.returnValue = '¿Estás seguro de salir? Se perderán los datos no guardados.';
            }
        });
        
        // Auto-focus en campos importantes
        document.addEventListener('livewire:updated', function () {
            // Focus en nombre del cliente cuando se abre modal de granja
            if (@this.tipoFactura === 'granja' && @this.mostrarModal) {
                setTimeout(() => {
                    const clienteInput = document.querySelector('input[wire\\:model="nomCliFac"]');
                    if (clienteInput) clienteInput.focus();
                }, 100);
            }
            
            // Focus en selector de proveedor cuando se abre modal de proveedor
            if (@this.tipoFactura === 'proveedor' && @this.mostrarModal) {
                setTimeout(() => {
                    const proveedorSelect = document.querySelector('select[wire\\:model="idProveFac"]');
                    if (proveedorSelect) proveedorSelect.focus();
                }, 100);
            }
        });
        
        // Formateo automático de números en campos de precio
        function formatearPrecio(input) {
            let value = input.value.replace(/[^\d.]/g, '');
            if (value) {
                input.value = parseFloat(value).toFixed(2);
            }
        }
        
        // Validación de stock en tiempo real
        function validarStock(cantidad, stockDisponible) {
            if (parseFloat(cantidad) > parseFloat(stockDisponible)) {
                alert('La cantidad ingresada supera el stock disponible (' + stockDisponible + ')');
                return false;
            }
            return true;
        }
        
        // Notificación de éxito personalizada
        window.addEventListener('factura-guardada', event => {
            // Crear notificación personalizada
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg z-50';
            notification.innerHTML = `
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <span>${event.detail.message}</span>
                </div>
            `;
            document.body.appendChild(notification);
            
            // Remover después de 3 segundos
            setTimeout(() => {
                notification.remove();
            }, 3000);
        });
        
        // Prevenir envío múltiple del formulario
        let formSubmitting = false;
        document.addEventListener('submit', function(event) {
            if (formSubmitting) {
                event.preventDefault();
                return false;
            }
            formSubmitting = true;
            
            // Resetear después de 3 segundos
            setTimeout(() => {
                formSubmitting = false;
            }, 3000);
        });
    </script>
    @endpush

    <!-- =============================================== -->
    <!-- ESTILOS ADICIONALES                            -->
    <!-- =============================================== -->
    
    @push('styles')
    <style>
        /* Animaciones personalizadas */
        .fade-in {
            animation: fadeIn 0.3s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Hover effects mejorados */
        .hover-scale:hover {
            transform: scale(1.02);
            transition: transform 0.2s ease-in-out;
        }
        
        /* Scrollbar personalizado para tablas */
        .overflow-x-auto::-webkit-scrollbar {
            height: 6px;
        }
        
        .overflow-x-auto::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }
        
        .overflow-x-auto::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }
        
        .overflow-x-auto::-webkit-scrollbar-thumb:hover {
            background: #a1a1a1;
        }
        
        /* Efecto de carga en botones */
        .loading-button {
            position: relative;
            overflow: hidden;
        }
        
        .loading-button::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .loading-button:hover::after {
            left: 100%;
        }
        
        /* Mejorar apariencia de los radio buttons */
        .form-radio:checked {
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1);
        }
        
        /* Efectos de focus mejorados */
        .focus\:ring-2:focus {
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1);
        }
        
        /* Animación para cards de estadísticas */
        .stats-card {
            transition: all 0.3s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
    </style>
    @endpush

<!-- =============================================== -->
<!-- CIERRE DE LA ESTRUCTURA PRINCIPAL              -->
<!-- =============================================== -->
</div>

{{-- Fin del componente Livewire de Facturas --}}

<!-- ✅ SCRIPTS JAVASCRIPT PARA INTERACTIVIDAD -->
<script>
document.addEventListener('livewire:init', () => {
    
    // ===============================================
    // EVENTOS LIVEWIRE PRINCIPALES
    // ===============================================
    
    // Actualizar página cuando se guarde una factura
    Livewire.on('factura-guardada', (event) => {
        console.log('Factura guardada:', event.mensaje);
        
        // Mostrar notificación de éxito personalizada
        mostrarNotificacion('success', event.mensaje || 'Factura guardada exitosamente');
        
        // Recargar estadísticas
        setTimeout(() => {
            Livewire.dispatch('actualizar-estadisticas');
        }, 1000);
        
        // Opcional: Cerrar modal automáticamente
        setTimeout(() => {
            Livewire.dispatch('cerrar-modal');
        }, 2000);
    });

    // Manejar errores de validación
    Livewire.on('error-validacion', (event) => {
        mostrarNotificacion('error', event.mensaje || 'Error en la validación de datos');
        
        // Hacer scroll al primer campo con error
        setTimeout(() => {
            const primerError = document.querySelector('.text-red-500');
            if (primerError) {
                primerError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }, 100);
    });

    // Evento cuando se selecciona un producto
    Livewire.on('producto-seleccionado', (event) => {
        console.log('Producto seleccionado:', event);
        
        // Auto-focus en campo cantidad si está vacío
        setTimeout(() => {
            const cantidadInput = document.querySelector('input[wire\\:model="cantidadProducto"]');
            if (cantidadInput && !cantidadInput.value) {
                cantidadInput.focus();
            }
        }, 100);
    });

    // ===============================================
    // FUNCIONALIDADES DE TECLADO
    // ===============================================
    
    // Cerrar modal con ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            Livewire.dispatch('cerrar-modal');
        }
        
        // Atajos de teclado adicionales
        if (e.ctrlKey || e.metaKey) {
            switch(e.key) {
                case 's': // Ctrl+S para guardar
                    e.preventDefault();
                    const submitBtn = document.querySelector('button[type="submit"]');
                    if (submitBtn && !submitBtn.disabled) {
                        submitBtn.click();
                    }
                    break;
                case 'n': // Ctrl+N para nueva factura
                    e.preventDefault();
                    Livewire.dispatch('abrir-modal');
                    break;
            }
        }
    });

    // ===============================================
    // AUTO-SAVE Y VALIDACIONES
    // ===============================================
    
    // Auto-save en campos importantes
    let autoSaveTimer;
    document.addEventListener('input', function(e) {
        if (e.target.matches('[wire\\:model*="obsFac"]') || 
            e.target.matches('[wire\\:model*="servicioSuministrado"]')) {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(() => {
                console.log('Auto-guardando:', e.target.name || 'campo');
                // Aquí podrías dispatch un evento para auto-guardar
                // Livewire.dispatch('auto-save', { campo: e.target.value });
            }, 2000);
        }
    });

    // ===============================================
    // ENFOQUE AUTOMÁTICO EN CAMPOS
    // ===============================================
    
    // Auto-focus inteligente cuando cambia el DOM
    document.addEventListener('livewire:updated', function () {
        // Focus en nombre del cliente cuando se abre modal de granja
        if (document.querySelector('input[wire\\:model="nomCliFac"]') && 
            !document.querySelector('input[wire\\:model="nomCliFac"]').value) {
            setTimeout(() => {
                document.querySelector('input[wire\\:model="nomCliFac"]')?.focus();
            }, 100);
        }
        
        // Focus en selector de proveedor cuando se abre modal de proveedor
        if (document.querySelector('select[wire\\:model="idProveFac"]') && 
            !document.querySelector('select[wire\\:model="idProveFac"]').value) {
            setTimeout(() => {
                document.querySelector('select[wire\\:model="idProveFac"]')?.focus();
            }, 100);
        }
        
        // Focus en campo cantidad cuando se selecciona producto
        if (document.querySelector('input[wire\\:model="cantidadProducto"]')) {
            const cantidadInput = document.querySelector('input[wire\\:model="cantidadProducto"]');
            if (cantidadInput && !cantidadInput.value) {
                setTimeout(() => cantidadInput.focus(), 100);
            }
        }
    });
});

// ===============================================
// FUNCIONES UTILITARIAS GLOBALES
// ===============================================

// Función mejorada para formatear números colombianos
function formatNumber(num) {
    if (isNaN(num)) return '$0';
    return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(num);
}

// Función para mostrar notificaciones personalizadas
function mostrarNotificacion(tipo, mensaje, duracion = 4000) {
    // Remover notificaciones anteriores
    const notificacionesExistentes = document.querySelectorAll('.notificacion-custom');
    notificacionesExistentes.forEach(n => n.remove());
    
    const colores = {
        success: 'bg-green-500 border-green-600',
        error: 'bg-red-500 border-red-600',
        warning: 'bg-yellow-500 border-yellow-600',
        info: 'bg-blue-500 border-blue-600'
    };
    
    const iconos = {
        success: 'fas fa-check-circle',
        error: 'fas fa-exclamation-circle',
        warning: 'fas fa-exclamation-triangle',
        info: 'fas fa-info-circle'
    };
    
    const notificacion = document.createElement('div');
    notificacion.className = `notificacion-custom fixed top-4 right-4 ${colores[tipo]} text-white px-6 py-4 rounded-lg shadow-lg z-50 border-l-4 transform translate-x-full transition-transform duration-300`;
    notificacion.innerHTML = `
        <div class="flex items-center">
            <i class="${iconos[tipo]} mr-3 text-lg"></i>
            <div>
                <p class="font-medium">${mensaje}</p>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    document.body.appendChild(notificacion);
    
    // Animar entrada
    setTimeout(() => {
        notificacion.classList.remove('translate-x-full');
    }, 10);
    
    // Auto-remover
    setTimeout(() => {
        notificacion.classList.add('translate-x-full');
        setTimeout(() => notificacion.remove(), 300);
    }, duracion);
}

// ===============================================
// VALIDACIONES EN TIEMPO REAL
// ===============================================

// Validación para campos numéricos
document.addEventListener('input', function(e) {
    if (e.target.type === 'number') {
        let value = parseFloat(e.target.value);
        
        // No permitir valores negativos
        if (value < 0) {
            e.target.value = 0;
            mostrarNotificacion('warning', 'No se permiten valores negativos');
        }
        
        // Validar límites específicos
        if (e.target.matches('[wire\\:model="cantidadProducto"]')) {
            const max = parseFloat(e.target.getAttribute('max'));
            if (max && value > max) {
                e.target.value = max;
                mostrarNotificacion('warning', `Cantidad máxima: ${max}`);
            }
        }
        
        // Formatear precios automáticamente
        if (e.target.matches('[wire\\:model="precioUnitario"]') && value > 0) {
            // Redondear a 2 decimales
            e.target.value = Math.round(value * 100) / 100;
        }
    }
});

// ===============================================
// CONFIRMACIONES Y PREVENCIONES
// ===============================================

// Confirmación antes de eliminar con detalles
document.addEventListener('click', function(e) {
    if (e.target.closest('[wire\\:click*="eliminarFactura"]')) {
        const boton = e.target.closest('[wire\\:click*="eliminarFactura"]');
        const facturaId = boton.getAttribute('wire:click').match(/\d+/)?.[0];
        
        if (!confirm(`¿Está seguro de que desea eliminar la factura #${facturaId}?\n\nEsta acción no se puede deshacer y se perderán todos los datos asociados.`)) {
            e.preventDefault();
            e.stopPropagation();
        }
    }
    
    // Confirmación para limpiar productos seleccionados
    if (e.target.closest('[wire\\:click*="limpiarProductosSeleccionados"]')) {
        if (!confirm('¿Desea limpiar todos los productos seleccionados?')) {
            e.preventDefault();
            e.stopPropagation();
        }
    }
});

// Prevenir envío múltiple de formularios
let formSubmitting = false;
document.addEventListener('submit', function(e) {
    if (formSubmitting) {
        e.preventDefault();
        mostrarNotificacion('warning', 'Procesando... Por favor espere');
        return false;
    }
    
    formSubmitting = true;
    
    // Mostrar indicador de carga
    const submitBtn = e.target.querySelector('button[type="submit"]');
    if (submitBtn) {
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Guardando...';
        submitBtn.disabled = true;
        
        // Restaurar después de 5 segundos como failsafe
        setTimeout(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            formSubmitting = false;
        }, 5000);
    }
    
    // Resetear flag después de 3 segundos
    setTimeout(() => {
        formSubmitting = false;
    }, 3000);
});

// ===============================================
// PREVENCIÓN DE PÉRDIDA DE DATOS
// ===============================================

// Advertir antes de salir si hay datos sin guardar
window.addEventListener('beforeunload', function(e) {
    const hayProductos = document.querySelector('.min-w-full tbody tr:not(:last-child)');
    const hayCamposLlenos = document.querySelector('input[wire\\:model="nomCliFac"]')?.value ||
                           document.querySelector('textarea[wire\\:model="servicioSuministrado"]')?.value;
    
    if (hayProductos || hayCamposLlenos) {
        e.preventDefault();
        e.returnValue = '¿Estás seguro de salir? Se perderán los datos no guardados.';
        return e.returnValue;
    }
});

// ===============================================
// MEJORAS DE USABILIDAD
// ===============================================

// Click fuera del modal para cerrar (opcional)
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('fixed') && 
        e.target.classList.contains('inset-0') && 
        e.target.classList.contains('bg-gray-500')) {
        // Solo cerrar si no hay datos importantes
        const hayDatos = document.querySelector('input[wire\\:model="nomCliFac"]')?.value;
        if (!hayDatos || confirm('¿Cerrar sin guardar?')) {
            Livewire.dispatch('cerrar-modal');
        }
    }
});

// Tooltip dinámico para botones deshabilitados
document.addEventListener('mouseover', function(e) {
    if (e.target.disabled && e.target.tagName === 'BUTTON') {
        if (!e.target.title) {
            e.target.title = 'Complete los campos requeridos para habilitar esta acción';
        }
    }
});

// ===============================================
// DEBUG Y LOGGING (DESARROLLO)
// ===============================================

// Solo en desarrollo - remover en producción
if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
    console.log('🌱 FAMASY Sistema de Facturación - Modo Desarrollo');
    
    // Log de eventos Livewire para debug
    Livewire.on('*', (event, data) => {
        console.log('📡 Evento Livewire:', event, data);
    });
}

console.log('✅ FAMASY JavaScript cargado correctamente');
</script>
