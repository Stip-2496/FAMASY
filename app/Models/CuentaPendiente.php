<?php
// app/Models/CuentaPendiente.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class CuentaPendiente extends Model
{
    use HasFactory;

    protected $table = 'cuentaspendientes';
    protected $primaryKey = 'idCuePen';

    protected $fillable = [
        'tipCuePen',
        'idFacCuePen',
        'idComGasCuePen',
        'idCliCuePen',
        'idProveCuePen',
        'montoOriginal',
        'montoPagado',
        'montoSaldo',
        'fecVencimiento',
        'diasVencido',
        'estCuePen'
    ];

    protected $casts = [
        'fecVencimiento' => 'date',
        'montoOriginal' => 'decimal:2',
        'montoPagado' => 'decimal:2',
        'montoSaldo' => 'decimal:2',
        'diasVencido' => 'integer'
    ];

    /**
     * Relación con Cliente
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'idCliCuePen', 'idCli');
    }

    /**
     * Relación con Proveedor
     */
    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'idProveCuePen', 'idProve');
    }

    /**
     * Relación con Factura
     */
    public function factura(): BelongsTo
    {
        return $this->belongsTo(Factura::class, 'idFacCuePen', 'idFac');
    }

    /**
     * Relación con CompraGasto
     */
    public function compraGasto(): BelongsTo
    {
        return $this->belongsTo(CompraGasto::class, 'idComGasCuePen', 'idComGas');
    }

    /**
     * Scope para cuentas por cobrar
     */
    public function scopePorCobrar($query)
    {
        return $query->where('tipCuePen', 'por_cobrar');
    }

    /**
     * Scope para cuentas por pagar
     */
    public function scopePorPagar($query)
    {
        return $query->where('tipCuePen', 'por_pagar');
    }

    /**
     * Scope para cuentas pendientes
     */
    public function scopePendientes($query)
    {
        return $query->where('estCuePen', '!=', 'pagado');
    }

    /**
     * Scope para cuentas vencidas
     */
    public function scopeVencidas($query)
    {
        return $query->where('fecVencimiento', '<', Carbon::now())
                    ->where('estCuePen', '!=', 'pagado');
    }

    /**
     * Scope para próximas a vencer
     */
    public function scopeProximasVencer($query, $dias = 7)
    {
        return $query->whereBetween('fecVencimiento', [
                        Carbon::now(),
                        Carbon::now()->addDays($dias)
                    ])
                    ->where('estCuePen', '!=', 'pagado');
    }

    /**
     * Accessor para obtener el nombre del deudor/acreedor
     */
    public function getNombreDeudorAttribute()
    {
        if ($this->tipCuePen === 'por_cobrar' && $this->cliente) {
            return $this->cliente->nomCli;
        }
        
        if ($this->tipCuePen === 'por_pagar' && $this->proveedor) {
            return $this->proveedor->nomProve;
        }
        
        return 'N/A';
    }

    /**
     * Accessor para obtener el documento del deudor/acreedor
     */
    public function getDocumentoDeudorAttribute()
    {
        if ($this->tipCuePen === 'por_cobrar' && $this->cliente) {
            return $this->cliente->docCli;
        }
        
        if ($this->tipCuePen === 'por_pagar' && $this->proveedor) {
            return $this->proveedor->nitProve;
        }
        
        return '';
    }

    /**
     * Accessor para verificar si está vencida
     */
    public function getEstaVencidaAttribute()
    {
        return $this->fecVencimiento < Carbon::now() && $this->estCuePen !== 'pagado';
    }

    /**
     * Accessor para obtener días para vencer (negativos si ya venció)
     */
    public function getDiasParaVencerAttribute()
    {
        $hoy = Carbon::now();
        $vencimiento = Carbon::parse($this->fecVencimiento);
        
        return $vencimiento->diffInDays($hoy, false);
    }

    /**
     * Accessor para obtener el nivel de urgencia
     */
    public function getNivelUrgenciaAttribute()
    {
        if ($this->estCuePen === 'pagado') {
            return 'completado';
        }
        
        $diasParaVencer = $this->dias_para_vencer;
        
        if ($diasParaVencer < 0) {
            return 'vencido';
        }
        
        if ($diasParaVencer <= 3) {
            return 'critico';
        }
        
        if ($diasParaVencer <= 7) {
            return 'alto';
        }
        
        if ($diasParaVencer <= 15) {
            return 'medio';
        }
        
        return 'bajo';
    }

    /**
     * Método para marcar como pagado
     */
    public function marcarComoPagado($montoPagado = null)
    {
        $montoPagado = $montoPagado ?? $this->montoOriginal;
        
        $this->update([
            'montoPagado' => $montoPagado,
            'montoSaldo' => $this->montoOriginal - $montoPagado,
            'estCuePen' => $montoPagado >= $this->montoOriginal ? 'pagado' : 'parcial'
        ]);
        
        return $this;
    }

    /**
     * Método para registrar pago parcial
     */
    public function registrarPagoParcial($monto)
    {
        if ($monto <= 0 || $monto > $this->montoSaldo) {
            throw new \Exception('Monto de pago inválido');
        }
        
        $nuevoMontoPagado = $this->montoPagado + $monto;
        $nuevoSaldo = $this->montoOriginal - $nuevoMontoPagado;
        
        $this->update([
            'montoPagado' => $nuevoMontoPagado,
            'montoSaldo' => $nuevoSaldo,
            'estCuePen' => $nuevoSaldo <= 0 ? 'pagado' : 'parcial'
        ]);
        
        return $this;
    }

    /**
     * Método estático para estadísticas
     */
    public static function estadisticas()
    {
        return [
            'total_por_cobrar' => self::porCobrar()->pendientes()->sum('montoSaldo'),
            'total_por_pagar' => self::porPagar()->pendientes()->sum('montoSaldo'),
            'cuentas_vencidas' => self::vencidas()->count(),
            'proximas_vencer' => self::proximasVencer()->count(),
            'total_pendientes' => self::pendientes()->count()
        ];
    }

    /**
     * Boot method para actualizar automáticamente días vencidos
     */
    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($model) {
            if ($model->fecVencimiento) {
                $vencimiento = Carbon::parse($model->fecVencimiento);
                $hoy = Carbon::now();
                
                if ($vencimiento->isPast()) {
                    $model->diasVencido = $hoy->diffInDays($vencimiento);
                    if ($model->estCuePen === 'pendiente') {
                        $model->estCuePen = 'vencido';
                    }
                } else {
                    $model->diasVencido = 0;
                }
            }
        });
    }
}