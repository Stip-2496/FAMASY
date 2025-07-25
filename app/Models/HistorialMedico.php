<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorialMedico extends Model
{
    // Nombre de la tabla (opcional si sigue convención de nombres)
    protected $table = 'historialmedico';
    
    // Clave primaria
    protected $primaryKey = 'idHisMed';
    
    // Campos que se pueden llenar masivamente
    protected $fillable = [
        'idAniHis', 
        'fecHisMed',
        'desHisMed',
        'tipHisMed',
        'responHisMed',
        'obsHisMed'
    ];
    
    // Conversión automática de fechas
    protected $dates = ['fecHisMed'];
    
    /**
     * Relación con el modelo Animal
     */
    public function animal()
    {
        return $this->belongsTo(Animal::class, 'idAniHis', 'idAni');
    }
}