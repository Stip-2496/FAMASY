<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Animal extends Model
{
    protected $table = 'animales';
    protected $primaryKey = 'idAni';
    
    protected $fillable = [
        'ideAni',
        'espAni',
        'nomAni',
        'razAni',
        'sexAni',
        'fecNacAni',
        'fecComAni',
        'proAni',
        'pesAni',
        'estAni',
        'estReproAni',
        'estSaludAni',
        'obsAni',
        'nitAni',          
        'fotoAni',         
        'ubicacionAni'     
    ];
}
