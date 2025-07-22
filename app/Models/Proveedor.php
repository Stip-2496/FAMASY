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

    protected $rules = [
        'nomProve' => 'required|string|max:100',
        'nitProve' => 'required|string|max:20|unique:proveedores,nitProve',
        'conProve' => 'nullable|string|size:10|regex:/^[0-9]{10}$/',
        'telProve' => 'nullable|string|max:20|regex:/^[0-9\s\-\(\)]+$/',
        'emailProve' => 'nullable|email|max:100',
        'dirProve' => 'nullable|string|max:255',
        'tipSumProve' => 'nullable|string|max:100',
        'obsProve' => 'nullable|string'
    ];

    public function getRules($id = null)
    {
        $rules = $this->rules;
        
        // Para actualización, modificar la regla unique
        if ($id) {
            $rules['nitProve'] = 'required|string|max:20|unique:proveedores,nitProve,' . $id . ',idProve';
        }
        
        return $rules;
    }

    // Accessor para formatear el nombre
    public function getNomProveAttribute($value)
    {
        return ucwords(strtolower($value));
    }

    // Mutator para el NIT (limpiar espacios)
    public function setNitProveAttribute($value)
    {
        $this->attributes['nitProve'] = preg_replace('/\s+/', '', $value);
    }
}