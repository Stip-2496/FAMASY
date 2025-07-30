<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrestamoHerramienta extends Model
{
    use HasFactory;

    protected $table = 'prestamosherramientas';
    protected $primaryKey = 'idPreHer';
    
    protected $fillable = [
        'idHerPre',
        'idUsuPre',
        'fecPre',
        'fecDev',
        'estPre',
        'obsPre',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'fecPre' => 'date:Y-m-d',
        'fecDev' => 'date:Y-m-d'
    ];

    // Relaciones
    public function herramienta()
    {
        return $this->belongsTo(Herramienta::class, 'idHerPre', 'idHer');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'idUsuPre', 'id');
    }

    public function movimientos()
    {
        return $this->hasMany(Inventario::class, 'idPreHer', 'idPreHer');
    }

    // Scopes
    public function scopePrestados($query)
    {
        return $query->where('estPre', 'prestado');
    }

    public function scopeDevueltos($query)
    {
        return $query->where('estPre', 'devuelto');
    }

    public function scopeVencidos($query)
    {
        return $query->where('estPre', 'vencido');
    }
}