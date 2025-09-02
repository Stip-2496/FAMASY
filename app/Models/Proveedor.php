<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    use HasFactory;

    protected $table = 'proveedores';
    protected $primaryKey = 'idProve';

    protected $fillable = [
        'nomProve',
        'nitProve',
        'conProve',
        'telProve',
        'emailProve',
        'dirProve',
        'tipSumProve',
        'obsProve'
    ];

    /**
     * Obtiene las reglas de validación
     * 
     * @param int|null $id ID del proveedor a ignorar en reglas unique
     * @return array Reglas de validación
     */
    public function getRules(?int $id = null): array
    {
        $rules = [
            'nomProve' => 'required|string|max:100',
            'conProve' => 'nullable|string|size:10|regex:/^[0-9]{10}$/',
            'telProve' => 'nullable|string|max:20|regex:/^[0-9\s\-\(\)]+$/',
            'emailProve' => 'nullable|email|max:100',
            'dirProve' => 'nullable|string|max:255',
            'tipSumProve' => 'nullable|string|max:100',
            'obsProve' => 'nullable|string'
        ];

        // Regla especial para nitProve con unique
        if ($id) {
            // Para actualización: ignorar el registro actual
            $rules['nitProve'] = "required|string|max:20|unique:proveedores,nitProve,{$id},idProve";
        } else {
            // Para creación: verificar que sea único
            $rules['nitProve'] = 'required|string|max:20|unique:proveedores,nitProve';
        }
        
        return $rules;
    }

    /**
     * Accessor para formatear el nombre
     */
    public function getNomProveAttribute($value): string
    {
        return ucwords(strtolower($value));
    }

    /**
     * Mutator para el NIT (limpiar espacios)
     */
    public function setNitProveAttribute($value): void
    {
        $this->attributes['nitProve'] = preg_replace('/\s+/', '', $value);
    }
}