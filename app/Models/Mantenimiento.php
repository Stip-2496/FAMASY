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
        'idHerMan',       // Solo cuando se selecciona herramienta del catálogo
        'nomHerMan',      // Campo para nombre libre de herramienta
        'fecMan',
        'tipMan',
        'estMan',
        'desMan',
        'resMan',
        'obsMan'
    ];

    protected $casts = [
        'fecMan' => 'date',
        'idHerMan' => 'integer'
    ];

    // ✅ RELACIÓN CORREGIDA: Solo buscar herramienta si idHerMan existe y > 0
    public function herramienta()
    {
        return $this->belongsTo(Herramienta::class, 'idHerMan', 'idHer');
    }

    public function movimientos()
    {
        return $this->hasMany(Inventario::class, 'idMan', 'idMan');
    }

    // ===== SCOPES =====
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

    public function scopePredictivos($query)
    {
        return $query->where('tipMan', 'predictivo');
    }

    // ===== MÉTODOS AUXILIARES =====

    /**
     * ✅ MÉTODO CORREGIDO: Obtiene el nombre completo de la herramienta
     * Prioriza nomHerMan (nombre libre) sobre la relación con herramienta
     */
    public function getNombreHerramientaCompleto()
    {
        // 1. Si hay nombre libre, usarlo
        if (!empty($this->nomHerMan)) {
            return $this->nomHerMan;
        }
        
        // 2. Si hay idHerMan válido (no null y > 0) y existe la herramienta
        if ($this->idHerMan && $this->idHerMan > 0 && $this->herramienta) {
            return $this->herramienta->nomHer;
        }
        
        // 3. Fallback
        return 'Sin herramienta especificada';
    }

    /**
     * ✅ NUEVO: Método para obtener el tipo de herramienta (si está relacionada)
     */
    public function getTipoHerramienta()
    {
        if ($this->idHerMan && $this->idHerMan > 0 && $this->herramienta) {
            return $this->herramienta->tipHer ?? 'No especificado';
        }
        
        return 'Herramienta personalizada';
    }

    /**
     * ✅ NUEVO: Método para verificar si usa herramienta del catálogo
     */
    public function usaHerramientaCatalogo()
    {
        return $this->idHerMan && $this->idHerMan > 0 && $this->herramienta;
    }

    /**
     * ✅ NUEVO: Método para verificar si usa nombre libre
     */
    public function usaNombreLibre()
    {
        return !empty($this->nomHerMan);
    }

    /**
     * ✅ NUEVO: Obtiene el estado con formato amigable
     */
    public function getEstadoFormateado()
    {
        $estados = [
            'pendiente' => '🔄 Pendiente',
            'en proceso' => '⚙️ En Proceso',
            'completado' => '✅ Completado'
        ];

        return $estados[$this->estMan] ?? $this->estMan;
    }

    /**
     * ✅ NUEVO: Obtiene el tipo con formato amigable
     */
    public function getTipoFormateado()
    {
        $tipos = [
            'preventivo' => '🛡️ Preventivo',
            'correctivo' => '🔧 Correctivo',
            'predictivo' => '📊 Predictivo'
        ];

        return $tipos[$this->tipMan] ?? $this->tipMan;
    }

    /**
     * ✅ NUEVO: Verifica si el mantenimiento está vencido
     */
    public function estaVencido()
    {
        if ($this->estMan === 'completado') {
            return false;
        }

        return $this->fecMan < now()->toDateString();
    }

    /**
     * ✅ NUEVO: Obtiene días hasta/desde la fecha programada
     */
    public function getDiasHastaFecha()
    {
        $fechaProgramada = \Carbon\Carbon::parse($this->fecMan);
        $hoy = \Carbon\Carbon::now();
        
        return $hoy->diffInDays($fechaProgramada, false);
    }

    /**
     * ✅ NUEVO: Scope para mantenimientos próximos (próximos 7 días)
     */
    public function scopeProximos($query, $dias = 7)
    {
        return $query->where('fecMan', '>=', now()->toDateString())
                    ->where('fecMan', '<=', now()->addDays($dias)->toDateString())
                    ->where('estMan', '!=', 'completado');
    }

    /**
     * ✅ NUEVO: Scope para mantenimientos vencidos
     */
    public function scopeVencidos($query)
    {
        return $query->where('fecMan', '<', now()->toDateString())
                    ->where('estMan', '!=', 'completado');
    }

    // ===== MUTATORS Y ACCESSORS =====

    /**
     * ✅ NUEVO: Accessor para formatear la fecha
     */
    public function getFechaFormateadaAttribute()
    {
        return $this->fecMan ? $this->fecMan->format('d/m/Y') : '-';
    }

    /**
     * ✅ NUEVO: Mutator para limpiar el nombre de herramienta
     */
    public function setNomHerManAttribute($value)
    {
        $this->attributes['nomHerMan'] = $value ? trim($value) : null;
    }
}