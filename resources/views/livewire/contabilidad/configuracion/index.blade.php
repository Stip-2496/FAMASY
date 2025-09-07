<?php


use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Log;

new #[Layout('layouts.auth')] class extends Component {
    
    // Propiedades para configuración general
    public $moneda = 'COP';
    public $formato_fecha = 'DD/MM/YYYY';
    public $periodo_fiscal = 'enero';
    public $notificaciones = true;
    public $backup_auto = false;
    
    // Propiedades para información de empresa
    public $nombre_empresa = 'FAMASY';
    public $nit_empresa = '';
    public $direccion_empresa = '';
    public $ciudad_empresa = 'Medellín';
    public $telefono_empresa = '';
    public $email_empresa = '';
    
    // Propiedades para impuestos
    public $iva_general = 19;
    public $retencion_fuente = 3.5;
    public $ica = 0.414;
    public $regimen_tributario = 'Régimen Simplificado';
    
    // Propiedades para categorías
    public $categorias = [];
    public $modalCategoria = false;
    public $nueva_categoria = [
        'nombre' => '',
        'tipo' => 'gasto',
        'color' => '#3B82F6',
        'descripcion' => ''
    ];
    
    public function mount()
    {
        $this->cargarConfiguracion();
        $this->cargarCategorias();
    }
    
    public function cargarConfiguracion()
    {
        try {
            // Aquí cargarías la configuración desde la base de datos
            // Por ahora usamos valores por defecto
            
            Log::info('Configuración cargada correctamente');
        } catch (\Exception $e) {
            Log::error('Error cargando configuración: ' . $e->getMessage());
        }
    }
    
    public function cargarCategorias()
    {
        try {
            // Cargar categorías desde la base de datos
            // Por ahora simulamos algunas categorías
            $this->categorias = [
                [
                    'id' => 1,
                    'nombre' => 'Oficina',
                    'tipo' => 'gasto',
                    'color' => '#3B82F6',
                    'estado' => 'activa'
                ],
                [
                    'id' => 2,
                    'nombre' => 'Marketing',
                    'tipo' => 'gasto',
                    'color' => '#10B981',
                    'estado' => 'activa'
                ],
                [
                    'id' => 3,
                    'nombre' => 'Ventas',
                    'tipo' => 'ingreso',
                    'color' => '#8B5CF6',
                    'estado' => 'activa'
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error cargando categorías: ' . $e->getMessage());
            $this->categorias = [];
        }
    }
    
    public function guardarConfiguracion()
    {
        try {
            // Validar datos básicos
            $this->validate([
                'nombre_empresa' => 'required|string|max:255',
                'moneda' => 'required|in:COP,USD,EUR',
                'iva_general' => 'required|numeric|min:0|max:100',
                'retencion_fuente' => 'required|numeric|min:0|max:100'
            ]);
            
            // Aquí guardarías en la base de datos
            Log::info('Configuración guardada', [
                'empresa' => $this->nombre_empresa,
                'moneda' => $this->moneda,
                'iva' => $this->iva_general
            ]);
            
            session()->flash('success', 'Configuración guardada exitosamente');
            
        } catch (\Exception $e) {
            Log::error('Error guardando configuración: ' . $e->getMessage());
            session()->flash('error', 'Error al guardar la configuración');
        }
    }
    
    public function resetearConfiguracion()
    {
        try {
            // Restablecer valores por defecto
            $this->moneda = 'COP';
            $this->formato_fecha = 'DD/MM/YYYY';
            $this->periodo_fiscal = 'enero';
            $this->notificaciones = true;
            $this->backup_auto = false;
            $this->nombre_empresa = 'FAMASY';
            $this->ciudad_empresa = 'Medellín';
            $this->iva_general = 19;
            $this->retencion_fuente = 3.5;
            $this->ica = 0.414;
            $this->regimen_tributario = 'Régimen Simplificado';
            
            session()->flash('success', 'Configuración restablecida a valores por defecto');
            
        } catch (\Exception $e) {
            Log::error('Error restableciendo configuración: ' . $e->getMessage());
            session()->flash('error', 'Error al restablecer la configuración');
        }
    }
    
    public function abrirModalCategoria()
    {
        $this->modalCategoria = true;
        $this->nueva_categoria = [
            'nombre' => '',
            'tipo' => 'gasto',
            'color' => '#3B82F6',
            'descripcion' => ''
        ];
    }
    
    public function cerrarModalCategoria()
    {
        $this->modalCategoria = false;
        $this->resetValidation();
    }
    
    public function guardarCategoria()
    {
        try {
            $this->validate([
                'nueva_categoria.nombre' => 'required|string|max:255',
                'nueva_categoria.tipo' => 'required|in:gasto,ingreso',
                'nueva_categoria.color' => 'required|string'
            ]);
            
            // Aquí guardarías en la base de datos
            $nuevaCategoria = [
                'id' => count($this->categorias) + 1,
                'nombre' => $this->nueva_categoria['nombre'],
                'tipo' => $this->nueva_categoria['tipo'],
                'color' => $this->nueva_categoria['color'],
                'estado' => 'activa'
            ];
            
            $this->categorias[] = $nuevaCategoria;
            
            $this->cerrarModalCategoria();
            session()->flash('success', 'Categoría creada exitosamente');
            
        } catch (\Exception $e) {
            Log::error('Error guardando categoría: ' . $e->getMessage());
            session()->flash('error', 'Error al guardar la categoría');
        }
    }
    
    public function eliminarCategoria($categoriaId)
    {
        try {
            $this->categorias = array_filter($this->categorias, function($categoria) use ($categoriaId) {
                return $categoria['id'] != $categoriaId;
            });
            
            session()->flash('success', 'Categoría eliminada exitosamente');
            
        } catch (\Exception $e) {
            Log::error('Error eliminando categoría: ' . $e->getMessage());
            session()->flash('error', 'Error al eliminar la categoría');
        }
    }
    
    public function crearBackup()
    {
        try {
            // Aquí implementarías la lógica de backup
            Log::info('Creando backup manual...');
            
            // Simular proceso de backup
            session()->flash('info', 'Creando backup...');
            
            // Después de un tiempo simular éxito
            $this->dispatch('backup-creado');
            
        } catch (\Exception $e) {
            Log::error('Error creando backup: ' . $e->getMessage());
            session()->flash('error', 'Error al crear el backup');
        }
    }
    
    public function verificarEstado()
    {
        try {
            Log::info('Estado actual de la configuración:', [
                'empresa' => $this->nombre_empresa,
                'moneda' => $this->moneda,
                'categorias' => count($this->categorias)
            ]);
            
            session()->flash('info', 'Estado verificado. Ver logs para detalles.');
            
        } catch (\Exception $e) {
            Log::error('Error verificando estado: ' . $e->getMessage());
            session()->flash('error', 'Error al verificar el estado');
        }
    }
}; ?>

@section('title', 'Configuración Contable')

<!-- ✅ TODO EL CONTENIDO DEBE ESTAR DENTRO DE UN SOLO DIV CONTENEDOR -->
<div>
    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
    @endif

    @if (session()->has('info'))
        <div class="mb-4 p-4 bg-blue-100 border border-blue-400 text-blue-700 rounded-lg">
            <i class="fas fa-info-circle mr-2"></i>{{ session('info') }}
        </div>
    @endif

    <div class="w-full px-6 py-6 mx-auto">
        <!-- Header -->
        <div class="flex flex-wrap -mx-3 mb-6">
            <div class="w-full px-3">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                    <div class="mb-4 md:mb-0">
                        <nav class="text-sm text-gray-600 mb-2">
                            <a href="{{ route('contabilidad.index') }}" wire:navigate class="hover:text-blue-600">Dashboard</a>
                            <span class="mx-2">/</span>
                            <span class="text-gray-900">Configuración</span>
                        </nav>
                        <h1 class="text-3xl font-bold text-gray-800 flex items-center">
                            <i class="fas fa-cog mr-3 text-gray-600"></i> 
                            Configuración Contable
                        </h1>
                        <p class="text-gray-600 mt-1">Personalización y ajustes del módulo</p>
                    </div>
                    <div class="flex space-x-3">
                        <button wire:click="verificarEstado" 
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center transition duration-200">
                            <i class="fas fa-search mr-2"></i> Verificar Estado
                        </button>
                        <button wire:click="guardarConfiguracion" 
                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center transition duration-200">
                            <i class="fas fa-save mr-2"></i> Guardar Cambios
                        </button>
                        <button wire:click="resetearConfiguracion" 
                           class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center transition duration-200">
                            <i class="fas fa-undo mr-2"></i> Restablecer
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs de Configuración -->
        <div class="flex flex-wrap -mx-3 mb-6">
            <div class="w-full px-3">
                <div class="bg-white shadow-lg rounded-lg">
                    <div class="border-b border-gray-200">
                        <nav class="flex space-x-8 px-6">
                            <button onclick="cambiarTab('general')" 
                                    class="tab-button py-4 px-1 border-b-2 font-medium text-sm transition duration-200 border-blue-500 text-blue-600" 
                                    data-tab="general">
                                <i class="fas fa-cogs mr-2"></i>General
                            </button>
                            <button onclick="cambiarTab('categorias')" 
                                    class="tab-button py-4 px-1 border-b-2 font-medium text-sm transition duration-200 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300" 
                                    data-tab="categorias">
                                <i class="fas fa-tags mr-2"></i>Categorías
                            </button>
                            <button onclick="cambiarTab('cuentas')" 
                                    class="tab-button py-4 px-1 border-b-2 font-medium text-sm transition duration-200 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300" 
                                    data-tab="cuentas">
                                <i class="fas fa-university mr-2"></i>Cuentas
                            </button>
                            <button onclick="cambiarTab('impuestos')" 
                                    class="tab-button py-4 px-1 border-b-2 font-medium text-sm transition duration-200 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300" 
                                    data-tab="impuestos">
                                <i class="fas fa-percent mr-2"></i>Impuestos
                            </button>
                            <button onclick="cambiarTab('backup')" 
                                    class="tab-button py-4 px-1 border-b-2 font-medium text-sm transition duration-200 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300" 
                                    data-tab="backup">
                                <i class="fas fa-database mr-2"></i>Backup
                            </button>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab General -->
        <div id="tab-general" class="tab-content">
            <div class="flex flex-wrap -mx-3 mb-6">
                <div class="w-full md:w-1/2 px-3 mb-6">
                    <div class="bg-white shadow-lg rounded-lg">
                        <div class="p-6 border-b border-gray-200">
                            <h6 class="text-lg font-semibold text-gray-800">Configuración General</h6>
                            <p class="text-sm text-gray-600">Ajustes básicos del módulo</p>
                        </div>
                        <div class="p-6 space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Moneda Principal</label>
                                <select wire:model="moneda" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="COP">🇨🇴 Peso Colombiano (COP)</option>
                                    <option value="USD">🇺🇸 Dólar Americano (USD)</option>
                                    <option value="EUR">🇪🇺 Euro (EUR)</option>
                                </select>
                                @error('moneda') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Formato de Fecha</label>
                                <select wire:model="formato_fecha" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="DD/MM/YYYY">DD/MM/YYYY</option>
                                    <option value="MM/DD/YYYY">MM/DD/YYYY</option>
                                    <option value="YYYY-MM-DD">YYYY-MM-DD</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Período Fiscal</label>
                                <select wire:model="periodo_fiscal" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="enero">Enero - Diciembre</option>
                                    <option value="abril">Abril - Marzo</option>
                                    <option value="julio">Julio - Junio</option>
                                </select>
                            </div>
                            
                            <div class="flex items-center space-x-3">
                                <input type="checkbox" wire:model="notificaciones" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label class="text-sm text-gray-700">Activar notificaciones automáticas</label>
                            </div>
                            
                            <div class="flex items-center space-x-3">
                                <input type="checkbox" wire:model="backup_auto" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label class="text-sm text-gray-700">Backup automático diario</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="w-full md:w-1/2 px-3 mb-6">
                    <div class="bg-white shadow-lg rounded-lg">
                        <div class="p-6 border-b border-gray-200">
                            <h6 class="text-lg font-semibold text-gray-800">Información de la Empresa</h6>
                            <p class="text-sm text-gray-600">Datos para reportes y facturas</p>
                        </div>
                        <div class="p-6 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nombre de la Empresa</label>
                                <input type="text" wire:model="nombre_empresa" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @error('nombre_empresa') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">NIT / RUT</label>
                                <input type="text" wire:model="nit_empresa" placeholder="123456789-0" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Dirección</label>
                                <input type="text" wire:model="direccion_empresa" placeholder="Calle 123 #45-67" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Ciudad</label>
                                    <input type="text" wire:model="ciudad_empresa" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Teléfono</label>
                                    <input type="text" wire:model="telefono_empresa" placeholder="+57 300 123 4567" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                <input type="email" wire:model="email_empresa" placeholder="contabilidad@famasy.com" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab Categorías -->
        <div id="tab-categorias" class="tab-content hidden">
            <div class="flex flex-wrap -mx-3 mb-6">
                <div class="w-full px-3">
                    <div class="bg-white shadow-lg rounded-lg">
                        <div class="p-6 border-b border-gray-200">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h6 class="text-lg font-semibold text-gray-800">Gestión de Categorías</h6>
                                    <p class="text-sm text-gray-600">Administrar categorías de ingresos y gastos</p>
                                </div>
                                <button wire:click="abrirModalCategoria" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center transition duration-200">
                                    <i class="fas fa-plus mr-2"></i> Nueva Categoría
                                </button>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Color</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($categorias as $categoria)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $categoria['nombre'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ ucfirst($categoria['tipo']) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="w-4 h-4 rounded-full mr-2" style="background-color: {{ $categoria['color'] }}"></div>
                                                <span class="text-sm text-gray-500">{{ $categoria['color'] }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">{{ ucfirst($categoria['estado']) }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <button class="text-blue-600 hover:text-blue-900">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button wire:click="eliminarCategoria({{ $categoria['id'] }})" 
                                                        wire:confirm="¿Estás seguro de eliminar esta categoría?"
                                                        class="text-red-600 hover:text-red-900">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                            <div class="py-8">
                                                <i class="fas fa-tags text-4xl text-gray-300 mb-4"></i>
                                                <p class="text-lg font-medium mb-2">No hay categorías</p>
                                                <p class="text-sm text-gray-400 mb-4">Crea tu primera categoría</p>
                                                <button wire:click="abrirModalCategoria" 
                                                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition duration-200">
                                                    <i class="fas fa-plus mr-2"></i> Nueva Categoría
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab Cuentas -->
        <div id="tab-cuentas" class="tab-content hidden">
            <div class="flex flex-wrap -mx-3 mb-6">
                <div class="w-full px-3">
                    <div class="bg-white shadow-lg rounded-lg p-6">
                        <h6 class="text-lg font-semibold text-gray-800 mb-4">Cuentas Bancarias</h6>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-4">
                                    <h4 class="font-semibold">Cuenta Principal</h4>
                                    <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">Activa</span>
                                </div>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span>Banco:</span>
                                        <span class="font-medium">Bancolombia</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Número:</span>
                                        <span class="font-medium">****-****-1234</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Tipo:</span>
                                        <span class="font-medium">Ahorros</span>
                                    </div>
                                </div>
                                <div class="mt-4 flex space-x-2">
                                    <button class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm">Editar</button>
                                    <button class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm">Eliminar</button>
                                </div>
                            </div>
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 flex items-center justify-center">
                                <div class="text-center">
                                    <i class="fas fa-plus text-3xl text-gray-400 mb-2"></i>
                                    <p class="text-gray-500 mb-2">Agregar Nueva Cuenta</p>
                                    <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Agregar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab Impuestos -->
        <div id="tab-impuestos" class="tab-content hidden">
            <div class="flex flex-wrap -mx-3 mb-6">
                <div class="w-full px-3">
                    <div class="bg-white shadow-lg rounded-lg p-6">
                        <h6 class="text-lg font-semibold text-gray-800 mb-4">Configuración de Impuestos</h6>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">IVA General (%)</label>
                                <input type="number" wire:model="iva_general" min="0" max="100" step="0.1" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @error('iva_general') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Retención en la Fuente (%)</label>
                                <input type="number" wire:model="retencion_fuente" min="0" max="100" step="0.1" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @error('retencion_fuente') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">ICA (%)</label>
                                <input type="number" wire:model="ica" min="0" max="100" step="0.001" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Régimen Tributario</label>
                                <select wire:model="regimen_tributario" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="Régimen Simplificado">Régimen Simplificado</option>
                                    <option value="Régimen Común">Régimen Común</option>
                                    <option value="Gran Contribuyente">Gran Contribuyente</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <div class="flex items-start">
                                <i class="fas fa-exclamation-triangle text-yellow-600 mr-3 mt-1"></i>
                                <div>
                                    <h4 class="text-sm font-semibold text-yellow-800">Importante</h4>
                                    <p class="text-sm text-yellow-700 mt-1">Los cambios en la configuración de impuestos afectarán todos los cálculos futuros. Revisa cuidadosamente antes de guardar.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab Backup -->
        <div id="tab-backup" class="tab-content hidden">
            <div class="flex flex-wrap -mx-3 mb-6">
                <div class="w-full px-3">
                    <div class="bg-white shadow-lg rounded-lg p-6">
                        <h6 class="text-lg font-semibold text-gray-800 mb-4">Gestión de Respaldos</h6>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Backup Manual -->
                            <div class="border border-gray-200 rounded-lg p-6">
                                <div class="text-center">
                                    <i class="fas fa-download text-4xl text-blue-600 mb-4"></i>
                                    <h4 class="text-lg font-semibold text-gray-800 mb-2">Backup Manual</h4>
                                    <p class="text-sm text-gray-600 mb-6">Crea un respaldo completo de todos tus datos contables</p>
                                    <button wire:click="crearBackup" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-lg flex items-center justify-center transition duration-200">
                                        <i class="fas fa-cloud-download-alt mr-2"></i> Crear Backup Ahora
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Configuración Automática -->
                            <div class="border border-gray-200 rounded-lg p-6">
                                <h4 class="text-lg font-semibold text-gray-800 mb-4">Backup Automático</h4>
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-700">Backup automático diario</span>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" wire:model="backup_auto" class="sr-only peer">
                                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                                        </label>
                                    </div>
                                    
                                    <div class="text-sm text-gray-600">
                                        <p><i class="fas fa-clock mr-2"></i>Hora programada: 2:00 AM</p>
                                        <p><i class="fas fa-calendar mr-2"></i>Último backup: Hace 2 días</p>
                                        <p><i class="fas fa-hdd mr-2"></i>Espacio usado: 45.2 MB</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Historial de Backups -->
                        <div class="mt-8">
                            <h4 class="text-lg font-semibold text-gray-800 mb-4">Historial de Respaldos</h4>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tamaño</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">02/08/2025 02:00</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Automático</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">45.2 MB</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Completado</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex space-x-2">
                                                    <button class="text-blue-600 hover:text-blue-900">
                                                        <i class="fas fa-download"></i> Descargar
                                                    </button>
                                                    <button class="text-red-600 hover:text-red-900">
                                                        <i class="fas fa-trash"></i> Eliminar
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">01/08/2025 14:30</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Manual</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">44.8 MB</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Completado</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex space-x-2">
                                                    <button class="text-blue-600 hover:text-blue-900">
                                                        <i class="fas fa-download"></i> Descargar
                                                    </button>
                                                    <button class="text-red-600 hover:text-red-900">
                                                        <i class="fas fa-trash"></i> Eliminar
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Nueva Categoría -->
    @if($modalCategoria)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Nueva Categoría</h3>
                    <button wire:click="cerrarModalCategoria" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <form wire:submit.prevent="guardarCategoria" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nombre</label>
                        <input type="text" wire:model="nueva_categoria.nombre" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ej: Gastos de oficina">
                        @error('nueva_categoria.nombre') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipo</label>
                        <select wire:model="nueva_categoria.tipo" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="gasto">Gasto</option>
                            <option value="ingreso">Ingreso</option>
                        </select>
                        @error('nueva_categoria.tipo') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Color</label>
                        <div class="flex items-center space-x-2">
                            <input type="color" wire:model="nueva_categoria.color" class="h-10 w-20 border border-gray-300 rounded cursor-pointer">
                            <input type="text" wire:model="nueva_categoria.color" class="flex-1 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        @error('nueva_categoria.color') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Descripción (Opcional)</label>
                        <textarea wire:model="nueva_categoria.descripcion" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Descripción de la categoría..."></textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" wire:click="cerrarModalCategoria" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg transition duration-200">
                            Cancelar
                        </button>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-200">
                            <i class="fas fa-save mr-2"></i> Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- JavaScript para manejo de tabs -->
    <script>
        function cambiarTab(tabName) {
            // Ocultar todos los contenidos de tabs
            document.querySelectorAll('.tab-content').forEach(function(el) {
                el.classList.add('hidden');
            });
            
            // Mostrar el tab seleccionado
            document.getElementById('tab-' + tabName).classList.remove('hidden');
            
            // Actualizar estilos de botones
            document.querySelectorAll('.tab-button').forEach(function(button) {
                button.classList.remove('border-blue-500', 'text-blue-600');
                button.classList.add('border-transparent', 'text-gray-500');
            });
            
            // Activar el botón seleccionado
            const activeButton = document.querySelector('[data-tab="' + tabName + '"]');
            activeButton.classList.remove('border-transparent', 'text-gray-500');
            activeButton.classList.add('border-blue-500', 'text-blue-600');
        }
        
        // Listener para eventos de Livewire
        document.addEventListener('livewire:navigated', () => {
            // Asegurar que el tab general esté activo por defecto
            cambiarTab('general');
        });
        
        // Listener para backup creado
        Livewire.on('backup-creado', () => {
            setTimeout(() => {
                Livewire.dispatch('$refresh');
                // Mostrar mensaje de éxito
                const successDiv = document.createElement('div');
                successDiv.className = 'mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg';
                successDiv.innerHTML = '<i class="fas fa-check-circle mr-2"></i>Backup creado exitosamente';
                document.querySelector('.w-full.px-6.py-6.mx-auto').insertBefore(successDiv, document.querySelector('.w-full.px-6.py-6.mx-auto').firstChild);
                
                // Remover mensaje después de 5 segundos
                setTimeout(() => {
                    successDiv.remove();
                }, 5000);
            }, 2000);
        });
    </script>
</div>