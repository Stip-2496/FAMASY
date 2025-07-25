@section('title', 'Reportes Contables')

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
                        <span class="text-gray-900">Reportes</span>
                    </nav>
                    <h1 class="text-3xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-chart-line mr-3 text-indigo-600"></i> 
                        Reportes Contables
                    </h1>
                    <p class="text-gray-600 mt-1">Análisis detallado y reportes financieros</p>
                </div>
                <div class="flex space-x-3">
                    <button onclick="generarReportePersonalizado()" 
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center transition duration-200">
                        <i class="fas fa-plus mr-2"></i> Reporte Personalizado
                    </button>
                    <button onclick="programarReporte()" 
                       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center transition duration-200">
                        <i class="fas fa-calendar mr-2"></i> Programar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Selector de Período -->
    <div class="flex flex-wrap -mx-3 mb-6">
        <div class="w-full px-3">
            <div class="bg-white shadow-lg rounded-lg p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Período</label>
                        <select id="periodoSelect" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="mes_actual">Mes Actual</option>
                            <option value="mes_anterior">Mes Anterior</option>
                            <option value="trimestre">Trimestre Actual</option>
                            <option value="ano">Año Actual</option>
                            <option value="personalizado">Período Personalizado</option>
                        </select>
                    </div>
                    <div id="fechaDesde" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Desde</label>
                        <input type="date" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div id="fechaHasta" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Hasta</label>
                        <input type="date" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div class="flex items-end">
                        <button onclick="actualizarReportes()" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition duration-200">
                            <i class="fas fa-sync mr-2"></i> Actualizar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reportes Rápidos -->
    <div class="flex flex-wrap -mx-3 mb-6">
        <div class="w-full md:w-1/4 px-3 mb-6">
            <div class="bg-white shadow-lg rounded-lg hover:shadow-xl transition duration-300 cursor-pointer" onclick="generarReporte('flujo-caja')">
                <div class="p-6 text-center">
                    <div class="bg-blue-100 p-4 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                        <i class="fas fa-chart-line text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Flujo de Caja</h3>
                    <p class="text-sm text-gray-600 mb-4">Análisis de ingresos y egresos</p>
                    <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition duration-200">
                        <i class="fas fa-download mr-1"></i> Generar
                    </button>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/4 px-3 mb-6">
            <div class="bg-white shadow-lg rounded-lg hover:shadow-xl transition duration-300 cursor-pointer" onclick="generarReporte('estado-resultados')">
                <div class="p-6 text-center">
                    <div class="bg-green-100 p-4 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                        <i class="fas fa-chart-pie text-green-600 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Estado de Resultados</h3>
                    <p class="text-sm text-gray-600 mb-4">Ganancias y pérdidas del período</p>
                    <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm transition duration-200">
                        <i class="fas fa-download mr-1"></i> Generar
                    </button>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/4 px-3 mb-6">
            <div class="bg-white shadow-lg rounded-lg hover:shadow-xl transition duration-300 cursor-pointer" onclick="generarReporte('balance-general')">
                <div class="p-6 text-center">
                    <div class="bg-purple-100 p-4 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                        <i class="fas fa-balance-scale text-purple-600 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Balance General</h3>
                    <p class="text-sm text-gray-600 mb-4">Activos, pasivos y patrimonio</p>
                    <button class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm transition duration-200">
                        <i class="fas fa-download mr-1"></i> Generar
                    </button>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/4 px-3 mb-6">
            <div class="bg-white shadow-lg rounded-lg hover:shadow-xl transition duration-300 cursor-pointer" onclick="generarReporte('cuentas-pendientes')">
                <div class="p-6 text-center">
                    <div class="bg-yellow-100 p-4 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                        <i class="fas fa-clock text-yellow-600 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Cuentas Pendientes</h3>
                    <p class="text-sm text-gray-600 mb-4">Por cobrar y por pagar</p>
                    <button class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg text-sm transition duration-200">
                        <i class="fas fa-download mr-1"></i> Generar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos de Análisis -->
    <div class="flex flex-wrap -mx-3 mb-6">
        <!-- Gráfico de Tendencias -->
        <div class="w-full xl:w-2/3 px-3 mb-6">
            <div class="bg-white shadow-lg rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <div>
                            <h6 class="text-lg font-semibold text-gray-800">Tendencias Financieras</h6>
                            <p class="text-sm text-gray-600">Evolución de ingresos y gastos</p>
                        </div>
                        <div class="flex space-x-2">
                            <select class="border border-gray-300 rounded-lg px-3 py-1 text-sm">
                                <option>Últimos 12 meses</option>
                                <option>Últimos 6 meses</option>
                                <option>Últimos 3 meses</option>
                            </select>
                            <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 rounded-lg text-sm transition duration-200">
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="relative h-80">
                        <canvas id="tendenciasChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPIs Principales -->
        <div class="w-full xl:w-1/3 px-3 mb-6">
            <div class="bg-white shadow-lg rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <h6 class="text-lg font-semibold text-gray-800">Indicadores Clave</h6>
                    <p class="text-sm text-gray-600">KPIs del período seleccionado</p>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                        <div>
                            <p class="text-sm font-medium text-blue-600">Margen de Ganancia</p>
                            <p class="text-2xl font-bold text-blue-800">0%</p>
                        </div>
                        <div class="bg-blue-200 p-2 rounded-full">
                            <i class="fas fa-percentage text-blue-600"></i>
                        </div>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                        <div>
                            <p class="text-sm font-medium text-green-600">ROI</p>
                            <p class="text-2xl font-bold text-green-800">0%</p>
                        </div>
                        <div class="bg-green-200 p-2 rounded-full">
                            <i class="fas fa-arrow-up text-green-600"></i>
                        </div>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg">
                        <div>
                            <p class="text-sm font-medium text-yellow-600">Liquidez</p>
                            <p class="text-2xl font-bold text-yellow-800">0%</p>
                        </div>
                        <div class="bg-yellow-200 p-2 rounded-full">
                            <i class="fas fa-tint text-yellow-600"></i>
                        </div>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                        <div>
                            <p class="text-sm font-medium text-red-600">Días Promedio Cobro</p>
                            <p class="text-2xl font-bold text-red-800">0</p>
                        </div>
                        <div class="bg-red-200 p-2 rounded-full">
                            <i class="fas fa-calendar text-red-600"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reportes Detallados -->
    <div class="flex flex-wrap -mx-3 mb-6">
        <div class="w-full px-3">
            <div class="bg-white shadow-lg rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <div>
                            <h6 class="text-lg font-semibold text-gray-800">Reportes Detallados</h6>
                            <p class="text-sm text-gray-600">Análisis profundo por categorías</p>
                        </div>
                        <div class="flex space-x-2">
                            <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm transition duration-200">
                                <i class="fas fa-file-excel mr-1"></i> Exportar Todo
                            </button>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Reporte por Categorías -->
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition duration-200">
                            <div class="flex items-center mb-3">
                                <div class="bg-blue-100 p-2 rounded-lg mr-3">
                                    <i class="fas fa-tags text-blue-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-800">Por Categorías</h4>
                                    <p class="text-sm text-gray-600">Gastos desglosados</p>
                                </div>
                            </div>
                            <div class="space-y-2 mb-4">
                                <div class="flex justify-between text-sm">
                                    <span>Oficina:</span>
                                    <span class="font-medium">$0.00</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span>Marketing:</span>
                                    <span class="font-medium">$0.00</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span>Servicios:</span>
                                    <span class="font-medium">$0.00</span>
                                </div>
                            </div>
                            <button onclick="generarReporte('categorias')" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded text-sm transition duration-200">
                                <i class="fas fa-download mr-1"></i> Generar Reporte
                            </button>
                        </div>

                        <!-- Reporte de Proveedores -->
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition duration-200">
                            <div class="flex items-center mb-3">
                                <div class="bg-green-100 p-2 rounded-lg mr-3">
                                    <i class="fas fa-users text-green-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-800">Por Proveedores</h4>
                                    <p class="text-sm text-gray-600">Análisis de proveedores</p>
                                </div>
                            </div>
                            <div class="space-y-2 mb-4">
                                <div class="flex justify-between text-sm">
                                    <span>Total Proveedores:</span>
                                    <span class="font-medium">0</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span>Gasto Promedio:</span>
                                    <span class="font-medium">$0.00</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span>Más Frecuente:</span>
                                    <span class="font-medium">-</span>
                                </div>
                            </div>
                            <button onclick="generarReporte('proveedores')" class="w-full bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded text-sm transition duration-200">
                                <i class="fas fa-download mr-1"></i> Generar Reporte
                            </button>
                        </div>

                        <!-- Reporte de Proyecciones -->
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition duration-200">
                            <div class="flex items-center mb-3">
                                <div class="bg-purple-100 p-2 rounded-lg mr-3">
                                    <i class="fas fa-crystal-ball text-purple-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-800">Proyecciones</h4>
                                    <p class="text-sm text-gray-600">Análisis predictivo</p>
                                </div>
                            </div>
                            <div class="space-y-2 mb-4">
                                <div class="flex justify-between text-sm">
                                    <span>Próximo Mes:</span>
                                    <span class="font-medium">$0.00</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span>Tendencia:</span>
                                    <span class="font-medium text-green-600">Estable</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span>Confianza:</span>
                                    <span class="font-medium">-</span>
                                </div>
                            </div>
                            <button onclick="generarReporte('proyecciones')" class="w-full bg-purple-600 hover:bg-purple-700 text-white px-3 py-2 rounded text-sm transition duration-200">
                                <i class="fas fa-download mr-1"></i> Generar Reporte
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Historial de Reportes -->
    <div class="flex flex-wrap -mx-3">
        <div class="w-full px-3">
            <div class="bg-white shadow-lg rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <div>
                            <h6 class="text-lg font-semibold text-gray-800">Historial de Reportes</h6>
                            <p class="text-sm text-gray-600">Reportes generados recientemente</p>
                        </div>
                        <button class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm transition duration-200">
                            <i class="fas fa-trash mr-1"></i> Limpiar Historial
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reporte</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Período</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Generación</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                    <div class="py-8">
                                        <i class="fas fa-chart-line text-4xl text-gray-300 mb-4"></i>
                                        <p class="text-lg font-medium mb-2">No hay reportes generados</p>
                                        <p class="text-sm text-gray-400 mb-4">Los reportes que generes aparecerán aquí</p>
                                        <button onclick="generarReporte('flujo-caja')" 
                                                class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg transition duration-200">
                                            <i class="fas fa-plus mr-2"></i> Generar Primer Reporte
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

