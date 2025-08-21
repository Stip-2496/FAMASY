<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class SingleSuperAdmin implements Rule
{
    protected $ignoreUserId;

    public function __construct($ignoreUserId = null)
    {
        $this->ignoreUserId = $ignoreUserId;
    }

    public function passes($attribute, $value)
    {
        // Permitir siempre - la lógica de negocio se maneja en el controlador
        return true;
    }

    public function message()
    {
        return 'Si asigna el rol de Superusuario, el superusuario actual será cambiado a Administrador automáticamente.';
    }
}