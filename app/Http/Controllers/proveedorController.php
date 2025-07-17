<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;

class ProveedorController extends Controller
{
   public function index(Request $request)
{
    $query = Proveedor::query();

    if ($request->filled('nit')) {
        $query->where('nitProve', 'like', '%' . $request->nit . '%');
    }

    $proveedores = $query->get();

    return view('proveedores.index', compact('proveedores'));
}


    public function create()
    {
        return view('proveedores.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nomProve' => 'required|string|max:100',
            'emailProve' => 'nullable|email',
            'telProve' => 'nullable|string|max:20'
        ]);

        Proveedor::create($request->all());
        return redirect()->route('proveedores.index')->with('success', 'Proveedor creado correctamente');
    }

    public function edit($id)
    {
        $proveedor = Proveedor::findOrFail($id);
        return view('proveedores.edit', compact('proveedor'));
    }

    public function update(Request $request, $id)
    {
        $proveedor = Proveedor::findOrFail($id);
        $proveedor->update($request->all());
        return redirect()->route('proveedores.index')->with('success', 'Proveedor actualizado');
    }

    public function destroy($id)
    {
        Proveedor::destroy($id);
        return redirect()->route('proveedores.index')->with('success', 'Proveedor eliminado');
    }
}

