<?php
use App\Models\User;
use App\Models\Rol;
use App\Models\Contacto;
use App\Models\Direccion;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Carbon\Carbon;
use App\Models\Auditoria;
use Illuminate\Support\Str;

new #[Layout('layouts.auth')] class extends Component {
    use WithPagination;
    
    // Propiedades para las métricas
    public $totalUsuarios = 0;
    public $usuariosActivos = 0;
    public $usuariosNuevos = 0;
    public $usuariosInactivos = 0;
    public $intentosFallidos = 0;
    public $usuariosElevados = 0;
    public $usuariosIncompletos = 0;
    
    // Datos para gráficos
    public $distribucionRoles = [];
    public $tendenciaUsuarios = [];
    public $registrosNuevos = [];
    
    // Eventos recientes
    public $eventosRecientes = [];
    public $eventosAnormales = [];

    public function mount()
    {
        $this->cargarDatos();
    }

    public function cargarDatos()
    {
        try {
            // Total de usuarios
            $this->totalUsuarios = User::count();
            
            // Usuarios activos (últimos 30 días)
            $this->usuariosActivos = User::where('created_at', '>=', now()->subDays(30))
                                        ->count();
            
            // Usuarios nuevos (últimos 7 días)
            $this->usuariosNuevos = User::where('created_at', '>=', now()->subDays(7))
                                      ->count();
            
            // Usuarios inactivos (más de 90 días sin actividad)
            $this->usuariosInactivos = User::where('created_at', '<=', now()->subDays(90))
                                         ->count();
            
            // Intentos fallidos de login (últimos 7 días)
            $this->intentosFallidos = Auditoria::where('opeAud', 'LOGIN_FAILED')
                                            ->where('fecAud', '>=', now()->subDays(7))
                                            ->count();
            
            // Usuarios con permisos elevados (roles específicos)
            $this->usuariosElevados = User::whereIn('idRolUsu', [1, 2]) // IDs de roles admin/superadmin
                                        ->count();
            
            // Usuarios con datos incompletos
            $this->usuariosIncompletos = User::whereNull('nomUsu')
                                           ->orWhereNull('apeUsu')
                                           ->orWhereNull('numDocUsu')
                                           ->count();
            
            // Distribución por roles
            $this->distribucionRoles = Rol::withCount('usuarios')
                                        ->orderBy('usuarios_count', 'desc')
                                        ->get()
                                        ->map(function($rol) {
                                            return [
                                                'nombre' => $rol->nomRol,
                                                'cantidad' => $rol->usuarios_count,
                                                'color' => $this->getColorForRole($rol->nomRol)
                                            ];
                                        });
            
            // Tendencia de usuarios (últimos 12 meses)
            $this->tendenciaUsuarios = User::select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as total')
            )
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get()
            ->map(function($item) {
                return [
                    'periodo' => Carbon::create($item->year, $item->month, 1)->format('M Y'),
                    'total' => $item->total
                ];
            });
            
            // Registros nuevos (últimos 30 días)
            $this->registrosNuevos = User::select(
                DB::raw('DATE(created_at) as fecha'),
                DB::raw('COUNT(*) as total')
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('fecha')
            ->orderBy('fecha', 'asc')
            ->get();
            
            // Eventos recientes (últimos 5 eventos normales)
            $this->eventosRecientes = Auditoria::query()
                ->with('usuario')
                ->whereNotIn('opeAud', ['LOGIN_FAILED', 'UNAUTHORIZED_ACCESS'])
                ->orderBy('fecAud', 'desc')
                ->take(5)
                ->get()
                ->map(function($log) {
                    return [
                        'fecha' => $log->fecAud->format('d/m H:i'),
                        'usuario' => $log->usuAud ?? 'Sistema',
                        'operacion' => $log->opeAud,
                        'detalle' => Str::limit($log->desAud, 40)
                    ];
                });
            
            // Eventos anormales (últimos 5 eventos anómalos)
            $this->eventosAnormales = Auditoria::query()
                ->with('usuario')
                ->where(function($query) {
                    $query->whereIn('opeAud', ['LOGIN_FAILED', 'UNAUTHORIZED_ACCESS'])
                          ->orWhere('desAud', 'like', '%intento fallido%')
                          ->orWhere('desAud', 'like', '%acceso no autorizado%');
                })
                ->orderBy('fecAud', 'desc')
                ->take(5)
                ->get()
                ->map(function($log) {
                    return [
                        'fecha' => $log->fecAud->format('d/m H:i'),
                        'usuario' => $log->usuAud ?? 'Sistema',
                        'operacion' => $log->opeAud,
                        'detalle' => Str::limit($log->desAud, 40),
                        'severidad' => $log->getSeveridadAttribute()
                    ];
                });
            
        } catch (\Exception $e) {
            // Log del error
            \Log::error('Error al cargar datos del dashboard de usuarios: ' . $e->getMessage());
            
            // Mostrar mensaje de error
            session()->flash('error', 'Error al cargar algunos datos del dashboard');
        }
    }
    
    private function getColorForRole($roleName)
    {
        $colors = [
            'Superusuario' => '#3b82f6', // blue-500
            'Administrador' => '#10b981', // emerald-500
            'Aprendiz' => '#f59e0b', // amber-500
            'Invitado' => '#ef4444', // red-500
            'Editor' => '#8b5cf6', // violet-500
            'Usuario' => '#06b6d4' // cyan-500
        ];
        
        return $colors[$roleName] ?? '#6b7280'; // gray-500 por defecto
    }
}; ?>

