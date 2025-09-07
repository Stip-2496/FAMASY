<?php
// app/Models/MovimientoContable.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class MovimientoContable extends Model
{
    use HasFactory;

    protected $table = 'movimientoscontables';
    protected $primaryKey = 'idMovCont';

    protected $fillable = [
        'fecMovCont',
        'tipoMovCont',
        'catMovCont',
        'conceptoMovCont',
        'montoMovCont',
        'idFacMovCont',
        'idComGasMovCont',
        'idAniMovCont',
        'idProAniMovCont',
        'idInvMovCont',
        'obsMovCont'
    ];

    protected $casts = [
        'fecMovCont' => 'date',
        'montoMovCont' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Reglas de validación adaptadas a tu estructura
    public function getRules($id = null): array
    {
        return [
            'fecMovCont' => 'required|date',
            'tipoMovCont' => 'required|in:ingreso,egreso',
            'conceptoMovCont' => 'required|string|max:200', // Tu BD tiene 200 caracteres
            'montoMovCont' => 'required|numeric|min:0.01',
            'catMovCont' => 'required|string|max:100', // En tu BD es requerido
            'obsMovCont' => 'nullable|string'
        ];
    }

    // Accessors para compatibilidad con las vistas
    public function getFechaAttribute()
    {
        return $this->fecMovCont;
    }

    public function getTipoAttribute()
    {
        return $this->tipoMovCont;
    }

    public function getCategoriaAttribute()
    {
        return $this->catMovCont;
    }

    public function getConceptoAttribute()
    {
        return $this->conceptoMovCont;
    }

    public function getDescripcionAttribute()
    {
        return $this->conceptoMovCont;
    }

    public function getMontoAttribute()
    {
        return $this->montoMovCont;
    }

    public function getObservacionesAttribute()
    {
        return $this->obsMovCont;
    }

    // Scopes para consultas frecuentes
    public function scopeIngresos($query)
    {
        return $query->where('tipoMovCont', 'ingreso');
    }

    public function scopeEgresos($query)
    {
        return $query->where('tipoMovCont', 'egreso');
    }

    public function scopeDelMes($query, $mes = null, $ano = null)
    {
        $mes = $mes ?? now()->month;
        $ano = $ano ?? now()->year;
        
        return $query->whereMonth('fecMovCont', $mes)
                    ->whereYear('fecMovCont', $ano);
    }

    public function scopeRecientes($query, $dias = 30)
    {
        return $query->where('fecMovCont', '>=', Carbon::now()->subDays($dias))
                    ->orderBy('fecMovCont', 'desc');
    }

    public function scopeEntreFechas($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecMovCont', [$fechaInicio, $fechaFin]);
    }

    public function scopePorCategoria($query, $categoria)
    {
        return $query->where('catMovCont', 'like', '%' . $categoria . '%');
    }

    // Relaciones basadas en tu estructura de BD existente
    public function factura()
    {
        return $this->belongsTo(Factura::class, 'idFacMovCont', 'idFac');
    }

    public function compraGasto()
    {
        return $this->belongsTo(CompraGasto::class, 'idComGasMovCont', 'idComGas');
    }

    public function animal()
    {
        return $this->belongsTo(Animal::class, 'idAniMovCont', 'idAni');
    }

    public function produccionAnimal()
    {
        return $this->belongsTo(ProduccionAnimal::class, 'idProAniMovCont', 'idProAni');
    }

    public function inventario()
    {
        return $this->belongsTo(Inventario::class, 'idInvMovCont', 'idInv');
    }

    // Métodos estáticos para métricas rápidas
    public static function totalIngresosMes($mes = null, $ano = null)
    {
        return self::ingresos()->delMes($mes, $ano)->sum('montoMovCont') ?? 0;
    }

    public static function totalEgresosMes($mes = null, $ano = null)
    {
        return self::egresos()->delMes($mes, $ano)->sum('montoMovCont') ?? 0;
    }

    public static function balanceMes($mes = null, $ano = null)
    {
        $ingresos = self::totalIngresosMes($mes, $ano);
        $egresos = self::totalEgresosMes($mes, $ano);
        return $ingresos - $egresos;
    }

    public static function totalPorCategoria($categoria, $tipo = null)
    {
        $query = self::porCategoria($categoria);
        
        if ($tipo) {
            $query->where('tipoMovCont', $tipo);
        }
        
        return $query->sum('montoMovCont') ?? 0;
    }

    public static function resumenMensual($ano = null)
    {
        $ano = $ano ?? now()->year;
        $resumen = [];
        
        for ($mes = 1; $mes <= 12; $mes++) {
            $resumen[$mes] = [
                'mes' => $mes,
                'ingresos' => self::totalIngresosMes($mes, $ano),
                'egresos' => self::totalEgresosMes($mes, $ano),
                'balance' => self::balanceMes($mes, $ano)
            ];
        }
        
        return $resumen;
    }

    // Métodos de utilidad
    public function esIngreso()
    {
        return $this->tipoMovCont === 'ingreso';
    }

    public function esEgreso()
    {
        return $this->tipoMovCont === 'egreso';
    }

    public function getMontoFormateadoAttribute()
    {
        return number_format($this->montoMovCont, 2);
    }

    public function getFechaFormateadaAttribute()
    {
        return $this->fecMovCont->format('d/m/Y');
    }

    public function getTipoIconoAttribute()
    {
        return $this->tipoMovCont === 'ingreso' ? 'fa-arrow-up' : 'fa-arrow-down';
    }

    public function getTipoColorAttribute()
    {
        return $this->tipoMovCont === 'ingreso' ? 'text-green-600' : 'text-red-600';
    }
}