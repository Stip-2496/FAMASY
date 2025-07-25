<?php

namespace App\Http\Controllers;

use App\Models\ProduccionAnimal;
use App\Models\Animal;
use Illuminate\Http\Request;

class ProduccionController extends Controller
{
    /**
     * Mostrar listado de registros
     */
    public function index(Request $request)
    {
        $producciones = ProduccionAnimal::with('animal')
            ->when($request->tipo, fn($q, $tipo) => $q->where('tipProAni', $tipo))
            ->when($request->fecha, fn($q, $fecha) => $q->whereDate('fecProAni', $fecha))
            ->orderBy('fecProAni', 'desc')
            ->paginate(10);

        return view('pecuario.produccion.index', compact('producciones'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        $animales = Animal::where('estAni', 'vivo')->get(['idAni', 'nomAni', 'espAni']);
        return view('pecuario.produccion.create', compact('animales'));
    }

    /**
     * Guardar nuevo registro
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'idAniPro' => 'required|exists:animales,idAni',
            'tipProAni' => 'required|in:leche,huevos,carne,lana',
            'canProAni' => 'required|numeric|min:0.01|max:9999.99',
            'uniProAni' => 'nullable|string|max:20',
            'fecProAni' => 'required|date|before_or_equal:today',
            'obsProAni' => 'nullable|string|max:500'
        ]);

        ProduccionAnimal::create($validated);

        return redirect()->route('pecuario.produccion.index')
               ->with('success', 'Registro creado correctamente');
    }

    /**
     * Mostrar detalles
     */
    public function show(ProduccionAnimal $produccion)
    {
        return view('pecuario.produccion.show', compact('produccion'));
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit(ProduccionAnimal $produccion)
    {
        return view('pecuario.produccion.edit', compact('produccion'));
    }

    /**
     * Actualizar registro
     */
    public function update(Request $request, ProduccionAnimal $produccion)
    {
        $validated = $request->validate([
            'tipProAni' => 'required|in:leche,huevos,carne,lana',
            'canProAni' => 'required|numeric|min:0.01|max:9999.99',
            'uniProAni' => 'nullable|string|max:20',
            'fecProAni' => 'required|date|before_or_equal:today',
            'obsProAni' => 'nullable|string|max:500'
        ]);

        $produccion->update($validated);

        return redirect()->route('pecuario.produccion.show', $produccion)
               ->with('success', 'Registro actualizado');
    }

    /**
     * Eliminar registro
     */
    public function destroy(ProduccionAnimal $produccion)
    {
        $produccion->delete();
        return redirect()->route('pecuario.produccion.index')
               ->with('success', 'Registro eliminado');
    }
}