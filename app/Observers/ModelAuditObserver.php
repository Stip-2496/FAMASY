<?php

namespace App\Observers;

use App\Models\Auditoria;
use App\Models\Rol;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ModelAuditObserver
{
    public function created(Model $model)
    {
        $module = $this->getModuleFromTable($model->getTable());
        $description = $this->generateCreationDescription($model, $module);
        $this->logOperation('INSERT', $model, $description);
    }

    public function updated(Model $model)
    {
        $module = $this->getModuleFromTable($model->getTable());
        $description = $this->generateUpdateDescription($model, $module);
        $this->logOperation('UPDATE', $model, $description);
    }

    public function deleted(Model $model)
    {
        $module = $this->getModuleFromTable($model->getTable());
        $description = $this->generateDeletionDescription($model, $module);
        $this->logOperation('DELETE', $model, $description);
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
            'desAud' => $description,
            'ipAud' => request()->ip()
        ]);
    }

    /**
     * Determina el módulo basado en el nombre de la tabla
     */
    protected function getModuleFromTable($tableName)
    {
        $tableToModuleMap = [
            'animales' => 'Pecuario - Animales',
            'historialmedico' => 'Pecuario - Salud Animal',
            'produccionanimal' => 'Pecuario - Producción',
            'herramientas' => 'Inventario - Herramientas',
            'insumos' => 'Inventario - Insumos',
            'mantenimientos' => 'Inventario - Mantenimientos',
            'prestamosherramientas' => 'Inventario - Préstamos',
            'inventario' => 'Inventario - Movimientos',
            'clientes' => 'Ventas - Clientes',
            'proveedores' => 'Proveedores',
            'facturas' => 'Contabilidad - Facturas',
            'movimientoscontables' => 'Contabilidad - Movimientos',
            'pagos' => 'Contabilidad - Pagos',
            'cuentaspendientes' => 'Contabilidad - Cuentas Pendientes',
            'comprasgastos' => 'Contabilidad - Gastos',
            'users' => 'Configuración - Usuarios',
            'roles' => 'Configuración - Roles',
            'database_backups' => 'Configuración - Respaldos',
            'contacto' => 'Contactos',
            'direccion' => 'Direcciones',
        ];

        return $tableToModuleMap[$tableName] ?? 'Sistema';
    }

    /**
     * Genera una descripción detallada para la creación de registros
     */
    protected function generateCreationDescription(Model $model, $module)
    {
        $keyValue = $model->getKey();
        
        // Descripciones específicas por modelo
        switch (get_class($model)) {
            case 'App\Models\Animal':
                return "Nuevo animal registrado en $module. " . 
                       "Nombre: {$model->nomAni} (ID: $keyValue). " .
                       "Especie: {$model->espAni}, Raza: {$model->razAni}, " .
                       "Fecha de nacimiento: " . ($model->fecNacAni ? Carbon::parse($model->fecNacAni)->format('d/m/Y') : 'N/A') . ", " .
                       "Sexo: {$model->sexAni}, Peso: " . ($model->pesAni ?? 'N/A') . " kg, " .
                       "Estado: {$model->estAni}";

            case 'App\Models\Herramienta':
                return "Nueva herramienta registrada en $module. " .
                       "Nombre: {$model->nomHer} (ID: $keyValue). " .
                       "Categoría: {$model->catHer}, Estado: {$model->estHer}, " .
                       "Ubicación: {$model->ubiHer}, Stock mínimo: {$model->stockMinHer}";

            case 'App\Models\Factura':
                $clienteNombre = $model->cliente ? $model->cliente->nomCli : $model->nomCliFac;
                return "Nueva factura creada en $module. " .
                       "Número: FAC-" . str_pad($keyValue, 6, '0', STR_PAD_LEFT) . ". " .
                       "Cliente: {$clienteNombre}, " .
                       "Monto: $" . number_format($model->totFac, 2) . ", " .
                       "Fecha: " . ($model->fecFac ? $model->fecFac->format('d/m/Y') : 'N/A') . ", " .
                       "Estado: {$model->estFac}";

            case 'App\Models\User':
                return "Nuevo usuario registrado en $module. " .
                       "Nombre: {$model->nomUsu} {$model->apeUsu} (ID: $keyValue). " .
                       "Email: {$model->email}, Documento: {$model->tipDocUsu} {$model->numDocUsu}, " .
                       "Rol: " . ($model->rol ? $model->rol->nomRol : 'N/A');

            case 'App\Models\Cliente':
                return "Nuevo cliente registrado en $module. " .
                       "Nombre: {$model->nomCli} (ID: $keyValue). " .
                       "Documento: {$model->tipDocCli} {$model->docCli}, " .
                       "Teléfono: {$model->telCli}, Email: {$model->emailCli}, " .
                       "Tipo: {$model->tipCli}, Estado: {$model->estCli}";

            case 'App\Models\Proveedor':
                return "Nuevo proveedor registrado en $module. " .
                       "Nombre: {$model->nomProve} (ID: $keyValue). " .
                       "NIT: {$model->nitProve}, Contacto: {$model->conProve}, " .
                       "Teléfono: {$model->telProve}, Tipo de suministro: {$model->tipSumProve}";

            case 'App\Models\CompraGasto':
                $tipo = $model->tipComGas === 'compra' ? 'Compra' : 'Gasto';
                return "Nueva {$tipo} registrada en $module. " .
                       "Descripción: {$model->desComGas} (ID: $keyValue). " .
                       "Monto: $" . number_format($model->monComGas, 2) . ", " .
                       "Fecha: " . ($model->fecComGas ? $model->fecComGas->format('d/m/Y') : 'N/A') . ", " .
                       "Categoría: {$model->catComGas}, Proveedor: {$model->provComGas}";

            case 'App\Models\ProduccionAnimal':
                $animalNombre = $model->animal ? $model->animal->nomAni : 'Animal ID: ' . $model->idAniPro;
                return "Nueva producción registrada en $module. " .
                       "Animal: {$animalNombre} (ID: $keyValue). " .
                       "Tipo: {$model->tipProAni}, Cantidad: {$model->canProAni} {$model->uniProAni}, " .
                       "Fecha: " . ($model->fecProAni ? $model->fecProAni->format('d/m/Y') : 'N/A');

            case 'App\Models\PrestamoHerramienta':
                $herramientaNombre = $model->herramienta ? $model->herramienta->nomHer : 'Herramienta ID: ' . $model->idHerPre;
                $usuarioNombre = $model->usuario ? $model->usuario->nomUsu . ' ' . $model->usuario->apeUsu : 'Usuario ID: ' . $model->idUsuPre;
                return "Nuevo préstamo registrado en $module. " .
                       "Herramienta: {$herramientaNombre} (ID: $keyValue). " .
                       "Usuario: {$usuarioNombre}, " .
                       "Fecha préstamo: " . ($model->fecPre ? $model->fecPre->format('d/m/Y') : 'N/A') . ", " .
                       "Fecha devolución: " . ($model->fecDev ? $model->fecDev->format('d/m/Y') : 'N/A') . ", " .
                       "Estado: {$model->estPre}";

            case 'App\Models\HistorialMedico':
                $animalNombre = $model->animal ? $model->animal->nomAni : 'Animal ID: ' . $model->idAni;
                return "Nuevo historial médico registrado en $module. " .
                       "Animal: {$animalNombre} (ID: $keyValue). " .
                       "Tipo: {$model->tipHisMed}, Fecha: " . ($model->fecHisMed ? $model->fecHisMed->format('d/m/Y') : 'N/A') . ", " .
                       "Tratamiento: " . (strlen($model->traHisMed) > 50 ? substr($model->traHisMed, 0, 50) . '...' : $model->traHisMed);

            case 'App\Models\Mantenimiento':
                $herramientaNombre = $model->getNombreHerramientaCompleto();
                return "Nuevo mantenimiento registrado en $module. " .
                       "Herramienta: {$herramientaNombre} (ID: $keyValue). " .
                       "Tipo: {$model->getTipoFormateado()}, Fecha: " . ($model->fecMan ? $model->fecMan->format('d/m/Y') : 'N/A') . ", " .
                       "Estado: {$model->getEstadoFormateado()}, Descripción: " . (strlen($model->desMan) > 30 ? substr($model->desMan, 0, 30) . '...' : $model->desMan);

            case 'App\Models\MovimientoContable':
                $tipo = $model->tipoMovCont === 'ingreso' ? 'Ingreso' : 'Egreso';
                return "Nuevo movimiento contable registrado en $module. " .
                       "{$tipo} (ID: $keyValue). " .
                       "Concepto: {$model->conceptoMovCont}, " .
                       "Monto: $" . number_format($model->montoMovCont, 2) . ", " .
                       "Fecha: " . ($model->fecMovCont ? $model->fecMovCont->format('d/m/Y') : 'N/A') . ", " .
                       "Categoría: {$model->catMovCont}";

            case 'App\Models\Pago':
                $destino = $model->factura ? 'Factura FAC-' . str_pad($model->factura->idFac, 6, '0', STR_PAD_LEFT) : 
                          ($model->compraGasto ? 'Compra/Gasto ID: ' . $model->compraGasto->idComGas : 'N/A');
                return "Nuevo pago registrado en $module. " .
                       "Destino: {$destino} (ID: $keyValue). " .
                       "Monto: $" . number_format($model->montoPago, 2) . ", " .
                       "Fecha: " . ($model->fecPago ? $model->fecPago->format('d/m/Y') : 'N/A') . ", " .
                       "Método: {$model->metPago}";

            case 'App\Models\CuentaPendiente':
                $tipo = $model->tipCuePen === 'por_cobrar' ? 'Por Cobrar' : 'Por Pagar';
                $deudor = $model->tipCuePen === 'por_cobrar' ? 
                         ($model->cliente ? $model->cliente->nomCli : 'Cliente ID: ' . $model->idCliCuePen) :
                         ($model->proveedor ? $model->proveedor->nomProve : 'Proveedor ID: ' . $model->idProveCuePen);
                return "Nueva cuenta {$tipo} registrada en $module. " .
                       "Deudor/Acreedor: {$deudor} (ID: $keyValue). " .
                       "Monto: $" . number_format($model->montoOriginal, 2) . ", " .
                       "Saldo: $" . number_format($model->montoSaldo, 2) . ", " .
                       "Vencimiento: " . ($model->fecVencimiento ? $model->fecVencimiento->format('d/m/Y') : 'N/A') . ", " .
                       "Estado: {$model->estCuePen}";

            case 'App\Models\Insumo':
                return "Nuevo insumo registrado en $module. " .
                       "Nombre: {$model->nomIns} (ID: $keyValue). " .
                       "Tipo: {$model->tipIns}, Marca: {$model->marIns}, " .
                       "Unidad: {$model->uniIns}, Stock mínimo: {$model->stockMinIns}, " .
                       "Proveedor: " . ($model->proveedor ? $model->proveedor->nomProve : 'N/A');

            case 'App\Models\DatabaseBackup':
                return "Nuevo respaldo de base de datos creado en $module. " .
                       "Nombre: {$model->nomBac} (ID: $keyValue). " .
                       "Versión: {$model->verBac}, Tipo: {$model->tipBac}, " .
                       "Archivo: {$model->arcBac}";

            case 'App\Models\Contacto':
                return "Nuevo contacto registrado en $module. " .
                       "Teléfono/Celular: {$model->celCon} (ID: $keyValue)";

            case 'App\Models\Direccion':
                $contactoId = $model->contacto ? $model->contacto->idCon : $model->idConDir;
                return "Nueva dirección registrada en $module. " .
                       "Contacto ID: {$contactoId} (ID: $keyValue). " .
                       "Ciudad: {$model->ciuDir}, Departamento: {$model->depDir}, " .
                       "Código postal: {$model->codPosDir}";

            default:
                // Descripción genérica para otros modelos
                $significantFields = $this->getSignificantFields($model);
                $details = implode(', ', array_slice($significantFields, 0, 4));
                
                if (count($significantFields) > 4) {
                    $details .= ', ...';
                }
                
                return "Nuevo registro creado en $module. ID: $keyValue. Detalles: [$details]";
        }
    }

    /**
     * Genera una descripción detallada para la actualización de registros
     */
    protected function generateUpdateDescription(Model $model, $module)
    {
        $keyValue = $model->getKey();
        $changes = $model->getChanges();
        unset($changes['updated_at']);
        
        if (empty($changes)) {
            return "Actualización en $module. ID: $keyValue. (Sin cambios detectados)";
        }
        
        // Descripciones específicas por modelo
        switch (get_class($model)) {
            case 'App\Models\Animal':
                $changeDetails = [];
                foreach ($changes as $field => $newValue) {
                    $oldValue = $model->getOriginal($field);
                    $changeDetails[] = $this->formatFieldChange($field, $oldValue, $newValue);
                }
                $animalNombre = $model->nomAni ?? 'Animal ID: ' . $keyValue;
                return "Animal actualizado en $module. Nombre: {$animalNombre} (ID: $keyValue). " .
                       "Cambios: " . implode(', ', $changeDetails);

            case 'App\Models\Herramienta':
                $changeDetails = [];
                foreach ($changes as $field => $newValue) {
                    $oldValue = $model->getOriginal($field);
                    
                    if ($field === 'estHer' && $newValue === 'prestado') {
                        $prestamo = $model->prestamos()->where('estPre', 'prestado')->latest()->first();
                        if ($prestamo && $prestamo->usuario) {
                            $usuarioPrestamo = $prestamo->usuario->nomUsu . ' ' . $prestamo->usuario->apeUsu;
                            $changeDetails[] = "Prestada a: $usuarioPrestamo (Devolución: " . 
                                              ($prestamo->fecDev ? $prestamo->fecDev->format('d/m/Y') : 'N/A') . ")";
                        } else {
                            $changeDetails[] = $this->formatFieldChange($field, $oldValue, $newValue);
                        }
                    } else {
                        $changeDetails[] = $this->formatFieldChange($field, $oldValue, $newValue);
                    }
                }
                $herramientaNombre = $model->nomHer ?? 'Herramienta ID: ' . $keyValue;
                return "Herramienta actualizada en $module. Nombre: {$herramientaNombre} (ID: $keyValue). " .
                       "Cambios: " . implode(', ', $changeDetails);

            case 'App\Models\Factura':
                $changeDetails = [];
                foreach ($changes as $field => $newValue) {
                    $oldValue = $model->getOriginal($field);
                    
                    if ($field === 'estFac') {
                        $changeDetails[] = "Estado: {$this->getEstadoFactura($oldValue)} → {$this->getEstadoFactura($newValue)}";
                    } else {
                        $changeDetails[] = $this->formatFieldChange($field, $oldValue, $newValue);
                    }
                }
                $clienteNombre = $model->cliente ? $model->cliente->nomCli : $model->nomCliFac;
                return "Factura actualizada en $module. Cliente: {$clienteNombre} (ID: $keyValue). " .
                       "Cambios: " . implode(', ', $changeDetails);

            case 'App\Models\User':
                $changeDetails = [];
                foreach ($changes as $field => $newValue) {
                    $oldValue = $model->getOriginal($field);
                    
                    if ($field === 'idRolUsu') {
                        $oldRol = $this->getNombreRol($oldValue);
                        $newRol = $this->getNombreRol($newValue);
                        $changeDetails[] = "Rol: {$oldRol} → {$newRol}";
                    } else {
                        $changeDetails[] = $this->formatFieldChange($field, $oldValue, $newValue);
                    }
                }
                $usuarioNombre = $model->nomUsu . ' ' . $model->apeUsu;
                return "Usuario actualizado en $module. Nombre: {$usuarioNombre} (ID: $keyValue). " .
                       "Cambios: " . implode(', ', $changeDetails);

            case 'App\Models\PrestamoHerramienta':
                $changeDetails = [];
                foreach ($changes as $field => $newValue) {
                    $oldValue = $model->getOriginal($field);
                    
                    if ($field === 'estPre') {
                        $changeDetails[] = "Estado: {$this->getEstadoPrestamo($oldValue)} → {$this->getEstadoPrestamo($newValue)}";
                    } else {
                        $changeDetails[] = $this->formatFieldChange($field, $oldValue, $newValue);
                    }
                }
                $herramientaNombre = $model->herramienta ? $model->herramienta->nomHer : 'Herramienta ID: ' . $model->idHerPre;
                $usuarioNombre = $model->usuario ? $model->usuario->nomUsu . ' ' . $model->usuario->apeUsu : 'Usuario ID: ' . $model->idUsuPre;
                return "Préstamo actualizado en $module. Herramienta: {$herramientaNombre}, Usuario: {$usuarioNombre} (ID: $keyValue). " .
                       "Cambios: " . implode(', ', $changeDetails);

            case 'App\Models\Mantenimiento':
                $changeDetails = [];
                foreach ($changes as $field => $newValue) {
                    $oldValue = $model->getOriginal($field);
                    
                    if ($field === 'estMan') {
                        $changeDetails[] = "Estado: {$this->getEstadoMantenimiento($oldValue)} → {$this->getEstadoMantenimiento($newValue)}";
                    } else {
                        $changeDetails[] = $this->formatFieldChange($field, $oldValue, $newValue);
                    }
                }
                $herramientaNombre = $model->getNombreHerramientaCompleto();
                return "Mantenimiento actualizado en $module. Herramienta: {$herramientaNombre} (ID: $keyValue). " .
                       "Cambios: " . implode(', ', $changeDetails);

            default:
                // Descripción genérica para otros modelos
                $changeDetails = [];
                foreach ($changes as $field => $newValue) {
                    $oldValue = $model->getOriginal($field);
                    $changeDetails[] = $this->formatFieldChange($field, $oldValue, $newValue);
                }
                
                $details = implode(', ', array_slice($changeDetails, 0, 3));
                if (count($changeDetails) > 3) {
                    $details .= ', ...';
                }
                
                return "Actualización en $module. ID: $keyValue. Cambios: [$details]";
        }
    }

    /**
     * Genera una descripción detallada para la eliminación de registros
     */
    protected function generateDeletionDescription(Model $model, $module)
    {
        $keyValue = $model->getKey();
        
        // Descripciones específicas por modelo
        switch (get_class($model)) {
            case 'App\Models\Animal':
                return "Animal eliminado del $module. " .
                       "Nombre: {$model->nomAni} (ID: $keyValue). " .
                       "Especie: {$model->espAni}, Raza: {$model->razAni}, " .
                       "Sexo: {$model->sexAni}, Estado: {$model->estAni}";

            case 'App\Models\Factura':
                $clienteNombre = $model->cliente ? $model->cliente->nomCli : $model->nomCliFac;
                return "Factura eliminada del $module. " .
                       "Número: FAC-" . str_pad($keyValue, 6, '0', STR_PAD_LEFT) . ". " .
                       "Cliente: {$clienteNombre}, " .
                       "Monto: $" . number_format($model->totFac, 2) . ", " .
                       "Fecha: " . ($model->fecFac ? $model->fecFac->format('d/m/Y') : 'N/A') . ", " .
                       "Estado: {$model->estFac}";

            case 'App\Models\User':
                return "Usuario eliminado del $module. " .
                       "Nombre: {$model->nomUsu} {$model->apeUsu} (ID: $keyValue). " .
                       "Email: {$model->email}, Documento: {$model->tipDocUsu} {$model->numDocUsu}, " .
                       "Rol: " . ($model->rol ? $model->rol->nomRol : 'N/A');

            case 'App\Models\Cliente':
                return "Cliente eliminado del $module. " .
                       "Nombre: {$model->nomCli} (ID: $keyValue). " .
                       "Documento: {$model->tipDocCli} {$model->docCli}, " .
                       "Teléfono: {$model->telCli}, Tipo: {$model->tipCli}";

            case 'App\Models\Proveedor':
                return "Proveedor eliminado del $module. " .
                       "Nombre: {$model->nomProve} (ID: $keyValue). " .
                       "NIT: {$model->nitProve}, Contacto: {$model->conProve}, " .
                       "Tipo de suministro: {$model->tipSumProve}";

            case 'App\Models\CompraGasto':
                $tipo = $model->tipComGas === 'compra' ? 'Compra' : 'Gasto';
                return "{$tipo} eliminada del $module. " .
                       "Descripción: {$model->desComGas} (ID: $keyValue). " .
                       "Monto: $" . number_format($model->monComGas, 2) . ", " .
                       "Fecha: " . ($model->fecComGas ? $model->fecComGas->format('d/m/Y') : 'N/A') . ", " .
                       "Categoría: {$model->catComGas}";

            case 'App\Models\PrestamoHerramienta':
                $herramientaNombre = $model->herramienta ? $model->herramienta->nomHer : 'Herramienta ID: ' . $model->idHerPre;
                $usuarioNombre = $model->usuario ? $model->usuario->nomUsu . ' ' . $model->usuario->apeUsu : 'Usuario ID: ' . $model->idUsuPre;
                return "Préstamo eliminado del $module. " .
                       "Herramienta: {$herramientaNombre} (ID: $keyValue). " .
                       "Usuario: {$usuarioNombre}, " .
                       "Fecha préstamo: " . ($model->fecPre ? $model->fecPre->format('d/m/Y') : 'N/A') . ", " .
                       "Estado: {$model->estPre}";

            case 'App\Models\DatabaseBackup':
                return "Respaldo de base de datos eliminado del $module. " .
                       "Nombre: {$model->nomBac} (ID: $keyValue). " .
                       "Versión: {$model->verBac}, Tipo: {$model->tipBac}";

            default:
                // Descripción genérica para otros modelos
                $significantFields = $this->getSignificantFields($model);
                $details = implode(', ', array_slice($significantFields, 0, 3));
                
                if (count($significantFields) > 3) {
                    $details .= ', ...';
                }
                
                return "Registro eliminado del $module. ID: $keyValue. Datos eliminados: [$details]";
        }
    }

    /**
     * Obtiene campos significativos para mostrar
     */
    protected function getSignificantFields(Model $model)
    {
        $significantFields = [];
        $attributes = $model->getAttributes();
        
        // Excluir campos técnicos y timestamps
        $excludedFields = ['id', 'created_at', 'updated_at', 'deleted_at', 'password', 'remember_token'];
        
        foreach ($attributes as $field => $value) {
            if (!in_array($field, $excludedFields) && !is_array($value) && !is_object($value) && !empty($value)) {
                $formattedValue = is_null($value) ? 'N/A' : (string) $value;
                
                // Acortar valores muy largos
                if (strlen($formattedValue) > 50) {
                    $formattedValue = substr($formattedValue, 0, 50) . '...';
                }
                
                $fieldName = $this->getFieldDisplayName($field);
                $significantFields[] = "{$fieldName}: {$formattedValue}";
            }
        }
        
        return $significantFields;
    }

    /**
     * Formatea los cambios de campo de manera legible
     */
    protected function formatFieldChange($field, $oldValue, $newValue)
    {
        $fieldName = $this->getFieldDisplayName($field);
        
        // Formatear valores
        $displayOldValue = $this->formatValue($oldValue);
        $displayNewValue = $this->formatValue($newValue);
        
        return "{$fieldName}: {$displayOldValue} → {$displayNewValue}";
    }

    /**
     * Obtiene nombre de campo para mostrar
     */
    protected function getFieldDisplayName($field)
    {
        $fieldNames = [
            // Animal
            'nomAni' => 'Nombre', 'espAni' => 'Especie', 'razAni' => 'Raza', 
            'sexAni' => 'Sexo', 'fecNacAni' => 'Fecha nacimiento', 'pesAni' => 'Peso',
            'estAni' => 'Estado', 'estSaludAni' => 'Estado salud',
            
            // Herramienta
            'nomHer' => 'Nombre', 'catHer' => 'Categoría', 'estHer' => 'Estado',
            'ubiHer' => 'Ubicación', 'stockMinHer' => 'Stock mínimo',
            
            // Factura
            'nomCliFac' => 'Cliente', 'totFac' => 'Monto', 'fecFac' => 'Fecha',
            'estFac' => 'Estado', 'metPagFac' => 'Método pago',
            
            // User
            'nomUsu' => 'Nombre', 'apeUsu' => 'Apellido', 'email' => 'Email',
            'tipDocUsu' => 'Tipo documento', 'numDocUsu' => 'Número documento',
            'idRolUsu' => 'Rol',
            
            // Cliente
            'nomCli' => 'Nombre', 'tipDocCli' => 'Tipo documento', 'docCli' => 'Documento',
            'telCli' => 'Teléfono', 'emailCli' => 'Email', 'tipCli' => 'Tipo',
            'estCli' => 'Estado',
            
            // Proveedor
            'nomProve' => 'Nombre', 'nitProve' => 'NIT', 'conProve' => 'Contacto',
            'telProve' => 'Teléfono', 'emailProve' => 'Email', 'tipSumProve' => 'Tipo suministro',
            
            // CompraGasto
            'desComGas' => 'Descripción', 'monComGas' => 'Monto', 'fecComGas' => 'Fecha',
            'catComGas' => 'Categoría', 'provComGas' => 'Proveedor', 'metPagComGas' => 'Método pago',
            
            // Y muchos más campos según sea necesario...
        ];
        
        return $fieldNames[$field] ?? $field;
    }

    /**
     * Formatea valores para mostrar
     */
    protected function formatValue($value)
    {
        if (is_null($value)) {
            return 'N/A';
        }
        
        if (is_bool($value)) {
            return $value ? 'Sí' : 'No';
        }
        
        if (is_numeric($value)) {
            return (string) $value;
        }
        
        // Acortar textos largos
        if (strlen($value) > 30) {
            return substr($value, 0, 30) . '...';
        }
        
        return $value;
    }

    /**
     * Helper para obtener nombre de estado de factura
     */
    protected function getEstadoFactura($estado)
    {
        $estados = [
            'emitida' => 'Emitida',
            'pagada' => 'Pagada',
            'anulada' => 'Anulada',
            'pendiente' => 'Pendiente'
        ];
        
        return $estados[$estado] ?? $estado;
    }

    /**
     * Helper para obtener nombre de estado de préstamo
     */
    protected function getEstadoPrestamo($estado)
    {
        $estados = [
            'prestado' => 'Prestado',
            'devuelto' => 'Devuelto',
            'vencido' => 'Vencido'
        ];
        
        return $estados[$estado] ?? $estado;
    }

    /**
     * Helper para obtener nombre de estado de mantenimiento
     */
    protected function getEstadoMantenimiento($estado)
    {
        $estados = [
            'pendiente' => 'Pendiente',
            'en proceso' => 'En proceso',
            'completado' => 'Completado'
        ];
        
        return $estados[$estado] ?? $estado;
    }

    /**
     * Helper para obtener nombre de rol
     */
    protected function getNombreRol($rolId)
    {
        try {
            // Buscar el rol en la base de datos
            $rol = Rol::find($rolId);
            
            if ($rol) {
                return $rol->nomRol;
            }
            
            // Si no se encuentra el rol, intentar buscar por otros medios
            $rol = Rol::where('idRol', $rolId)->first();
            
            if ($rol) {
                return $rol->nomRol;
            }
            
            return "Rol ID: {$rolId} (no encontrado)";
            
        } catch (\Exception $e) {
            // En caso de error (tabla no existe, etc.), retornar el ID
            return "Rol ID: {$rolId}";
        }
    }
}