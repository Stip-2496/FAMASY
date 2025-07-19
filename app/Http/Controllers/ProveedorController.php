<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProveedorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $proveedores = Proveedor::orderBy('nomProve', 'asc')->paginate(10);
        return view('proveedores.index', compact('proveedores'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('proveedores.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $proveedor = new Proveedor();
        $validator = Validator::make($request->all(), $proveedor->getRules());

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            Proveedor::create($request->all());
            return redirect()->route('proveedores.index')
                ->with('success', 'Proveedor registrado exitosamente');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al registrar el proveedor: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $proveedor = Proveedor::findOrFail($id);
        return view('proveedores.show', compact('proveedor'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $proveedor = Proveedor::findOrFail($id);
        return view('proveedores.edit', compact('proveedor'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $proveedor = Proveedor::findOrFail($id);
        $validator = Validator::make($request->all(), $proveedor->getRules($id));

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $proveedor->update($request->all());
            return redirect()->route('proveedores.index')
                ->with('success', 'Información del proveedor actualizada correctamente');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al actualizar el proveedor: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $proveedor = Proveedor::findOrFail($id);
            $proveedor->delete();
            return redirect()->route('proveedores.index')
                ->with('success', 'Proveedor eliminado del sistema');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al eliminar el proveedor: ' . $e->getMessage());
        }
    }

    /**
     * Search providers by criteria
     */
    public function search(Request $request)
    {
        $query = Proveedor::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomProve', 'LIKE', "%{$search}%")
                    ->orWhere('nitProve', 'LIKE', "%{$search}%")
                    ->orWhere('conProve', 'LIKE', "%{$search}%")
                    ->orWhere('tipSumProve', 'LIKE', "%{$search}%");
            });
        }

        $proveedores = $query->orderBy('nomProve', 'asc')->paginate(10);
        return view('proveedores.index', compact('proveedores'));
    }
}
