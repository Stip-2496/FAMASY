<?php
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Models\Herramienta;
use App\Models\Insumo;
use App\Models\Mantenimiento;
use App\Models\PrestamoHerramienta;
use App\Models\Inventario;

new #[Layout('layouts.auth')] class extends Component {
    // Statistics properties
    public $totalHerramientas;
    public $prestamosActivos;
    public $prestamosPorVencer;
    public $alertasStock;
    public $totalMantenimientos;
    public $mantenimientosProgramados;
    
    // Additional data
    public $insumosProximosVencer;
    public $estadisticasInsumos;
    public $ultimosMovimientos;
    
    public function mount()
    {
        $this->loadStatistics();
        $this->loadAdditionalData();
    }
    
    protected function loadStatistics()
    {
        // Herramientas statistics
        $this->totalHerramientas = Herramienta::activas()->count();
        
        // Préstamos statistics
        $this->prestamosActivos = PrestamoHerramienta::where('estPre', 'prestado')->count();
        $this->prestamosPorVencer = PrestamoHerramienta::where('estPre', 'prestado')
            ->where('fecDev', '<=', now()->addDays(3))
            ->count();
        
        // Stock alerts (placeholder - implement your actual logic)
        $this->alertasStock = 8; // This should be calculated based on your business logic
        
        // Mantenimientos statistics
        $this->totalMantenimientos = Mantenimiento::count();
        $this->mantenimientosProgramados = Mantenimiento::where('estMan', 'pendiente')->count();
    }
    
    protected function loadAdditionalData()
    {
        // Insumos próximos a vencer
        $this->insumosProximosVencer = Insumo::activos()
            ->whereBetween('fecVenIns', [now(), now()->addDays(30)])
            ->orderBy('fecVenIns')
            ->take(5)
            ->get();
        
        // Estadísticas de insumos
        $this->estadisticasInsumos = [
            'total' => Insumo::activos()->count(),
            'disponibles' => Insumo::activos()->where('estIns', 'disponible')->count(),
            'por_vencer' => Insumo::activos()->whereBetween('fecVenIns', [now(), now()->addDays(30)])->count(),
            'vencidos' => Insumo::activos()->where('fecVenIns', '<', now())->count(),
        ];
        
        // Últimos movimientos
        $this->ultimosMovimientos = Inventario::with(['insumo', 'herramienta', 'usuario'])
            ->orderBy('fecMovInv', 'desc')
            ->take(5)
            ->get();
    }
    
    public function with(): array
    {
        return [
            'totalHerramientas' => $this->totalHerramientas,
            'prestamosActivos' => $this->prestamosActivos,
            'prestamosPorVencer' => $this->prestamosPorVencer,
            'alertasStock' => $this->alertasStock,
            'totalMantenimientos' => $this->totalMantenimientos,
            'mantenimientosProgramados' => $this->mantenimientosProgramados,
            'insumosProximosVencer' => $this->insumosProximosVencer,
            'estadisticasInsumos' => $this->estadisticasInsumos,
            'ultimosMovimientos' => $this->ultimosMovimientos,
        ];
    }
}; ?>

