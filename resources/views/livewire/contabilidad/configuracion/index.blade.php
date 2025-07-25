@section('title', 'Configuración Contable')

<x-auth-layout>
<div class="w-full px-6 py-6 mx-auto">
    <!-- Header -->
    <div class="flex flex-wrap -mx-3 mb-6">
        <div class="w-full px-3">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                <div class="mb-4 md:mb-0">
                    <nav class="text-sm text-gray-600 mb-2">
                        <a href="{{ route('contabilidad.index') }}" class="hover:text-blue-600">Dashboard</a>
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
                    <button onclick="guardarConfiguracion()" 
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center transition duration-200">
                        <i class="fas fa-save mr-2"></i> Guardar Cambios
                    </button>
                    <button onclick="resetearConfiguracion()" 
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
                            <select class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="COP">🇨🇴 Peso Colombiano (COP)</option>
                                <option value="USD">🇺🇸 Dólar Americano (USD)</option>
                                <option value="EUR">🇪🇺 Euro (EUR)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Formato de Fecha</label>
                            <select class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="DD/MM/YYYY">DD/MM/YYYY</option>
                                <option value="MM/DD/YYYY">MM/DD/YYYY</option>
                                <option value="YYYY-MM-DD">YYYY-MM-DD</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Período Fiscal</label>
                            <select class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="enero">Enero - Diciembre</option>
                                <option value="abril">Abril - Marzo</option>
                                <option value="julio">Julio - Junio</option>
                            </select>
                        </div>
                        <div class="flex items-center space-x-3">
                            <input type="checkbox" id="notificaciones" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="notificaciones" class="text-sm text-gray-700">Activar notificaciones automáticas</label>
                        </div>
                        <div class="flex items-center space-x-3">
                            <input type="checkbox" id="backup_auto" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="backup_auto" class="text-sm text-gray-700">Backup automático diario</label>
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
                            <input type="text" value="FAMASY" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">NIT / RUT</label>
                            <input type="text" placeholder="123456789-0" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Dirección</label>
                            <input type="text" placeholder="Calle 123 #45-67" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Ciudad</label>
                                <input type="text" value="Medellín" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Teléfono</label>
                                <input type="text" placeholder="+57 300 123 4567" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email" placeholder="contabilidad@famasy.com" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
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
                            <button onclick="openModal('nuevaCategoriaModal')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center transition duration-200">
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
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Oficina</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Gasto</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-4 h-4 rounded-full bg-blue-500 mr-2"></div>
                                            <span class="text-sm text-gray-500">#3B82F6</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Activa</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <button class="text-blue-600 hover:text-blue-900">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="text-red-600 hover:text-red-900">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <!-- Más categorías predefinidas -->
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Marketing</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Gasto</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-4 h-4 rounded-full bg-green-500 mr-2"></div>
                                            <span class="text-sm text-gray-500">#10B981</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Activa</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <button class="text-blue-600 hover:text-blue-900">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="text-red-600 hover:text-red-900">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Ventas</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Ingreso</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-4 h-4 rounded-full bg-purple-500 mr-2"></div>
                                            <span class="text-sm text-gray-500">#8B5CF6</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Activa</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <button class="text-blue-600 hover:text-blue-900">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="text-red-600 hover:text-red-900">
                                                <i class="fas fa-trash"></i>
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
                            <input type="number" value="19" min="0" max="100" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Retención en la Fuente (%)</label>
                            <input type="number" value="3.5" step="0.1" min="0" max="100" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">ICA (%)</label>
                            <input type="number" value="0.414" step="0.001" min="0" max="100" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Régimen Tributario</label>
                            <select class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option>Régimen Simplificado</option>
                                <option>Régimen Común</option>
                                <option>Gran Contribuyente</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Backup -->
    <div id="tab-backup" class="tab-content hidden">
        <div class="flex flex-wrap -mx-3 mb-6">
            <div class="w-full md:w-1/2 px-3 mb-6">
                <div class="bg-white shadow-lg rounded-lg p-6">
                    <h6 class="text-lg font-semibold text-gray-800 mb-4">Backup y Restauración</h6>
                    <div class="space-y-4">
                        <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                            <div class="flex items-center">
                                <i class="fas fa-check-circle text-green-600 text-xl mr-3"></i>
                                <div>
                                    <p class="text-sm font-medium text-green-800">Último backup realizado</p>
                                    <p class="text-xs text-green-600">Hoy, 10:30 AM - Automático</p>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <button onclick="crearBackup()" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center justify-center">
                                <i class="fas fa-download mr-2"></i> Crear Backup Manual
                            </button>
                            <button class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center justify-center">
                                <i class="fas fa-upload mr-2"></i> Restaurar desde Archivo
                            </button>
                            <button class="w-full bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg flex items-center justify-center">
                                <i class="fas fa-cloud mr-2"></i> Configurar Backup en la Nube
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-full md:w-1/2 px-3 mb-6">
                <div class="bg-white shadow-lg rounded-lg p-6">
                    <h6 class="text-lg font-semibold text-gray-800 mb-4">Historial de Backups</h6>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                            <div>
                                <p class="text-sm font-medium">backup_2025-07-23_10-30.sql</p>
                                <p class="text-xs text-gray-500">23/07/2025 - 2.3 MB</p>
                            </div>
                            <div class="flex space-x-2">
                                <button class="text-blue-600 hover:text-blue-800" title="Descargar">
                                    <i class="fas fa-download"></i>
                                </button>
                                <button class="text-red-600 hover:text-red-800" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                            <div>
                                <p class="text-sm font-medium">backup_2025-07-22_10-30.sql</p>
                                <p class="text-xs text-gray-500">22/07/2025 - 2.1 MB</p>
                            </div>
                            <div class="flex space-x-2">
                                <button class="text-blue-600 hover:text-blue-800" title="Descargar">
                                    <i class="fas fa-download"></i>
                                </button>
                                <button class="text-red-600 hover:text-red-800" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nueva Categoría -->
