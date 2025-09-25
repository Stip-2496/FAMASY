<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Direccion extends Model
{
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