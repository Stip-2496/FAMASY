<?php
// resources/views/livewire/contabilidad/facturas/index.blade.php

use App\Models\Factura;
use App\Models\Cliente;
use App\Models\MovimientoContable;
use App\Models\Proveedor; // ✅ NUEVO: Modelo Proveedor
use App\Models\ComprasGasto; // ✅ NUEVO: Modelo ComprasGastos
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

new #[Layout('layouts.auth')] class extends Component {
    use WithPagination;

    // Propiedades para filtros (SIN .live para evitar auto-reload)
    public $estado = '';
    public $cliente_buscar = '';
    public $fecha_desde = '';
    public $fecha_hasta = '';
    public $per_page = 15;

    // Propiedades para el modal de nueva factura
    public $modalAbierto = false;
    public $numero = '';
    public $idCliFac = '';
    public $nomCliFac = '';
    public $tipDocCliFac = 'CC';
    public $docCliFac = '';
    public $fecFac = '';
    public $subtotalFac = 0;
    public $ivaFac = 19;
    public $totFac = 0;
    public $metPagFac = '';
    public $obsFac = '';

    // ✅ NUEVAS propiedades para proveedores
    public $tipoFactura = 'cliente'; // 'cliente', 'granja', 'proveedor'
    public $idProveFac = '';
    public $nomProveFac = '';
    public $nitProveFac = '';
    public $categoriaCompra = '';
    public $productos = [];
    public $productoSeleccionado = '';
    public $cantidadProducto = 1;
    public $precioUnitario = 0;

    // Estado de carga
    public $cargandoCliente = false;
    public $cargandoProveedor = false;
    public $clientesCargados = false;
    public $proveedoresCargados = false;

    // Estadísticas
    public $estadisticas = [];
    public $clientes = null;
    public $proveedores = null; // ✅ NUEVO: Lista de proveedores
    public $productosGranja = [];
    
    // ✅ NUEVO: Categorías de compras/gastos
    public $categoriasCompra = [
        'insumos_agricolas' => 'Insumos Agrícolas',
        'alimento_animal' => 'Alimento para Animales',
        'medicamentos' => 'Medicamentos Veterinarios',
        'herramientas' => 'Herramientas y Equipos',
        'combustible' => 'Combustible',
        'mantenimiento' => 'Mantenimiento',
        'servicios' => 'Servicios Profesionales',
        'suministros' => 'Suministros Generales',
        'transporte' => 'Transporte y Logística',
        'otros' => 'Otros Gastos'
    ];

    public function mount()
    {
        $this->fecha_desde = now()->startOfMonth()->format('Y-m-d');
        $this->fecha_hasta = now()->endOfMonth()->format('Y-m-d');
        $this->fecFac = date('Y-m-d');
        $this->numero = $this->generarNumeroFactura();
        $this->clientes = collect(); // ✅ Inicializar como colección vacía
        $this->proveedores = collect(); // ✅ NUEVO: Inicializar proveedores
        $this->cargarProductosGranja();
        $this->calcularEstadisticas();
    }

    public function cargarClientes()
    {
        try {
            if ($this->clientesCargados) {
                return; // Evitar cargas múltiples
            }

            $this->clientes = Cliente::where('estCli', 'activo')
                ->orderBy('nomCli')
                ->get();
                
            $this->clientesCargados = true;
            Log::info('Clientes cargados: ' . $this->clientes->count());
        } catch (\Exception $e) {
            Log::error('Error cargando clientes: ' . $e->getMessage());
            $this->clientes = collect(); // Asegurar que sea una colección
        }
    }

    // ✅ NUEVO: Cargar proveedores
    public function cargarProveedores()
    {
        try {
            if ($this->proveedoresCargados) {
                return; // Evitar cargas múltiples
            }

            $this->proveedores = DB::table('proveedores')
                ->orderBy('nomProve')
                ->get();
                
            $this->proveedoresCargados = true;
            Log::info('Proveedores cargados: ' . $this->proveedores->count());
        } catch (\Exception $e) {
            Log::error('Error cargando proveedores: ' . $e->getMessage());
            $this->proveedores = collect();
        }
    }

    // ✅ Helper method para verificar si hay clientes
    public function getClientesVaciosProperty()
    {
        return $this->clientes instanceof \Illuminate\Support\Collection 
            ? $this->clientes->isEmpty() 
            : empty($this->clientes);
    }

    // ✅ NUEVO: Helper method para verificar si hay proveedores
    public function getProveedoresVaciosProperty()
    {
        return $this->proveedores instanceof \Illuminate\Support\Collection 
            ? $this->proveedores->isEmpty() 
            : empty($this->proveedores);
    }

    public function cargarProductosGranja()
    {
        // Productos predefinidos de la granja
        $this->productosGranja = [
            // OVINOS
            ['id' => 'ovino_leche', 'nombre' => 'Leche de Oveja', 'categoria' => 'Ovinos', 'unidad' => 'Litros', 'precio_sugerido' => 8000],
            ['id' => 'ovino_lana', 'nombre' => 'Lana de Oveja', 'categoria' => 'Ovinos', 'unidad' => 'Kilos', 'precio_sugerido' => 15000],
            ['id' => 'ovino_pie', 'nombre' => 'Ovino en Pie', 'categoria' => 'Ovinos', 'unidad' => 'Animal', 'precio_sugerido' => 300000],
            
            // BOVINOS  
            ['id' => 'bovino_leche', 'nombre' => 'Leche de Vaca', 'categoria' => 'Bovinos', 'unidad' => 'Litros', 'precio_sugerido' => 2500],
            ['id' => 'bovino_pie', 'nombre' => 'Bovino en Pie', 'categoria' => 'Bovinos', 'unidad' => 'Animal', 'precio_sugerido' => 2500000],
            
            // POLLOS
            ['id' => 'pollo_pie', 'nombre' => 'Pollo en Pie', 'categoria' => 'Pollos', 'unidad' => 'Kilos', 'precio_sugerido' => 12000],
            
            // GALLINAS Y HUEVOS
            ['id' => 'huevos_a', 'nombre' => 'Huevos Categoría A (Panal 30 und)', 'categoria' => 'Gallinas', 'unidad' => 'Panal', 'precio_sugerido' => 18000],
            ['id' => 'huevos_aa', 'nombre' => 'Huevos Categoría AA (Panal 30 und)', 'categoria' => 'Gallinas', 'unidad' => 'Panal', 'precio_sugerido' => 22000],
            ['id' => 'huevos_aaa', 'nombre' => 'Huevos Categoría AAA (Panal 30 und)', 'categoria' => 'Gallinas', 'unidad' => 'Panal', 'precio_sugerido' => 25000],
            ['id' => 'gallina_pie', 'nombre' => 'Gallina en Pie', 'categoria' => 'Gallinas', 'unidad' => 'Animal', 'precio_sugerido' => 35000],
        ];
    }

    public function calcularEstadisticas()
    {
        try {
            $totalFacturado = Factura::sum('totFac') ?? 0;
            $pagadas = Factura::where('estFac', 'pagada')->count();
            $pendientes = Factura::where('estFac', 'emitida')->count();
            $vencidas = Factura::where('estFac', 'pendiente')
                ->where('fecFac', '<', now()->subDays(30))
                ->count();

            $this->estadisticas = [
                'total_facturado' => $totalFacturado,
                'pagadas' => $pagadas,
                'pendientes' => $pendientes,
                'vencidas' => $vencidas
            ];
        } catch (\Exception $e) {
            Log::error('Error calculando estadísticas de facturas: ' . $e->getMessage());
            $this->estadisticas = [
                'total_facturado' => 0,
                'pagadas' => 0,
                'pendientes' => 0,
                'vencidas' => 0
            ];
        }
    }

    public function getFacturasProperty()
    {
        try {
            $query = Factura::query();

            // Aplicar filtros SOLO cuando se hace clic en "Filtrar"
            if ($this->estado) {
                $query->where('estFac', $this->estado);
            }

            if ($this->cliente_buscar) {
                $query->where('nomCliFac', 'like', '%' . $this->cliente_buscar . '%');
            }

            if ($this->fecha_desde) {
                $query->where('fecFac', '>=', $this->fecha_desde);
            }

            if ($this->fecha_hasta) {
                $query->where('fecFac', '<=', $this->fecha_hasta);
            }

            return $query->orderBy('fecFac', 'desc')
                        ->paginate($this->per_page);

        } catch (\Exception $e) {
            Log::error('Error al obtener facturas: ' . $e->getMessage());
            return Factura::paginate(1);
        }
    }

    // SOLO se ejecuta al hacer clic en "Filtrar"
    public function aplicarFiltros()
    {
        $this->resetPage();
        $this->calcularEstadisticas();
    }

    public function limpiarFiltros()
    {
        $this->reset(['estado', 'cliente_buscar', 'fecha_desde', 'fecha_hasta']);
        $this->fecha_desde = now()->startOfMonth()->format('Y-m-d');
        $this->fecha_hasta = now()->endOfMonth()->format('Y-m-d');
        $this->resetPage();
        $this->calcularEstadisticas();
    }

    public function abrirModal()
    {
        Log::info('Abriendo modal de nueva factura');
        
        $this->modalAbierto = true;
        $this->resetearCamposModal();
        
        // Cargar clientes SOLO cuando se abre el modal
        $this->cargarClientes();
        
        $this->dispatch('modal-opened');
        Log::info('Modal abierto, clientes disponibles: ' . $this->clientes->count());
    }

    private function resetearCamposModal()
    {
        $this->reset(['idCliFac', 'nomCliFac', 'tipDocCliFac', 'docCliFac', 'subtotalFac', 'ivaFac', 'totFac', 'metPagFac', 'obsFac', 'productos']);
        // ✅ NUEVO: Reset campos de proveedor
        $this->reset(['idProveFac', 'nomProveFac', 'nitProveFac', 'categoriaCompra']);
        
        $this->tipDocCliFac = 'CC';
        $this->ivaFac = 19;
        $this->subtotalFac = 0;
        $this->totFac = 0;
        $this->fecFac = date('Y-m-d');
        $this->numero = $this->generarNumeroFactura();
        $this->tipoFactura = 'cliente';
        $this->cargandoCliente = false;
        $this->cargandoProveedor = false; // ✅ NUEVO
    }

    // ✅ MÉTODO OPTIMIZADO: Sin wire:model.live
    public function cambiarTipoFactura($tipo)
    {
        $this->tipoFactura = $tipo;
        $this->productos = [];
        $this->resetearCamposSegunTipo();
        $this->calcularTotales();
        
        // Cargar datos según el tipo
        if ($tipo === 'cliente') {
            $this->cargarClientes();
        } elseif ($tipo === 'proveedor') {
            $this->cargarProveedores();
        }
    }

    // ✅ NUEVO: Resetear campos según tipo de factura
    private function resetearCamposSegunTipo()
    {
        if ($this->tipoFactura === 'proveedor') {
            // Limpiar campos de cliente
            $this->idCliFac = '';
            $this->nomCliFac = '';
            $this->tipDocCliFac = 'CC';
            $this->docCliFac = '';
            
            // Limpiar campos de proveedor
            $this->idProveFac = '';
            $this->nomProveFac = '';
            $this->nitProveFac = '';
            $this->categoriaCompra = '';
        } else {
            // Limpiar campos de proveedor
            $this->idProveFac = '';
            $this->nomProveFac = '';
            $this->nitProveFac = '';
            $this->categoriaCompra = '';
        }
    }

    // ✅ NUEVO: Seleccionar proveedor
    public function seleccionarProveedor($proveedorId)
    {
        if (empty($proveedorId)) {
            $this->resetearInfoProveedor();
            return;
        }

        $this->cargandoProveedor = true;
        
        try {
            $proveedor = $this->proveedores->firstWhere('idProve', $proveedorId);
            if ($proveedor) {
                $this->idProveFac = $proveedor->idProve;
                $this->nomProveFac = $proveedor->nomProve;
                $this->nitProveFac = $proveedor->nitProve;
                
                Log::info('Proveedor seleccionado: ' . $proveedor->nomProve);
            }
        } catch (\Exception $e) {
            Log::error('Error seleccionando proveedor: ' . $e->getMessage());
            session()->flash('error', 'Error al cargar información del proveedor');
        } finally {
            $this->cargandoProveedor = false;
        }
    }

    private function resetearInfoProveedor()
    {
        $this->idProveFac = '';
        $this->nomProveFac = '';
        $this->nitProveFac = '';
    }

    public function agregarProducto()
    {
        if ($this->productoSeleccionado && $this->cantidadProducto > 0 && $this->precioUnitario > 0) {
            $producto = collect($this->productosGranja)->firstWhere('id', $this->productoSeleccionado);
            
            if ($producto) {
                $subtotal = $this->cantidadProducto * $this->precioUnitario;
                
                $this->productos[] = [
                    'id' => $producto['id'],
                    'nombre' => $producto['nombre'],
                    'categoria' => $producto['categoria'],
                    'unidad' => $producto['unidad'],
                    'cantidad' => $this->cantidadProducto,
                    'precio_unitario' => $this->precioUnitario,
                    'subtotal' => $subtotal
                ];
                
                // Limpiar campos
                $this->productoSeleccionado = '';
                $this->cantidadProducto = 1;
                $this->precioUnitario = 0;
                
                $this->calcularTotales();
            }
        }
    }

    public function eliminarProducto($index)
    {
        unset($this->productos[$index]);
        $this->productos = array_values($this->productos);
        $this->calcularTotales();
    }

    // ✅ MÉTODO OPTIMIZADO: Manual trigger
    public function seleccionarProducto($productoId)
    {
        $this->productoSeleccionado = $productoId;
        if ($productoId) {
            $producto = collect($this->productosGranja)->firstWhere('id', $productoId);
            if ($producto) {
                $this->precioUnitario = $producto['precio_sugerido'];
            }
        }
    }

    // ✅ MÉTODO OPTIMIZADO: Solo se ejecuta al perder foco
    public function actualizarTotales()
    {
        $this->calcularTotales();
    }

    public function calcularTotales()
    {
        if ($this->tipoFactura === 'granja') {
            $this->subtotalFac = array_sum(array_column($this->productos, 'subtotal'));
        }
        
        $subtotal = floatval($this->subtotalFac);
        $iva = floatval($this->ivaFac);
        $ivaCalculado = $subtotal * ($iva / 100);
        $this->totFac = $subtotal + $ivaCalculado;
    }

    public function cerrarModal()
    {
        $this->modalAbierto = false;
        $this->resetValidation();
        $this->dispatch('modal-closed');
    }

    // ✅ MÉTODO OPTIMIZADO: Sin loading automático
    public function seleccionarCliente($clienteId)
    {
        if (empty($clienteId)) {
            $this->resetearInfoCliente();
            return;
        }

        $this->cargandoCliente = true;
        
        try {
            $cliente = Cliente::find($clienteId);
            if ($cliente) {
                $this->idCliFac = $cliente->idCli;
                $this->nomCliFac = $cliente->nomCli;
                $this->tipDocCliFac = $cliente->tipDocCli;
                $this->docCliFac = $cliente->docCli;
                
                Log::info('Cliente seleccionado: ' . $cliente->nomCli);
            }
        } catch (\Exception $e) {
            Log::error('Error seleccionando cliente: ' . $e->getMessage());
            session()->flash('error', 'Error al cargar información del cliente');
        } finally {
            $this->cargandoCliente = false;
        }
    }

    private function resetearInfoCliente()
    {
        $this->idCliFac = '';
        $this->nomCliFac = '';
        $this->tipDocCliFac = 'CC';
        $this->docCliFac = '';
    }

    private function generarNumeroFactura()
    {
        try {
            $ultimaFactura = Factura::orderBy('idFac', 'desc')->first();
            $numero = $ultimaFactura ? $ultimaFactura->idFac + 1 : 1;
            return 'FAC-' . date('Ymd') . '-' . str_pad($numero, 3, '0', STR_PAD_LEFT);
        } catch (\Exception $e) {
            return 'FAC-' . date('Ymd') . '-001';
        }
    }

    public function rules(): array
    {
        return [
            'nomCliFac' => 'required|string|max:100',
            'tipDocCliFac' => 'required|in:NIT,CC,CE,Pasaporte',
            'docCliFac' => 'required|string|max:20',
            'fecFac' => 'required|date',
            'subtotalFac' => 'required|numeric|min:0.01',
            'ivaFac' => 'required|numeric|min:0|max:100',
            'totFac' => 'required|numeric|min:0.01',
            'metPagFac' => 'nullable|string|max:50',
            'obsFac' => 'nullable|string'
        ];
    }

    public function guardarFactura()
    {
        Log::info('Iniciando proceso de guardar factura tipo: ' . $this->tipoFactura);
        
        $this->validate();

        try {
            DB::beginTransaction();

            if ($this->tipoFactura === 'proveedor') {
                // ✅ NUEVO: Crear registro en comprasgastos para proveedores
                $compraGasto = DB::table('comprasgastos')->insertGetId([
                    'tipComGas' => 'compra',
                    'catComGas' => $this->categoriasCompra[$this->categoriaCompra] ?? 'Compra General',
                    'desComGas' => 'Factura de compra - ' . $this->nomProveFac,
                    'monComGas' => $this->totFac,
                    'fecComGas' => $this->fecFac,
                    'metPagComGas' => $this->metPagFac,
                    'provComGas' => $this->nomProveFac,
                    'obsComGas' => $this->obsFac,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // Crear movimiento contable (egreso)
                MovimientoContable::create([
                    'fecMovCont' => $this->fecFac,
                    'tipoMovCont' => 'egreso',
                    'catMovCont' => $this->categoriasCompra[$this->categoriaCompra] ?? 'Compras',
                    'conceptoMovCont' => 'Compra #' . $this->numero . ' - ' . $this->nomProveFac,
                    'montoMovCont' => $this->totFac,
                    'idComGasMovCont' => $compraGasto,
                    'obsMovCont' => 'Movimiento generado automáticamente por facturación de compra'
                ]);

                Log::info('Compra/Gasto creado con ID: ' . $compraGasto);
                $mensaje = 'Factura de compra registrada exitosamente';
                
            } else {
                // Crear la factura normal (cliente o granja)
                $factura = Factura::create([
                    'idUsuFac' => auth()->id(),
                    'idCliFac' => $this->idCliFac ?: null,
                    'nomCliFac' => $this->nomCliFac,
                    'tipDocCliFac' => $this->tipDocCliFac,
                    'docCliFac' => $this->docCliFac,
                    'fecFac' => $this->fecFac,
                    'subtotalFac' => $this->subtotalFac,
                    'ivaFac' => $this->subtotalFac * ($this->ivaFac / 100),
                    'totFac' => $this->totFac,
                    'metPagFac' => $this->metPagFac,
                    'estFac' => 'emitida',
                    'obsFac' => $this->obsFac . ($this->tipoFactura === 'granja' ? ' | Factura de productos de granja' : '')
                ]);

                Log::info('Factura creada con ID: ' . $factura->idFac);

                // Si es factura de granja, guardar detalles de productos
                if ($this->tipoFactura === 'granja' && !empty($this->productos)) {
                    foreach ($this->productos as $producto) {
                        // Crear detalles en tabla facturadetalles
                        DB::table('facturadetalles')->insert([
                            'idFacDet' => $factura->idFac,
                            'conceptoDet' => $producto['nombre'],
                            'cantidadDet' => $producto['cantidad'],
                            'precioUnitDet' => $producto['precio_unitario'],
                            'subtotalDet' => $producto['subtotal'],
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }

                // Crear movimiento contable (ingreso)
                MovimientoContable::create([
                    'fecMovCont' => $this->fecFac,
                    'tipoMovCont' => 'ingreso',
                    'catMovCont' => $this->tipoFactura === 'granja' ? 'Venta Productos Granja' : 'Facturación',
                    'conceptoMovCont' => 'Factura #' . $this->numero . ' - ' . $this->nomCliFac,
                    'montoMovCont' => $this->totFac,
                    'idFacMovCont' => $factura->idFac,
                    'obsMovCont' => 'Movimiento generado automáticamente por facturación'
                ]);

                $mensaje = 'Factura creada exitosamente';
            }

            DB::commit();

            $this->cerrarModal();
            $this->calcularEstadisticas();
            
            $this->dispatch('factura-creada');
            session()->flash('success', $mensaje);
            
            Log::info('Factura/Compra guardada exitosamente');
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error al guardar factura: ' . $e->getMessage());
            session()->flash('error', 'Error al crear la factura: ' . $e->getMessage());
        }
    }

    public function cambiarEstadoFactura($facturaId, $nuevoEstado)
    {
        try {
            $factura = Factura::find($facturaId);
            if ($factura) {
                $factura->update(['estFac' => $nuevoEstado]);
                $this->calcularEstadisticas();
                session()->flash('success', 'Estado de factura actualizado');
            }
        } catch (\Exception $e) {
            Log::error('Error al cambiar estado de factura: ' . $e->getMessage());
            session()->flash('error', 'Error al actualizar el estado');
        }
    }

    public function eliminarFactura($facturaId)
    {
        try {
            $factura = Factura::find($facturaId);
            if ($factura) {
                MovimientoContable::where('idFacMovCont', $facturaId)->delete();
                $factura->delete();
                
                $this->calcularEstadisticas();
                session()->flash('success', 'Factura eliminada correctamente');
            }
        } catch (\Exception $e) {
            Log::error('Error al eliminar factura: ' . $e->getMessage());
            session()->flash('error', 'Error al eliminar la factura');
        }
    }

    public function exportarFacturas()
    {
        session()->flash('info', 'Función de exportación en desarrollo');
    }

    // ✅ NUEVO: Crear proveedor rápido
    public function crearProveedorRapido()
    {
        try {
            $proveedorId = DB::table('proveedores')->insertGetId([
                'nomProve' => 'Proveedor de Prueba',
                'nitProve' => '900' . rand(100000, 999999) . '-' . rand(1, 9),
                'conProve' => 'Contacto Principal',
                'telProve' => '601' . rand(1000000, 9999999),
                'emailProve' => 'proveedor' . rand(100, 999) . '@example.com',
                'dirProve' => 'Dirección Ejemplo #123',
                'tipSumProve' => 'Insumos Agrícolas',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            $this->proveedoresCargados = false; // Force reload
            $this->cargarProveedores();
            session()->flash('success', 'Proveedor de prueba creado exitosamente');
        } catch (\Exception $e) {
            Log::error('Error creando proveedor de prueba: ' . $e->getMessage());
            session()->flash('error', 'Error al crear proveedor de prueba');
        }
    }

    public function crearClienteRapido()
    {
        try {
            $cliente = Cliente::create([
                'nomCli' => 'Cliente de Prueba',
                'tipDocCli' => 'CC',
                'docCli' => '12345678' . rand(10, 99),
                'telCli' => '300' . rand(1000000, 9999999),
                'emailCli' => 'cliente' . rand(100, 999) . '@example.com',
                'dirCli' => 'Calle Ejemplo #123',
                'tipCli' => 'particular',
                'estCli' => 'activo'
            ]);
            
            $this->clientesCargados = false; // Force reload
            $this->cargarClientes();
            session()->flash('success', 'Cliente de prueba creado: ' . $cliente->nomCli);
        } catch (\Exception $e) {
            Log::error('Error creando cliente de prueba: ' . $e->getMessage());
            session()->flash('error', 'Error al crear cliente de prueba');
        }
    }
}; ?>

@section('title', 'Facturas')

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
                        <span class="text-gray-900">Facturas</span>
                    </nav>
                    <h1 class="text-3xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-file-invoice mr-3 text-green-600"></i> 
                        Gestión de Facturas
                    </h1>
                    <p class="text-gray-600 mt-1">Administración completa de facturas de clientes y productos de granja</p>
                </div>
                <div class="flex space-x-3">
                    <button wire:click="abrirModal" 
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center transition duration-200">
                        <i class="fas fa-plus mr-2"></i> Nueva Factura
                    </button>
                    @if($this->clientesVacios && $tipoFactura === 'cliente')
                        <button wire:click="crearClienteRapido" 
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center transition duration-200">
                            <i class="fas fa-user-plus mr-2"></i> Crear Cliente Prueba
                        </button>
                    @endif
                    @if($this->proveedoresVacios && $tipoFactura === 'proveedor')
                        <button wire:click="crearProveedorRapido" 
                                class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg flex items-center transition duration-200">
                            <i class="fas fa-truck mr-2"></i> Crear Proveedor Prueba
                        </button>
                    @endif
                    <a href="{{ route('contabilidad.reportes.index') }}" wire:navigate
                       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center transition duration-200">
                        <i class="fas fa-chart-line mr-2"></i> Reportes
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas Rápidas -->
    <div class="flex flex-wrap -mx-3 mb-6">
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
        <div class="w-full md:w-1/4 px-3 mb-6">
            <div class="bg-white shadow-lg rounded-lg p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-blue-600 uppercase tracking-wide mb-1">Pagadas</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $estadisticas['pagadas'] ?? 0 }}</p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-check text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/4 px-3 mb-6">
            <div class="bg-white shadow-lg rounded-lg p-6 border-l-4 border-yellow-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-yellow-600 uppercase tracking-wide mb-1">Pendientes</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $estadisticas['pendientes'] ?? 0 }}</p>
                    </div>
                    <div class="bg-yellow-100 p-3 rounded-full">
                        <i class="fas fa-clock text-yellow-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/4 px-3 mb-6">
            <div class="bg-white shadow-lg rounded-lg p-6 border-l-4 border-red-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-red-600 uppercase tracking-wide mb-1">Vencidas</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $estadisticas['vencidas'] ?? 0 }}</p>
                    </div>
                    <div class="bg-red-100 p-3 rounded-full">
                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros SIN auto-reload -->
    <div class="flex flex-wrap -mx-3 mb-6">
        <div class="w-full px-3">
            <div class="bg-white shadow-lg rounded-lg p-6">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                        <select wire:model="estado" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="">Todos</option>
                            <option value="emitida">Emitidas</option>
                            <option value="pagada">Pagadas</option>
                            <option value="pendiente">Pendientes</option>
                            <option value="anulada">Anuladas</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Cliente</label>
                        <input type="text" wire:model="cliente_buscar" placeholder="Buscar cliente..." 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Desde</label>
                        <input type="date" wire:model="fecha_desde" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Hasta</label>
                        <input type="date" wire:model="fecha_hasta" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    <div class="flex items-end space-x-2">
                        <button wire:click="aplicarFiltros" class="flex-1 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition duration-200">
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

    <!-- Tabla de Facturas -->
    <div class="flex flex-wrap -mx-3">
        <div class="w-full px-3">
            <div class="bg-white shadow-lg rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <div>
                            <h6 class="text-lg font-semibold text-gray-800">Lista de Facturas</h6>
                            <p class="text-sm text-gray-600">{{ $this->facturas->total() }} facturas encontradas</p>
                        </div>
                        <div class="flex space-x-2">
                            <button wire:click="exportarFacturas" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition duration-200">
                                <i class="fas fa-file-excel mr-1"></i> Exportar
                            </button>
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Número</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Emisión</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($this->facturas as $factura)
                            <tr class="hover:bg-gray-50 transition duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    FAC-{{ $factura->idFac }}
                                    @if(str_contains($factura->obsFac, 'productos de granja'))
                                        <span class="ml-2 inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            <i class="fas fa-leaf mr-1"></i> Granja
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <div>
                                        <div class="font-medium">{{ $factura->nomCliFac }}</div>
                                        <div class="text-gray-500">{{ $factura->tipDocCliFac }}: {{ $factura->docCliFac }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ date('d/m/Y', strtotime($factura->fecFac)) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    ${{ number_format($factura->totFac, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                               {{ $factura->estFac == 'pagada' ? 'bg-green-100 text-green-800' : 
                                                  ($factura->estFac == 'anulada' ? 'bg-red-100 text-red-800' : 
                                                  ($factura->estFac == 'pendiente' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800')) }}">
                                        {{ ucfirst($factura->estFac) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        @if($factura->estFac == 'emitida')
                                        <button wire:click="cambiarEstadoFactura({{ $factura->idFac }}, 'pagada')" 
                                                class="text-green-600 hover:text-green-900" title="Marcar como pagada">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        @endif
                                        <button class="text-blue-600 hover:text-blue-900" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="text-purple-600 hover:text-purple-900" title="Imprimir">
                                            <i class="fas fa-print"></i>
                                        </button>
                                        <button wire:click="eliminarFactura({{ $factura->idFac }})" 
                                                wire:confirm="¿Estás seguro de eliminar esta factura?"
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
                                        <i class="fas fa-file-invoice text-4xl text-gray-300 mb-4"></i>
                                        <p class="text-lg font-medium mb-2">No hay facturas registradas</p>
                                        <p class="text-sm text-gray-400 mb-4">Comienza creando tu primera factura</p>
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
 
                <!-- Paginación -->
                @if($this->facturas->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $this->facturas->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- ✅ MODAL OPTIMIZADO - Sin wire:model.live problemáticos -->
    @if($modalAbierto)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:key="modal-{{ $modalAbierto }}">
        <div class="relative top-10 mx-auto p-5 border w-11/12 md:w-4/5 lg:w-3/4 shadow-lg rounded-lg bg-white max-h-[90vh] overflow-y-auto">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Nueva Factura</h3>
                    <button wire:click="cerrarModal" class="text-gray-400 hover:text-gray-600 text-xl">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <!-- ✅ Selector de Tipo OPTIMIZADO -->
                <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Factura</label>
                    <div class="flex space-x-4">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" name="tipoFactura" value="cliente" 
                                   wire:click="cambiarTipoFactura('cliente')"
                                   @if($tipoFactura === 'cliente') checked @endif
                                   class="form-radio text-blue-600">
                            <span class="ml-2 text-sm">
                                <i class="fas fa-user mr-1 text-blue-600"></i>
                                Factura a Cliente (Servicios/Otros)
                            </span>
                        </label>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" name="tipoFactura" value="granja" 
                                   wire:click="cambiarTipoFactura('granja')"
                                   @if($tipoFactura === 'granja') checked @endif
                                   class="form-radio text-green-600">
                            <span class="ml-2 text-sm">
                                <i class="fas fa-leaf mr-1 text-green-600"></i>
                                Factura de Productos de Granja
                            </span>
                        </label>
                    </div>
                </div>
                
                @if($this->clientesVacios)
                    <div class="mb-4 p-4 bg-yellow-100 border border-yellow-400 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>
                            <div>
                                <p class="text-yellow-800 font-medium">No hay clientes registrados</p>
                                <p class="text-yellow-700 text-sm">Necesitas crear al menos un cliente antes de generar facturas.</p>
                                <button wire:click="crearClienteRapido" class="mt-2 bg-yellow-600 hover:bg-yellow-700 text-white px-3 py-1 rounded text-sm">
                                    Crear Cliente de Prueba
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
                
                <form wire:submit.prevent="guardarFactura" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Número de Factura</label>
                            <input type="text" wire:model="numero" readonly
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-100">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Fecha de Emisión</label>
                            <input type="date" wire:model="fecFac" 
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                            @error('fecFac') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- ✅ Información del Cliente (solo si tipo = cliente) -->
                    @if($tipoFactura === 'cliente')
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="text-md font-semibold text-gray-800 mb-3">
                            <i class="fas fa-user mr-2"></i>Información del Cliente
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Seleccionar Cliente</label>
                                <div class="relative">
                                    <select wire:change="seleccionarCliente($event.target.value)"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                                        <option value="">Seleccionar cliente...</option>
                                        @foreach($clientes as $cliente)
                                        <option value="{{ $cliente->idCli }}" @if($idCliFac == $cliente->idCli) selected @endif>
                                            {{ $cliente->nomCli }} - {{ $cliente->docCli }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @if($cargandoCliente)
                                        <div class="absolute right-2 top-2">
                                            <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-green-600"></div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nombre del Cliente</label>
                                <input type="text" wire:model="nomCliFac" 
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500" 
                                       placeholder="Nombre del cliente">
                                @error('nomCliFac') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Documento</label>
                                <select wire:model="tipDocCliFac" 
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                                    <option value="CC">Cédula de Ciudadanía</option>
                                    <option value="NIT">NIT</option>
                                    <option value="CE">Cédula de Extranjería</option>
                                    <option value="Pasaporte">Pasaporte</option>
                                </select>
                                @error('tipDocCliFac') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Número de Documento</label>
                                <input type="text" wire:model="docCliFac" 
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500" 
                                       placeholder="Número de documento">
                                @error('docCliFac') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- ✅ Productos de Granja OPTIMIZADO -->
                    @if($tipoFactura === 'granja')
                    <div class="bg-green-50 p-4 rounded-lg">
                        <h4 class="text-md font-semibold text-gray-800 mb-3">
                            <i class="fas fa-leaf mr-2 text-green-600"></i>Productos de la Granja
                        </h4>
                        
                        <!-- Agregar Producto -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Producto</label>
                                <select wire:change="seleccionarProducto($event.target.value)"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                                    <option value="">Seleccionar producto...</option>
                                    @foreach($productosGranja as $producto)
                                    <option value="{{ $producto['id'] }}" @if($productoSeleccionado == $producto['id']) selected @endif>
                                        {{ $producto['nombre'] }} ({{ $producto['categoria'] }})
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Cantidad</label>
                                <input type="number" wire:model="cantidadProducto" min="1" step="0.01"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Precio Unitario</label>
                                <input type="number" wire:model="precioUnitario" min="0" step="0.01"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                            </div>
                            <div class="flex items-end">
                                <button type="button" wire:click="agregarProducto" 
                                        class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition duration-200">
                                    <i class="fas fa-plus mr-2"></i>Agregar
                                </button>
                            </div>
                        </div>

                        <!-- Lista de Productos Agregados -->
                        @if(!empty($productos))
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Cantidad</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Precio Unit.</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Acción</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($productos as $index => $producto)
                                    <tr>
                                        <td class="px-4 py-2 text-sm">
                                            <div>
                                                <div class="font-medium">{{ $producto['nombre'] }}</div>
                                                <div class="text-gray-500 text-xs">{{ $producto['categoria'] }}</div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-2 text-sm">{{ $producto['cantidad'] }} {{ $producto['unidad'] }}</td>
                                        <td class="px-4 py-2 text-sm">${{ number_format($producto['precio_unitario'], 2) }}</td>
                                        <td class="px-4 py-2 text-sm font-medium">${{ number_format($producto['subtotal'], 2) }}</td>
                                        <td class="px-4 py-2 text-sm">
                                            <button type="button" wire:click="eliminarProducto({{ $index }})" 
                                                    class="text-red-600 hover:text-red-900">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endif
                    </div>
                    @endif

                    <!-- ✅ Totales OPTIMIZADO -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="text-md font-semibold text-gray-800 mb-3">
                            <i class="fas fa-calculator mr-2"></i>Totales
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Subtotal
                                    @if($tipoFactura === 'granja')
                                        <span class="text-xs text-gray-500">(Calculado automáticamente)</span>
                                    @endif
                                </label>
                                <input type="number" step="0.01" wire:model="subtotalFac" 
                                       wire:blur="actualizarTotales"
                                       @if($tipoFactura === 'granja') readonly @endif
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500 @if($tipoFactura === 'granja') bg-gray-100 @endif" 
                                       placeholder="0.00" min="0">
                                @error('subtotalFac') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">IVA (%)</label>
                                <input type="number" step="0.01" wire:model="ivaFac" 
                                       wire:blur="actualizarTotales"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                                       min="0" max="100">
                                @error('ivaFac') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Total</label>
                                <input type="text" value="${{ number_format($totFac, 2) }}" readonly
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-100 text-lg font-bold text-green-600">
                                @error('totFac') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Información Adicional -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Método de Pago</label>
                            <select wire:model="metPagFac" 
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                                <option value="">Seleccionar método...</option>
                                <option value="efectivo">Efectivo</option>
                                <option value="transferencia">Transferencia Bancaria</option>
                                <option value="tarjeta_credito">Tarjeta de Crédito</option>
                                <option value="tarjeta_debito">Tarjeta de Débito</option>
                                <option value="cheque">Cheque</option>
                                <option value="credito">Crédito</option>
                            </select>
                            @error('metPagFac') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Observaciones</label>
                            <textarea wire:model="obsFac" rows="3" 
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500" 
                                      placeholder="Observaciones adicionales..."></textarea>
                            @error('obsFac') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4 border-t">
                        <button type="button" wire:click="cerrarModal" 
                                class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition duration-200">
                            Cancelar
                        </button>
                        <button type="submit" 
                                class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg transition duration-200 disabled:opacity-50"
                                @if(($tipoFactura === 'cliente' && $this->clientesVacios) || ($tipoFactura === 'proveedor' && $this->proveedoresVacios)) disabled @endif>
                            <i class="fas fa-save mr-2"></i> 
                            @if($tipoFactura === 'proveedor')
                                Registrar Compra
                            @else
                                Guardar Factura
                            @endif
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- ✅ Loading Overlay SOLO para acciones críticas -->
    <div wire:loading.flex wire:target="guardarFactura" 
         class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50 items-center justify-center">
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <div class="flex items-center space-x-3">
                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-green-600"></div>
                <span class="text-gray-700">Guardando factura...</span>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // ✅ Configurar barra de progreso de Livewire (como wire:navigate)
    document.addEventListener('livewire:init', () => {
        // Activar la barra de progreso para todas las acciones
        Livewire.hook('morph.updating', () => {
            // Mostrar indicador de carga sutil
            if (!document.querySelector('.livewire-progress-bar')) {
                const progressBar = document.createElement('div');
                progressBar.className = 'livewire-progress-bar';
                progressBar.innerHTML = `
                    <div class="fixed top-0 left-0 w-full h-1 bg-blue-200 z-50">
                        <div class="h-full bg-blue-600 transition-all duration-300 ease-out progress-bar-fill" style="width: 0%"></div>
                    </div>
                `;
                document.body.appendChild(progressBar);
                
                // Animar progreso
                const fill = progressBar.querySelector('.progress-bar-fill');
                setTimeout(() => fill.style.width = '30%', 100);
                setTimeout(() => fill.style.width = '60%', 300);
                setTimeout(() => fill.style.width = '90%', 500);
            }
        });
        
        Livewire.hook('morph.updated', () => {
            // Completar y remover barra
            const progressBar = document.querySelector('.livewire-progress-bar');
            if (progressBar) {
                const fill = progressBar.querySelector('.progress-bar-fill');
                fill.style.width = '100%';
                setTimeout(() => {
                    progressBar.remove();
                }, 200);
            }
        });
        
        // ✅ Loading states sutiles en botones específicos
        Livewire.hook('morph.updating', () => {
            // Deshabilitar botones durante la carga
            document.querySelectorAll('[wire\\:click]').forEach(button => {
                if (!button.disabled) {
                    button.style.opacity = '0.7';
                    button.style.pointerEvents = 'none';
                    
                    // Agregar spinner sutil al botón
                    const icon = button.querySelector('i');
                    if (icon && !icon.classList.contains('fa-spin')) {
                        icon.dataset.originalClass = icon.className;
                        icon.className = 'fas fa-spinner fa-spin mr-2';
                    }
                }
            });
        });
        
        Livewire.hook('morph.updated', () => {
            // Rehabilitar botones
            document.querySelectorAll('[wire\\:click]').forEach(button => {
                button.style.opacity = '1';
                button.style.pointerEvents = 'auto';
                
                // Restaurar icono original
                const icon = button.querySelector('i');
                if (icon && icon.dataset.originalClass) {
                    icon.className = icon.dataset.originalClass;
                    delete icon.dataset.originalClass;
                }
            });
        });
    });

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
        Livewire.on('modal-opened', () => {
            console.log('Modal abierto - Clientes cargados');
        });
        
        Livewire.on('modal-closed', () => {
            console.log('Modal cerrado');
        });
        
        Livewire.on('factura-creada', () => {
            console.log('Factura creada exitosamente');
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