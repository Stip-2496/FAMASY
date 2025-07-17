<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    protected $table = 'proveedores';
    protected $primaryKey = 'idProve';

    protected $fillable = [
        'nomProve', 'nitProve', 'conProve', 'telProve',
        'emailProve', 'dirProve', 'tipSumProve', 'obsProve'
    ];

    public $timestamps = true;
}

