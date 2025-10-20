<?php
// app/Livewire/Pecuario/Dashboard.php
namespace App\Livewire\Pecuario;

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Models\Animal;
use App\Models\HistorialMedico;
use App\Models\ProduccionAnimal;

new #[Layout('layouts.auth')] class extends Component {
    public $animalStats;
    public $medicalStats;
    public $productionStats;

    // Paleta de colores mejorada
    private array $colors = [
        '#4BC0C0', '#FF6384', '#FFCE56', '#36A2EB', '#8A2BE2', 
        '#FF9F40', '#9966FF', '#7ACB5E', '#FF6B6B', '#6A5ACD',
        '#20B2AA', '#FFA07A', '#9370DB', '#32CD32', '#BA55D3'
    ];

    public function mount()
    {
        $this->loadChartData();
    }

    public function loadChartData(): void
    {
        // --- Estadísticas de Animales ---
        $animalStatus = Animal::selectRaw('estAni, count(*) as count')
                               ->groupBy('estAni')
                               ->get();
        $totalAnimals = $animalStatus->sum('count');

        $this->animalStats = [
            'status' => $animalStatus->map(function ($item) use ($totalAnimals) {
                return [
                    'label' => $item->estAni,
                    'count' => $item->count,
                    'percentage' => $totalAnimals > 0 ? round(($item->count / $totalAnimals) * 100, 1) : 0,
                    'color' => $this->getColor($item->estAni),
                ];
            })->toArray(),
            'species' => Animal::selectRaw('espAni, count(*) as count')
                               ->groupBy('espAni')
                               ->get()->map(function($item) {
                                   return [
                                       'label' => $item->espAni,
                                       'count' => $item->count,
                                       'color' => $this->getColor($item->espAni),
                                   ];
                               })->toArray(),
            'sex' => Animal::selectRaw('sexAni, count(*) as count')
                           ->groupBy('sexAni')
                           ->get()->map(function($item) {
                               return [
                                   'label' => $item->sexAni,
                                   'count' => $item->count,
                                   'color' => $this->getColor($item->sexAni),
                               ];
                           })->toArray(),
            'total' => $totalAnimals
        ];

        // --- Estadísticas de Historial Médico ---
        $this->medicalStats = [
            'types' => HistorialMedico::selectRaw('tipHisMed, count(*) as count')
                                      ->groupBy('tipHisMed')
                                      ->get()->map(function($item) {
                                          return [
                                              'label' => $item->tipHisMed,
                                              'count' => $item->count,
                                              'color' => $this->getColor($item->tipHisMed),
                                          ];
                                      })->toArray(),
        ];

        // --- Estadísticas de Producción Animal ---
        $this->productionStats = [
            'types' => ProduccionAnimal::selectRaw('tipProAni, SUM(canProAni) as total_cantidad')
                                          ->groupBy('tipProAni')
                                          ->get()->map(function($item) {
                                              return [
                                                  'label' => $item->tipProAni,
                                                  'total_cantidad' => $item->total_cantidad,
                                                  'color' => $this->getColor($item->tipProAni),
                                              ];
                                          })->toArray(),
        ];

        // Emitir evento cuando los datos estén cargados
        $this->dispatch('datosCargados');
    }

    public function getColor($key): string
    {
        // Generar un índice basado en el hash del string para consistencia
        $index = abs(crc32($key)) % count($this->colors);
        return $this->colors[$index];
    }
}; ?>

@section('title', 'Dashboard Pecuario')

