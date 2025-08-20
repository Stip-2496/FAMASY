<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Animal extends Model
{
    protected $table = 'animales';
    protected $primaryKey = 'idAni';
    
    protected $fillable = [
        'espAni',
        'nomAni',
        'razAni',
        'sexAni',
        'fecNacAni',
        'fecComAni',
        'pesAni',
        'estAni',
        'estReproAni',
        'estSaludAni',
        'obsAni',
        'nitAni',          // ✅ Nuevo campo: NIT del animal
        'fotoAni',         // ✅ Nuevo campo: Foto del animal
        'ubicacionAni'     // ✅ Nuevo campo: Ubicación
    ];
}
