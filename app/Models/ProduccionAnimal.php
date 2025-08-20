<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProduccionAnimal extends Model
{
    protected $table = 'produccionanimal';
    protected $primaryKey = 'idProAni';
    
    protected $fillable = [
        'idAniPro',
        'tipProAni',
        'canProAni',
        'uniProAni',
        'fecProAni',
        'obsProAni',
        'canTotProAni'
    ];
    
    protected $casts = [
        'fecProAni' => 'date',
        'canProAni' => 'decimal:2',
        'canTotProAni' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    /**
     * Relación con el animal
     */
    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class, 'idAniPro', 'idAni');
    }
    
    /**
     * Scope para filtrar por tipo de producción
     */
    public function scopeTipo($query, $tipo)
    {
        return $query->where('tipProAni', $tipo);
    }
    
    /**
     * Accesor para cantidad formateada
     */
    public function getCantidadFormateadaAttribute(): string
    {
        $unidad = $this->uniProAni ?? 'unidad';
        return number_format($this->canProAni, 2).' '.$unidad;
    }
    
    /**
     * Accesor para cantidad total formateada
     */
    public function getCantidadTotalFormateadaAttribute(): string
    {
        $unidad = $this->uniProAni ?? 'unidad';
        return number_format($this->canTotProAni, 2).' '.$unidad;
    }
}