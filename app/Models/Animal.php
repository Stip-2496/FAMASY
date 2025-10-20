<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Animal extends Model
{
    protected $table = 'animales';
    protected $primaryKey = 'idAni';
    
    protected $fillable = [
        'espAni',
        'razAni',
        'sexAni',
        'fecNacAni',
        'fecComAni',
        'pesAni',
        'estAni',
        'estReproAni',
        'estSaludAni',
        'obsAni',
        'nitAni',
        'ubicacionAni',
        'proAni'
    ];

    // Convierte cadenas vacías a NULL para campos de fecha
    protected function casts(): array
    {
        return [
            'fecNacAni' => 'date',
            'fecComAni' => 'date',
        ];
    }
}