<div>
    @section('title', 'Dashboard Inventario')
    
    <div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-indigo-100">
        <div class="container mx-auto px-4 py-8">
            
            <!-- Header Principal -->
            <div class="text-center mb-12">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-full mb-6 shadow-lg">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <h1 class="text-5xl font-bold bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent mb-4">
                    Sistema de Inventario
                </h1>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto leading-relaxed">
                    Control inteligente y gestión completa del inventario de herramientas, insumos y equipos
                </p>
            </div>

            <!-- Estadísticas Rápidas -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
                
                <!-- Total Herramientas -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Total Herramientas</p>
                                <p class="text-3xl font-bold text-gray-900 mt-2">{{ $totalHerramientas }}</p>
                                <p class="text-sm text-green-600 mt-1">
                                    <span class="inline-flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-4 4"></path>
                                        </svg>
                                        +12% este mes
                                    </span>
                                </p>
                            </div>
                            <div class="p-3 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Préstamos Activos -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Préstamos Activos</p>
                                <p class="text-3xl font-bold text-gray-900 mt-2">{{ $prestamosActivos }}</p>
                                <p class="text-sm text-yellow-600 mt-1">
                                    <span class="inline-flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        {{ $prestamosPorVencer }} por vencer
                                    </span>
                                </p>
                            </div>
                            <div class="p-3 bg-gradient-to-r from-yellow-500 to-orange-500 rounded-full">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stock Bajo -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Alertas de Stock</p>
                                <p class="text-3xl font-bold text-gray-900 mt-2">{{ $alertasStock }}</p>
                                <p class="text-sm text-red-600 mt-1">
                                    <span class="inline-flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                        </svg>
                                        Requiere atención
                                    </span>
                                </p>
                            </div>
                            <div class="p-3 bg-gradient-to-r from-red-500 to-pink-500 rounded-full">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mantenimientos -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Mantenimientos</p>
                                <p class="text-3xl font-bold text-gray-900 mt-2">{{ $totalMantenimientos }}</p>
                                <p class="text-sm text-purple-600 mt-1">
                                    <span class="inline-flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        {{ $mantenimientosProgramados }} programados
                                    </span>
                                </p>
                            </div>
                            <div class="p-3 bg-gradient-to-r from-purple-500 to-indigo-500 rounded-full">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Módulos Principales -->
            <div class="mb-8">
                <h2 class="text-3xl font-bold text-gray-800 mb-2 text-center">Módulos del Sistema</h2>
                <p class="text-gray-600 text-center mb-8">Acceda a las diferentes funcionalidades del sistema de inventario</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                
                <!-- Herramientas -->
                <div class="group bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2">
                    <div class="relative">
                        <div class="bg-gradient-to-br from-emerald-500 to-teal-600 p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-white text-xl font-bold mb-1">Herramientas</h3>
                                    <p class="text-emerald-100 text-sm">Gestión integral</p>
                                </div>
                                <div class="p-3 bg-white bg-opacity-20 rounded-full group-hover:bg-opacity-30 transition-all duration-300">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div class="absolute top-0 right-0 w-32 h-32 bg-white bg-opacity-10 rounded-full -mr-16 -mt-16"></div>
                    </div>
                    <div class="p-6">
                        <p class="text-gray-600 mb-6 leading-relaxed">Control completo de herramientas, préstamos, mantenimientos y ubicaciones en tiempo real.</p>
                        <div class="flex gap-3">
                            <a href="{{ route('inventario.herramientas.index') }}" wire:navigate
                               class="flex-1 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 text-center transform hover:scale-105">
                                Ver Lista
                            </a>
                            <a href="{{ route('inventario.herramientas.create') }}" wire:navigate
                               class="flex-1 bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 text-center transform hover:scale-105">
                                Agregar
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Insumos -->
                <div class="group bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2">
                    <div class="relative">
                        <div class="bg-gradient-to-br from-orange-500 to-red-500 p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-white text-xl font-bold mb-1">Insumos</h3>
                                    <p class="text-orange-100 text-sm">Control de stock</p>
                                </div>
                                <div class="p-3 bg-white bg-opacity-20 rounded-full group-hover:bg-opacity-30 transition-all duration-300">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div class="absolute top-0 right-0 w-32 h-32 bg-white bg-opacity-10 rounded-full -mr-16 -mt-16"></div>
                    </div>
                    <div class="p-6">
                        <p class="text-gray-600 mb-6 leading-relaxed">Administración de insumos, control de stock, fechas de vencimiento y alertas automáticas.</p>
                        <div class="flex gap-3">
                            <a href="{{ route('inventario.insumos.index') }}" wire:navigate
                               class="flex-1 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 text-center transform hover:scale-105">
                                Ver Lista
                            </a>
                            <a href="{{ route('inventario.insumos.create') }}" wire:navigate
                               class="flex-1 bg-gradient-to-r from-orange-600 to-orange-700 hover:from-orange-700 hover:to-orange-800 text-white px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 text-center transform hover:scale-105">
                                Agregar
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Movimientos -->
                <div class="group bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2">
                    <div class="relative">
                        <div class="bg-gradient-to-br from-purple-500 to-indigo-600 p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-white text-xl font-bold mb-1">Movimientos</h3>
                                    <p class="text-purple-100 text-sm">Kardex completo</p>
                                </div>
                                <div class="p-3 bg-white bg-opacity-20 rounded-full group-hover:bg-opacity-30 transition-all duration-300">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div class="absolute top-0 right-0 w-32 h-32 bg-white bg-opacity-10 rounded-full -mr-16 -mt-16"></div>
                    </div>
                    <div class="p-6">
                        <p class="text-gray-600 mb-6 leading-relaxed">Registro detallado de entradas, salidas y consumos con trazabilidad completa.</p>
                        <div class="flex gap-3">
                            <a href="{{ route('inventario.movimientos.index') }}" wire:navigate
                               class="flex-1 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 text-center transform hover:scale-105">
                                Ver Lista
                            </a>
                            <a href="{{ route('inventario.movimientos.create') }}" wire:navigate
                               class="flex-1 bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 text-center transform hover:scale-105">
                                Registrar
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Préstamos -->
                <div class="group bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2">
                    <div class="relative">
                        <div class="bg-gradient-to-br from-cyan-500 to-blue-600 p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-white text-xl font-bold mb-1">Préstamos</h3>
                                    <p class="text-cyan-100 text-sm">Control de préstamos</p>
                                </div>
                                <div class="p-3 bg-white bg-opacity-20 rounded-full group-hover:bg-opacity-30 transition-all duration-300">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div class="absolute top-0 right-0 w-32 h-32 bg-white bg-opacity-10 rounded-full -mr-16 -mt-16"></div>
                    </div>
                    <div class="p-6">
                        <p class="text-gray-600 mb-6 leading-relaxed">Gestión completa de préstamos, devoluciones y seguimiento de herramientas.</p>
                        <div class="flex gap-3">
                            <a href="{{ route('inventario.prestamos.index') }}" wire:navigate
                               class="flex-1 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 text-center transform hover:scale-105">
                                Ver Lista
                            </a>
                            <a href="{{ route('inventario.prestamos.create') }}" wire:navigate
                               class="flex-1 bg-gradient-to-r from-cyan-600 to-cyan-700 hover:from-cyan-700 hover:to-cyan-800 text-white px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 text-center transform hover:scale-105">
                                Nuevo
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Mantenimientos -->
                <div class="group bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2">
                    <div class="relative">
                        <div class="bg-gradient-to-br from-amber-500 to-orange-600 p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-white text-xl font-bold mb-1">Mantenimientos</h3>
                                    <p class="text-amber-100 text-sm">Programación</p>
                                </div>
                                <div class="p-3 bg-white bg-opacity-20 rounded-full group-hover:bg-opacity-30 transition-all duration-300">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div class="absolute top-0 right-0 w-32 h-32 bg-white bg-opacity-10 rounded-full -mr-16 -mt-16"></div>
                    </div>
                    <div class="p-6">
                        <p class="text-gray-600 mb-6 leading-relaxed">Programación y seguimiento de mantenimientos preventivos y correctivos.</p>
                        <div class="flex gap-3">
                            <a href="{{ route('inventario.mantenimientos.index') }}" wire:navigate
                               class="flex-1 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 text-center transform hover:scale-105">
                                Ver Lista
                            </a>
                            <a href="{{ route('inventario.mantenimientos.create') }}" wire:navigate
                               class="flex-1 bg-gradient-to-r from-amber-600 to-amber-700 hover:from-amber-700 hover:to-amber-800 text-white px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 text-center transform hover:scale-105">
                                Programar
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Reportes -->
                <div class="group bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2">
                    <div class="relative">
                        <div class="bg-gradient-to-br from-teal-500 to-green-600 p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-white text-xl font-bold mb-1">Reportes</h3>
                                    <p class="text-teal-100 text-sm">Análisis y estadísticas</p>
                                </div>
                                <div class="p-3 bg-white bg-opacity-20 rounded-full group-hover:bg-opacity-30 transition-all duration-300">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div class="absolute top-0 right-0 w-32 h-32 bg-white bg-opacity-10 rounded-full -mr-16 -mt-16"></div>
                    </div>
                    <div class="p-6">
                        <p class="text-gray-600 mb-6 leading-relaxed">Reportes detallados, gráficos y análisis de rendimiento del inventario.</p>
                        <div class="flex gap-3">
                            <a href="#" 
                               class="flex-1 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 text-center transform hover:scale-105">
                                Ver Reportes
                            </a>
                            <a href="#" 
                               class="flex-1 bg-gradient-to-r from-teal-600 to-teal-700 hover:from-teal-700 hover:to-teal-800 text-white px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 text-center transform hover:scale-105">
                                Generar
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones Rápidas -->
            <div class="mt-16 bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-gray-800 to-gray-900 px-8 py-6">
                    <h3 class="text-2xl font-bold text-white mb-2">Acciones Rápidas</h3>
                    <p class="text-gray-300">Operaciones frecuentes del sistema</p>
                </div>
                <div class="p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        
                        <!-- Agregar Herramienta -->
                        <a href="{{ route('inventario.herramientas.create') }}" 
                           class="group flex flex-col items-center p-6 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl border-2 border-blue-100 hover:border-blue-300 transition-all duration-300 transform hover:-translate-y-1 hover:shadow-lg">
                            <div class="p-4 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full group-hover:from-blue-600 group-hover:to-blue-700 transition-all duration-300 mb-4">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                            </div>
                            <h4 class="font-semibold text-gray-800 mb-2">Nueva Herramienta</h4>
                            <p class="text-sm text-gray-600 text-center">Registrar herramienta</p>
                        </a>

                        <!-- Nuevo Préstamo -->
                        <a href="{{ route('inventario.prestamos.create') }}" 
                           class="group flex flex-col items-center p-6 bg-gradient-to-br from-yellow-50 to-orange-50 rounded-xl border-2 border-yellow-100 hover:border-yellow-300 transition-all duration-300 transform hover:-translate-y-1 hover:shadow-lg">
                            <div class="p-4 bg-gradient-to-r from-yellow-500 to-orange-500 rounded-full group-hover:from-yellow-600 group-hover:to-orange-600 transition-all duration-300 mb-4">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"></path>
                                </svg>
                            </div>
                            <h4 class="font-semibold text-gray-800 mb-2">Nuevo Préstamo</h4>
                            <p class="text-sm text-gray-600 text-center">Registrar préstamo</p>
                        </a>

                        <!-- Registrar Movimiento -->
                        <a href="{{ route('inventario.movimientos.create') }}" 
                           class="group flex flex-col items-center p-6 bg-gradient-to-br from-purple-50 to-indigo-50 rounded-xl border-2 border-purple-100 hover:border-purple-300 transition-all duration-300 transform hover:-translate-y-1 hover:shadow-lg">
                            <div class="p-4 bg-gradient-to-r from-purple-500 to-indigo-500 rounded-full group-hover:from-purple-600 group-hover:to-indigo-600 transition-all duration-300 mb-4">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                </svg>
                            </div>
                            <h4 class="font-semibold text-gray-800 mb-2">Movimiento</h4>
                            <p class="text-sm text-gray-600 text-center">Registrar entrada/salida</p>
                        </a>

                        <!-- Programar Mantenimiento -->
                        <a href="{{ route('inventario.mantenimientos.create') }}" 
                           class="group flex flex-col items-center p-6 bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl border-2 border-green-100 hover:border-green-300 transition-all duration-300 transform hover:-translate-y-1 hover:shadow-lg">
                            <div class="p-4 bg-gradient-to-r from-green-500 to-emerald-500 rounded-full group-hover:from-green-600 group-hover:to-emerald-600 transition-all duration-300 mb-4">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <h4 class="font-semibold text-gray-800 mb-2">Mantenimiento</h4>
                            <p class="text-sm text-gray-600 text-center">Programar servicio</p>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Footer con información adicional -->
            <div class="mt-16 text-center">
                <div class="inline-flex items-center space-x-6 text-gray-500">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Sistema Online
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Actualizado en tiempo real
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                        Datos seguros
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Animación de entrada para las tarjetas
        const cards = document.querySelectorAll('.group');
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry, index) => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }, index * 100);
                }
            });
        });
        
        cards.forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(card);
        });
        
        // Efecto de conteo para las estadísticas
        const counters = document.querySelectorAll('.text-3xl');
        
        counters.forEach(counter => {
            const target = parseInt(counter.textContent);
            let count = 0;
            const increment = target / 100;
            
            const updateCounter = () => {
                if (count < target) {
                    count += increment;
                    counter.textContent = Math.ceil(count);
                    setTimeout(updateCounter, 20);
                } else {
                    counter.textContent = target;
                }
            };
            
            // Iniciar animación cuando sea visible
            const counterObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        updateCounter();
                        counterObserver.unobserve(entry.target);
                    }
                });
            });
            
            counterObserver.observe(counter);
        });
    });
    </script>
    @endpush

    <style>
        /* Animaciones personalizadas */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        .group:hover .animate-float {
            animation: float 2s ease-in-out infinite;
        }
        
        /* Efectos de hover mejorados */
        .hover-scale {
            transition: transform 0.3s ease;
        }
        
        .hover-scale:hover {
            transform: scale(1.05);
        }
        
        /* Gradientes personalizados */
        .bg-gradient-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</div>