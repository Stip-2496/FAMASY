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
                ];
            })->toArray(),
            'species' => Animal::selectRaw('espAni, count(*) as count')
                               ->groupBy('espAni')
                               ->get()->toArray(),
            'sex' => Animal::selectRaw('sexAni, count(*) as count')
                           ->groupBy('sexAni')
                           ->get()->toArray(),
            'total' => $totalAnimals // Agregamos el total aquí
        ];

        // --- Estadísticas de Historial Médico ---
        $this->medicalStats = [
            'types' => HistorialMedico::selectRaw('tipHisMed, count(*) as count')
                                      ->groupBy('tipHisMed')
                                      ->get()->toArray(),
        ];

        // --- Estadísticas de Producción Animal ---
        $this->productionStats = [
            'types' => ProduccionAnimal::selectRaw('tipProAni, SUM(canProAni) as total_cantidad')
                                          ->groupBy('tipProAni')
                                          ->get()->toArray(),
        ];
    }

    public function getColor(int $index): string
    {
        return $this->colors[$index % count($this->colors)];
    }
}; ?>

@section('title', 'Dashboard Pecuario')

<div class="container mx-auto px-4 py-8">
    <!-- Encabezado mejorado -->
    <div class="text-center mb-10">
        <h1 class="text-4xl font-bold text-gray-800 mb-3">Módulo Pecuario</h1>
        <p class="text-lg text-gray-600 max-w-2xl mx-auto">Panel de control integral para la gestión de animales, producción y salud</p>
    </div>

    <!-- Tarjetas resumen -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <!-- Total animales -->
        <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl shadow-sm border border-blue-100 p-6 flex items-center">
            <div class="bg-blue-100 p-4 rounded-lg mr-4">
                <i class="fas fa-paw text-blue-600 text-2xl"></i>
            </div>
            <div>
                <h3 class="text-gray-500 text-sm font-medium">Animales Registrados</h3>
                <p class="text-2xl font-bold text-gray-800">
                    {{ $animalStats['total'] ?? 0 }}
                </p>
            </div>
        </div>

        <!-- Total registros médicos -->
        <div class="bg-gradient-to-r from-green-50 to-green-100 rounded-xl shadow-sm border border-green-100 p-6 flex items-center">
            <div class="bg-green-100 p-4 rounded-lg mr-4">
                <i class="fas fa-notes-medical text-green-600 text-2xl"></i>
            </div>
            <div>
                <h3 class="text-gray-500 text-sm font-medium">Registros Médicos</h3>
                <p class="text-2xl font-bold text-gray-800">
                    {{ array_sum(array_column($medicalStats['types'], 'count')) ?? 0 }}
                </p>
            </div>
        </div>

        <!-- Total producción -->
        <div class="bg-gradient-to-r from-amber-50 to-amber-100 rounded-xl shadow-sm border border-amber-100 p-6 flex items-center">
            <div class="bg-amber-100 p-4 rounded-lg mr-4">
                <i class="fas fa-chart-bar text-amber-600 text-2xl"></i>
            </div>
            <div>
                <h3 class="text-gray-500 text-sm font-medium">Producción Total</h3>
                <p class="text-2xl font-bold text-gray-800">
                    {{ array_sum(array_column($productionStats['types'], 'total_cantidad')) ?? 0 }} unidades
                </p>
            </div>
        </div>
    </div>

    <!-- Sección de gráficos -->
    <div class="mb-12">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 pb-2 border-b border-gray-200">Análisis y Estadísticas</h2>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Gráfico de estado de animales -->
            <div class="bg-white rounded-xl shadow-md border border-gray-100 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Estado de los Animales</h3>
                    <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full">
                        <i class="fas fa-info-circle mr-1"></i> Distribución
                    </span>
                </div>
                <div class="flex flex-col md:flex-row items-center">
                    <div class="w-48 h-48 relative mb-4 md:mb-0 md:mr-6">
                        @php
                            $currentAngle = 0;
                            $pieCss = [];
                            $hasData = !empty($animalStats['status']) && array_sum(array_column($animalStats['status'], 'count')) > 0;

                            foreach ($animalStats['status'] as $index => $item) {
                                $percentage = $item['percentage'];
                                $angle = ($percentage / 100) * 360;
                                $color = $this->getColor($index);
                                
                                $pieCss[] = "{$color} {$currentAngle}deg " . ($currentAngle + $angle) . "deg";
                                $currentAngle += $angle;
                            }
                        @endphp
                        @if($hasData)
                            <div class="w-full h-full rounded-full shadow" style="background: conic-gradient({{ implode(', ', $pieCss) }});"></div>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="text-xl font-bold text-gray-700">{{ $animalStats['total'] ?? 0 }}</span>
                            </div>
                        @else
                            <div class="w-full h-full rounded-full bg-gray-100 flex items-center justify-center">
                                <span class="text-gray-400 text-sm">Sin datos</span>
                            </div>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="space-y-3">
                            @forelse($animalStats['status'] as $index => $item)
                                <div class="flex items-center">
                                    <span class="w-3 h-3 rounded-full mr-2 flex-shrink-0" style="background-color: {{ $this->getColor($index) }};"></span>
                                    <span class="text-sm font-medium text-gray-700 truncate">{{ ucfirst($item['label']) }}</span>
                                    <span class="ml-auto text-sm font-semibold text-gray-700">{{ $item['count'] }} ({{ $item['percentage'] }}%)</span>
                                </div>
                            @empty
                                <p class="text-center text-gray-500 text-sm">No hay datos disponibles</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráfico de animales por especie -->
            <div class="bg-white rounded-xl shadow-md border border-gray-100 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Animales por Especie</h3>
                    <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">
                        <i class="fas fa-chart-bar mr-1"></i> Comparativo
                    </span>
                </div>
                <div class="h-64">
                    @php
                        $maxCount = !empty($animalStats['species']) ? max(array_column($animalStats['species'], 'count')) : 0;
                    @endphp
                    @if(!empty($animalStats['species']))
                        <div class="space-y-4">
                            @foreach($animalStats['species'] as $index => $item)
                                <div>
                                    <div class="flex justify-between text-xs text-gray-500 mb-1">
                                        <span>{{ ucfirst($item['espAni']) }}</span>
                                        <span>{{ $item['count'] }}</span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-2.5">
                                        <div class="h-2.5 rounded-full" 
                                             style="width: {{ $maxCount > 0 ? ($item['count'] / $maxCount) * 100 : 0 }}%; 
                                                    background-color: {{ $this->getColor($index) }};"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="h-full flex items-center justify-center">
                            <p class="text-gray-400 text-sm">No hay datos de especies disponibles</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Gráfico de producción -->
            <div class="bg-white rounded-xl shadow-md border border-gray-100 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Producción por Tipo</h3>
                    <span class="text-xs bg-amber-100 text-amber-800 px-2 py-1 rounded-full">
                        <i class="fas fa-boxes mr-1"></i> Volumen
                    </span>
                </div>
                <div class="h-64">
                    @php
                        $maxProduction = !empty($productionStats['types']) ? max(array_column($productionStats['types'], 'total_cantidad')) : 0;
                    @endphp
                    @if(!empty($productionStats['types']))
                        <div class="space-y-4">
                            @foreach($productionStats['types'] as $index => $item)
                                <div>
                                    <div class="flex justify-between text-xs text-gray-500 mb-1">
                                        <span>{{ ucfirst($item['tipProAni']) }}</span>
                                        <span>{{ $item['total_cantidad'] }} unidades</span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-2.5">
                                        <div class="h-2.5 rounded-full" 
                                             style="width: {{ $maxProduction > 0 ? ($item['total_cantidad'] / $maxProduction) * 100 : 0 }}%; 
                                                    background-color: {{ $this->getColor($index + 4) }};"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="h-full flex items-center justify-center">
                            <p class="text-gray-400 text-sm">No hay datos de producción disponibles</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Gráfico de registros médicos -->
            <div class="bg-white rounded-xl shadow-md border border-gray-100 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Registros Médicos</h3>
                    <span class="text-xs bg-purple-100 text-purple-800 px-2 py-1 rounded-full">
                        <i class="fas fa-file-medical mr-1"></i> Tipología
                    </span>
                </div>
                <div class="h-64 overflow-y-auto pr-2">
                    @php
                        $maxMedical = !empty($medicalStats['types']) ? max(array_column($medicalStats['types'], 'count')) : 0;
                    @endphp
                    @if(!empty($medicalStats['types']))
                        <div class="space-y-4">
                            @foreach($medicalStats['types'] as $index => $item)
                                <div>
                                    <div class="flex justify-between text-xs text-gray-500 mb-1">
                                        <span>{{ ucfirst($item['tipHisMed']) }}</span>
                                        <span>{{ $item['count'] }} registros</span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-2.5">
                                        <div class="h-2.5 rounded-full" 
                                             style="width: {{ $maxMedical > 0 ? ($item['count'] / $maxMedical) * 100 : 0 }}%; 
                                                    background-color: {{ $this->getColor($index + 2) }};"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="h-full flex items-center justify-center">
                            <p class="text-gray-400 text-sm">No hay registros médicos disponibles</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Módulos de gestión -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 pb-2 border-b border-gray-200">Módulos de Gestión</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
           <!-- Módulo Animales -->
