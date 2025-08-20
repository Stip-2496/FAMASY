<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorialMedico extends Model
{
    protected $table = 'historialmedico';
    protected $primaryKey = 'idHisMed';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'idAni', // ¡IMPORTANTE! Agregar idAni al fillable
        'fecHisMed',
        'desHisMed',
        'traHisMed', // Tratamiento
        'tipHisMed',
        'responHisMed',
        'obsHisMed',
        'idIns', // ID del insumo
        'dosHisMed', // Dosis
        'durHisMed', // Duración
        'estRecHisMed', // Estado de recuperación
        'resHisMed', // Resultado
        'obsHisMed2' // Observaciones adicionales
    ];

    protected $dates = ['fecHisMed'];

    public function getRouteKeyName()
    {
        return 'idHisMed';
    }

    public function animal()
    {
        return $this->belongsTo(Animal::class, 'idAni', 'idAni')
            ->withDefault(function ($animal, $historial) {
                // Datos por defecto cuando no se encuentra el animal
                $animal->nomAni = 'Animal no encontrado';
                $animal->espAni = 'N/A';
                $animal->razAni = 'N/A';
                $animal->sexAni = 'N/A';
                $animal->pesAni = null;
                $animal->estSaludAni = 'N/A';
            });
    }

    // Relación con insumo
    public function insumo()
    {
        return $this->belongsTo(Insumo::class, 'idIns', 'idIns');
    }
}