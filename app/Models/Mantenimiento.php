<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mantenimiento extends Model
{
    use HasFactory;

    protected $table = 'mantenimientos';
    protected $primaryKey = 'idMan';
        
    protected $fillable = [
        'idHerMan',           // Mantener para compatibilidad con registros existentes
        'nomHerMan',  // Nuevo campo para nombre libre de herramienta
        'fecMan',
        'tipMan',
        'estMan',
        'desMan',
        'resMan',
        'obsMan'
    ];

    protected $casts = [
        'fecMan' => 'date'
    ];

    // Relaciones
    public function herramienta()
    {
        return $this->belongsTo(Herramienta::class, 'idHerMan', 'idHer');
    }

    public function movimientos()
    {
        return $this->hasMany(Inventario::class, 'idMan', 'idMan');
    }

    // Scopes
    public function scopePendientes($query)
    {
        return $query->where('estMan', 'pendiente');
    }

    public function scopeEnProceso($query)
    {
        return $query->where('estMan', 'en proceso');
    }

    public function scopeCompletados($query)
    {
        return $query->where('estMan', 'completado');
    }

    public function scopePreventivos($query)
    {
        return $query->where('tipMan', 'preventivo');
    }

    public function scopeCorrectivos($query)
    {
        return $query->where('tipMan', 'correctivo');
    }

    // Accessor para obtener el nombre de la herramienta (sea de relación o campo libre)
    public function getNombreHerramientaCompleto()
    {
        if ($this->nomHerMan) {
            return $this->nomMan;
        }
        
        if ($this->herramienta) {
            return $this->herramienta->nomHer;
        }
        
        return 'Sin herramienta especificada';
    }
}
