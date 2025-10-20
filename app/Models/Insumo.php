<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Insumo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'insumos';
    protected $primaryKey = 'idIns';
    
    protected $fillable = [
        'nomIns',
        'tipIns',
        'marIns',
        'canIns',
        'uniIns',
        'fecVenIns',
        'stockMinIns',
        'stockMaxIns',
        'idProveIns',
        'estIns',
        'obsIns'
    ];

    protected $casts = [
        'canIns' => 'decimal:2',
        'stockMinIns' => 'decimal:2',
        'stockMaxIns' => 'decimal:2',
        'fecVenIns' => 'date'
    ];

    // Dates para Soft Delete
    protected $dates = ['deleted_at', 'fecVenIns'];

    // Relaciones
    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'idProveIns', 'idProve');
    }

    public function movimientos()
    {
        return $this->hasMany(Inventario::class, 'idIns', 'idIns');
    }

    // Métodos para manejar soft delete
    
    /**
     * Verificar si el insumo puede ser eliminado
     */
    public function puedeEliminar()
    {
        // Verificar si tiene movimientos de inventario
        $tieneMovimientos = $this->movimientos()->count() > 0;
        
        return !$tieneMovimientos;
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

        return implode(', ', $razones);
    }

    // Scopes para filtrar insumos

    /**
     * Scope para insumos activos (no eliminados)
     */
    public function scopeActivos($query)
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * Scope para insumos eliminados
     */
    public function scopeEliminados($query)
    {
        return $query->onlyTrashed();
    }

    /**
     * Scope para filtrar por estado
     */
    public function scopeByEstado($query, $estado)
    {
        if ($estado) {
            return $query->where('estIns', $estado);
        }
        return $query;
    }

    /**
     * Scope para filtrar por tipo
     */
    public function scopeByTipo($query, $tipo)
    {
        if ($tipo) {
            return $query->where('tipIns', $tipo);
        }
        return $query;
    }

    /**
     * Scope para filtrar por nivel de stock
     * Nota: Temporalmente deshabilitado hasta implementar cálculo de stock actual
     */
    public function scopeByStock($query, $nivelStock)
    {
        // Por ahora retornamos la query sin filtrar
        // TODO: Implementar cálculo de stock actual desde movimientos
        return $query;
    }

    /**
     * Scope para filtrar por vencimiento
     */
    public function scopeByVencimiento($query, $vencimiento)
    {
        $hoy = now();
        
        if ($vencimiento === 'vencido') {
            return $query->where('fecVenIns', '<', $hoy);
        } elseif ($vencimiento === 'urgente') {
            return $query->whereBetween('fecVenIns', [$hoy, $hoy->copy()->addDays(7)]);
        } elseif ($vencimiento === 'critico') {
            return $query->whereBetween('fecVenIns', [$hoy, $hoy->copy()->addDays(30)]);
        }
        return $query;
    }
}