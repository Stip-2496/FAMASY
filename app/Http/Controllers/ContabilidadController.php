<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ContabilidadController extends Controller
{
    // ================ DASHBOARD ================
    public function index()
    {
        try {
            $metricas = [
                'ingresos_mes' => 0,
                'gastos_mes' => 0,
                'balance' => 0,
                'cuentas_pendientes' => 0
            ];

            return view('auth.contabilidad.index', compact('metricas'));
        } catch (\Exception $e) {
            Log::error('Error en dashboard contabilidad: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el dashboard');
        }
    }

    // ================ MOVIMIENTOS ================
    public function movimientos()
    {
        try {
            $movimientos = []; // Aquí irán los datos de la BD
            return view('auth.contabilidad.movimientos.index', compact('movimientos'));
        } catch (\Exception $e) {
            Log::error('Error en movimientos: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar movimientos');
        }
    }

    public function storeMovimiento(Request $request)
    {
        try {
            $request->validate([
                'tipo' => 'required|in:ingreso,egreso',
                'descripcion' => 'required|string|max:255',
                'monto' => 'required|numeric|min:0',
                'fecha' => 'required|date'
            ]);

            Log::info('Nuevo movimiento creado', $request->all());

            return redirect()->route('contabilidad.movimientos.index')
                           ->with('success', 'Movimiento registrado correctamente');
        } catch (\Exception $e) {
            Log::error('Error al guardar movimiento: ' . $e->getMessage());
            return back()->with('error', 'Error al guardar el movimiento');
        }
    }

    // ================ FACTURAS ================
    public function facturas()
    {
        try {
            $facturas = []; // Aquí irán los datos de la BD
            return view('auth.contabilidad.facturas.index', compact('facturas'));
        } catch (\Exception $e) {
            Log::error('Error en facturas: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar facturas');
        }
    }

    public function storeFactura(Request $request)
    {
        try {
            $request->validate([
                'numero' => 'required|string',
                'cliente' => 'required|string',
                'subtotal' => 'required|numeric|min:0',
                'fecha_emision' => 'required|date',
                'fecha_vencimiento' => 'required|date'
            ]);

            Log::info('Nueva factura creada', $request->all());

            return redirect()->route('contabilidad.facturas.index')
                           ->with('success', 'Factura registrada correctamente');
        } catch (\Exception $e) {
            Log::error('Error al guardar factura: ' . $e->getMessage());
            return back()->with('error', 'Error al guardar la factura');
        }
    }

    // ================ GASTOS ================
    public function gastos()
    {
        try {
            $gastos = []; // Aquí irán los datos de la BD
            return view('auth.contabilidad.gastos.index', compact('gastos'));
        } catch (\Exception $e) {
            Log::error('Error en gastos: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar gastos');
        }
    }

    public function storeGasto(Request $request)
    {
        try {
            $request->validate([
                'descripcion' => 'required|string|max:255',
                'monto' => 'required|numeric|min:0',
                'categoria' => 'required|string',
                'fecha' => 'required|date'
            ]);

            Log::info('Nuevo gasto creado', $request->all());

            return redirect()->route('contabilidad.gastos.index')
                           ->with('success', 'Gasto registrado correctamente');
        } catch (\Exception $e) {
            Log::error('Error al guardar gasto: ' . $e->getMessage());
            return back()->with('error', 'Error al guardar el gasto');
        }
    }

    // ================ PAGOS ================
    public function pagos()
    {
        try {
            $pagos = []; // Aquí irán los datos de la BD
            return view('auth.contabilidad.pagos.index', compact('pagos'));
        } catch (\Exception $e) {
            Log::error('Error en pagos: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar pagos');
        }
    }

    public function storePago(Request $request)
    {
        try {
            $request->validate([
                'proveedor' => 'required|string',
                'monto' => 'required|numeric|min:0',
                'metodo_pago' => 'required|string',
                'fecha' => 'required|date'
            ]);

            Log::info('Nuevo pago creado', $request->all());

            return redirect()->route('contabilidad.pagos.index')
                           ->with('success', 'Pago registrado correctamente');
        } catch (\Exception $e) {
            Log::error('Error al guardar pago: ' . $e->getMessage());
            return back()->with('error', 'Error al guardar el pago');
        }
    }

    // ================ CUENTAS PENDIENTES ================
    public function cuentasPendientes()
    {
        try {
            $cuentas_pendientes = []; // Aquí irán los datos de la BD
            return view('auth.contabilidad.cuentas-pendientes.index', compact('cuentas_pendientes'));
        } catch (\Exception $e) {
            Log::error('Error en cuentas pendientes: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar cuentas pendientes');
        }
    }

    public function storeCuentaPendiente(Request $request)
    {
        try {
            $request->validate([
                'descripcion' => 'required|string|max:255',
                'monto' => 'required|numeric|min:0',
                'fecha_vencimiento' => 'required|date',
                'tipo' => 'required|in:cobrar,pagar'
            ]);

            Log::info('Nueva cuenta pendiente creada', $request->all());

            return redirect()->route('contabilidad.cuentas-pendientes.index')
                           ->with('success', 'Cuenta pendiente registrada correctamente');
        } catch (\Exception $e) {
            Log::error('Error al guardar cuenta pendiente: ' . $e->getMessage());
            return back()->with('error', 'Error al guardar la cuenta pendiente');
        }
    }

    // ================ REPORTES ================
    public function reportes()
    {
        try {
            $reportes_data = []; // Aquí irán los datos de la BD
            return view('auth.contabilidad.reportes.index', compact('reportes_data'));
        } catch (\Exception $e) {
            Log::error('Error en reportes: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar reportes');
        }
    }

    // ================ CONFIGURACIÓN ================
    public function configuracion()
    {
        try {
            $configuraciones = []; // Aquí irán los datos de la BD
            return view('auth.contabilidad.configuracion.index', compact('configuraciones'));
        } catch (\Exception $e) {
            Log::error('Error en configuración: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar configuración');
        }
    }
}
