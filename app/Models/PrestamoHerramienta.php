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
        'idUsuSol',
        'fecPre',
        'fecDev',
        'estPre',
        'obsPre',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'fecPre' => 'datetime',
        'fecDev' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Método editar
    public function puedeEditar(): bool
    {
        return $this->fecPre->isToday() && $this->estPre !== 'devuelto';
    }

    // Relaciones
    public function herramienta()
    {
        return $this->belongsTo(Herramienta::class, 'idHerPre', 'idHer');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'idUsuPre', 'id');
    }

    // Función del solicitante
    public function solicitante()
    {
        return $this->belongsTo(User::class, 'idUsuSol', 'id');
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