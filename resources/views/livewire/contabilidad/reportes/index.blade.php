<?php
// resources/views/livewire/contabilidad/reportes/index.blade.php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

new #[Layout('layouts.auth')] class extends Component {

    // Propiedades para filtros
    public $periodo = 'mes_actual';
    public $fechaInicio = '';
    public $fechaFin = '';
    public $tipoReporte = '';
    public $estadisticas = [];
    public $tendencias_data = [];
    public $categorias_data = [];
    public $cuentas_pendientes_data = [];
    public $fecha_personalizada_desde = '';
    public $fecha_personalizada_hasta = '';

    // Datos de reportes
    public $flujoCaja = [];
    public $estadoResultados = [];
    public $balanceGeneral = [];
    public $cuentasPendientes = [];
    public $indicadoresClave = [];
    public $gastosPorCategoria = [];
    public $tendenciasFinancieras = [];
    public $reportesGenerados = [];
    public $cargandoPdf = false;

    public function mount()
    {
        $this->fechaInicio = now()->startOfMonth()->format('Y-m-d');
        $this->fechaFin = now()->endOfMonth()->format('Y-m-d');
        $this->fecha_personalizada_desde = $this->fechaInicio;
        $this->fecha_personalizada_hasta = $this->fechaFin;

        // Cargar datos inmediatamente
        $this->cargarReportes();

        Log::info('Reportes montados con datos:', [
            'tendencias' => count($this->tendencias_data['labels'] ?? []),
            'categorias' => count($this->categorias_data['labels'] ?? []),
            'cuentas' => count($this->cuentas_pendientes_data['labels'] ?? [])
        ]);
    }

    public function actualizarGraficos()
    {
        $this->cargarReportes();
        $this->dispatch('reporte-actualizado');
        session()->flash('success', 'Gráficos actualizados');
    }

    public function cargarReportes()
    {
        $this->calcularFlujoCaja();
        $this->calcularEstadoResultados();
        $this->calcularBalanceGeneral();
        $this->calcularCuentasPendientes();
        $this->calcularIndicadoresClave();
        $this->calcularGastosPorCategoria();
        $this->calcularTendenciasFinancieras();
    }

    public function calcularFlujoCaja()
    {
        try {
            $fechaInicio = $this->fechaInicio;
            $fechaFin = $this->fechaFin;

            // Ingresos del período
            $ingresos = DB::table('movimientoscontables')
                ->where('tipoMovCont', 'ingreso')
                ->whereBetween('fecMovCont', [$fechaInicio, $fechaFin])
                ->sum('montoMovCont') ?? 0;

            // Egresos del período
            $egresos = DB::table('movimientoscontables')
                ->where('tipoMovCont', 'egreso')
                ->whereBetween('fecMovCont', [$fechaInicio, $fechaFin])
                ->sum('montoMovCont') ?? 0;

            // Flujo neto
            $flujoNeto = $ingresos - $egresos;

            // Flujo por días (últimos 30 días)
            $flujoDiario = DB::table('movimientoscontables')
                ->select(
                    'fecMovCont',
                    DB::raw('SUM(CASE WHEN tipoMovCont = "ingreso" THEN montoMovCont ELSE 0 END) as ingresos_dia'),
                    DB::raw('SUM(CASE WHEN tipoMovCont = "egreso" THEN montoMovCont ELSE 0 END) as egresos_dia')
                )
                ->whereBetween('fecMovCont', [now()->subDays(30), now()])
                ->groupBy('fecMovCont')
                ->orderBy('fecMovCont', 'desc')
                ->limit(10)
                ->get();

            $this->flujoCaja = [
                'total_ingresos' => $ingresos,
                'total_egresos' => $egresos,
                'flujo_neto' => $flujoNeto,
                'flujo_diario' => $flujoDiario,
                'margen' => $ingresos > 0 ? (($flujoNeto / $ingresos) * 100) : 0
            ];
        } catch (\Exception $e) {
            Log::error('Error calculando flujo de caja: ' . $e->getMessage());
            $this->flujoCaja = [
                'total_ingresos' => 0,
                'total_egresos' => 0,
                'flujo_neto' => 0,
                'flujo_diario' => collect(),
                'margen' => 0
            ];
        }
    }

    // ✅ NUEVO: Método principal para generar PDF
    public function generarReportePDF($tipo)
    {
        try {
            $this->validate([
                'fechaInicio' => 'required|date',
                'fechaFin' => 'required|date|after_or_equal:fechaInicio',
            ], [
                'fechaInicio.required' => 'La fecha de inicio es obligatoria',
                'fechaFin.required' => 'La fecha de fin es obligatoria',
                'fechaFin.after_or_equal' => 'La fecha de fin debe ser posterior o igual a la fecha de inicio',
            ]);

            $this->cargandoPdf = true;

            // Generar datos actualizados
            $this->cargarReportes();

            // Obtener datos según el tipo de reporte
            $datosReporte = $this->obtenerDatosParaPDF($tipo);

            // Generar PDF
            $nombreArchivo = $this->crearPDF($tipo, $datosReporte);

            // Registrar en historial
            $this->registrarReporteGenerado($tipo, $nombreArchivo);

            // Registrar en auditoría
            $this->registrarAuditoria('GENERAR_REPORTE_PDF', $tipo, $nombreArchivo);

            $this->cargandoPdf = false;

            session()->flash('success', "Reporte PDF '$tipo' generado exitosamente");

            // Descargar automáticamente
            return response()->download(storage_path("app/reportes/{$nombreArchivo}"));
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->cargandoPdf = false;
            session()->flash('error', 'Error de validación: ' . implode(', ', $e->validator->errors()->all()));
        } catch (\Exception $e) {
            $this->cargandoPdf = false;
            Log::error("Error generando PDF $tipo: " . $e->getMessage());
            session()->flash('error', "Error al generar el reporte PDF: " . $e->getMessage());
        }
    }

    // ✅ NUEVO: Obtener datos específicos para cada tipo de reporte
    private function obtenerDatosParaPDF($tipo)
    {
        $datosComunes = [
            'fecha_generacion' => now()->format('d/m/Y H:i:s'),
            'periodo' => "{$this->fechaInicio} - {$this->fechaFin}",
            'usuario' => auth()->user()->nomUsu . ' ' . auth()->user()->apeUsu,
            'granja' => 'Granja Experimental San Juan Bosco'
        ];

        switch ($tipo) {
            case 'flujo-caja':
                return array_merge($datosComunes, [
                    'titulo' => 'Reporte de Flujo de Caja',
                    'flujo_caja' => $this->flujoCaja,
                    'movimientos_detallados' => $this->obtenerMovimientosDetallados(),
                    'proyeccion' => $this->calcularProyeccionFlujoCaja()
                ]);

            case 'estado-resultados':
                return array_merge($datosComunes, [
                    'titulo' => 'Estado de Resultados',
                    'estado_resultados' => $this->estadoResultados,
                    'ingresos_pecuarios' => $this->calcularIngresosPecuarios(),
                    'costos_por_categoria' => $this->gastosPorCategoria,
                    'analisis_rentabilidad' => $this->calcularAnalisisRentabilidad()
                ]);

            case 'balance-general':
                return array_merge($datosComunes, [
                    'titulo' => 'Balance General',
                    'balance' => $this->balanceGeneral,
                    'detalle_activos' => $this->obtenerDetalleActivos(),
                    'detalle_pasivos' => $this->obtenerDetallePasivos(),
                    'ratios_financieros' => $this->calcularRatiosFinancieros()
                ]);

            case 'cuentas-pendientes':
                return array_merge($datosComunes, [
                    'titulo' => 'Reporte de Cuentas Pendientes',
                    'cuentas_pendientes' => $this->cuentasPendientes,
                    'analisis_vencimientos' => $this->analizarVencimientos(),
                    'recomendaciones' => $this->generarRecomendacionesCobranza()
                ]);

            case 'completo':
                return array_merge($datosComunes, [
                    'titulo' => 'Reporte Financiero Completo',
                    'flujo_caja' => $this->flujoCaja,
                    'estado_resultados' => $this->estadoResultados,
                    'balance_general' => $this->balanceGeneral,
                    'cuentas_pendientes' => $this->cuentasPendientes,
                    'indicadores_clave' => $this->indicadoresClave,
                    'ingresos_pecuarios' => $this->calcularIngresosPecuarios(),
                    'resumen_ejecutivo' => $this->generarResumenEjecutivo()
                ]);

            default:
                throw new \Exception("Tipo de reporte no válido: $tipo");
        }
    }

    // ✅ NUEVO: Crear archivo PDF
    private function crearPDF($tipo, $datosReporte)
    {
        // Asegurar que el directorio existe
        Storage::makeDirectory('reportes');

        $nombreArchivo = 'reporte-' . $tipo . '-' . now()->format('Y-m-d-H-i-s') . '.pdf';

        // Seleccionar vista específica según el tipo
        $vistaTemplate = match ($tipo) {
            'flujo-caja' => 'pdf.reportes.flujo-caja',
            'estado-resultados' => 'pdf.reportes.estado-resultados',
            'balance-general' => 'pdf.reportes.balance-general',
            'cuentas-pendientes' => 'pdf.reportes.cuentas-pendientes',
            'completo' => 'pdf.reportes.completo',
            default => 'pdf.reportes.generico'
        };

        // Generar PDF con orientación adecuada
        $orientacion = ($tipo === 'completo' || $tipo === 'balance-general') ? 'landscape' : 'portrait';

        $pdf = PDF::loadView($vistaTemplate, $datosReporte)
            ->setPaper('a4', $orientacion)
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => true,
                'defaultFont' => 'Arial'
            ]);

        // Guardar archivo
        $rutaCompleta = storage_path("app/reportes/{$nombreArchivo}");
        $pdf->save($rutaCompleta);

        return $nombreArchivo;
    }

    // ✅ NUEVO: Registrar reporte en historial
    private function registrarReporteGenerado($tipo, $nombreArchivo)
    {
        DB::table('reportes')->insert([
            'idUsuRep' => auth()->id(),
            'nomRep' => ucfirst(str_replace('-', ' ', $tipo)),
            'tipRep' => $this->mapearTipoReporte($tipo),
            'desRep' => "Reporte generado para el período {$this->fechaInicio} - {$this->fechaFin}",
            'fecRep' => now()->format('Y-m-d'),
            'formatoRep' => 'pdf',
            'archivoRep' => $nombreArchivo,
            'estadoRep' => 'activo',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    // ✅ NUEVO: Mapear tipos de reporte para la BD
    private function mapearTipoReporte($tipo)
    {
        return match ($tipo) {
            'flujo-caja', 'balance-general' => 'financiero',
            'estado-resultados' => 'produccion',
            'cuentas-pendientes' => 'financiero',
            'completo' => 'financiero',
            default => 'financiero'
        };
    }

    // ✅ NUEVO: Validaciones robustas
    public function validarDatosReporte()
    {
        return $this->validate([
            'fechaInicio' => [
                'required',
                'date',
                'before_or_equal:' . now()->format('Y-m-d'),
                'after_or_equal:' . now()->subYears(5)->format('Y-m-d')
            ],
            'fechaFin' => [
                'required',
                'date',
                'after_or_equal:fechaInicio',
                'before_or_equal:' . now()->format('Y-m-d')
            ],
            'periodo' => 'required|in:hoy,semana,mes_actual,mes_anterior,trimestre,semestre,ano,personalizado',
            'tipoReporte' => 'nullable|in:flujo-caja,estado-resultados,balance-general,cuentas-pendientes,completo'
        ], [
            'fechaInicio.required' => 'La fecha de inicio es obligatoria',
            'fechaInicio.date' => 'La fecha de inicio debe ser una fecha válida',
            'fechaInicio.before_or_equal' => 'La fecha de inicio no puede ser futura',
            'fechaInicio.after_or_equal' => 'La fecha de inicio no puede ser anterior a 5 años',
            'fechaFin.required' => 'La fecha de fin es obligatoria',
            'fechaFin.date' => 'La fecha de fin debe ser una fecha válida',
            'fechaFin.after_or_equal' => 'La fecha de fin debe ser posterior o igual a la fecha de inicio',
            'fechaFin.before_or_equal' => 'La fecha de fin no puede ser futura',
            'periodo.required' => 'Debe seleccionar un período',
            'periodo.in' => 'El período seleccionado no es válido'
        ]);
    }

    // ✅ MEJORAR: Método con validación
    public function aplicarPeriodoPersonalizado()
    {
        try {
            $this->validate([
                'fecha_personalizada_desde' => 'required|date|before_or_equal:fecha_personalizada_hasta',
                'fecha_personalizada_hasta' => 'required|date|after_or_equal:fecha_personalizada_desde|before_or_equal:' . now()->format('Y-m-d')
            ], [
                'fecha_personalizada_desde.required' => 'Debe seleccionar la fecha de inicio',
                'fecha_personalizada_desde.before_or_equal' => 'La fecha de inicio debe ser anterior a la fecha de fin',
                'fecha_personalizada_hasta.required' => 'Debe seleccionar la fecha de fin',
                'fecha_personalizada_hasta.after_or_equal' => 'La fecha de fin debe ser posterior a la fecha de inicio',
                'fecha_personalizada_hasta.before_or_equal' => 'La fecha de fin no puede ser futura'
            ]);

            // Validar rango máximo (no más de 2 años)
            $fechaInicio = Carbon::parse($this->fecha_personalizada_desde);
            $fechaFin = Carbon::parse($this->fecha_personalizada_hasta);

            if ($fechaInicio->diffInMonths($fechaFin) > 24) {
                throw new \Exception('El rango de fechas no puede ser mayor a 24 meses');
            }

            $this->fechaInicio = $this->fecha_personalizada_desde;
            $this->fechaFin = $this->fecha_personalizada_hasta;

            $this->cargarReportes();
            $this->dispatch('reporte-actualizado');

            session()->flash('success', 'Período personalizado aplicado correctamente');
        } catch (\Illuminate\Validation\ValidationException $e) {
            session()->flash('error', 'Error de validación: ' . implode(', ', $e->validator->errors()->all()));
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    // ✅ NUEVO: Sistema de auditoría para reportes
    private function registrarAuditoria($operacion, $detalle, $archivo = null)
    {
        try {
            DB::table('auditoria')->insert([
                'idUsuAud' => auth()->id(),
                'usuAud' => auth()->user()->nomUsu . ' ' . auth()->user()->apeUsu,
                'rolAud' => auth()->user()->rol->nomRol ?? 'Usuario',
                'opeAud' => $operacion,
                'tablaAud' => 'reportes_contables',
                'regAud' => $detalle,
                'desAud' => $this->generarDescripcionAuditoria($operacion, $detalle, $archivo),
                'ipAud' => request()->ip(),
                'fecAud' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error registrando auditoría: ' . $e->getMessage());
        }
    }

    // ✅ NUEVO: Generar descripción detallada para auditoría
    private function generarDescripcionAuditoria($operacion, $detalle, $archivo)
    {
        $descripciones = [
            'GENERAR_REPORTE_PDF' => "Generó reporte PDF '$detalle' para período {$this->fechaInicio} - {$this->fechaFin}. Archivo: $archivo",
            'CONSULTAR_REPORTES' => "Consultó reportes contables para período {$this->fechaInicio} - {$this->fechaFin}",
            'EXPORTAR_DATOS' => "Exportó datos contables en formato $detalle",
            'MODIFICAR_PARAMETROS' => "Modificó parámetros de reporte: $detalle"
        ];

        return $descripciones[$operacion] ?? "Operación $operacion en reportes contables: $detalle";
    }

    // ✅ MEJORAR: Método con auditoría
    public function generarReporte($tipo)
    {
        try {
            $this->validarDatosReporte();

            Log::info("Generando reporte: $tipo");

            // Registrar consulta en auditoría
            $this->registrarAuditoria('CONSULTAR_REPORTES', $tipo);

            switch ($tipo) {
                case 'flujo-caja':
                    $this->calcularFlujoCaja();
                    break;
                case 'estado-resultados':
                    $this->calcularEstadoResultados();
                    break;
                case 'balance-general':
                    $this->calcularBalanceGeneral();
                    break;
                case 'cuentas-pendientes':
                    $this->calcularCuentasPendientes();
                    break;
                case 'categorias':
                    $this->calcularGastosPorCategoria();
                    break;
                case 'tendencias':
                    $this->calcularTendenciasFinancieras();
                    break;
                case 'proyecciones':
                    $this->calcularProyecciones();
                    break;
            }

            $this->cargarReportes();
            $this->dispatch('reporte-actualizado');

            session()->flash('success', "Reporte '$tipo' generado exitosamente");
        } catch (\Illuminate\Validation\ValidationException $e) {
            session()->flash('error', 'Error de validación: ' . implode(', ', $e->validator->errors()->all()));
        } catch (\Exception $e) {
            Log::error("Error generando reporte $tipo: " . $e->getMessage());
            session()->flash('error', "Error al generar el reporte '$tipo': " . $e->getMessage());
        }
    }

    public function calcularEstadoResultados()
    {
        try {
            $fechaInicio = $this->fechaInicio;
            $fechaFin = $this->fechaFin;

            // Ingresos por categoría
            $ingresosPorCategoria = DB::table('movimientoscontables')
                ->where('tipoMovCont', 'ingreso')
                ->whereBetween('fecMovCont', [$fechaInicio, $fechaFin])
                ->select('catMovCont', DB::raw('SUM(montoMovCont) as total'))
                ->groupBy('catMovCont')
                ->orderBy('total', 'desc')
                ->get();

            // Gastos por categoría
            $gastosPorCategoria = DB::table('movimientoscontables')
                ->where('tipoMovCont', 'egreso')
                ->whereBetween('fecMovCont', [$fechaInicio, $fechaFin])
                ->select('catMovCont', DB::raw('SUM(montoMovCont) as total'))
                ->groupBy('catMovCont')
                ->orderBy('total', 'desc')
                ->get();

            $totalIngresos = $ingresosPorCategoria->sum('total');
            $totalGastos = $gastosPorCategoria->sum('total');

            $this->estadoResultados = [
                'ingresos_por_categoria' => $ingresosPorCategoria,
                'gastos_por_categoria' => $gastosPorCategoria,
                'total_ingresos' => $totalIngresos,
                'total_gastos' => $totalGastos,
                'utilidad_bruta' => $totalIngresos - $totalGastos,
                'margen_utilidad' => $totalIngresos > 0 ? ((($totalIngresos - $totalGastos) / $totalIngresos) * 100) : 0
            ];
        } catch (\Exception $e) {
            Log::error('Error calculando estado de resultados: ' . $e->getMessage());
            $this->estadoResultados = [
                'ingresos_por_categoria' => collect(),
                'gastos_por_categoria' => collect(),
                'total_ingresos' => 0,
                'total_gastos' => 0,
                'utilidad_bruta' => 0,
                'margen_utilidad' => 0
            ];
        }
    }

    public function calcularCuentasPendientes()
    {
        try {
            // Análisis de vencimientos
            $vencidas = DB::table('cuentaspendientes')
                ->where('fecVencimiento', '<', now())
                ->where('estCuePen', '!=', 'pagado')
                ->selectRaw('tipCuePen, COUNT(*) as cantidad, SUM(montoSaldo) as total')
                ->groupBy('tipCuePen')
                ->get();

            $proximasVencer = DB::table('cuentaspendientes')
                ->whereBetween('fecVencimiento', [now(), now()->addDays(7)])
                ->where('estCuePen', '!=', 'pagado')
                ->selectRaw('tipCuePen, COUNT(*) as cantidad, SUM(montoSaldo) as total')
                ->groupBy('tipCuePen')
                ->get();

            $alDia = DB::table('cuentaspendientes')
                ->where('fecVencimiento', '>', now()->addDays(7))
                ->where('estCuePen', '!=', 'pagado')
                ->count();

            $this->cuentasPendientes = [
                'vencidas' => $vencidas,
                'proximas_vencer' => $proximasVencer,
                'total_vencidas' => $vencidas->sum('total'),
                'total_proximas' => $proximasVencer->sum('total')
            ];

            // ✅ AGREGAR: Datos para el gráfico
            $this->cuentas_pendientes_data = [
                'labels' => ['Al día', 'Próximas a vencer', 'Vencidas'],
                'data' => [
                    $alDia,
                    $proximasVencer->sum('cantidad'),
                    $vencidas->sum('cantidad')
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error calculando cuentas pendientes: ' . $e->getMessage());
            $this->cuentasPendientes = [
                'vencidas' => collect(),
                'proximas_vencer' => collect(),
                'total_vencidas' => 0,
                'total_proximas' => 0
            ];
            $this->cuentas_pendientes_data = [
                'labels' => [],
                'data' => []
            ];
        }
    }

    // ✅ AGREGAR: Método para calcular ingresos pecuarios
    public function calcularIngresosPecuarios()
    {
        try {
            $fechaInicio = $this->fechaInicio;
            $fechaFin = $this->fechaFin;

            // Ingresos por venta de animales
            $ventasAnimales = DB::table('facturas as f')
                ->join('facturadetalles as fd', 'f.idFac', '=', 'fd.idFacDet')
                ->join('animales as a', 'fd.idAniDet', '=', 'a.idAni')
                ->whereBetween('f.fecFac', [$fechaInicio, $fechaFin])
                ->where('f.estFac', '!=', 'anulada')
                ->select(
                    'a.espAni',
                    DB::raw('COUNT(*) as cantidad_vendida'),
                    DB::raw('SUM(fd.subtotalDet) as total_ingresos')
                )
                ->groupBy('a.espAni')
                ->get();

            // Ingresos por producción animal (leche, huevos, etc.)
            $produccionAnimal = DB::table('produccionanimal as pa')
                ->join('animales as a', 'pa.idAniPro', '=', 'a.idAni')
                ->join('inventario as i', 'i.idProduccionAnimal', '=', 'pa.idProAni')
                ->leftJoin('facturas as f', 'i.idFac', '=', 'f.idFac')
                ->whereBetween('pa.fecProAni', [$fechaInicio, $fechaFin])
                ->select(
                    'pa.tipProAni',
                    'a.espAni',
                    DB::raw('SUM(pa.canProAni) as cantidad_producida'),
                    DB::raw('SUM(CASE WHEN f.idFac IS NOT NULL THEN i.costoTotInv ELSE 0 END) as ingresos_vendidos'),
                    DB::raw('SUM(i.costoTotInv) as valor_total_produccion')
                )
                ->groupBy('pa.tipProAni', 'a.espAni')
                ->get();

            // Costos veterinarios y alimentación
            $costosVeterinarios = DB::table('historialmedico as hm')
                ->join('comprasgastos as cg', 'cg.desComGas', 'LIKE', DB::raw("CONCAT('%', hm.desHisMed, '%')"))
                ->whereBetween('hm.fecHisMed', [$fechaInicio, $fechaFin])
                ->where('hm.tipHisMed', 'tratamiento')
                ->sum('cg.monComGas') ?? 0;

            return [
                'ventas_animales' => $ventasAnimales,
                'produccion_animal' => $produccionAnimal,
                'costos_veterinarios' => $costosVeterinarios,
                'rentabilidad_por_especie' => $this->calcularRentabilidadPorEspecie($ventasAnimales, $produccionAnimal)
            ];
        } catch (\Exception $e) {
            Log::error('Error calculando ingresos pecuarios: ' . $e->getMessage());
            return [
                'ventas_animales' => collect(),
                'produccion_animal' => collect(),
                'costos_veterinarios' => 0,
                'rentabilidad_por_especie' => collect()
            ];
        }
    }

    // ✅ AGREGAR: Método para calcular rentabilidad por especie
    private function calcularRentabilidadPorEspecie($ventasAnimales, $produccionAnimal)
    {
        $rentabilidad = collect();

        foreach ($ventasAnimales as $venta) {
            $produccion = $produccionAnimal->where('espAni', $venta->espAni);
            $ingresosTotales = $venta->total_ingresos + $produccion->sum('ingresos_vendidos');

            // Calcular costos asociados a la especie
            $costosAlimentacion = DB::table('inventario as i')
                ->join('insumos as ins', 'i.idIns', '=', 'ins.idIns')
                ->where('ins.categoria', 'alimento')
                ->where('i.tipMovInv', 'consumo')
                ->whereBetween('i.fecMovInv', [$this->fechaInicio, $this->fechaFin])
                ->sum('i.costoTotInv') ?? 0;

            $margenRentabilidad = $costosAlimentacion > 0
                ? (($ingresosTotales - $costosAlimentacion) / $costosAlimentacion) * 100
                : 0;

            $rentabilidad->push([
                'especie' => $venta->espAni,
                'ingresos_totales' => $ingresosTotales,
                'costos_totales' => $costosAlimentacion,
                'margen_rentabilidad' => $margenRentabilidad,
                'cantidad_animales' => $venta->cantidad_vendida
            ]);
        }

        return $rentabilidad;
    }

    // ✅ AGREGAR: Método mejorado para balance general
    public function calcularBalanceGeneral()
    {
        try {
            // ACTIVOS CORRIENTES
            $efectivoReal = DB::table('movimientoscontables')
                ->selectRaw('SUM(CASE WHEN tipoMovCont = "ingreso" THEN montoMovCont ELSE -montoMovCont END) as saldo')
                ->whereBetween('fecMovCont', [now()->startOfYear(), $this->fechaFin])
                ->value('saldo') ?? 0;

            $cuentasPorCobrar = DB::table('cuentaspendientes')
                ->where('tipCuePen', 'por_cobrar')
                ->where('estCuePen', '!=', 'pagado')
                ->sum('montoSaldo') ?? 0;

            // INVENTARIO DE INSUMOS (valorizado)
            $valorInventarioInsumos = DB::table('v_stock_insumos')
                ->sum('valorInventario') ?? 0;

            // INVENTARIO DE PRODUCCIÓN ANIMAL
            $valorInventarioProduccion = DB::table('inventario as i')
                ->whereNotNull('i.idProduccionAnimal')
                ->where('i.tipMovInv', 'entrada')
                ->sum('i.costoTotInv') ?? 0;

            // ACTIVOS FIJOS - HERRAMIENTAS (con depreciación)
            $valorHerramientas = DB::table('v_stock_herramientas')
                ->sum('valorInventario') ?? 0;

            // Calcular depreciación (ejemplo: 10% anual)
            $añosUso = now()->diffInYears(now()->startOfYear()); // Simplificado
            $depreciacionAcumulada = $valorHerramientas * 0.10 * $añosUso;
            $valorNetHerramientas = max(0, $valorHerramientas - $depreciacionAcumulada);

            // TOTAL ACTIVOS
            $totalActivosCorrientes = $efectivoReal + $cuentasPorCobrar + $valorInventarioInsumos + $valorInventarioProduccion;
            $totalActivosFijos = $valorNetHerramientas;
            $totalActivos = $totalActivosCorrientes + $totalActivosFijos;

            // PASIVOS
            $cuentasPorPagar = DB::table('cuentaspendientes')
                ->where('tipCuePen', 'por_pagar')
                ->where('estCuePen', '!=', 'pagado')
                ->sum('montoSaldo') ?? 0;

            // PATRIMONIO
            $patrimonio = $totalActivos - $cuentasPorPagar;

            // RATIOS FINANCIEROS
            $razonLiquidez = $cuentasPorPagar > 0 ? ($totalActivosCorrientes / $cuentasPorPagar) : 0;
            $razonDeuda = $totalActivos > 0 ? ($cuentasPorPagar / $totalActivos) * 100 : 0;

            $this->balanceGeneral = [
                // Activos Corrientes
                'efectivo' => max(0, $efectivoReal),
                'cuentas_por_cobrar' => $cuentasPorCobrar,
                'inventario_insumos' => $valorInventarioInsumos,
                'inventario_produccion' => $valorInventarioProduccion,
                'total_activos_corrientes' => $totalActivosCorrientes,

                // Activos Fijos
                'valor_herramientas' => $valorHerramientas,
                'depreciacion_acumulada' => $depreciacionAcumulada,
                'valor_neto_herramientas' => $valorNetHerramientas,
                'total_activos_fijos' => $totalActivosFijos,

                // Totales
                'total_activos' => $totalActivos,
                'total_pasivos' => $cuentasPorPagar,
                'patrimonio' => $patrimonio,

                // Ratios
                'razon_liquidez' => $razonLiquidez,
                'razon_deuda' => $razonDeuda,
                'rotacion_inventario' => $this->calcularRotacionInventario()
            ];
        } catch (\Exception $e) {
            Log::error('Error calculando balance general mejorado: ' . $e->getMessage());
            $this->balanceGeneral = $this->getBalanceDefault();
        }
    }

    // ✅ AGREGAR: Calcular rotación de inventario
    private function calcularRotacionInventario()
    {
        try {
            $costoVentas = DB::table('inventario')
                ->where('tipMovInv', 'venta')
                ->whereBetween('fecMovInv', [$this->fechaInicio, $this->fechaFin])
                ->sum('costoTotInv') ?? 0;

            $inventarioPromedio = DB::table('v_stock_insumos')
                ->avg('valorInventario') ?? 0;

            return $inventarioPromedio > 0 ? ($costoVentas / $inventarioPromedio) : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }


    public function exportarTodo()
    {
        session()->flash('info', 'Función de exportación completa en desarrollo');
    }

    public function actualizarReportes()
    {
        $this->cargarReportes();

        // ✅ IMPORTANTE: Emitir evento para actualizar gráficos
        $this->dispatch('reporte-actualizado');

        session()->flash('success', 'Reportes actualizados correctamente');
    }

    public function calcularIndicadoresClave()
    {
        try {
            $mesAnterior = now()->subMonth();
            $mesActual = now();

            // Ingresos mes actual vs anterior
            $ingresosMesActual = DB::table('movimientoscontables')
                ->where('tipoMovCont', 'ingreso')
                ->whereMonth('fecMovCont', $mesActual->month)
                ->whereYear('fecMovCont', $mesActual->year)
                ->sum('montoMovCont') ?? 0;

            $ingresosMesAnterior = DB::table('movimientoscontables')
                ->where('tipoMovCont', 'ingreso')
                ->whereMonth('fecMovCont', $mesAnterior->month)
                ->whereYear('fecMovCont', $mesAnterior->year)
                ->sum('montoMovCont') ?? 0;

            // Gastos mes actual vs anterior
            $gastosMesActual = DB::table('movimientoscontables')
                ->where('tipoMovCont', 'egreso')
                ->whereMonth('fecMovCont', $mesActual->month)
                ->whereYear('fecMovCont', $mesActual->year)
                ->sum('montoMovCont') ?? 0;

            $gastosMesAnterior = DB::table('movimientoscontables')
                ->where('tipoMovCont', 'egreso')
                ->whereMonth('fecMovCont', $mesAnterior->month)
                ->whereYear('fecMovCont', $mesAnterior->year)
                ->sum('montoMovCont') ?? 0;

            // Calcular variaciones
            $variacionIngresos = $ingresosMesAnterior > 0
                ? (($ingresosMesActual - $ingresosMesAnterior) / $ingresosMesAnterior) * 100
                : 0;

            $variacionGastos = $gastosMesAnterior > 0
                ? (($gastosMesActual - $gastosMesAnterior) / $gastosMesAnterior) * 100
                : 0;

            $margenActual = $ingresosMesActual > 0 ? ((($ingresosMesActual - $gastosMesActual) / $ingresosMesActual) * 100) : 0;

            // ✅ CORREGIR: Asignar tanto a indicadoresClave como a estadisticas
            $this->indicadoresClave = [
                'ingresos_mes_actual' => $ingresosMesActual,
                'ingresos_mes_anterior' => $ingresosMesAnterior,
                'variacion_ingresos' => $variacionIngresos,
                'gastos_mes_actual' => $gastosMesActual,
                'gastos_mes_anterior' => $gastosMesAnterior,
                'variacion_gastos' => $variacionGastos,
                'margen_mes_actual' => $margenActual
            ];

            // ✅ AGREGAR: Estadísticas para la vista
            $this->estadisticas = [
                'totalIngresos' => $ingresosMesActual,
                'totalGastos' => $gastosMesActual,
                'balance' => $ingresosMesActual - $gastosMesActual,
                'margenGanancia' => $margenActual,
                'roi' => $ingresosMesActual > 0 ? (($ingresosMesActual - $gastosMesActual) / $gastosMesActual * 100) : 0,
                'liquidez' => $this->balanceGeneral['razon_liquidez'] ?? 'N/A',
                'diasPromedioCobro' => $this->calcularDiasPromedioCobro()
            ];
        } catch (\Exception $e) {
            Log::error('Error calculando indicadores clave: ' . $e->getMessage());

            // Valores por defecto
            $this->indicadoresClave = [
                'ingresos_mes_actual' => 0,
                'ingresos_mes_anterior' => 0,
                'variacion_ingresos' => 0,
                'gastos_mes_actual' => 0,
                'gastos_mes_anterior' => 0,
                'variacion_gastos' => 0,
                'margen_mes_actual' => 0
            ];

            $this->estadisticas = [
                'totalIngresos' => 0,
                'totalGastos' => 0,
                'balance' => 0,
                'margenGanancia' => 0,
                'roi' => 0,
                'liquidez' => 'N/A',
                'diasPromedioCobro' => 0
            ];
        }
    }

    public function calcularDiasPromedioCobro()
    {
        try {
            $cuentasPorCobrar = DB::table('cuentaspendientes')
                ->where('tipCuePen', 'por_cobrar')
                ->where('estCuePen', '!=', 'pagado')
                ->get();

            if ($cuentasPorCobrar->isEmpty()) {
                return 0;
            }

            $totalDias = 0;
            $contador = 0;

            foreach ($cuentasPorCobrar as $cuenta) {
                $diasVencimiento = Carbon::parse($cuenta->fecVencimiento)->diffInDays(Carbon::now());
                $totalDias += $diasVencimiento;
                $contador++;
            }

            return $contador > 0 ? round($totalDias / $contador) : 0;
        } catch (\Exception $e) {
            Log::error('Error calculando días promedio de cobro: ' . $e->getMessage());
            return 0;
        }
    }

    public function calcularGastosPorCategoria()
    {
        try {
            $gastos = DB::table('comprasgastos')
                ->where('tipComGas', 'gasto')
                ->whereBetween('fecComGas', [$this->fechaInicio, $this->fechaFin])
                ->select('catComGas', DB::raw('SUM(monComGas) as total'), DB::raw('COUNT(*) as cantidad'))
                ->groupBy('catComGas')
                ->orderBy('total', 'desc')
                ->get();

            $this->gastosPorCategoria = $gastos;

            // ✅ AGREGAR: Datos para el gráfico
            $this->categorias_data = [
                'labels' => $gastos->pluck('catComGas')->toArray(),
                'data' => $gastos->pluck('total')->toArray()
            ];
        } catch (\Exception $e) {
            Log::error('Error calculando gastos por categoría: ' . $e->getMessage());
            $this->gastosPorCategoria = collect();
            $this->categorias_data = [
                'labels' => [],
                'data' => []
            ];
        }
    }

    public function calcularTendenciasFinancieras()
    {
        try {
            // Obtener datos de los últimos 12 meses
            $tendencias = [];
            $labels = [];
            $datosIngresos = [];
            $datosGastos = [];

            for ($i = 11; $i >= 0; $i--) {
                $fecha = now()->subMonths($i);

                $ingresos = DB::table('movimientoscontables')
                    ->where('tipoMovCont', 'ingreso')
                    ->whereMonth('fecMovCont', $fecha->month)
                    ->whereYear('fecMovCont', $fecha->year)
                    ->sum('montoMovCont') ?? 0;

                $egresos = DB::table('movimientoscontables')
                    ->where('tipoMovCont', 'egreso')
                    ->whereMonth('fecMovCont', $fecha->month)
                    ->whereYear('fecMovCont', $fecha->year)
                    ->sum('montoMovCont') ?? 0;

                $mesLabel = $fecha->format('M Y');
                $labels[] = $mesLabel;
                $datosIngresos[] = $ingresos;
                $datosGastos[] = $egresos;

                $tendencias[] = [
                    'mes' => $mesLabel,
                    'ingresos' => $ingresos,
                    'egresos' => $egresos,
                    'utilidad' => $ingresos - $egresos
                ];
            }

            $this->tendenciasFinancieras = collect($tendencias);

            // ✅ AGREGAR: Datos para el gráfico
            $this->tendencias_data = [
                'labels' => $labels,
                'ingresos' => $datosIngresos,
                'gastos' => $datosGastos
            ];
        } catch (\Exception $e) {
            Log::error('Error calculando tendencias financieras: ' . $e->getMessage());
            $this->tendenciasFinancieras = collect();
            $this->tendencias_data = [
                'labels' => [],
                'ingresos' => [],
                'gastos' => []
            ];
        }
    }

    public function actualizarPeriodo()
    {
        switch ($this->periodo) {
            case 'hoy':
                $this->fechaInicio = now()->format('Y-m-d');
                $this->fechaFin = now()->format('Y-m-d');
                break;
            case 'semana':
                $this->fechaInicio = now()->startOfWeek()->format('Y-m-d');
                $this->fechaFin = now()->endOfWeek()->format('Y-m-d');
                break;
            case 'mes_actual':
                $this->fechaInicio = now()->startOfMonth()->format('Y-m-d');
                $this->fechaFin = now()->endOfMonth()->format('Y-m-d');
                break;
            case 'mes_anterior':
                $this->fechaInicio = now()->subMonth()->startOfMonth()->format('Y-m-d');
                $this->fechaFin = now()->subMonth()->endOfMonth()->format('Y-m-d');
                break;
            case 'trimestre':
                $this->fechaInicio = now()->startOfQuarter()->format('Y-m-d');
                $this->fechaFin = now()->endOfQuarter()->format('Y-m-d');
                break;
            case 'semestre':
                $this->fechaInicio = now()->month <= 6
                    ? now()->startOfYear()->format('Y-m-d')
                    : now()->startOfYear()->addMonths(6)->format('Y-m-d');
                $this->fechaFin = now()->month <= 6
                    ? now()->startOfYear()->addMonths(5)->endOfMonth()->format('Y-m-d')
                    : now()->endOfYear()->format('Y-m-d');
                break;
            case 'ano':
                $this->fechaInicio = now()->startOfYear()->format('Y-m-d');
                $this->fechaFin = now()->endOfYear()->format('Y-m-d');
                break;
            case 'personalizado':
                // Las fechas se configuran en aplicarPeriodoPersonalizado()
                return;
        }

        $this->cargarReportes();

        // ✅ IMPORTANTE: Emitir evento para actualizar gráficos
        $this->dispatch('reporte-actualizado');
    }
    public function verificarDatos()
    {
        try {
            // Verificar movimientos contables
            $totalMovimientos = DB::table('movimientoscontables')->count();
            $ingresos = DB::table('movimientoscontables')->where('tipoMovCont', 'ingreso')->count();
            $egresos = DB::table('movimientoscontables')->where('tipoMovCont', 'egreso')->count();

            // Verificar gastos por categoría
            $totalGastos = DB::table('comprasgastos')->count();

            // Verificar cuentas pendientes
            $totalCuentas = DB::table('cuentaspendientes')->count();

            Log::info("DEBUG REPORTES:");
            Log::info("- Movimientos: $totalMovimientos (Ingresos: $ingresos, Egresos: $egresos)");
            Log::info("- Gastos: $totalGastos");
            Log::info("- Cuentas: $totalCuentas");
            Log::info("- Período: {$this->fechaInicio} - {$this->fechaFin}");
            Log::info("- Tendencias data: ", $this->tendencias_data);
            Log::info("- Categorías data: ", $this->categorias_data);

            session()->flash('info', "Datos verificados. Total movimientos: $totalMovimientos. Ver logs para detalles.");
        } catch (\Exception $e) {
            Log::error('Error verificando datos: ' . $e->getMessage());
            session()->flash('error', 'Error al verificar datos');
        }
    }
}; ?>

@section('title', 'Reportes Contables')

<div class="w-full px-6 py-6 mx-auto">
    
    <!-- ✅ SECCIÓN 1: Header Principal -->
    <div class="flex flex-wrap -mx-3 mb-6">
        <div class="w-full px-3">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                <div class="mb-4 md:mb-0">
                    <nav class="text-sm text-gray-600 mb-2">
                        <a href="{{ route('contabilidad.index') }}" wire:navigate class="hover:text-blue-600">Dashboard</a>
                        <span class="mx-2">/</span>
                        <span class="text-gray-900">Reportes</span>
                    </nav>
                    <h1 class="text-3xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-chart-line mr-3 text-indigo-600"></i>
                        Reportes Contables
                    </h1>
                    <p class="text-gray-600 mt-1">Análisis detallado y reportes financieros</p>
                </div>
                <div class="flex space-x-3">
                    <button wire:click="generarReportePDF('completo')"
                        wire:loading.attr="disabled"
                        wire:target="generarReportePDF"
                        class="bg-indigo-600 hover:bg-indigo-700 disabled:bg-indigo-400 text-white px-4 py-2 rounded-lg flex items-center transition duration-200">
                        <div wire:loading.remove wire:target="generarReportePDF">
                            <i class="fas fa-file-pdf mr-2"></i> Exportar PDF
                        </div>
                        <div wire:loading wire:target="generarReportePDF" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Generando PDF...
                        </div>
                    </button>

                    <button wire:click="actualizarReportes"
                        wire:loading.attr="disabled"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center transition duration-200">
                        <div wire:loading.remove wire:target="actualizarReportes">
                            <i class="fas fa-sync mr-2"></i> Actualizar
                        </div>
                        <div wire:loading wire:target="actualizarReportes">
                            <i class="fas fa-sync fa-spin mr-2"></i> Actualizando...
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ SECCIÓN 2: Reportes Generados (si existen) -->
    @if(!empty($reportesGenerados))
    <div class="flex flex-wrap -mx-3 mb-6">
        <div class="w-full px-3">
            <div class="bg-white shadow-lg rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <h6 class="text-lg font-semibold text-gray-800">Reportes Generados</h6>
                    <p class="text-sm text-gray-600">Descargar reportes PDF generados recientemente</p>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($reportesGenerados as $reporte)
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition duration-200">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center">
                                    <div class="bg-red-100 p-2 rounded-lg mr-3">
                                        <i class="fas fa-file-pdf text-red-600"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-800">{{ $reporte['nombre'] }}</h4>
                                        <p class="text-sm text-gray-600">{{ $reporte['fecha'] }}</p>
                                    </div>
                                </div>
                                <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">
                                    {{ $reporte['tamano'] }}
                                </span>
                            </div>
                            <div class="flex space-x-2">
                                <a href="{{ route('reportes.descargar', $reporte['archivo']) }}"
                                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-center px-3 py-2 rounded text-sm transition duration-200">
                                    <i class="fas fa-download mr-1"></i> Descargar
                                </a>
                                <button wire:click="eliminarReporte('{{ $reporte['archivo'] }}')"
                                    class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded text-sm transition duration-200"
                                    onclick="confirm('¿Está seguro de eliminar este reporte?') || event.stopImmediatePropagation()">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- ✅ SECCIÓN 3: Modal de Confirmación -->
    <div x-data="{ showModal: false }" @keydown.escape="showModal = false">
        {{-- Trigger button --}}
        <div class="flex justify-end mb-6">
            <button @click="showModal = true"
                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm transition duration-200">
                <i class="fas fa-trash mr-1"></i> Limpiar Historial
            </button>
        </div>

        {{-- Modal --}}
        <div x-show="showModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 overflow-y-auto"
            style="display: none;">

            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fas fa-exclamation-triangle text-red-600"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">
                                    Confirmar Eliminación
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        ¿Está seguro que desea eliminar todo el historial de reportes? Esta acción no se puede deshacer.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button wire:click="limpiarHistorial"
                            @click="showModal = false"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Eliminar
                        </button>
                        <button @click="showModal = false"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ SECCIÓN 4: Selector de Período -->
    <div class="flex flex-wrap -mx-3 mb-6">
        <div class="w-full px-3">
            <div class="bg-white shadow-lg rounded-lg p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Período</label>
                        <select wire:model="periodo" wire:change="actualizarPeriodo" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="mes_actual">Mes Actual</option>
                            <option value="mes_anterior">Mes Anterior</option>
                            <option value="trimestre">Trimestre Actual</option>
                            <option value="semestre">Semestre Actual</option>
                            <option value="ano">Año Actual</option>
                            <option value="personalizado">Período Personalizado</option>
                        </select>
                    </div>
                    @if($periodo === 'personalizado')
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Desde</label>
                        <input type="date" wire:model="fecha_personalizada_desde" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Hasta</label>
                        <input type="date" wire:model="fecha_personalizada_hasta" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div class="flex items-end">
                        <button wire:click="aplicarPeriodoPersonalizado" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition duration-200">
                            <i class="fas fa-sync mr-2"></i> Aplicar
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ SECCIÓN 5: Reportes Rápidos -->
    <div class="flex flex-wrap -mx-3 mb-6">
        <div class="w-full md:w-1/4 px-3 mb-6">
            <div class="bg-white shadow-lg rounded-lg hover:shadow-xl transition duration-300 cursor-pointer group" wire:click="generarReporte('flujo-caja')">
                <div class="p-6 text-center">
                    <div class="bg-blue-100 group-hover:bg-blue-200 p-4 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center transition duration-300">
                        <i class="fas fa-chart-line text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Flujo de Caja</h3>
                    <p class="text-sm text-gray-600 mb-4">Análisis de ingresos y egresos</p>

                    <div class="text-xs text-gray-500 mb-3">
                        <div class="flex justify-between">
                            <span>Balance:</span>
                            <span class="{{ ($estadisticas['balance'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                ${{ number_format($estadisticas['balance'] ?? 0, 0) }}
                            </span>
                        </div>
                    </div>

                    <div class="flex space-x-2">
                        <button wire:click.stop="generarReporte('flujo-caja')"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded text-sm transition duration-200">
                            <i class="fas fa-eye mr-1"></i> Ver
                        </button>
                        <button wire:click.stop="generarReportePDF('flujo-caja')"
                            class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded text-sm transition duration-200">
                            <i class="fas fa-file-pdf"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="w-full md:w-1/4 px-3 mb-6">
            <div class="bg-white shadow-lg rounded-lg hover:shadow-xl transition duration-300 cursor-pointer" wire:click="generarReporte('estado-resultados')">
                <div class="p-6 text-center">
                    <div class="bg-green-100 p-4 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                        <i class="fas fa-chart-pie text-green-600 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Estado de Resultados</h3>
                    <p class="text-sm text-gray-600 mb-4">Ganancias y pérdidas del período</p>
                    <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm transition duration-200">
                        <i class="fas fa-download mr-1"></i> Generar
                    </button>
                </div>
            </div>
        </div>
        
        <div class="w-full md:w-1/4 px-3 mb-6">
            <div class="bg-white shadow-lg rounded-lg hover:shadow-xl transition duration-300 cursor-pointer" wire:click="generarReporte('balance-general')">
                <div class="p-6 text-center">
                    <div class="bg-purple-100 p-4 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                        <i class="fas fa-balance-scale text-purple-600 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Balance General</h3>
                    <p class="text-sm text-gray-600 mb-4">Activos, pasivos y patrimonio</p>
                    <button class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm transition duration-200">
                        <i class="fas fa-download mr-1"></i> Generar
                    </button>
                </div>
            </div>
        </div>
        
        <div class="w-full md:w-1/4 px-3 mb-6">
            <div class="bg-white shadow-lg rounded-lg hover:shadow-xl transition duration-300 cursor-pointer" wire:click="generarReporte('cuentas-pendientes')">
                <div class="p-6 text-center">
                    <div class="bg-yellow-100 p-4 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                        <i class="fas fa-clock text-yellow-600 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Cuentas Pendientes</h3>
                    <p class="text-sm text-gray-600 mb-4">Por cobrar y por pagar</p>
                    <button class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg text-sm transition duration-200">
                        <i class="fas fa-download mr-1"></i> Generar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ SECCIÓN 6: Gráficos de Análisis -->
    <div class="flex flex-wrap -mx-3 mb-6">
        <!-- Gráfico de Tendencias -->
        <div class="w-full xl:w-2/3 px-3 mb-6">
            <div class="bg-white shadow-lg rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <div>
                            <h6 class="text-lg font-semibold text-gray-800">Tendencias Financieras</h6>
                            <p class="text-sm text-gray-600">Evolución de ingresos y gastos</p>
                        </div>
                        <div class="flex space-x-2">
                            <select class="border border-gray-300 rounded-lg px-3 py-1 text-sm">
                                <option>Últimos 12 meses</option>
                                <option>Últimos 6 meses</option>
                                <option>Últimos 3 meses</option>
                            </select>
                            <button wire:click="generarReporte('tendencias')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 rounded-lg text-sm transition duration-200">
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="relative h-80">
                        <canvas id="tendenciasChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPIs Principales -->
        <div class="w-full xl:w-1/3 px-3 mb-6">
            <div class="bg-white shadow-lg rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <h6 class="text-lg font-semibold text-gray-800">Indicadores Clave</h6>
                    <p class="text-sm text-gray-600">KPIs del período seleccionado</p>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                        <div>
                            <p class="text-sm font-medium text-blue-600">Margen de Ganancia</p>
                            <p class="text-2xl font-bold text-blue-800">{{ $estadisticas['margenGanancia'] ?? 0 }}%</p>
                        </div>
                        <div class="bg-blue-200 p-2 rounded-full">
                            <i class="fas fa-percentage text-blue-600"></i>
                        </div>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                        <div>
                            <p class="text-sm font-medium text-green-600">ROI</p>
                            <p class="text-2xl font-bold text-green-800">{{ $estadisticas['roi'] ?? 0 }}%</p>
                        </div>
                        <div class="bg-green-200 p-2 rounded-full">
                            <i class="fas fa-arrow-up text-green-600"></i>
                        </div>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg">
                        <div>
                            <p class="text-sm font-medium text-yellow-600">Liquidez</p>
                            <p class="text-2xl font-bold text-yellow-800">{{ $estadisticas['liquidez'] ?? 'N/A' }}</p>
                        </div>
                        <div class="bg-yellow-200 p-2 rounded-full">
                            <i class="fas fa-tint text-yellow-600"></i>
                        </div>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                        <div>
                            <p class="text-sm font-medium text-red-600">Días Promedio Cobro</p>
                            <p class="text-2xl font-bold text-red-800">{{ $estadisticas['diasPromedioCobro'] ?? 0 }}</p>
                        </div>
                        <div class="bg-red-200 p-2 rounded-full">
                            <i class="fas fa-calendar text-red-600"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ SECCIÓN 7: Reportes Detallados -->
    <div class="flex flex-wrap -mx-3 mb-6">
        <div class="w-full px-3">
            <div class="bg-white shadow-lg rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <div>
                            <h6 class="text-lg font-semibold text-gray-800">Reportes Detallados</h6>
                            <p class="text-sm text-gray-600">Análisis profundo por categorías</p>
                        </div>
                        <div class="flex space-x-2">
                            <button wire:click="exportarTodo" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm transition duration-200">
                                <i class="fas fa-file-excel mr-1"></i> Exportar Todo
                            </button>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Reporte por Categorías -->
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition duration-200">
                            <div class="flex items-center mb-3">
                                <div class="bg-blue-100 p-2 rounded-lg mr-3">
                                    <i class="fas fa-tags text-blue-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-800">Por Categorías</h4>
                                    <p class="text-sm text-gray-600">Gastos desglosados</p>
                                </div>
                            </div>
                            <div class="space-y-2 mb-4">
                                @if(count($categorias_data['labels'] ?? []) > 0)
                                @foreach($categorias_data['labels'] as $index => $categoria)
                                <div class="flex justify-between text-sm">
                                    <span>{{ $categoria }}:</span>
                                    <span class="font-medium">${{ number_format($categorias_data['data'][$index] ?? 0, 2) }}</span>
                                </div>
                                @endforeach
                                @else
                                <div class="flex justify-between text-sm">
                                    <span>Sin datos:</span>
                                    <span class="font-medium">$0.00</span>
                                </div>
                                @endif
                            </div>
                            <button wire:click="generarReporte('categorias')" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded text-sm transition duration-200">
                                <i class="fas fa-download mr-1"></i> Generar Reporte
                            </button>
                        </div>

                        <!-- Reporte de Flujo de Caja -->
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition duration-200">
                            <div class="flex items-center mb-3">
                                <div class="bg-green-100 p-2 rounded-lg mr-3">
                                    <i class="fas fa-chart-line text-green-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-800">Flujo de Caja</h4>
                                    <p class="text-sm text-gray-600">Análisis de liquidez</p>
                                </div>
                            </div>
                            <div class="space-y-2 mb-4">
                                <div class="flex justify-between text-sm">
                                    <span>Total Ingresos:</span>
                                    <span class="font-medium">${{ number_format($estadisticas['totalIngresos'] ?? 0, 2) }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span>Total Gastos:</span>
                                    <span class="font-medium">${{ number_format($estadisticas['totalGastos'] ?? 0, 2) }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span>Balance:</span>
                                    <span class="font-medium {{ ($estadisticas['balance'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        ${{ number_format($estadisticas['balance'] ?? 0, 2) }}
                                    </span>
                                </div>
                            </div>
                            <button wire:click="generarReporte('flujo-caja')" class="w-full bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded text-sm transition duration-200">
                                <i class="fas fa-download mr-1"></i> Generar Reporte
                            </button>
                        </div>

                        <!-- Reporte de Proyecciones -->
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition duration-200">
                            <div class="flex items-center mb-3">
                                <div class="bg-purple-100 p-2 rounded-lg mr-3">
                                    <i class="fas fa-crystal-ball text-purple-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-800">Proyecciones</h4>
                                    <p class="text-sm text-gray-600">Análisis predictivo</p>
                                </div>
                            </div>
                            <div class="space-y-2 mb-4">
                                <div class="flex justify-between text-sm">
                                    <span>Próximo Mes:</span>
                                    <span class="font-medium">${{ number_format(($estadisticas['totalIngresos'] ?? 0) * 1.05, 2) }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span>Tendencia:</span>
                                    <span class="font-medium {{ ($estadisticas['balance'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ ($estadisticas['balance'] ?? 0) >= 0 ? 'Positiva' : 'Negativa' }}
                                    </span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span>Confianza:</span>
                                    <span class="font-medium">{{ ($estadisticas['totalIngresos'] ?? 0) > 0 ? 'Alta' : 'Baja' }}</span>
                                </div>
                            <button wire:click="generarReporte('proyecciones')" class="w-full bg-purple-600 hover:bg-purple-700 text-white px-3 py-2 rounded text-sm transition duration-200">
                                <i class="fas fa-download mr-1"></i> Generar Reporte
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ SECCIÓN 8: Gráficos Adicionales -->
    <div class="flex flex-wrap -mx-3 mb-6">
        <!-- Gráfico de Categorías -->
        <div class="w-full md:w-1/2 px-3 mb-6">
            <div class="bg-white shadow-lg rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <h6 class="text-lg font-semibold text-gray-800">Gastos por Categoría</h6>
                    <p class="text-sm text-gray-600">Distribución porcentual</p>
                </div>
                <div class="p-6">
                    <div class="relative h-64">
                        <canvas id="categoriasChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico de Cuentas Pendientes -->
        <div class="w-full md:w-1/2 px-3 mb-6">
            <div class="bg-white shadow-lg rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <h6 class="text-lg font-semibold text-gray-800">Cuentas Pendientes</h6>
                    <p class="text-sm text-gray-600">Estado actual</p>
                </div>
                <div class="p-6">
                    <div class="relative h-64">
                        <canvas id="cuentasPendientesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ SECCIÓN 9: Historial de Reportes -->
    <div class="flex flex-wrap -mx-3">
        <div class="w-full px-3">
            <div class="bg-white shadow-lg rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <div>
                            <h6 class="text-lg font-semibold text-gray-800">Historial de Reportes</h6>
                            <p class="text-sm text-gray-600">Reportes generados recientemente</p>
                        </div>
                        <button class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm transition duration-200">
                            <i class="fas fa-trash mr-1"></i> Limpiar Historial
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reporte</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Período</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Generación</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                    <div class="py-8">
                                        <i class="fas fa-chart-line text-4xl text-gray-300 mb-4"></i>
                                        <p class="text-lg font-medium mb-2">No hay reportes generados</p>
                                        <p class="text-sm text-gray-400 mb-4">Los reportes que generes aparecerán aquí</p>
                                        <button wire:click="generarReporte('flujo-caja')"
                                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg transition duration-200">
                                            <i class="fas fa-plus mr-2"></i> Generar Primer Reporte
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- ✅ NOTIFICACIONES FLASH -->
@if(session('success'))
<div class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
    <div class="flex items-center">
        <i class="fas fa-check-circle mr-2"></i>
        {{ session('success') }}
    </div>
</div>
@endif

@if(session('error'))
<div class="fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
    <div class="flex items-center">
        <i class="fas fa-exclamation-circle mr-2"></i>
        {{ session('error') }}
    </div>
</div>
@endif

@if(session('info'))
<div class="fixed top-4 right-4 bg-blue-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
    <div class="flex items-center">
        <i class="fas fa-info-circle mr-2"></i>
        {{ session('info') }}
    </div>
</div>
@endif

<!-- ✅ TU JAVASCRIPT ACTUAL (SIN CAMBIOS) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// ✅ SOLUCIÓN COMPLETA DE JAVASCRIPT - EXACTAMENTE IGUAL
let tendenciasChart = null;
let categoriasChart = null; 
let cuentasPendientesChart = null;

// ✅ Función para destruir gráficos existentes
function destruirGraficos() {
    if (tendenciasChart) {
        tendenciasChart.destroy();
        tendenciasChart = null;
    }
    if (categoriasChart) {
        categoriasChart.destroy();
        categoriasChart = null;
    }
    if (cuentasPendientesChart) {
        cuentasPendientesChart.destroy();
        cuentasPendientesChart = null;
    }
}

// ✅ Función mejorada para obtener datos
function obtenerDatos() {
    try {
        return {
            tendencias: @json($tendencias_data ?? ['labels' => [], 'ingresos' => [], 'gastos' => []]),
            categorias: @json($categorias_data ?? ['labels' => [], 'data' => []]),
            cuentasPendientes: @json($cuentas_pendientes_data ?? ['labels' => [], 'data' => []])
        };
    } catch (e) {
        console.error('Error obteniendo datos:', e);
        return {
            tendencias: { labels: [], ingresos: [], gastos: [] },
            categorias: { labels: [], data: [] },
            cuentasPendientes: { labels: [], data: [] }
        };
    }
}

// ✅ Función para verificar que el canvas existe
function canvasExiste(id) {
    const canvas = document.getElementById(id);
    return canvas && canvas.getContext;
}

// ✅ Gráfico de Tendencias mejorado
function inicializarTendenciasChart(data) {
    if (!canvasExiste('tendenciasChart')) {
        console.warn('Canvas tendenciasChart no disponible');
        return;
    }
    
    const ctx = document.getElementById('tendenciasChart').getContext('2d');
    
    tendenciasChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels || [],
            datasets: [{
                label: 'Ingresos',
                data: data.ingresos || [],
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }, {
                label: 'Gastos',
                data: data.gastos || [],
                borderColor: '#ef4444',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index',
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': $' + 
                                   new Intl.NumberFormat().format(context.raw);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + new Intl.NumberFormat().format(value);
                        }
                    },
                    grid: {
                        color: 'rgba(156, 163, 175, 0.3)'
                    }
                },
                x: {
                    grid: {
                        color: 'rgba(156, 163, 175, 0.3)'
                    }
                }
            }
        }
    });
}

// ✅ Gráfico de Categorías mejorado
function inicializarCategoriasChart(data) {
    if (!canvasExiste('categoriasChart')) {
        console.warn('Canvas categoriasChart no disponible');
        return;
    }
    
    const ctx = document.getElementById('categoriasChart').getContext('2d');
    
    categoriasChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.labels || [],
            datasets: [{
                data: data.data || [],
                backgroundColor: [
                    '#3b82f6', '#10b981', '#f59e0b', 
                    '#ef4444', '#8b5cf6', '#6b7280',
                    '#ec4899', '#14b8a6', '#f97316'
                ],
                borderWidth: 0,
                hoverBorderWidth: 3,
                hoverBorderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.raw / total) * 100).toFixed(1);
                            return context.label + ': $' + 
                                   new Intl.NumberFormat().format(context.raw) + 
                                   ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
}

// ✅ Gráfico de Cuentas Pendientes mejorado
function inicializarCuentasPendientesChart(data) {
    if (!canvasExiste('cuentasPendientesChart')) {
        console.warn('Canvas cuentasPendientesChart no disponible');
        return;
    }
    
    const ctx = document.getElementById('cuentasPendientesChart').getContext('2d');
    
    cuentasPendientesChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: data.labels || [],
            datasets: [{
                data: data.data || [],
                backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                borderWidth: 0,
                hoverBorderWidth: 3,
                hoverBorderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + context.raw + ' cuentas';
                        }
                    }
                }
            }
        }
    });
}

// ✅ Función principal para inicializar todos los gráficos
function inicializarTodosLosGraficos() {
    console.log('🚀 Inicializando todos los gráficos...');
    
    // Destruir gráficos existentes
    destruirGraficos();
    
    // Obtener datos actualizados
    const datos = obtenerDatos();
    console.log('📊 Datos para gráficos:', datos);
    
    // Inicializar cada gráfico con timeout para asegurar que el DOM esté listo
    setTimeout(() => {
        inicializarTendenciasChart(datos.tendencias);
    }, 100);
    
    setTimeout(() => {
        inicializarCategoriasChart(datos.categorias);
    }, 200);
    
    setTimeout(() => {
        inicializarCuentasPendientesChart(datos.cuentasPendientes);
    }, 300);
}

// ✅ Auto-cerrar notificaciones
function autoCerrarNotificaciones() {
    setTimeout(function() {
        const notifications = document.querySelectorAll('.fixed.top-4.right-4');
        notifications.forEach(notification => {
            notification.style.transition = 'opacity 0.5s ease-out';
            notification.style.opacity = '0';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 500);
        });
    }, 5000);
}

// ✅ EVENTOS PRINCIPALES
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ DOM cargado completamente');
    inicializarTodosLosGraficos();
    autoCerrarNotificaciones();
});

// ✅ Eventos de Livewire
document.addEventListener('livewire:init', () => {
    console.log('⚡ Livewire inicializado');
    
    Livewire.on('reporte-actualizado', () => {
        console.log('📊 Evento reporte-actualizado recibido');
        setTimeout(inicializarTodosLosGraficos, 300);
    });
});

// ✅ Actualizar después de navegación
document.addEventListener('livewire:navigated', () => {
    console.log('🔄 Livewire navegated');
    setTimeout(inicializarTodosLosGraficos, 500);
});

// ✅ Actualizar después de updates
document.addEventListener('livewire:updated', () => {
    console.log('🔄 Livewire updated');
    setTimeout(inicializarTodosLosGraficos, 200);
});

// ✅ Redimensionar gráficos cuando cambie el tamaño de ventana
window.addEventListener('resize', function() {
    if (tendenciasChart) tendenciasChart.resize();
    if (categoriasChart) categoriasChart.resize();
    if (cuentasPendientesChart) cuentasPendientesChart.resize();
});
</script>