<div class="bg-gradient-to-br from-green-600 to-green-700 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
    <div class="p-6 text-white relative overflow-hidden">
        <div class="absolute -right-6 -top-6 opacity-10">
            <i class="fas fa-cow text-6xl"></i>
        </div>
        <div class="flex items-center justify-between mb-4 relative z-10">
            <h3 class="text-xl font-bold">Gestión de Animales</h3>
            <i class="fas fa-paw text-3xl opacity-80"></i>
        </div>
        <p class="text-green-100 mb-6 text-sm leading-relaxed relative z-10">
            Administra el inventario completo de animales, con información detallada de cada individuo en tu explotación.
        </p>
        <div class="flex flex-col sm:flex-row gap-3 relative z-10">
            <a href="{{ route('pecuario.animales.index') }}" wire:navigate 
               class="flex-1 bg-white/20 hover:bg-white/30 text-white font-medium py-2 px-4 rounded-lg text-center text-sm transition-all duration-200 flex items-center justify-center">
                <i class="fas fa-list mr-2"></i> Ver Listado
            </a>
            <a href="{{ route('pecuario.animales.create') }}" wire:navigate 
               class="flex-1 bg-white text-green-700 hover:bg-gray-100 font-medium py-2 px-4 rounded-lg text-center text-sm transition-all duration-200 flex items-center justify-center">
                <i class="fas fa-plus mr-2"></i> Registrar
            </a>
        </div>
    </div>