@section('title', 'Panel de Usuarios')

<div class="bg-gray-100 min-h-full p-4">
    <!-- Header -->
    <div class="mb-6 text-center">
        <h1 class="text-5xl font-bold bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent mb-4">Panel de Usuarios</h1>
        <p class="text-xl text-gray-600 max-w-2xl mx-auto leading-relaxed">Administra todos los usuarios del sistema</p>
    </div>

    <!-- Tarjetas principales -->
    <div class="mt-12">
        <div class="mb-12 grid gap-y-10 gap-x-6 md:grid-cols-2 xl:grid-cols-3">
            <!-- Card 1: Total de usuarios con Pie Chart -->
            <div>
                <div class="relative flex flex-col bg-clip-border rounded-xl bg-white text-gray-700 shadow-md mb-4">
                    <div class="bg-clip-border mx-4 rounded-xl overflow-hidden bg-gradient-to-tr from-blue-600 to-blue-400 text-white shadow-blue-500/40 shadow-lg absolute -mt-4 grid h-16 w-16 place-items-center">
                        <a href="{{ route('settings.manage-users') }}" wire:navigate ><i class="fas fa-users text-xl"></i></a>
                    </div>
                    <div class="p-4 text-right">
                        <p class="block antialiased font-sans text-sm leading-normal font-normal text-blue-gray-600">Total de usuarios</p>
                        <h4 class="block antialiased tracking-normal font-sans text-2xl font-semibold leading-snug text-blue-gray-900">{{ number_format($totalUsuarios) }}</h4>
                    </div>
                    <div class="border-t border-blue-gray-50 p-1.5">
                        <p class="block antialiased font-sans text-base leading-relaxed font-normal text-blue-gray-600">
                            <strong class="text-green-500">+{{ round(($usuariosNuevos / max($totalUsuarios, 1)) * 100) }}%</strong>
                            <span class="text-xs text-gray-500 block mt-1">{{ $intentosFallidos }} intentos fallidos (7d)</span>
                        </p>
                    </div>
                </div>
                <!-- Pie Chart Container -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 h-[200px] flex items-center justify-center">
                    <canvas id="totalUsersChart"></canvas>
                </div>
            </div>

            <!-- Card 2: Usuarios activos con Line Chart -->
            <div>
                <div class="relative flex flex-col bg-clip-border rounded-xl bg-white text-gray-700 shadow-md mb-4">
                    <div class="bg-clip-border mx-4 rounded-xl overflow-hidden bg-gradient-to-tr from-pink-600 to-pink-400 text-white shadow-pink-500/40 shadow-lg absolute -mt-4 grid h-16 w-16 place-items-center">
                        <i class="fas fa-user-check text-xl"></i>
                    </div>
                    <div class="p-4 text-right">
                        <p class="block antialiased font-sans text-sm leading-normal font-normal text-blue-gray-600">Usuarios activos</p>
                        <h4 class="block antialiased tracking-normal font-sans text-2xl font-semibold leading-snug text-blue-gray-900">{{ number_format($usuariosActivos) }}</h4>
                    </div>
                    <div class="border-t border-blue-gray-50 p-4">
                        <p class="block antialiased font-sans text-base leading-relaxed font-normal text-blue-gray-600">
                            <strong class="text-green-500">+{{ round(($usuariosActivos / max($totalUsuarios, 1)) * 100) }}%</strong>&nbsp;del total
                        </p>
                    </div>
                </div>
                <!-- Line Chart Container -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 h-[200px] flex items-center justify-center">
                    <canvas id="activeUsersChart"></canvas>
                </div>
            </div>

            <!-- Card 3: Usuarios nuevos con Line Chart -->
            <div>
                <div class="relative flex flex-col bg-clip-border rounded-xl bg-white text-gray-700 shadow-md mb-4">
                    <div class="bg-clip-border mx-4 rounded-xl overflow-hidden bg-gradient-to-tr from-green-600 to-green-400 text-white shadow-green-500/40 shadow-lg absolute -mt-4 grid h-16 w-16 place-items-center">
                        <i class="fas fa-user-plus text-xl"></i>
                    </div>
                    <div class="p-4 text-right">
                        <p class="block antialiased font-sans text-sm leading-normal font-normal text-blue-gray-600">Usuarios nuevos</p>
                        <h4 class="block antialiased tracking-normal font-sans text-2xl font-semibold leading-snug text-blue-gray-900">{{ number_format($usuariosNuevos) }}</h4>
                    </div>
                    <div class="border-t border-blue-gray-50 p-4">
                        <p class="block antialiased font-sans text-base leading-relaxed font-normal text-blue-gray-600">
                            <strong class="text-green-500">+{{ round(($usuariosNuevos / max($totalUsuarios, 1)) * 100) }}%</strong>&nbsp;este mes
                        </p>
                    </div>
                </div>
                <!-- Line Chart Container -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 h-[200px] flex items-center justify-center">
                    <canvas id="newUsersChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Cards de Eventos y Eventos Anormales -->
    <div class="mt-12 grid gap-y-10 gap-x-6 md:grid-cols-2">
        <!-- Card: Eventos -->
        <div>
            <div class="relative flex flex-col bg-clip-border rounded-xl bg-white text-gray-700 shadow-md mb-4">
                <div class="bg-clip-border mx-4 rounded-xl overflow-hidden bg-gradient-to-tr from-indigo-600 to-indigo-400 text-white shadow-indigo-500/40 shadow-lg absolute -mt-4 grid h-16 w-16 place-items-center">
                    <a href="{{ route('settings.manage-users.events') }}" wire:navigate ><i class="fas fa-calendar-check text-xl"></i></a>
                </div>
                <div class="p-4 text-right">
                    <p class="block antialiased font-sans text-sm leading-normal font-normal text-blue-gray-600">Eventos</p>
                    <h4 class="block antialiased tracking-normal font-sans text-2xl font-semibold leading-snug text-blue-gray-900">Últimos registros</h4>
                </div>
                <div class="border-t border-blue-gray-50 p-4">
                    <ul class="text-sm text-gray-600 space-y-2">
                        @foreach($eventosRecientes as $evento)
                        <li class="flex justify-between items-start">
                            <div class="flex-1 min-w-0">
                                <p class="truncate font-medium">{{ $evento['operacion'] }}</p>
                                <p class="truncate text-gray-500">{{ $evento['detalle'] }}</p>
                            </div>
                            <div class="ml-2 flex-shrink-0 flex flex-col items-end">
                                <span class="text-xs text-gray-400">{{ $evento['fecha'] }}</span>
                                <span class="text-xs text-gray-500">{{ $evento['usuario'] }}</span>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                    <a href="{{ route('settings.manage-users.events') }}" wire:navigate class="mt-3 inline-block text-indigo-600 text-sm font-medium hover:underline">Ver todos</a>
                </div>
            </div>
        </div>

        <!-- Card: Eventos Anormales -->
        <div>
            <div class="relative flex flex-col bg-clip-border rounded-xl bg-white text-gray-700 shadow-md mb-4">
                <div class="bg-clip-border mx-4 rounded-xl overflow-hidden bg-gradient-to-tr from-red-600 to-red-400 text-white shadow-red-500/40 shadow-lg absolute -mt-4 grid h-16 w-16 place-items-center">
                    <i class="fas fa-exclamation-triangle text-xl"></i>
                </div>
                <div class="p-4 text-right">
                    <p class="block antialiased font-sans text-sm leading-normal font-normal text-blue-gray-600">Eventos anormales</p>
                    <h4 class="block antialiased tracking-normal font-sans text-2xl font-semibold leading-snug text-blue-gray-900">Alertas recientes</h4>
                </div>
                <div class="border-t border-blue-gray-50 p-4">
                    <ul class="text-sm text-gray-600 space-y-2">
                        @foreach($eventosAnormales as $evento)
                        <li class="flex justify-between items-start">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center">
                                    @php
                                        $colorClasses = [
                                            'baja' => 'bg-green-100 text-green-800',
                                            'media' => 'bg-yellow-100 text-yellow-800',
                                            'alta' => 'bg-orange-100 text-orange-800',
                                            'critica' => 'bg-red-100 text-red-800'
                                        ];
                                    @endphp
                                    <span class="mr-2 px-1.5 py-0.5 text-xs rounded-full {{ $colorClasses[$evento['severidad']] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ substr($evento['severidad'], 0, 1) }}
                                    </span>
                                    <p class="truncate font-medium">{{ $evento['operacion'] }}</p>
                                </div>
                                <p class="truncate text-gray-500 ml-6">{{ $evento['detalle'] }}</p>
                            </div>
                            <div class="ml-2 flex-shrink-0 flex flex-col items-end">
                                <span class="text-xs text-gray-400">{{ $evento['fecha'] }}</span>
                                <span class="text-xs text-gray-500">{{ $evento['usuario'] }}</span>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                    <a href="{{ route('settings.manage-users.unusual-events') }}" wire:navigate class="mt-3 inline-block text-red-600 text-sm font-medium hover:underline">Ver todos</a>
                </div>
            </div>
        </div>
    </div>

    <!-- KPIs adicionales -->
    <div class="mt-12 grid gap-y-10 gap-x-6 md:grid-cols-2 xl:grid-cols-4">
        <!-- Card: Usuarios inactivos -->
        <div class="relative flex flex-col bg-clip-border rounded-xl bg-white text-gray-700 shadow-md mb-4">
            <div class="bg-clip-border mx-4 rounded-xl overflow-hidden bg-gradient-to-tr from-yellow-600 to-yellow-400 text-white shadow-yellow-500/40 shadow-lg absolute -mt-4 grid h-16 w-16 place-items-center">
                <i class="fas fa-clock text-xl"></i>
            </div>
            <div class="p-4 text-right">
                <p class="text-sm text-blue-gray-600">Usuarios inactivos (+90 días)</p>
                <h4 class="text-2xl font-semibold text-blue-gray-900">{{ number_format($usuariosInactivos) }}</h4>
            </div>
            <div class="border-t border-blue-gray-50 p-4">
                <p class="text-base text-gray-600">
                    <strong class="text-red-500">{{ round(($usuariosInactivos / max($totalUsuarios, 1)) * 100) }}%</strong> del total
                </p>
            </div>
        </div>

        <!-- Card: Usuarios con permisos elevados -->
        <div class="relative flex flex-col bg-clip-border rounded-xl bg-white text-gray-700 shadow-md mb-4">
            <div class="bg-clip-border mx-4 rounded-xl overflow-hidden bg-gradient-to-tr from-purple-600 to-purple-400 text-white shadow-purple-500/40 shadow-lg absolute -mt-4 grid h-16 w-16 place-items-center">
                <i class="fas fa-shield-alt text-xl"></i>
            </div>
            <div class="p-4 text-right">
                <p class="text-sm text-blue-gray-600">Usuarios con permisos elevados</p>
                <h4 class="text-2xl font-semibold text-blue-gray-900">{{ number_format($usuariosElevados) }}</h4>
            </div>
            <div class="border-t border-blue-gray-50 p-4">
                <p class="text-base text-gray-600">
                    <strong class="text-green-500">{{ round(($usuariosElevados / max($totalUsuarios, 1)) * 100) }}%</strong> del total
                </p>
            </div>
        </div>

        <!-- Card: Usuarios con datos incompletos -->
        <div class="relative flex flex-col bg-clip-border rounded-xl bg-white text-gray-700 shadow-md mb-4">
            <div class="bg-clip-border mx-4 rounded-xl overflow-hidden bg-gradient-to-tr from-teal-600 to-teal-400 text-white shadow-teal-500/40 shadow-lg absolute -mt-4 grid h-16 w-16 place-items-center">
                <i class="fas fa-exclamation-circle text-xl"></i>
            </div>
            <div class="p-4 text-right">
                <p class="text-sm text-blue-gray-600">Usuarios con datos incompletos</p>
                <h4 class="text-2xl font-semibold text-blue-gray-900">{{ round(($usuariosIncompletos / max($totalUsuarios, 1)) * 100) }}%</h4>
            </div>
            <div class="border-t border-blue-gray-50 p-4">
                <p class="text-base text-gray-600">
                    <strong class="text-yellow-500">Atención:</strong> Completar datos críticos
                </p>
            </div>
        </div>

        <!-- Card: Distribución por roles -->
        <div class="relative flex flex-col bg-clip-border rounded-xl bg-white text-gray-700 shadow-md mb-4">
            <div class="bg-clip-border mx-4 rounded-xl overflow-hidden bg-gradient-to-tr from-orange-600 to-orange-400 text-white shadow-orange-500/40 shadow-lg absolute -mt-4 grid h-16 w-16 place-items-center">
                <i class="fas fa-tags text-xl"></i>
            </div>
            <div class="p-4 text-right">
                <p class="text-sm text-blue-gray-600">Distribución por roles</p>
                <h4 class="text-2xl font-semibold text-blue-gray-900">{{ count($distribucionRoles) }}</h4>
            </div>
            <div class="border-t border-blue-gray-50 p-4">
                <p class="text-base text-gray-600">
                    <strong class="text-blue-500">Ver gráfico</strong> arriba
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Scripts para gráficos -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gráfico de distribución de roles (Pie Chart)
    const ctxPie = document.getElementById('totalUsersChart').getContext('2d');
    new Chart(ctxPie, {
        type: 'pie',
        data: {
            labels: @json($distribucionRoles->pluck('nombre')),
            datasets: [{
                data: @json($distribucionRoles->pluck('cantidad')),
                backgroundColor: @json($distribucionRoles->pluck('color')),
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

    // Gráfico de tendencia de usuarios activos (Line Chart)
    const ctxLine = document.getElementById('activeUsersChart').getContext('2d');
    new Chart(ctxLine, {
        type: 'line',
        data: {
            labels: @json($tendenciaUsuarios->pluck('periodo')),
            datasets: [{
                label: 'Usuarios registrados',
                data: @json($tendenciaUsuarios->pluck('total')),
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Gráfico de nuevos usuarios (Bar Chart)
    const ctxBar = document.getElementById('newUsersChart').getContext('2d');
    new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: @json($registrosNuevos->pluck('fecha')),
            datasets: [{
                label: 'Nuevos usuarios',
                data: @json($registrosNuevos->pluck('total')),
                backgroundColor: '#10b981',
                borderColor: '#10b981',
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
            }
        }
    });
});
</script>