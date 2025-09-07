<?php
// app/Models/CompraGasto.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class CompraGasto extends Model
{
    use HasFactory;

    protected $table = 'comprasgastos';
    protected $primaryKey = 'idComGas';

    protected $fillable = [
        'tipComGas',
        'catComGas',
        'desComGas',
        'monComGas',
        'fecComGas',
        'metPagComGas',
        'provComGas',
        'docComGas',
        'obsComGas'
    ];

    protected $casts = [
        'fecComGas' => 'date',
        'monComGas' => 'decimal:2'
    ];

    /**
     * Relación con Pagos
     */
    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'idComGasPago', 'idComGas');
    }

    /**
     * Relación con CuentasPendientes
     */
    public function cuentasPendientes(): HasMany
    {
        return $this->hasMany(CuentaPendiente::class, 'idComGasCuePen', 'idComGas');
    }

    /**
     * Relación con MovimientosContables
     */
    public function movimientosContables(): HasMany
    {
        return $this->hasMany(MovimientoContable::class, 'idComGasMovCont', 'idComGas');
    }

    /**
     * Scope para gastos
     */
    public function scopeGastos($query)
    {
        return $query->where('tipComGas', 'gasto');
    }

    /**
     * Scope para compras
     */
    public function scopeCompras($query)
    {
        return $query->where('tipComGas', 'compra');
    }

    /**
     * Scope por mes
     */
    public function scopeByMes($query, $mes, $año = null)
    {
        $año = $año ?? date('Y');
        return $query->whereMonth('fecComGas', $mes)
                    ->whereYear('fecComGas', $año);
    }

    /**
     * Scope por año
     */
    public function scopeByAño($query, $año)
    {
        return $query->whereYear('fecComGas', $año);
    }

    /**
     * Scope por categoría
     */
    public function scopeByCategoria($query, $categoria)
    {
        return $query->where('catComGas', $categoria);
    }

    /**
     * Scope por proveedor
     */
    public function scopeByProveedor($query, $proveedor)
    {
        return $query->where('provComGas', 'like', '%' . $proveedor . '%');
    }

    /**
     * Scope por método de pago
     */
    public function scopeByMetodoPago($query, $metodo)
    {
        return $query->where('metPagComGas', $metodo);
    }

    /**
     * Scope por fecha
     */
    public function scopeByFecha($query, $fecha)
    {
        return $query->whereDate('fecComGas', $fecha);
    }

    /**
     * Scope por rango de fechas
     */
    public function scopeByRangoFechas($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecComGas', [$fechaInicio, $fechaFin]);
    }

    /**
     * Accessor para obtener el tipo formateado
     */
    public function getTipoFormateadoAttribute()
    {
        return ucfirst($this->tipComGas);
    }

    /**
     * Accessor para obtener la fecha formateada
     */
    public function getFechaFormateadaAttribute()
    {
        return $this->fecComGas->format('d/m/Y');
    }

    /**
     * Accessor para obtener el monto formateado
     */
    public function getMontoFormateadoAttribute()
    {
        return '$' . number_format($this->monComGas, 2);
    }

    /**
     * Accessor para verificar si tiene documento
     */
    public function getTieneDocumentoAttribute()
    {
        return !empty($this->docComGas);
    }

    /**
     * Accessor para obtener el nombre corto del proveedor
     */
    public function getProveedorCortoAttribute()
    {
        return strlen($this->provComGas) > 20 
            ? substr($this->provComGas, 0, 20) . '...' 
            : $this->provComGas;
    }

    /**
     * Accessor para obtener la descripción corta
     */
    public function getDescripcionCortaAttribute()
    {
        return strlen($this->desComGas) > 50 
            ? substr($this->desComGas, 0, 50) . '...' 
            : $this->desComGas;
    }

    /**
     * Método para obtener el color de la categoría
     */
    public function getColorCategoria()
    {
        $colores = [
            'Servicios Públicos' => 'bg-blue-100 text-blue-800',
            'Mantenimiento' => 'bg-orange-100 text-orange-800',
            'Transporte' => 'bg-green-100 text-green-800',
            'Suministros' => 'bg-purple-100 text-purple-800',
            'Alimentación Animal' => 'bg-yellow-100 text-yellow-800',
            'Veterinario' => 'bg-red-100 text-red-800',
            'Combustible' => 'bg-gray-100 text-gray-800',
            'Seguros' => 'bg-indigo-100 text-indigo-800',
            'Impuestos' => 'bg-pink-100 text-pink-800',
            'Otros' => 'bg-gray-100 text-gray-800'
        ];

        return $colores[$this->catComGas] ?? 'bg-gray-100 text-gray-800';
    }

    /**
     * Método para verificar si es del mes actual
     */
    public function esDeMesActual()
    {
        return $this->fecComGas->isCurrentMonth();
    }

    /**
     * Método para verificar si es de la semana actual
     */
    public function esDeSemanaActual()
    {
        return $this->fecComGas->isCurrentWeek();
    }

    /**
     * Método estático para estadísticas de gastos
     */
    public static function estadisticasGastos($mes = null, $año = null)
    {
        $query = self::gastos();
        
        if ($mes) {
            $query->byMes($mes, $año);
        }
        
        if ($año && !$mes) {
            $query->byAño($año);
        }

        return [
            'total_gastos' => $query->sum('monComGas'),
            'total_transacciones' => $query->count(),
            'promedio_gasto' => $query->avg('monComGas'),
            'gasto_maximo' => $query->max('monComGas'),
            'gasto_minimo' => $query->min('monComGas'),
            'gastos_por_categoria' => $query->selectRaw('catComGas, SUM(monComGas) as total, COUNT(*) as cantidad')
                                          ->groupBy('catComGas')
                                          ->orderBy('total', 'desc')
                                          ->get(),
            'gastos_por_metodo' => $query->selectRaw('metPagComGas, SUM(monComGas) as total, COUNT(*) as cantidad')
                                        ->groupBy('metPagComGas')
                                        ->orderBy('total', 'desc')
                                        ->get()
        ];
    }

    /**
     * Método estático para estadísticas de compras
     */
    public static function estadisticasCompras($mes = null, $año = null)
    {
        $query = self::compras();
        
        if ($mes) {
            $query->byMes($mes, $año);
        }
        
        if ($año && !$mes) {
            $query->byAño($año);
        }

        return [
            'total_compras' => $query->sum('monComGas'),
            'total_transacciones' => $query->count(),
            'promedio_compra' => $query->avg('monComGas'),
            'compra_maxima' => $query->max('monComGas'),
            'compra_minima' => $query->min('monComGas'),
            'compras_por_categoria' => $query->selectRaw('catComGas, SUM(monComGas) as total, COUNT(*) as cantidad')
                                           ->groupBy('catComGas')
                                           ->orderBy('total', 'desc')
                                           ->get(),
            'compras_por_proveedor' => $query->selectRaw('provComGas, SUM(monComGas) as total, COUNT(*) as cantidad')
                                           ->groupBy('provComGas')
                                           ->orderBy('total', 'desc')
                                           ->limit(10)
                                           ->get()
        ];
    }

    /**
     * Método para obtener las categorías más usadas
     */
    public static function categoriasMasUsadas($tipo = 'gasto', $limit = 10)
    {
        return self::where('tipComGas', $tipo)
                  ->selectRaw('catComGas, COUNT(*) as uso_count, SUM(monComGas) as total_monto')
                  ->groupBy('catComGas')
                  ->orderBy('uso_count', 'desc')
                  ->limit($limit)
                  ->get();
    }

    /**
     * Método para obtener los proveedores más frecuentes
     */
    public static function proveedoresFrecuentes($limit = 10)
    {
        return self::selectRaw('provComGas, COUNT(*) as transacciones, SUM(monComGas) as total_monto')
                  ->groupBy('provComGas')
                  ->orderBy('transacciones', 'desc')
                  ->limit($limit)
                  ->get();
    }

    /**
     * Método para validar duplicados
     */
    public static function validarDuplicado($descripcion, $monto, $fecha, $proveedor)
    {
        return self::where('desComGas', $descripcion)
                  ->where('monComGas', $monto)
                  ->whereDate('fecComGas', $fecha)
                  ->where('provComGas', $proveedor)
                  ->exists();
    }

    /**
     * Boot method para validaciones automáticas
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            // Validar que la fecha no sea futura para gastos
            if ($model->tipComGas === 'gasto' && $model->fecComGas > Carbon::now()) {
                throw new \Exception('La fecha del gasto no puede ser futura');
            }
            
            // Validar monto positivo
            if ($model->monComGas <= 0) {
                throw new \Exception('El monto debe ser mayor a cero');
            }
        });
        
        static::updating(function ($model) {
            // Validar monto positivo en actualizaciones también
            if ($model->monComGas <= 0) {
                throw new \Exception('El monto debe ser mayor a cero');
            }
        });
    }
}