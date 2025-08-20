<?php
// app/Models/Factura.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Factura extends Model
{
    use HasFactory;

    protected $table = 'facturas';
    protected $primaryKey = 'idFac';

    protected $fillable = [
        'idUsuFac',
        'idCliFac',
        'nomCliFac',
        'tipDocCliFac',
        'docCliFac',
        'fecFac',
        'totFac',
        'subtotalFac',
        'ivaFac',
        'descuentoFac',
        'metPagFac',
        'estFac',
        'obsFac',
        'pdfFac'
    ];

    protected $casts = [
        'fecFac' => 'date',
        'totFac' => 'decimal:2',
        'subtotalFac' => 'decimal:2',
        'ivaFac' => 'decimal:2',
        'descuentoFac' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Reglas de validación
    public function getRules($id = null): array
    {
        return [
            'nomCliFac' => 'required|string|max:100',
            'tipDocCliFac' => 'required|in:NIT,CC,CE,Pasaporte',
            'docCliFac' => 'required|string|max:20',
            'fecFac' => 'required|date',
            'subtotalFac' => 'required|numeric|min:0',
            'ivaFac' => 'required|numeric|min:0',
            'totFac' => 'required|numeric|min:0.01',
            'metPagFac' => 'nullable|string|max:50',
            'estFac' => 'required|in:emitida,pagada,anulada,pendiente',
            'obsFac' => 'nullable|string'
        ];
    }

    // Relaciones
    public function usuario()
    {
        return $this->belongsTo(User::class, 'idUsuFac', 'id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'idCliFac', 'idCli');
    }

    public function detalles()
    {
        return $this->hasMany(FacturaDetalle::class, 'idFacDet', 'idFac');
    }

    public function movimientoContable()
    {
        return $this->hasOne(MovimientoContable::class, 'idFacMovCont', 'idFac');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'idFacPago', 'idFac');
    }

    public function cuentaPendiente()
    {
        return $this->hasOne(CuentaPendiente::class, 'idFacCuePen', 'idFac');
    }

    // Scopes
    public function scopeEmitidas($query)
    {
        return $query->where('estFac', 'emitida');
    }

    public function scopePagadas($query)
    {
        return $query->where('estFac', 'pagada');
    }

    public function scopePendientes($query)
    {
        return $query->where('estFac', 'pendiente');
    }

    public function scopeAnuladas($query)
    {
        return $query->where('estFac', 'anulada');
    }

    public function scopeDelMes($query, $mes = null, $ano = null)
    {
        $mes = $mes ?? now()->month;
        $ano = $ano ?? now()->year;
        
        return $query->whereMonth('fecFac', $mes)
                    ->whereYear('fecFac', $ano);
    }

    public function scopeEntreFechas($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecFac', [$fechaInicio, $fechaFin]);
    }

    public function scopePorCliente($query, $cliente)
    {
        return $query->where('nomCliFac', 'like', '%' . $cliente . '%');
    }

    // Accessors
    public function getNumeroFacturaAttribute()
    {
        return 'FAC-' . str_pad($this->idFac, 6, '0', STR_PAD_LEFT);
    }

    public function getFechaFormateadaAttribute()
    {
        return $this->fecFac->format('d/m/Y');
    }

    public function getEstadoColorAttribute()
    {
        return match($this->estFac) {
            'emitida' => 'bg-blue-100 text-blue-800',
            'pagada' => 'bg-green-100 text-green-800',
            'pendiente' => 'bg-yellow-100 text-yellow-800',
            'anulada' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    public function getMontoFormateadoAttribute()
    {
        return number_format($this->totFac, 2);
    }

    public function getTotalPagadoAttribute()
    {
        return $this->pagos()->sum('montoPago') ?? 0;
    }

    public function getSaldoPendienteAttribute()
    {
        return $this->totFac - $this->getTotalPagadoAttribute();
    }

    public function getEstaVencidaAttribute()
    {
        if ($this->estFac === 'pagada' || $this->estFac === 'anulada') {
            return false;
        }
        
        // Consideramos vencida una factura después de 30 días
        return $this->fecFac->addDays(30) < now();
    }

    public function getDiasVencimientoAttribute()
    {
        if ($this->estFac === 'pagada' || $this->estFac === 'anulada') {
            return 0;
        }
        
        $fechaVencimiento = $this->fecFac->addDays(30);
        return now()->diffInDays($fechaVencimiento, false);
    }

    // Métodos estáticos
    public static function totalFacturadoMes($mes = null, $ano = null)
    {
        return self::delMes($mes, $ano)
                  ->whereIn('estFac', ['emitida', 'pagada', 'pendiente'])
                  ->sum('totFac') ?? 0;
    }

    public static function totalPagadoMes($mes = null, $ano = null)
    {
        return self::pagadas()->delMes($mes, $ano)->sum('totFac') ?? 0;
    }

    public static function totalPendienteMes($mes = null, $ano = null)
    {
        return self::whereIn('estFac', ['emitida', 'pendiente'])
                  ->delMes($mes, $ano)
                  ->sum('totFac') ?? 0;
    }

    public static function resumenFacturacion($periodo = 'mes')
    {
        $fechaInicio = match($periodo) {
            'hoy' => now()->startOfDay(),
            'semana' => now()->startOfWeek(),
            'mes' => now()->startOfMonth(),
            'trimestre' => now()->subMonths(2)->startOfMonth(),
            'ano' => now()->startOfYear(),
            default => now()->startOfMonth()
        };

        return [
            'total_facturado' => self::where('fecFac', '>=', $fechaInicio)
                                    ->whereIn('estFac', ['emitida', 'pagada', 'pendiente'])
                                    ->sum('totFac'),
            'total_pagado' => self::pagadas()
                                 ->where('fecFac', '>=', $fechaInicio)
                                 ->sum('totFac'),
            'total_pendiente' => self::whereIn('estFac', ['emitida', 'pendiente'])
                                    ->where('fecFac', '>=', $fechaInicio)
                                    ->sum('totFac'),
            'cantidad_facturas' => self::where('fecFac', '>=', $fechaInicio)
                                      ->whereIn('estFac', ['emitida', 'pagada', 'pendiente'])
                                      ->count(),
            'promedio_factura' => self::where('fecFac', '>=', $fechaInicio)
                                     ->whereIn('estFac', ['emitida', 'pagada', 'pendiente'])
                                     ->avg('totFac') ?? 0
        ];
    }

    // Métodos de utilidad
    public function marcarComoPagada()
    {
        $this->update(['estFac' => 'pagada']);
        
        // Actualizar cuenta pendiente si existe
        if ($this->cuentaPendiente) {
            $this->cuentaPendiente->marcarComoPagado();
        }
    }

    public function anular($motivo = null)
    {
        $this->update([
            'estFac' => 'anulada',
            'obsFac' => $this->obsFac . ($motivo ? " | Anulada: $motivo" : " | Factura anulada")
        ]);
        
        // Eliminar movimiento contable
        if ($this->movimientoContable) {
            $this->movimientoContable->delete();
        }
        
        // Actualizar cuenta pendiente si existe
        if ($this->cuentaPendiente) {
            $this->cuentaPendiente->update(['estCuePen' => 'pagado']);
        }
    }

    public function calcularTotales()
    {
        $subtotal = $this->detalles()->sum('subtotalDet') ?? 0;
        $iva = $subtotal * 0.19; // IVA del 19%
        $total = $subtotal + $iva - $this->descuentoFac;
        
        $this->update([
            'subtotalFac' => $subtotal,
            'ivaFac' => $iva,
            'totFac' => $total
        ]);
        
        return $this;
    }

    public function generarPdf()
    {
        // Aquí iría la lógica para generar el PDF de la factura
        // Por ahora retornamos el nombre del archivo que se generaría
        $nombreArchivo = 'factura_' . $this->idFac . '_' . date('Ymd') . '.pdf';
        
        $this->update(['pdfFac' => $nombreArchivo]);
        
        return $nombreArchivo;
    }
}