<div class="min-h-full py-4">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-4 text-center">
            <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent mb-2">Módulo Pecuario</h1>
            <p class="text-sm text-gray-600 max-w-2xl mx-auto leading-relaxed">Panel de control integral para la gestión de animales, producción y salud</p>
        </div>

        <!-- Tarjetas principales -->
        <div class="mt-6">
            <div class="mb-6 grid gap-y-6 gap-x-4 md:grid-cols-2 xl:grid-cols-3">
                <!-- Card 1: Total animales -->
                <div>
                    <div class="relative flex flex-col bg-clip-border rounded-xl bg-white text-gray-700 shadow-md mb-3">
                        <a href="{{ route('pecuario.animales.index') }}" wire:navigate>
                            <div class="bg-clip-border mx-3 rounded-xl overflow-hidden bg-gradient-to-tr from-blue-600 to-blue-400 text-white shadow-blue-500/40 shadow-lg absolute -mt-3 grid h-12 w-12 place-items-center">
                                <i class="fas fa-paw text-base"></i>
                            </div>
                        </a>
                        <div class="p-3 text-right">
                            <p class="block antialiased font-sans text-xs leading-normal font-normal text-blue-gray-600">Animales Registrados</p>
                            <h4 class="block antialiased tracking-normal font-sans text-xl font-semibold leading-snug text-blue-gray-900">{{ number_format($animalStats['total'] ?? 0) }}</h4>
                        </div>
                        <div class="border-t border-blue-gray-50 p-3">
                            <p class="block antialiased font-sans text-sm leading-relaxed font-normal text-blue-gray-600">
                                <strong class="text-green-500">+{{ count($animalStats['status']) > 0 ? round((array_sum(array_column($animalStats['status'], 'count')) / max($animalStats['total'], 1)) * 100) : 0 }}%</strong>
                            </p>
                        </div>
                    </div>
                    <!-- Pie Chart Container -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3 h-[160px] flex items-center justify-center">
                        <canvas id="animalStatusChart"></canvas>
                    </div>
                </div>

                <!-- Card 2: Registros médicos -->
                <div>
                    <div class="relative flex flex-col bg-clip-border rounded-xl bg-white text-gray-700 shadow-md mb-3">
                        <a href="{{ route('pecuario.salud-peso.index') }}" wire:navigate>
                            <div class="bg-clip-border mx-3 rounded-xl overflow-hidden bg-gradient-to-tr from-green-600 to-green-400 text-white shadow-green-500/40 shadow-lg absolute -mt-3 grid h-12 w-12 place-items-center">
                                <i class="fas fa-heartbeat text-base"></i>
                            </div>
                        </a>
                        <div class="p-3 text-right">
                            <p class="block antialiased font-sans text-xs leading-normal font-normal text-blue-gray-600">Registros Médicos</p>
                            <h4 class="block antialiased tracking-normal font-sans text-xl font-semibold leading-snug text-blue-gray-900">{{ number_format(array_sum(array_column($medicalStats['types'], 'count')) ?? 0) }}</h4>
                        </div>
                        <div class="border-t border-blue-gray-50 p-3">
                            <p class="block antialiased font-sans text-sm leading-relaxed font-normal text-blue-gray-600">
                                <strong class="text-green-500">+{{ count($medicalStats['types']) > 0 ? round((array_sum(array_column($medicalStats['types'], 'count')) / max(array_sum(array_column($medicalStats['types'], 'count')), 1)) * 100) : 0 }}%</strong>
                            </p>
                        </div>
                    </div>
                    <!-- Bar Chart Container -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3 h-[160px] flex items-center justify-center">
                        <canvas id="medicalRecordsChart"></canvas>
                    </div>
                </div>

                <!-- Card 3: Producción total -->
                <div>
                    <div class="relative flex flex-col bg-clip-border rounded-xl bg-white text-gray-700 shadow-md mb-3">
                        <a href="{{ route('pecuario.produccion.index') }}" wire:navigate>
                            <div class="bg-clip-border mx-3 rounded-xl overflow-hidden bg-gradient-to-tr from-amber-600 to-amber-400 text-white shadow-amber-500/40 shadow-lg absolute -mt-3 grid h-12 w-12 place-items-center">
                                <i class="fas fa-chart-line text-base"></i>
                            </div>
                        </a>
                        <div class="p-3 text-right">
                            <p class="block antialiased font-sans text-xs leading-normal font-normal text-blue-gray-600">Producción Total</p>
                            <h4 class="block antialiased tracking-normal font-sans text-xl font-semibold leading-snug text-blue-gray-900">{{ number_format(array_sum(array_column($productionStats['types'], 'total_cantidad')) ?? 0) }} unidades</h4>
                        </div>
                        <div class="border-t border-blue-gray-50 p-3">
                            <p class="block antialiased font-sans text-sm leading-relaxed font-normal text-blue-gray-600">
                                <strong class="text-green-500">+{{ count($productionStats['types']) > 0 ? round((array_sum(array_column($productionStats['types'], 'total_cantidad')) / max(array_sum(array_column($productionStats['types'], 'total_cantidad')), 1)) * 100) : 0 }}%</strong>
                            </p>
                        </div>
                    </div>
                    <!-- Bar Chart Container -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3 h-[160px] flex items-center justify-center">
                        <canvas id="productionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cards de módulos de gestión -->
        <div class="mt-6 grid gap-y-6 gap-x-4 md:grid-cols-2 xl:grid-cols-3">
            <!-- Módulo Animales -->
            <div class="relative flex flex-col bg-clip-border rounded-xl bg-white text-gray-700 shadow-md">
                <a href="{{ route('pecuario.animales.index') }}" wire:navigate>
                    <div class="bg-clip-border mx-3 rounded-xl overflow-hidden bg-gradient-to-tr from-blue-600 to-blue-400 text-white shadow-blue-500/40 shadow-lg absolute -mt-3 grid h-12 w-12 place-items-center">
                        <i class="fas fa-paw text-base"></i>
                    </div>
                </a>
                <div class="p-3 text-right">
                    <p class="block antialiased font-sans text-xs leading-normal font-normal text-blue-gray-600">Gestión de Animales</p>
                    <h4 class="block antialiased tracking-normal font-sans text-xl font-semibold leading-snug text-blue-gray-900">Ganado</h4>
                </div>
                <div class="border-t border-blue-gray-50 p-3">
                    <p class="text-sm text-gray-600">Administra el ganado.</p>
                    <div class="flex gap-2 mt-2">
                        <a href="{{ route('pecuario.animales.index') }}" wire:navigate class="flex-1 bg-indigo-50 text-indigo-600 font-medium py-1 px-2 rounded-lg text-xs text-center hover:bg-indigo-100">Ver Listado</a>
                        <a href="{{ route('pecuario.animales.create') }}" wire:navigate class="flex-1 bg-indigo-50 text-indigo-600 font-medium py-1 px-2 rounded-lg text-xs text-center hover:bg-indigo-100">Registrar</a>
                    </div>
                </div>
            </div>

            <!-- Módulo Salud -->
            <div class="relative flex flex-col bg-clip-border rounded-xl bg-white text-gray-700 shadow-md">
                <a href="{{ route('pecuario.salud-peso.index') }}" wire:navigate>
                    <div class="bg-clip-border mx-3 rounded-xl overflow-hidden bg-gradient-to-tr from-green-600 to-green-400 text-white shadow-green-500/40 shadow-lg absolute -mt-3 grid h-12 w-12 place-items-center">
                        <i class="fas fa-heartbeat text-base"></i>
                    </div>
                </a>
                <div class="p-3 text-right">
                    <p class="block antialiased font-sans text-xs leading-normal font-normal text-blue-gray-600">Gestión de Salud</p>
                    <h4 class="block antialiased tracking-normal font-sans text-xl font-semibold leading-snug text-blue-gray-900">Historial</h4>
                </div>
                <div class="border-t border-blue-gray-50 p-3">
                    <p class="text-sm text-gray-600">Control veterinario completo.</p>
                    <div class="flex gap-2 mt-2">
                        <a href="{{ route('pecuario.salud-peso.index') }}" wire:navigate class="flex-1 bg-indigo-50 text-indigo-600 font-medium py-1 px-2 rounded-lg text-xs text-center hover:bg-indigo-100">Historial</a>
                        <a href="{{ route('pecuario.salud-peso.create') }}" wire:navigate class="flex-1 bg-indigo-50 text-indigo-600 font-medium py-1 px-2 rounded-lg text-xs text-center hover:bg-indigo-100">Registrar</a>
                    </div>
                </div>
            </div>

            <!-- Módulo Producción -->
            <div class="relative flex flex-col bg-clip-border rounded-xl bg-white text-gray-700 shadow-md">
                <a href="{{ route('pecuario.produccion.index') }}" wire:navigate>
                    <div class="bg-clip-border mx-3 rounded-xl overflow-hidden bg-gradient-to-tr from-amber-600 to-amber-400 text-white shadow-amber-500/40 shadow-lg absolute -mt-3 grid h-12 w-12 place-items-center">
                        <i class="fas fa-chart-line text-base"></i>
                    </div>
                </a>
                <div class="p-3 text-right">
                    <p class="block antialiased font-sans text-xs leading-normal font-normal text-blue-gray-600">Gestión de Producción</p>
                    <h4 class="block antialiased tracking-normal font-sans text-xl font-semibold leading-snug text-blue-gray-900">Estadísticas</h4>
                </div>
                <div class="border-t border-blue-gray-50 p-3">
                    <p class="text-sm text-gray-600">Registra y analiza los datos productivos.</p>
                    <div class="flex gap-2 mt-2">
                        <a href="{{ route('pecuario.produccion.index') }}" wire:navigate class="flex-1 bg-indigo-50 text-indigo-600 font-medium py-1 px-2 rounded-lg text-xs text-center hover:bg-indigo-100">Estadísticas</a>
                        <a href="{{ route('pecuario.produccion.create') }}" wire:navigate class="flex-1 bg-indigo-50 text-indigo-600 font-medium py-1 px-2 rounded-lg text-xs text-center hover:bg-indigo-100">Registrar</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Bandera para prevenir múltiples inicializaciones
