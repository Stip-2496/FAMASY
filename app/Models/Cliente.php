<?php
// app/Models/Cliente.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Cliente extends Model
{
    use HasFactory;

    protected $table = 'clientes';
    protected $primaryKey = 'idCli';

    protected $fillable = [
        'nomCli',
        'tipDocCli',
        'docCli',
        'telCli',
        'emailCli',
        'dirCli',
        'tipCli',
        'estCli',
        'obsCli'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relaciones
    public function facturas()
    {
        return $this->hasMany(Factura::class, 'idCliFac', 'idCli');
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('estCli', 'activo');
    }

    public function scopeInactivos($query)
    {
        return $query->where('estCli', 'inactivo');
    }

    public function scopeParticulares($query)
    {
        return $query->where('tipCli', 'particular');
    }

    public function scopeEmpresas($query)
    {
        return $query->where('tipCli', 'empresa');
    }

    public function scopeBuscar($query, $termino)
    {
        return $query->where(function($q) use ($termino) {
            $q->where('nomCli', 'like', '%' . $termino . '%')
              ->orWhere('docCli', 'like', '%' . $termino . '%')
              ->orWhere('emailCli', 'like', '%' . $termino . '%');
        });
    }

    // Accessors
    public function getNombreCompletoAttribute()
    {
        return $this->nomCli . ' (' . $this->tipDocCli . ': ' . $this->docCli . ')';
    }

    public function getEstadoColorAttribute()
    {
        return $this->estCli === 'activo' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
    }

    public function getTipoIconoAttribute()
    {
        return $this->tipCli === 'empresa' ? 'fas fa-building' : 'fas fa-user';
    }

    public function getTotalFacturadoAttribute()
    {
        return $this->facturas()
                   ->whereIn('estFac', ['emitida', 'pagada', 'pendiente'])
                   ->sum('totFac') ?? 0;
    }

    public function getTotalPagadoAttribute()
    {
        return $this->facturas()
                   ->where('estFac', 'pagada')
                   ->sum('totFac') ?? 0;
    }

    public function getCantidadFacturasAttribute()
    {
        return $this->facturas()->count();
    }

    public function getUltimaFacturaAttribute()
    {
        return $this->facturas()
                   ->orderBy('fecFac', 'desc')
                   ->first()?->fecFac?->format('d/m/Y');
    }

    public function getPromedioFacturaAttribute()
    {
        return $this->facturas()
                   ->whereIn('estFac', ['emitida', 'pagada', 'pendiente'])
                   ->avg('totFac') ?? 0;
    }

    // Métodos de utilidad
    public function activar()
    {
        $this->update(['estCli' => 'activo']);
    }

    public function desactivar()
    {
        $this->update(['estCli' => 'inactivo']);
    }

    public function esEmpresa()
    {
        return $this->tipCli === 'empresa';
    }

    public function esParticular()
    {
        return $this->tipCli === 'particular';
    }

    // Métodos estáticos
    public static function buscarPorDocumento($documento)
    {
        return self::where('docCli', $documento)->first();
    }

    public static function clientesActivos()
    {
        return self::activos()->orderBy('nomCli')->get();
    }

    public static function estadisticasClientes()
    {
        return [
            'total_clientes' => self::count(),
            'clientes_activos' => self::activos()->count(),
            'clientes_inactivos' => self::inactivos()->count(),
            'empresas' => self::empresas()->count(),
            'particulares' => self::particulares()->count(),
        ];
    }
}