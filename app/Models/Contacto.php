<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contacto extends Model
{
    protected $table = 'contacto';
    protected $primaryKey = 'idCon';
    public $timestamps = false; 

    protected $fillable = ['celCon'];

    public function direccion()
    {
        return $this->hasOne(Direccion::class, 'idConDir', 'idCon');
    }
}