let graficosInicializados = false;
let chartsInstances = {
    animalStatusChart: null,
    medicalRecordsChart: null,
    productionChart: null
};

// Función para verificar si Chart.js está cargado
function chartJsEstaCargado() {
    return typeof Chart !== 'undefined' && typeof Chart === 'function';
}

// Función para resetear el estado de inicialización
function resetearInicializacionGraficos() {
    graficosInicializados = false;
    
    // Destruir gráficos existentes
    if (chartsInstances.animalStatusChart) {
        chartsInstances.animalStatusChart.destroy();
        chartsInstances.animalStatusChart = null;
    }
    if (chartsInstances.medicalRecordsChart) {
        chartsInstances.medicalRecordsChart.destroy();
        chartsInstances.medicalRecordsChart = null;
    }
    if (chartsInstances.productionChart) {
        chartsInstances.productionChart.destroy();
        chartsInstances.productionChart = null;
    }
    
    console.log('Estado de gráficos reseteado para re-inicialización');
}

// Función para inicializar todos los gráficos
function inicializarGraficosPecuario() {
    // Prevenir múltiples ejecuciones si ya están inicializados
    if (graficosInicializados) {
        console.log('Gráficos ya inicializados, omitiendo...');
        return;
    }
    
    // Verificar que Chart.js esté cargado
    if (!chartJsEstaCargado()) {
        console.log('Chart.js no está cargado, reintentando en 100ms...');
        setTimeout(inicializarGraficosPecuario, 100);
        return;
    }
    
    console.log('Inicializando gráficos del dashboard pecuario...');
    
    // Verificar que los elementos canvas existan en el DOM
    const ctxAnimalStatus = document.getElementById('animalStatusChart');
    const ctxMedicalRecords = document.getElementById('medicalRecordsChart');
    const ctxProduction = document.getElementById('productionChart');
    
    if (!ctxAnimalStatus || !ctxMedicalRecords || !ctxProduction) {
        console.log('Elementos canvas no encontrados en el DOM, reintentando en 100ms...');
        setTimeout(inicializarGraficosPecuario, 100);
        return;
    }
    
    // Verificar que los datos estén disponibles
    const animalStats = @json($animalStats);
    const medicalStats = @json($medicalStats);
    const productionStats = @json($productionStats);
    
    if (!animalStats || !medicalStats || !productionStats || 
        !animalStats.status || !medicalStats.types || !productionStats.types) {
        console.log('Datos no disponibles, reintentando en 100ms...');
        setTimeout(inicializarGraficosPecuario, 100);
        return;
    }

    // Destruir gráficos existentes si los hay
    if (chartsInstances.animalStatusChart) chartsInstances.animalStatusChart.destroy();
    if (chartsInstances.medicalRecordsChart) chartsInstances.medicalRecordsChart.destroy();
    if (chartsInstances.productionChart) chartsInstances.productionChart.destroy();

    try {
        // Gráfico de estado de animales (Pie Chart)
        chartsInstances.animalStatusChart = new Chart(ctxAnimalStatus, {
            type: 'pie',
            data: {
                labels: animalStats.status.map(item => item.label),
                datasets: [{
                    data: animalStats.status.map(item => item.count),
                    backgroundColor: animalStats.status.map(item => item.color),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const value = context.raw;
                                const percentage = Math.round((value / total) * 100);
                                return `${context.label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });

        // Gráfico de registros médicos (Bar Chart)
        chartsInstances.medicalRecordsChart = new Chart(ctxMedicalRecords, {
            type: 'bar',
            data: {
                labels: medicalStats.types.map(item => item.label),
                datasets: [{
                    label: 'Registros Médicos',
                    data: medicalStats.types.map(item => item.count),
                    backgroundColor: medicalStats.types.map(item => item.color),
                    borderColor: medicalStats.types.map(item => item.color),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Gráfico de producción (Bar Chart)
        chartsInstances.productionChart = new Chart(ctxProduction, {
            type: 'bar',
            data: {
                labels: productionStats.types.map(item => item.label),
                datasets: [{
                    label: 'Producción Total (unidades)',
                    data: productionStats.types.map(item => item.total_cantidad),
                    backgroundColor: productionStats.types.map(item => item.color),
                    borderColor: productionStats.types.map(item => item.color),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
        
        graficosInicializados = true;
        console.log('Todos los gráficos inicializados correctamente');
        
    } catch (error) {
        console.error('Error inicializando gráficos:', error);
        console.log('Reintentando en 200ms...');
        setTimeout(inicializarGraficosPecuario, 200);
    }
}

// SOLO mantener estos dos event listeners:

// Cuando Livewire termina de cargar (primera carga)
document.addEventListener('livewire:load', function() {
    console.log('Livewire cargado - Programando inicialización de gráficos');
    setTimeout(inicializarGraficosPecuario, 800);
});

// Cuando los datos específicamente se han cargado (evento personalizado)
Livewire.on('datosCargados', () => {
    console.log('Datos cargados - Programando inicialización de gráficos');
    setTimeout(inicializarGraficosPecuario, 50);
});

// NUEVO: Escuchar cuando el componente es destruido (navegación fuera del dashboard)
Livewire.on('destroyed', () => {
    console.log('Componente destruido - Reseteando gráficos');
    resetearInicializacionGraficos();
});

// NUEVO: Escuchar cuando Livewire actualiza el DOM (navegación SPA)
document.addEventListener('livewire:navigated', function() {
    console.log('Livewire navigated - Verificando si estamos en dashboard pecuario');
    
    // Verificar si el dashboard está en el URL actual
    if (window.location.href.includes('/pecuario') || 
        document.getElementById('animalStatusChart')) {
        console.log('Dashboard pecuario detectado - Reseteando gráficos para re-inicialización');
        resetearInicializacionGraficos();
        setTimeout(inicializarGraficosPecuario, 300);
    }
});

// También intentar inicializar cuando el DOM esté listo como fallback
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM cargado - Intentando inicializar gráficos');
        setTimeout(inicializarGraficosPecuario, 500);
    });
} else {
    console.log('DOM ya listo - Intentando inicializar gráficos');
    setTimeout(inicializarGraficosPecuario, 500);
}
</script>