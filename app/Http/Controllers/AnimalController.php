<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Animal; // Asegúrate de importar el modelo

class AnimalController extends Controller
{
    /**
     * Mostrar listado de animales
     */
    public function index()
    {
        $animales = Animal::orderBy('nomAni', 'asc')->paginate(10);
        return view('pecuario.animales.index', compact('animales'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        return view('pecuario.animales.create');
    }

    /**
     * Guardar nuevo animal
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'espAni' => 'required|string|max:100',
            'nomAni' => 'nullable|string|max:100',
            'razAni' => 'nullable|string|max:100',
            'sexAni' => 'required|in:Hembra,Macho',
            'fecNacAni' => 'nullable|date',
            'pesAni' => 'nullable|numeric|between:0,9999.99',
            'estAni' => 'required|in:vivo,muerto,vendido'
        ]);

        Animal::create($validated);

        return redirect()->route('pecuario.animales.index')
               ->with('success', 'Animal registrado correctamente');
    }

    /**
     * Mostrar detalles de un animal
     */
    public function show($id)
    {
        $animal = Animal::findOrFail($id);
        return view('pecuario.animales.show', compact('animal'));
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        $animal = Animal::findOrFail($id);
        return view('pecuario.animales.edit', compact('animal'));
    }

    /**
     * Actualizar animal existente
     */
    public function update(Request $request, $id)
    {
        $animal = Animal::findOrFail($id);

        $validated = $request->validate([
            'espAni' => 'required|string|max:100',
            'nomAni' => 'nullable|string|max:100',
            'razAni' => 'nullable|string|max:100',
            'sexAni' => 'required|in:Hembra,Macho',
            'fecNacAni' => 'nullable|date',
            'pesAni' => 'nullable|numeric|between:0,9999.99',
            'estAni' => 'required|in:vivo,muerto,vendido'
        ]);

        $animal->update($validated);

        return redirect()->route('pecuario.animales.show', $animal->idAni)
               ->with('success', 'Datos actualizados correctamente');
    }

    /**
     * Eliminar animal
     */
    public function destroy($id)
    {
        $animal = Animal::findOrFail($id);
        $animal->delete();

        return redirect()->route('pecuario.animales.index')
               ->with('success', 'Animal eliminado correctamente');
    }
}