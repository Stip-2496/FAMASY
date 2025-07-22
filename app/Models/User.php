<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Campos asignables masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'password',
        'tipDocUsu',
        'numDocUsu',
        'nomUsu',
        'apeUsu',
        'fecNacUsu',
        'sexUsu',
        'idRolUsu',
        'idConUsu',
    ];

    /**
     * Campos ocultos al serializar.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Conversión de tipos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Iniciales del usuario (tomadas de nomUsu y apeUsu).
     */
    public function initials(): string
    {
        return Str::substr($this->nomUsu, 0, 1) . Str::substr($this->apeUsu, 0, 1);
    }

    /**
     * Relación: Usuario pertenece a un Rol.
     */
    public function rol()
    {
        return $this->belongsTo(Rol::class, 'idRolUsu', 'idRol');
    }

    /**
     * Relación: Usuario pertenece a un Contacto.
     */
    public function contacto()
    {
        return $this->belongsTo(Contacto::class, 'idConUsu', 'idCon');
    }

    /**
     * Relación: Usuario pertenece a un Direccion.
     */
    public function direccion()
    {
        return $this->hasOneThrough(Direccion::class, Contacto::class, 'idCon', 'idConDir', 'idConUsu', 'idCon');
    }

    /**
     * Relación: Usuario tiene muchos backups de base de datos.
     */
    public function databaseBackups()
    {
    return $this->hasMany(DatabaseBackup::class, 'idUsuBac');
    }

}