<script>
// Funciones para reportes
function generarReporte(tipo) {
    // Mostrar indicador de carga
    const loadingToast = mostrarToast('Generando reporte...', 'info');
    
    // Simular generación de reporte
    setTimeout(() => {
        loadingToast.remove();
        mostrarToast(`Reporte de ${tipo} generado exitosamente`, 'success');
        
        // Aquí iría la lógica real para generar el reporte
        // Por ejemplo, redirigir a una URL de descarga
        console.log(`Generando reporte: ${tipo}`);
    }, 2000);
}

function generarReportePersonalizado() {
    // Aquí se abriría un modal para configurar el reporte personalizado
    mostrarToast('Función de reportes personalizados próximamente', 'info');
}

function programarReporte() {
    // Aquí se abriría un modal para programar reportes automáticos
    mostrarToast('Función de programación de reportes próximamente', 'info');
}

function actualizarReportes() {
    mostrarToast('Reportes actualizados', 'success');
    // Aquí iría la lógica para actualizar los datos
}

// Manejar cambio de período
document.getElementById('periodoSelect').addEventListener('change', function() {
    const fechaDesde = document.getElementById('fechaDesde');
    const fechaHasta = document.getElementById('fechaHasta');
    
    if (this.value === 'personalizado') {
        fechaDesde.classList.remove('hidden');
        fechaHasta.classList.remove('hidden');
    } else {
        fechaDesde.classList.add('hidden');
        fechaHasta.classList.add('hidden');
    }
});

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

// Inicializar gráfico de tendencias (simulado)
document.addEventListener('DOMContentLoaded', function() {
    // Aquí se inicializaría Chart.js para el gráfico de tendencias
    console.log('Inicializando gráficos de reportes...');
});
</script>
</x-auth-layout>