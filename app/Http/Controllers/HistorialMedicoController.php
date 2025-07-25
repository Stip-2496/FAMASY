<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\HistorialMedico;
use Illuminate\Http\Request;

class HistorialMedicoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $historiales = HistorialMedico::with('animal')->latest()->paginate(10);
        return view('pecuario.salud-peso.index', compact('historiales'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $animales = Animal::where('estAni', 'vivo')->get();
        return view('pecuario.salud-peso.create', compact('animales'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'idAniHis' => 'required|exists:animales,idAni',
            'tipHisMed' => 'required|in:vacuna,tratamiento,control',
            'fecHisMed' => 'required|date',
            'desHisMed' => 'required|string|max:500',
            'responHisMed' => 'required|string|max:100',
            'obsHisMed' => 'nullable|string|max:500'
        ]);

        HistorialMedico::create($request->all());

        return redirect()->route('pecuario.salud-peso.index')
                         ->with('success', 'Registro médico creado exitosamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $historial = HistorialMedico::findOrFail($id);
        $animal = Animal::find($historial->idAniHis);
        return view('pecuario.salud-peso.show', compact('historial', 'animal'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $historial = HistorialMedico::findOrFail($id);
        $animal = Animal::find($historial->idAniHis);
        return view('pecuario.salud-peso.edit', compact('historial', 'animal'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'fecHisMed' => 'required|date',
            'desHisMed' => 'required|string|max:500',
            'responHisMed' => 'required|string|max:100',
            'obsHisMed' => 'nullable|string|max:500'
        ]);

        $historial = HistorialMedico::findOrFail($id);
        $historial->update($request->all());

        return redirect()->route('pecuario.salud-peso.index')
                         ->with('success', 'Registro médico actualizado exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $historial = HistorialMedico::findOrFail($id);
        $historial->delete();

        return redirect()->route('pecuario.salud-peso.index')
                         ->with('success', 'Registro médico eliminado exitosamente');
    }
}