<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventario extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'inventario';
    protected $primaryKey = 'idInv';
    
    protected $fillable = [
        'idIns',
        'idHer',
        'tipMovInv',
        'cantMovInv',
        'uniMovInv',
        'costoUnitInv',
        'costoTotInv',
        'fecMovInv',
        'loteInv',
        'fecVenceInv',
        'idComGas',
        'idFac',
        'idMan',
        'idPreHer',
        'idProve',
        'idUsuReg',
        'obsInv'
    ];

    protected $casts = [
        'cantMovInv' => 'decimal:2',
        'costoUnitInv' => 'decimal:2',
        'costoTotInv' => 'decimal:2',
        'fecMovInv' => 'datetime',
        'fecVenceInv' => 'date'
    ];

    // Relaciones
    public function insumo()
    {
        return $this->belongsTo(Insumo::class, 'idIns', 'idIns');
    }

    public function herramienta()
    {
        return $this->belongsTo(Herramienta::class, 'idHer', 'idHer');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'idProve', 'idProve');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'idUsuReg', 'id');
    }

    public function mantenimiento()
    {
        return $this->belongsTo(Mantenimiento::class, 'idMan', 'idMan');
    }

    public function prestamoHerramienta()
    {
        return $this->belongsTo(PrestamoHerramienta::class, 'idPreHer', 'idPreHer');
    }
}