<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Auditoria extends Model
{
    protected $table = 'auditoria';
    protected $primaryKey = 'idAud';
    public $timestamps = false;

    protected $fillable = [
        'idUsuAud',
        'usuAud',
        'rolAud',
        'opeAud',
        'tablaAud',
        'regAud',
        'desAud',
        'ipAud',
        'fecAud'
    ];

    //  Esto convierte automáticamente fecAud en Carbon
    protected $casts = [
        'fecAud' => 'datetime',
    ];

    // Relación con el usuario (si existe)
    public function usuario()
    {
        return $this->belongsTo(User::class, 'idUsuAud', 'id');
    }

    // Método para determinar si es un evento anómalo
    public function esAnomalo()
    {
        return in_array($this->opeAud, ['LOGIN_FAILED', 'UNAUTHORIZED_ACCESS']) || 
               str_contains($this->desAud, 'intento fallido') ||
               str_contains($this->desAud, 'acceso no autorizado');
    }

    // Método para determinar severidad
    public function getSeveridadAttribute()
    {
        if (str_contains($this->desAud, 'crítico') || $this->opeAud === 'UNAUTHORIZED_ACCESS') {
            return 'critica';
        } elseif (str_contains($this->desAud, 'peligroso')) {
            return 'alta';
        } elseif (str_contains($this->desAud, 'advertencia')) {
            return 'media';
        } elseif ($this->opeAud === 'LOGIN_FAILED') {
            return 'baja';
        }
        return null;
    }
}
