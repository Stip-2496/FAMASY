<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pago extends Model
{
    use HasFactory;

    protected $table = 'pagos';
    protected $primaryKey = 'idPago';

    protected $fillable = [
        'idFacPago',
        'idComGasPago',
        'fecPago',
        'montoPago',
        'metPago',
        'numCompPago',
        'entBancPago',
        'obsPago'
    ];

    protected $casts = [
        'fecPago' => 'date',
        'montoPago' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relación con Facturas
    public function factura(): BelongsTo
    {
        return $this->belongsTo(Factura::class, 'idFacPago', 'idFac');
    }

    // Relación con Compras/Gastos
    public function compraGasto(): BelongsTo
    {
        return $this->belongsTo(CompraGasto::class, 'idComGasPago', 'idComGas');
    }

    // Scopes para filtros
    public function scopeByMetodo($query, $metodo)
    {
        return $query->where('metPago', $metodo);
    }

    public function scopeByFecha($query, $fecha)
    {
        return $query->whereDate('fecPago', $fecha);
    }

    public function scopeByMes($query, $mes, $ano)
    {
        return $query->whereMonth('fecPago', $mes)
                    ->whereYear('fecPago', $ano);
    }
}
