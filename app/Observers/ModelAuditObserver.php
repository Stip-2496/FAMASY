<?php

namespace App\Observers;

use App\Models\Auditoria;
use Illuminate\Database\Eloquent\Model;

class ModelAuditObserver
{
    public function created(Model $model)
    {
        $this->logOperation('INSERT', $model, 'Creación de registro');
    }

    public function updated(Model $model)
    {
        $this->logOperation('UPDATE', $model, 'Actualización de registro');
    }

    public function deleted(Model $model)
    {
        $this->logOperation('DELETE', $model, 'Eliminación de registro');
    }

    protected function logOperation($operation, $model, $description)
    {
        $user = auth()->user();

        Auditoria::create([
            'idUsuAud' => $user ? $user->id : null,
            'usuAud' => $user ? $user->nomUsu . ' ' . $user->apeUsu : 'Sistema',
            'rolAud' => $user ? $user->rol->nomRol : 'N/A',
            'opeAud' => $operation,
            'tablaAud' => $model->getTable(),
            'regAud' => $model->getKey(),
            'desAud' => $description . ' en tabla ' . $model->getTable(),
            'ipAud' => request()->ip()
        ]);
    }
}