<div id="nuevaCategoriaModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 lg:w-1/3 shadow-lg rounded-lg bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Nueva Categoría</h3>
                <button onclick="closeModal('nuevaCategoriaModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nombre de la Categoría</label>
                    <input type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" 
                           placeholder="Ej: Tecnología">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipo</label>
                    <select class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="gasto">Gasto</option>
                        <option value="ingreso">Ingreso</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Color</label>
                    <div class="flex space-x-2">
                        <input type="color" value="#3B82F6" class="w-12 h-10 border border-gray-300 rounded cursor-pointer">
                        <input type="text" value="#3B82F6" class="flex-1 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
                    <textarea rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" 
                              placeholder="Descripción opcional de la categoría"></textarea>
                </div>
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="closeModal('nuevaCategoriaModal')" 
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition duration-200">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200">
                        <i class="fas fa-save mr-2"></i> Guardar Categoría
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Funciones para tabs
function cambiarTab(tabName) {
    // Ocultar todos los tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.add('hidden');
    });
    
    // Remover clase activa de todos los botones
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('border-blue-500', 'text-blue-600');
        button.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Mostrar tab seleccionado
    document.getElementById(`tab-${tabName}`).classList.remove('hidden');
    
    // Activar botón seleccionado
    const activeButton = document.querySelector(`[data-tab="${tabName}"]`);
    activeButton.classList.remove('border-transparent', 'text-gray-500');
    activeButton.classList.add('border-blue-500', 'text-blue-600');
}

// Funciones para modales
function openModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

// Funciones de configuración
function guardarConfiguracion() {
    mostrarToast('Configuración guardada exitosamente', 'success');
}

function resetearConfiguracion() {
    if (confirm('¿Estás seguro de que quieres restablecer la configuración a los valores por defecto?')) {
        mostrarToast('Configuración restablecida', 'success');
    }
}

function crearBackup() {
    mostrarToast('Creando backup...', 'info');
    setTimeout(() => {
        mostrarToast('Backup creado exitosamente', 'success');
    }, 2000);
}

// Función para mostrar notificaciones
function mostrarToast(mensaje, tipo = 'info') {
    const toast = document.createElement('div');
    const colores = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        info: 'bg-blue-500',
        warning: 'bg-yellow-500'
    };
    
    toast.className = `fixed top-4 right-4 ${colores[tipo]} text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-transform duration-300`;
    toast.innerHTML = `
        <div class="flex items-center">
            <i class="fas fa-${tipo === 'success' ? 'check' : tipo === 'error' ? 'times' : 'info'} mr-2"></i>
            <span>${mensaje}</span>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Remover después de 3 segundos
    setTimeout(() => {
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
    
    return toast;
}

// Cerrar modal al hacer clic fuera de él
window.onclick = function(event) {
    const modals = document.querySelectorAll('[id$="Modal"]');
    modals.forEach(modal => {
        if (event.target === modal) {
            modal.classList.add('hidden');
        }
    });
}
</script>
</x-auth-layout>