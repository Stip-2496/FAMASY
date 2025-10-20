<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // importante agregarlo

use Illuminate\Database\Eloquent\Model;

class Direccion extends Model
{
    use HasFactory; // importante agregarlo
    protected $table = 'direccion';
    protected $primaryKey = 'idDir';
    public $timestamps = false;
    protected $fillable = [
        'idConDir',
        'calDir',
        'barDir',
        'ciuDir',
        'depDir',
        'codPosDir',
        'paiDir',
    ];

    

    public function contacto()
    {
        return $this->belongsTo(Contacto::class, 'idConDir', 'idCon');
    }
}