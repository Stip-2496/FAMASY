<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    protected $table = 'rol';
    
    // Especificar la clave primaria correcta
    protected $primaryKey = 'idRol';

    protected $fillable = ['nomRol', 'desRol', 'estRol'];

    public $timestamps = true;

    public function usuarios()
    {
        return $this->hasMany(User::class, 'idRolUsu', 'idRol');
    }
}