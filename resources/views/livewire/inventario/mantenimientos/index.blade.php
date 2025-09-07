<?php
use App\Models\Mantenimiento;
use App\Models\Herramienta;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.auth')] class extends Component {
    use WithPagination;

    // Propiedades de estado
    public string $search = '';
    public string $filtroEstado = '';
    public string $filtroTipo = '';
    public string $buscarHerramienta = '';
    public string $vistaActual = 'cards'; // 'cards' o 'lista'
    
    // Modales
    public $showCompletarModal = false;
    public $showDeleteModal = false;
    
    // IDs de elementos activos
    public $mantenimientoToComplete = null;
    public $mantenimientoToDelete = null;
    
    // Datos del modal completar
    public $resultado = '';
    public $observaciones = '';

    public function with(): array
    {
        $query = Mantenimiento::query()->with('herramienta');
        
        // Aplicar filtros
        if ($this->filtroEstado) {
            $query->where('estMan', $this->filtroEstado);
        }

        if ($this->filtroTipo) {
            $query->where('tipMan', $this->filtroTipo);
        }

        if ($this->buscarHerramienta) {
            $query->where(function($q) {
                $q->whereHas('herramienta', function($sub) {
                    $sub->where('nomHer', 'like', '%'.$this->buscarHerramienta.'%');
                })->orWhere('nomHerMan', 'like', '%'.$this->buscarHerramienta.'%');
            });
        }
        
        // Aplicar ordenamiento
        $query->orderBy('fecMan', 'desc');
        
        return [
            'mantenimientos' => $query->paginate(12),
            'estadisticas' => [
                'total' => Mantenimiento::count(),
                'pendientes' => Mantenimiento::where('estMan', 'pendiente')->count(),
                'en_proceso' => Mantenimiento::where('estMan', 'en proceso')->count(),
                'completados' => Mantenimiento::where('estMan', 'completado')->count(),
                'vencidos' => Mantenimiento::where('fecMan', '<', now()->toDateString())
                    ->where('estMan', '!=', 'completado')->count(),
                'proximos' => Mantenimiento::where('fecMan', '>=', now()->toDateString())
                    ->where('fecMan', '<=', now()->addDays(7)->toDateString())
                    ->where('estMan', '!=', 'completado')->count(),
                'esta_semana' => Mantenimiento::whereBetween('fecMan', [
                    now()->startOfWeek()->toDateString(),
                    now()->endOfWeek()->toDateString()
                ])->where('estMan', '!=', 'completado')->count()
            ]
        ];
    }

    // ===== MÉTODOS DE INTERACCIÓN =====
    
    public function cambiarVista($vista): void
    {
        $this->vistaActual = $vista;
        session()->flash('info', 'Vista cambiada a ' . ($vista === 'cards' ? 'tarjetas' : 'lista'));
    }

    public function limpiarFiltros(): void
    {
        $this->reset(['filtroEstado', 'filtroTipo', 'buscarHerramienta']);
        $this->resetPage();
        session()->flash('success', 'Filtros limpiados correctamente');
    }

    public function confirmCompletar($id): void
    {
        $this->mantenimientoToComplete = $id;
        $this->showCompletarModal = true;
        $this->reset(['resultado', 'observaciones']);
    }

    public function completarMantenimiento(): void
    {
        $this->validate([
            'resultado' => 'required|string|max:100',
            'observaciones' => 'nullable|string|max:500'
        ]);

        try {
            $mantenimiento = Mantenimiento::findOrFail($this->mantenimientoToComplete);
            $mantenimiento->update([
                'estMan' => 'completado',
                'resMan' => $this->resultado,
                'obsMan' => $this->observaciones
            ]);
            
            session()->flash('success', '✅ Mantenimiento completado exitosamente');
            
            $this->reset(['showCompletarModal', 'mantenimientoToComplete', 'resultado', 'observaciones']);
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error al completar mantenimiento: ' . $e->getMessage());
        }
    }

    public function confirmDelete($id): void
    {
        $this->mantenimientoToDelete = $id;
        $this->showDeleteModal = true;
    }

    public function deleteMantenimiento(): void
    {
        try {
            $mantenimiento = Mantenimiento::findOrFail($this->mantenimientoToDelete);
            
            if ($mantenimiento->estMan !== 'pendiente') {
                session()->flash('error', '❌ Solo se pueden eliminar mantenimientos pendientes');
                $this->cancelDelete();
                return;
            }
            
            $nombreHerramienta = $mantenimiento->getNombreHerramientaCompleto();
            $mantenimiento->delete();
            
            session()->flash('success', "🗑️ Mantenimiento de '{$nombreHerramienta}' eliminado correctamente");
            
            $this->cancelDelete();
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar: ' . $e->getMessage());
            $this->cancelDelete();
        }
    }

    public function cancelDelete(): void
    {
        $this->reset(['showDeleteModal', 'mantenimientoToDelete']);
    }
}; ?>

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-400 rounded-r-lg shadow-sm">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-emerald-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-sm text-emerald-700 font-medium">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-400 rounded-r-lg shadow-sm">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-red-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-sm text-red-700 font-medium">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        @if(session('info'))
            <div class="mb-6 p-4 bg-blue-50 border-l-4 border-blue-400 rounded-r-lg shadow-sm">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-blue-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-sm text-blue-700 font-medium">{{ session('info') }}</p>
                </div>
            </div>
        @endif

        <!-- Header Ultra Moderno -->
        <div class="mb-8">
            <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 p-8 relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-green-500/5 to-emerald-500/5"></div>
                <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-green-400/10 to-emerald-600/10 rounded-full -mr-16 -mt-16"></div>
                
                <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center space-x-4 mb-6 lg:mb-0">
                        <div class="p-4 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl shadow-xl transform rotate-3 hover:rotate-0 transition-transform duration-300">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-4xl font-black bg-gradient-to-r from-gray-900 via-gray-800 to-green-800 bg-clip-text text-transparent leading-tight">
                                Centro de Mantenimientos
                            </h1>
                            <p class="text-gray-600 mt-1 text-lg">Gestión inteligente y control avanzado de mantenimientos</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <a href="{{ route('inventario.mantenimientos.create') }}" wire:navigate
                           class="group relative inline-flex items-center px-8 py-4 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                            <svg class="w-6 h-6 mr-3 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            <span class="relative z-10">Nuevo Mantenimiento</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas Mejoradas -->
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4 mb-8">
            @foreach([
                ['key' => 'total', 'label' => 'Total', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'color' => 'from-slate-500 to-slate-600', 'text' => 'text-slate-700'],
                ['key' => 'pendientes', 'label' => 'Pendientes', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'from-orange-500 to-red-500', 'text' => 'text-orange-700'],
                ['key' => 'en_proceso', 'label' => 'En Proceso', 'icon' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'from-yellow-500 to-amber-500', 'text' => 'text-yellow-700'],
                ['key' => 'completados', 'label' => 'Completados', 'icon' => 'M5 13l4 4L19 7', 'color' => 'from-green-500 to-emerald-500', 'text' => 'text-green-700'],
                ['key' => 'vencidos', 'label' => 'Vencidos', 'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.314 15.5c-.77.833.192 2.5 1.732 2.5z', 'color' => 'from-red-500 to-pink-500', 'text' => 'text-red-700'],
                ['key' => 'proximos', 'label' => 'Próximos 7d', 'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z', 'color' => 'from-purple-500 to-indigo-500', 'text' => 'text-purple-700'],
                ['key' => 'esta_semana', 'label' => 'Esta Semana', 'icon' => 'M8 7V3a4 4 0 118 0v4m-8 0h16l-1 12H9L8 7z', 'color' => 'from-blue-500 to-cyan-500', 'text' => 'text-blue-700']
            ] as $stat)
            <div class="group relative bg-white/70 backdrop-blur-sm rounded-2xl shadow-lg border border-white/30 p-6 hover:shadow-2xl hover:bg-white/90 transition-all duration-300 cursor-pointer transform hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-600 uppercase tracking-wide">{{ $stat['label'] }}</p>
                        <p class="text-3xl font-black {{ $stat['text'] }} mt-1">{{ $estadisticas[$stat['key']] }}</p>
                    </div>
                    <div class="p-3 bg-gradient-to-br {{ $stat['color'] }} rounded-xl shadow-lg group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $stat['icon'] }}"></path>
                        </svg>
                    </div>
                </div>
                <div class="absolute inset-0 bg-gradient-to-br {{ $stat['color'] }} opacity-0 group-hover:opacity-5 rounded-2xl transition-opacity duration-300"></div>
            </div>
            @endforeach
        </div>

        <!-- Controles y Filtros -->
        <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-xl border border-white/20 p-6 mb-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
                
                <!-- Controles de Vista -->
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                        <span class="text-sm font-medium text-gray-700">Vista:</span>
                        <div class="bg-gray-100 rounded-xl p-1">
                            <button wire:click="cambiarVista('cards')" 
                                    class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ $vistaActual === 'cards' ? 'bg-white shadow-md text-green-700' : 'text-gray-600 hover:text-gray-800' }}">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                                </svg>
                                Tarjetas
                            </button>
                            <button wire:click="cambiarVista('lista')" 
                                    class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ $vistaActual === 'lista' ? 'bg-white shadow-md text-green-700' : 'text-gray-600 hover:text-gray-800' }}">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                                </svg>
                                Lista
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 lg:max-w-2xl">
                    <select wire:model.live="filtroEstado" class="bg-white border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="">Todos los estados</option>
                        <option value="pendiente">🔄 Pendiente</option>
                        <option value="en proceso">⚙️ En Proceso</option>
                        <option value="completado">✅ Completado</option>
                    </select>

                    <select wire:model.live="filtroTipo" class="bg-white border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="">Todos los tipos</option>
                        <option value="preventivo">🛡️ Preventivo</option>
                        <option value="correctivo">🔧 Correctivo</option>
                        <option value="predictivo">📊 Predictivo</option>
                    </select>

                    <div class="relative">
                        <input wire:model.live.debounce.500ms="buscarHerramienta" type="text" placeholder="Buscar herramienta..." 
                               class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>

                    <button wire:click="limpiarFiltros" 
                            class="px-4 py-2.5 bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-medium rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Limpiar
                    </button>
                </div>
            </div>
        </div>

        <!-- Lista de Mantenimientos -->
        @if($mantenimientos->count() > 0)
            @if($vistaActual === 'cards')
                <!-- Vista de Tarjetas -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
                    @foreach($mantenimientos as $mantenimiento)
                    <div class="group relative bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/30 hover:shadow-2xl hover:bg-white/95 transition-all duration-300 transform hover:-translate-y-2 overflow-hidden">
                        
                        <!-- Header Gradient -->
                        <div class="h-2 bg-gradient-to-r {{ $mantenimiento->estMan === 'completado' ? 'from-green-500 to-emerald-500' : ($mantenimiento->estMan === 'en proceso' ? 'from-yellow-500 to-amber-500' : 'from-orange-500 to-red-500') }}"></div>
                        
                        <div class="p-6">
                            <!-- Header Card -->
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center space-x-3">
                                    <div class="p-2.5 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg group-hover:scale-110 transition-transform duration-300">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 8.172V5L8 4z"></path>
                                        </svg>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h3 class="font-bold text-gray-900 text-sm leading-tight truncate" title="{{ $mantenimiento->herramienta->nomHer ?? $mantenimiento->nomHerMan ?? 'Sin herramienta' }}">
                                            {{ $mantenimiento->herramienta->nomHer ?? $mantenimiento->nomHerMan ?? 'Sin herramienta' }}
                                        </h3>
                                        @if($mantenimiento->herramienta && $mantenimiento->herramienta->catHer)
                                        <p class="text-xs text-gray-500 truncate">{{ $mantenimiento->herramienta->catHer }}</p>
                                        @endif
                                    </div>
                                </div>
                                
                                @php
                                    $estadoConfig = [
                                        'pendiente' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-800', 'icon' => '🔄'],
                                        'en proceso' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'icon' => '⚙️'],
                                        'completado' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'icon' => '✅']
                                    ];
                                    $config = $estadoConfig[$mantenimiento->estMan] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'icon' => '❓'];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $config['bg'] }} {{ $config['text'] }}">
                                    {{ $config['icon'] }} {{ ucfirst($mantenimiento->estMan) }}
                                </span>
                            </div>

                            <!-- Información Principal -->
                            <div class="space-y-3 mb-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-medium text-gray-500">Tipo:</span>
                                    <span class="text-sm font-semibold text-gray-800 capitalize">
                                        @switch($mantenimiento->tipMan)
                                            @case('preventivo') 🛡️ Preventivo @break
                                            @case('correctivo') 🔧 Correctivo @break
                                            @case('predictivo') 📊 Predictivo @break
                                            @default ❓ {{ ucfirst($mantenimiento->tipMan) }}
                                        @endswitch
                                    </span>
                                </div>
                                
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-medium text-gray-500">Fecha:</span>
                                    <span class="text-sm font-semibold {{ $mantenimiento->fecMan < now()->toDateString() && $mantenimiento->estMan !== 'completado' ? 'text-red-600' : 'text-gray-800' }}">
                                        📅 {{ \Carbon\Carbon::parse($mantenimiento->fecMan)->format('d/m/Y') }}
                                    </span>
                                </div>

                                @if($mantenimiento->desMan)
                                <div class="bg-gray-50 rounded-lg p-3">
                                    <p class="text-xs text-gray-600 leading-relaxed">{{ \Str::limit($mantenimiento->desMan, 80) }}</p>
                                </div>
                                @endif

                                @if($mantenimiento->estMan === 'completado' && $mantenimiento->resMan)
                                <div class="bg-green-50 rounded-lg p-3 border border-green-200">
                                    <p class="text-xs font-medium text-green-800 mb-1">✅ Resultado:</p>
                                    <p class="text-xs text-green-700">{{ $mantenimiento->resMan }}</p>
                                </div>
                                @endif
                            </div>

                            <!-- Acciones -->
                            <div class="flex space-x-2">
                                @if($mantenimiento->estMan !== 'completado')
                                <button wire:click="confirmCompletar({{ $mantenimiento->idMan }})" 
                                        class="flex-1 bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600 text-white text-xs font-medium py-2 px-3 rounded-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                                    <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Completar
                                </button>
                                @endif

                                @if($mantenimiento->estMan === 'pendiente')
                                <button wire:click="confirmDelete({{ $mantenimiento->idMan }})" 
                                        class="bg-gradient-to-r from-red-500 to-pink-500 hover:from-red-600 hover:to-pink-600 text-white text-xs font-medium py-2 px-3 rounded-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                                @endif

                                <a href="{{ route('inventario.mantenimientos.show', $mantenimiento->idMan) }}" wire:navigate
                                   class="bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600 text-white text-xs font-medium py-2 px-3 rounded-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <!-- Vista de Lista -->
                <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-xl border border-white/20 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Herramienta</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Tipo</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Estado</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Fecha</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Descripción</th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($mantenimientos as $mantenimiento)
                                <tr class="hover:bg-blue-50/50 transition-colors duration-200">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center space-x-3">
                                            <div class="p-2 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg">
                                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 8.172V5L8 4z"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900">{{ $mantenimiento->herramienta->nomHer ?? $mantenimiento->nomHerMan ?? 'Sin herramienta' }}</p>
                                                @if($mantenimiento->herramienta && $mantenimiento->herramienta->catHer)
                                                <p class="text-xs text-gray-500">{{ $mantenimiento->herramienta->catHer }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            @switch($mantenimiento->tipMan)
                                                @case('preventivo') 🛡️ Preventivo @break
                                                @case('correctivo') 🔧 Correctivo @break
                                                @case('predictivo') 📊 Predictivo @break
                                                @default ❓ {{ ucfirst($mantenimiento->tipMan) }}
                                            @endswitch
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $estadoConfig = [
                                                'pendiente' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-800', 'icon' => '🔄'],
                                                'en proceso' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'icon' => '⚙️'],
                                                'completado' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'icon' => '✅']
                                            ];
                                            $config = $estadoConfig[$mantenimiento->estMan] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'icon' => '❓'];
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $config['bg'] }} {{ $config['text'] }}">
                                            {{ $config['icon'] }} {{ ucfirst($mantenimiento->estMan) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm {{ $mantenimiento->fecMan < now()->toDateString() && $mantenimiento->estMan !== 'completado' ? 'text-red-600 font-semibold' : 'text-gray-900' }}">
                                            📅 {{ \Carbon\Carbon::parse($mantenimiento->fecMan)->format('d/m/Y') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm text-gray-600 max-w-xs truncate">{{ $mantenimiento->desMan ?? '-' }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end space-x-2">
                                            @if($mantenimiento->estMan !== 'completado')
                                            <button wire:click="confirmCompletar({{ $mantenimiento->idMan }})" 
                                                    class="bg-green-500 hover:bg-green-600 text-white p-2 rounded-lg shadow hover:shadow-lg transition-all duration-200" title="Completar">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            </button>
                                            @endif

                                            @if($mantenimiento->estMan === 'pendiente')
                                            <button wire:click="confirmDelete({{ $mantenimiento->idMan }})" 
                                                    class="bg-red-500 hover:bg-red-600 text-white p-2 rounded-lg shadow hover:shadow-lg transition-all duration-200" title="Eliminar">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                            @endif

                                            <a href="{{ route('inventario.mantenimientos.show', $mantenimiento->idMan) }}" wire:navigate
                                               class="bg-blue-500 hover:bg-blue-600 text-white p-2 rounded-lg shadow hover:shadow-lg transition-all duration-200" title="Ver detalles">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Paginación -->
            <div class="mt-8">
                {{ $mantenimientos->links() }}
            </div>

        @else
            <!-- Estado Vacío -->
            <div class="text-center py-16">
                <div class="max-w-md mx-auto">
                    <div class="p-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full w-32 h-32 mx-auto mb-6 flex items-center justify-center shadow-2xl">
                        <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">No se encontraron mantenimientos</h3>
                    <p class="text-gray-600 mb-6">No hay mantenimientos que coincidan con los filtros aplicados.</p>
                    <div class="space-y-3">
                        <button wire:click="limpiarFiltros" 
                                class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-medium rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Limpiar Filtros
                        </button>
                        <br>
                        <a href="{{ route('inventario.mantenimientos.create') }}" wire:navigate
                           class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-medium rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Crear Primer Mantenimiento
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Modal Completar Mantenimiento -->
    @if($showCompletarModal)
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 z-50" wire:click.self="reset(['showCompletarModal', 'mantenimientoToComplete'])">
        <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full p-8 transform transition-all duration-300">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center space-x-3">
                    <div class="p-3 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900">Completar Mantenimiento</h3>
                </div>
                <button wire:click="reset(['showCompletarModal', 'mantenimientoToComplete'])" 
                        class="text-gray-400 hover:text-gray-600 transition-colors duration-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form wire:submit="completarMantenimiento" class="space-y-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Resultado del Mantenimiento *</label>
                    <input wire:model="resultado" type="text" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200"
                           placeholder="Ej: Mantenimiento realizado correctamente" maxlength="100" required>
                    @error('resultado') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Observaciones Adicionales</label>
                    <textarea wire:model="observaciones" rows="4" 
                              class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 resize-none"
                              placeholder="Detalles adicionales, repuestos utilizados, recomendaciones..." maxlength="500"></textarea>
                    @error('observaciones') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex space-x-3 pt-4">
                    <button type="button" wire:click="reset(['showCompletarModal', 'mantenimientoToComplete'])"
                            class="flex-1 px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl shadow-lg hover:shadow-xl transition-all duration-200">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="flex-1 px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-medium rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                        ✅ Completar Mantenimiento
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Modal Confirmar Eliminación -->
    @if($showDeleteModal)
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 z-50" wire:click.self="cancelDelete">
        <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-8 transform transition-all duration-300">
            <div class="text-center">
                <div class="p-4 bg-gradient-to-br from-red-500 to-pink-600 rounded-full w-20 h-20 mx-auto mb-6 flex items-center justify-center">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Confirmar Eliminación</h3>
                <p class="text-gray-600 mb-8">¿Estás seguro de que deseas eliminar este mantenimiento? Esta acción no se puede deshacer.</p>
                
                <div class="flex space-x-3">
                    <button wire:click="cancelDelete" 
                            class="flex-1 px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl shadow-lg hover:shadow-xl transition-all duration-200">
                        Cancelar
                    </button>
                    <button wire:click="deleteMantenimiento" 
                            class="flex-1 px-6 py-3 bg-gradient-to-r from-red-600 to-pink-600 hover:from-red-700 hover:to-pink-700 text-white font-medium rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                        🗑️ Eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>