</div>

<!-- Módulo Producción -->
<div class="bg-gradient-to-br from-green-600 to-green-700 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
    <div class="p-6 text-white relative overflow-hidden">
        <div class="absolute -right-6 -top-6 opacity-10">
            <i class="fas fa-egg text-6xl"></i>
        </div>
        <div class="flex items-center justify-between mb-4 relative z-10">
            <h3 class="text-xl font-bold">Gestión de Producción</h3>
            <i class="fas fa-chart-line text-3xl opacity-80"></i>
        </div>
        <p class="text-green-100 mb-6 text-sm leading-relaxed relative z-10">
            Registra y analiza los datos productivos de leche, huevos, carne u otros productos de tu explotación.
        </p>
        <div class="flex flex-col sm:flex-row gap-3 relative z-10">
            <a href="{{ route('pecuario.produccion.index') }}" wire:navigate 
               class="flex-1 bg-white/20 hover:bg-white/30 text-white font-medium py-2 px-4 rounded-lg text-center text-sm transition-all duration-200 flex items-center justify-center">
                <i class="fas fa-chart-pie mr-2"></i> Estadísticas
            </a>
            <a href="{{ route('pecuario.produccion.create') }}" wire:navigate 
               class="flex-1 bg-white text-green-700 hover:bg-gray-100 font-medium py-2 px-4 rounded-lg text-center text-sm transition-all duration-200 flex items-center justify-center">
                <i class="fas fa-plus mr-2"></i> Registrar
            </a>
        </div>
    </div>
</div>

<!-- Módulo Salud -->
<div class="bg-gradient-to-br from-green-600 to-green-700 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
    <div class="p-6 text-white relative overflow-hidden">
        <div class="absolute -right-6 -top-6 opacity-10">
            <i class="fas fa-syringe text-6xl"></i>
        </div>
        <div class="flex items-center justify-between mb-4 relative z-10">
            <h3 class="text-xl font-bold">Gestión de Salud</h3>
            <i class="fas fa-heartbeat text-3xl opacity-80"></i>
        </div>
        <p class="text-green-100 mb-6 text-sm leading-relaxed relative z-10">
            Control veterinario completo: vacunas, tratamientos, historial médico y seguimiento del estado de salud.
        </p>
        <div class="flex flex-col sm:flex-row gap-3 relative z-10">
            <a href="{{ route('pecuario.salud-peso.index') }}" wire:navigate 
               class="flex-1 bg-white/20 hover:bg-white/30 text-white font-medium py-2 px-4 rounded-lg text-center text-sm transition-all duration-200 flex items-center justify-center">
                <i class="fas fa-clipboard-list mr-2"></i> Historial
            </a>
            <a href="{{ route('pecuario.salud-peso.create') }}" wire:navigate 
               class="flex-1 bg-white text-green-700 hover:bg-gray-100 font-medium py-2 px-4 rounded-lg text-center text-sm transition-all duration-200 flex items-center justify-center">
                <i class="fas fa-plus mr-2"></i> Registrar
            </a>
        </div>
    </div>
</div>