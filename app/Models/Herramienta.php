<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Herramienta extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'herramientas';
    protected $primaryKey = 'idHer';
    
    protected $fillable = [
        'nomHer',
        'catHer', 
        'stockMinHer',
        'stockMaxHer',
        'idProveHer',
        'estHer',
        'ubiHer',
        'obsHer'
    ];

    protected $casts = [
        'stockMinHer' => 'integer',
        'stockMaxHer' => 'integer'
    ];

    // Dates para Soft Delete
    protected $dates = ['deleted_at'];

    // Relaciones
    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'idProveHer', 'idProve');
    }

    public function movimientos()
    {
        return $this->hasMany(Inventario::class, 'idHer', 'idHer');
    }

    public function prestamos()
    {
        return $this->hasMany(PrestamoHerramienta::class, 'idHerPre', 'idHer');
    }

    public function mantenimientos()
    {
        return $this->hasMany(Mantenimiento::class, 'idHerMan', 'idHer');
    }

    // Métodos para manejar soft delete
    
    /**
     * Verificar si la herramienta puede ser eliminada
     */
    public function puedeEliminar()
    {
        // Verificar si tiene movimientos de inventario
        $tieneMovimientos = $this->movimientos()->count() > 0;
        
        // Verificar si tiene préstamos activos
        $tienePrestamosPendientes = $this->prestamos()
            ->where('estPre', 'prestado')
            ->count() > 0;
        
        // Verificar si tiene mantenimientos pendientes
        $tieneMantenimientosPendientes = $this->mantenimientos()
            ->whereIn('estMan', ['pendiente', 'en proceso'])
            ->count() > 0;

        return !$tieneMovimientos && !$tienePrestamosPendientes && !$tieneMantenimientosPendientes;
    }

    /**
     * Obtener mensaje de por qué no se puede eliminar
     */
    public function razonNoEliminar()
    {
        $razones = [];

        if ($this->movimientos()->count() > 0) {
            $razones[] = 'tiene movimientos de inventario registrados';
        }

        if ($this->prestamos()->where('estPre', 'prestado')->count() > 0) {
            $razones[] = 'tiene préstamos activos';
        }

        if ($this->mantenimientos()->whereIn('estMan', ['pendiente', 'en proceso'])->count() > 0) {
            $razones[] = 'tiene mantenimientos pendientes';
        }

        return implode(', ', $razones);
    }

    // Scopes para filtrar herramientas

    /**
     * Scope para herramientas activas (no eliminadas)
     */
    public function scopeActivas($query)
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * Scope para herramientas eliminadas
     */
    public function scopeEliminadas($query)
    {
        return $query->onlyTrashed();
    }

    /**
     * Scope para filtrar por estado
     */
    public function scopeByEstado($query, $estado)
    {
        if ($estado) {
            return $query->where('estHer', $estado);
        }
        return $query;
    }

    /**
     * Scope para filtrar por categoría
     */
    public function scopeByCategoria($query, $categoria)
    {
        if ($categoria) {
            return $query->where('catHer', $categoria);
        }
        return $query;
    }
}