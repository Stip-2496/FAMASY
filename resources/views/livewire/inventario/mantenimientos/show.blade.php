<?php
use App\Models\Mantenimiento;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component {
    public Mantenimiento $mantenimiento;
    public $showCompleteModal = false;
    public $resMan = '';
    public $obsMan = '';

    public function mount(Mantenimiento $mantenimiento)
    {
        $this->mantenimiento = $mantenimiento->load(['herramienta', 'movimientos']);
    }

    public function confirmComplete()
    {
        $this->validate([
            'resMan' => 'required|string|max:100',
            'obsMan' => 'nullable|string'
        ]);

        $this->mantenimiento->update([
            'estMan' => 'completado',
            'resMan' => $this->resMan,
            'obsMan' => $this->obsMan
        ]);

        $this->showCompleteModal = false;
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Mantenimiento completado exitosamente'
        ]);
    }

    public function deleteMantenimiento()
    {
        try {
            $this->mantenimiento->delete();
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Mantenimiento eliminado correctamente'
            ]);

            $this->redirect(route('inventario.mantenimientos.index'), navigate: true);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al eliminar el mantenimiento: ' . $e->getMessage()
            ]);
        }
    }
}; ?>

@section('title', 'Detalles del Mantenimiento #' . $mantenimiento->idMan)

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Header Ultra Moderno -->
        <div class="mb-8">
            <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 p-8 relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-green-500/5 to-emerald-500/5"></div>
                <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-green-400/10 to-emerald-600/10 rounded-full -mr-16 -mt-16"></div>
                
                <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center space-x-4 mb-6 lg:mb-0">
                        @php
                            $iconConfig = match($mantenimiento->estMan) {
                                'pendiente' => ['gradient' => 'from-orange-500 to-red-500', 'icon_color' => 'text-orange-600'],
                                'en proceso' => ['gradient' => 'from-yellow-500 to-amber-500', 'icon_color' => 'text-yellow-600'],
                                'completado' => ['gradient' => 'from-green-500 to-emerald-500', 'icon_color' => 'text-green-600'],
                                default => ['gradient' => 'from-green-500 to-emerald-500', 'icon_color' => 'text-green-600']
                            };
                        @endphp
                        <div class="p-4 bg-gradient-to-br {{ $iconConfig['gradient'] }} rounded-2xl shadow-xl transform rotate-3 hover:rotate-0 transition-transform duration-300">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 616 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-4xl font-black bg-gradient-to-r from-gray-900 via-gray-800 to-green-800 bg-clip-text text-transparent leading-tight">
                                Mantenimiento #{{ $mantenimiento->idMan }}
                            </h1>
                            <p class="text-gray-600 mt-1 text-lg">Vista detallada y control completo del mantenimiento</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <a href="{{ route('inventario.mantenimientos.index') }}" wire:navigate
                           class="group relative inline-flex items-center px-6 py-3 bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                            <svg class="w-5 h-5 mr-2 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            <span class="relative z-10">Volver al Listado</span>
                        </a>
                        @if($mantenimiento->estMan !== 'completado')
                        <a href="{{ route('inventario.mantenimientos.edit', $mantenimiento) }}" wire:navigate
                           class="group relative inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                            <svg class="w-5 h-5 mr-2 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            <span class="relative z-10">Editar Mantenimiento</span>
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            <!-- Información Principal -->
            <div class="xl:col-span-2 space-y-8">
                <!-- Datos del Mantenimiento -->
                <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 overflow-hidden">
                    <div class="h-2 bg-gradient-to-r from-green-500 to-emerald-500"></div>
                    <div class="p-8">
                        <div class="flex items-center space-x-3 mb-8">
                            <div class="p-3 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl shadow-lg">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900">Información del Mantenimiento</h2>
                                <p class="text-gray-600">Detalles completos y estado actual</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Tipo de Mantenimiento -->
                            <div class="group">
                                <label class="block text-sm font-bold text-gray-700 mb-3">🔧 Tipo de Mantenimiento</label>
                                <div class="flex items-center">
                                    @php
                                        $tipoConfig = [
                                            'preventivo' => ['bg' => 'from-green-500 to-emerald-500', 'text' => 'text-white', 'icon' => '🛡️'],
                                            'correctivo' => ['bg' => 'from-red-500 to-pink-500', 'text' => 'text-white', 'icon' => '🔧'],
                                            'predictivo' => ['bg' => 'from-blue-500 to-indigo-500', 'text' => 'text-white', 'icon' => '📊']
                                        ];
                                        $config = $tipoConfig[$mantenimiento->tipMan] ?? ['bg' => 'from-gray-500 to-gray-600', 'text' => 'text-white', 'icon' => '❓'];
                                    @endphp
                                    <span class="inline-flex items-center px-5 py-3 rounded-2xl text-sm font-bold bg-gradient-to-r {{ $config['bg'] }} {{ $config['text'] }} shadow-xl group-hover:scale-105 transition-transform duration-300">
                                        {{ $config['icon'] }} {{ ucfirst($mantenimiento->tipMan) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Estado -->
                            <div class="group">
                                <label class="block text-sm font-bold text-gray-700 mb-3">📊 Estado Actual</label>
                                <div class="flex items-center">
                                    @php
                                        $estadoConfig = [
                                            'pendiente' => ['bg' => 'from-orange-500 to-red-500', 'text' => 'text-white', 'icon' => '🔄'],
                                            'en proceso' => ['bg' => 'from-yellow-500 to-amber-500', 'text' => 'text-white', 'icon' => '⚙️'],
                                            'completado' => ['bg' => 'from-green-500 to-emerald-500', 'text' => 'text-white', 'icon' => '✅']
                                        ];
                                        $config = $estadoConfig[$mantenimiento->estMan] ?? ['bg' => 'from-gray-500 to-gray-600', 'text' => 'text-white', 'icon' => '❓'];
                                    @endphp
                                    <span class="inline-flex items-center px-5 py-3 rounded-2xl text-sm font-bold bg-gradient-to-r {{ $config['bg'] }} {{ $config['text'] }} shadow-xl group-hover:scale-105 transition-transform duration-300">
                                        {{ $config['icon'] }} {{ ucfirst($mantenimiento->estMan) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Fecha del Mantenimiento -->
                            <div class="group">
                                <label class="block text-sm font-bold text-gray-700 mb-3">📅 Fecha Programada</label>
                                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-2xl p-4 group-hover:shadow-lg transition-shadow duration-300">
                                    <div class="flex items-center text-blue-900">
                                        <div class="p-2 bg-blue-500 rounded-lg mr-3">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="font-bold text-lg">{{ $mantenimiento->fecMan->format('d/m/Y') }}</p>
                                            <p class="text-sm text-blue-700">{{ $mantenimiento->fecMan->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Fecha de creación -->
                            <div class="group">
                                <label class="block text-sm font-bold text-gray-700 mb-3">📝 Fecha de Registro</label>
                                <div class="bg-gradient-to-br from-purple-50 to-pink-50 border-2 border-purple-200 rounded-2xl p-4 group-hover:shadow-lg transition-shadow duration-300">
                                    <div class="flex items-center text-purple-900">
                                        <div class="p-2 bg-purple-500 rounded-lg mr-3">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="font-bold text-lg">{{ $mantenimiento->created_at->format('d/m/Y H:i') }}</p>
                                            <p class="text-sm text-purple-700">{{ $mantenimiento->created_at->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Descripción -->
                        @if($mantenimiento->desMan)
                        <div class="mt-8">
                            <label class="block text-sm font-bold text-gray-700 mb-3">📄 Descripción del Mantenimiento</label>
                            <div class="bg-gradient-to-br from-gray-50 to-blue-50 border-2 border-gray-200 rounded-2xl p-6 shadow-lg">
                                <div class="flex items-start space-x-3">
                                    <div class="p-2 bg-gray-500 rounded-lg">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path>
                                        </svg>
                                    </div>
                                    <p class="text-gray-900 leading-relaxed flex-1">{{ $mantenimiento->desMan }}</p>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Resultado (solo si está completado) -->
                        @if($mantenimiento->resMan && $mantenimiento->estMan === 'completado')
                        <div class="mt-8">
                            <label class="block text-sm font-bold text-gray-700 mb-3">✅ Resultado del Mantenimiento</label>
                            <div class="bg-gradient-to-br from-green-50 to-emerald-50 border-2 border-green-200 rounded-2xl p-6 shadow-lg">
                                <div class="flex items-start space-x-3">
                                    <div class="p-3 bg-green-500 rounded-xl shadow-lg">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="font-bold text-green-900 text-lg mb-2">Mantenimiento Completado</h4>
                                        <p class="text-green-800 leading-relaxed">{{ $mantenimiento->resMan }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Observaciones -->
                        @if($mantenimiento->obsMan)
                        <div class="mt-8">
                            <label class="block text-sm font-bold text-gray-700 mb-3">💭 Observaciones Adicionales</label>
                            <div class="bg-gradient-to-br from-amber-50 to-yellow-50 border-2 border-amber-200 rounded-2xl p-6 shadow-lg">
                                <div class="flex items-start space-x-3">
                                    <div class="p-2 bg-amber-500 rounded-lg">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                                        </svg>
                                    </div>
                                    <p class="text-amber-900 leading-relaxed flex-1">{{ $mantenimiento->obsMan }}</p>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Movimientos Relacionados -->
                @if($mantenimiento->movimientos && $mantenimiento->movimientos->count() > 0)
                <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 overflow-hidden">
                    <div class="h-2 bg-gradient-to-r from-blue-500 to-indigo-500"></div>
                    <div class="p-8">
                        <div class="flex items-center space-x-3 mb-6">
                            <div class="p-3 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl shadow-lg">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900">Movimientos de Inventario</h3>
                                <p class="text-gray-600">Registros relacionados con este mantenimiento</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            @foreach($mantenimiento->movimientos as $movimiento)
                            <div class="bg-gradient-to-br from-white to-blue-50 border-2 border-blue-200 rounded-2xl p-6 shadow-lg hover:shadow-xl transition-shadow duration-300">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <div class="p-3 bg-gradient-to-br from-blue-500 to-indigo-500 rounded-xl">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="font-bold text-lg text-gray-900">{{ ucfirst($movimiento->tipMovInv) }}</p>
                                            <p class="text-sm text-gray-600">{{ $movimiento->fecMovInv->format('d/m/Y H:i') }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-lg font-bold text-gray-900">{{ number_format($movimiento->cantMovInv, 2) }} {{ $movimiento->uniMovInv }}</p>
                                        @if($movimiento->costoTotInv)
                                        <p class="text-sm text-gray-600 font-medium">${{ number_format($movimiento->costoTotInv, 2) }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Panel Lateral -->
            <div class="xl:col-span-1 space-y-8">
                <!-- Herramienta -->
                <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 overflow-hidden">
                    <div class="h-2 bg-gradient-to-r from-blue-500 to-indigo-500"></div>
                    <div class="p-6">
                        <div class="flex items-center space-x-3 mb-6">
                            <div class="p-3 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl shadow-lg">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 8.172V5L8 4z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900">Herramienta</h3>
                                <p class="text-sm text-gray-600">Información del equipo</p>
                            </div>
                        </div>

                        @if($mantenimiento->herramienta)
                        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-2xl p-6 mb-6">
                            <div class="flex items-center space-x-4 mb-4">
                                <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-500 rounded-2xl flex items-center justify-center shadow-lg">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 8.172V5L8 4z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-bold text-lg text-blue-900">{{ $mantenimiento->herramienta->nomHer }}</p>
                                    <p class="text-sm text-blue-700">{{ $mantenimiento->herramienta->catHer ?? 'Sin categoría' }}</p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                @if($mantenimiento->herramienta->marHer)
                                <div>
                                    <label class="block text-xs font-bold text-blue-700 mb-1">🏷️ Marca</label>
                                    <p class="text-blue-900 font-medium">{{ $mantenimiento->herramienta->marHer }}</p>
                                </div>
                                @endif

                                @if($mantenimiento->herramienta->modHer)
                                <div>
                                    <label class="block text-xs font-bold text-blue-700 mb-1">📦 Modelo</label>
                                    <p class="text-blue-900 font-medium">{{ $mantenimiento->herramienta->modHer }}</p>
                                </div>
                                @endif

                                @if($mantenimiento->herramienta->estHer)
                                <div>
                                    <label class="block text-xs font-bold text-blue-700 mb-1">⚡ Estado</label>
                                    <p class="text-blue-900 font-medium">{{ ucfirst($mantenimiento->herramienta->estHer) }}</p>
                                </div>
                                @endif
                            </div>
                        </div>

                        <a href="{{ route('inventario.herramientas.show', $mantenimiento->herramienta) }}" wire:navigate
                           class="w-full group relative inline-flex items-center justify-center px-6 py-4 bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                            <svg class="w-5 h-5 mr-2 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 616 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            <span class="relative z-10">Ver Herramienta Completa</span>
                        </a>
                        @else
                        <div class="bg-gradient-to-br from-gray-50 to-red-50 border-2 border-gray-200 rounded-2xl p-6 text-center">
                            <div class="p-4 bg-gray-400 rounded-2xl w-16 h-16 mx-auto mb-4 flex items-center justify-center">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.314 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                            </div>
                            <p class="text-gray-600 font-medium">Herramienta no encontrada</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Panel de Acciones -->
                <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 overflow-hidden">
                    <div class="h-2 bg-gradient-to-r from-emerald-500 to-green-500"></div>
                    <div class="p-6">
                        <div class="flex items-center space-x-3 mb-6">
                            <div class="p-3 bg-gradient-to-br from-emerald-500 to-green-600 rounded-xl shadow-lg">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900">Acciones Rápidas</h3>
                                <p class="text-sm text-gray-600">Controles disponibles</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            @if($mantenimiento->estMan !== 'completado')
                            <a href="{{ route('inventario.mantenimientos.edit', $mantenimiento) }}" wire:navigate
                               class="w-full group relative inline-flex items-center justify-center px-6 py-4 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                                <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                                <svg class="w-5 h-5 mr-3 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                <span class="relative z-10">✏️ Editar Mantenimiento</span>
                            </a>
                            @endif

                            @if($mantenimiento->estMan === 'en proceso')
                            <button wire:click="$set('showCompleteModal', true)" 
                                    class="w-full group relative inline-flex items-center justify-center px-6 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                                <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                                <svg class="w-5 h-5 mr-3 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="relative z-10">✅ Completar Mantenimiento</span>
                            </button>
                            @endif

                            @if($mantenimiento->estMan === 'pendiente')
                            <button wire:click="deleteMantenimiento" 
                                    onclick="return confirm('¿Está seguro de eliminar este mantenimiento? Esta acción no se puede deshacer.')"
                                    class="w-full group relative inline-flex items-center justify-center px-6 py-4 bg-gradient-to-r from-red-600 to-pink-600 hover:from-red-700 hover:to-pink-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                                <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                                <svg class="w-5 h-5 mr-3 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                <span class="relative z-10">🗑️ Eliminar Mantenimiento</span>
                            </button>
                            @endif

                            <a href="{{ route('inventario.mantenimientos.create') }}" wire:navigate
                               class="w-full group relative inline-flex items-center justify-center px-6 py-4 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                                <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                                <svg class="w-5 h-5 mr-3 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                <span class="relative z-10">➕ Nuevo Mantenimiento</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para completar mantenimiento - Ultra Moderno -->
@if($showCompleteModal)
<div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 z-50" wire:click.self="$set('showCompleteModal', false)">
    <div class="bg-white/95 backdrop-blur-xl rounded-3xl shadow-2xl max-w-lg w-full p-8 transform transition-all duration-300 border border-white/20">
        <div class="text-center">
            <div class="p-4 bg-gradient-to-br from-green-500 to-emerald-600 rounded-full w-20 h-20 mx-auto mb-6 flex items-center justify-center shadow-xl">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-3">Completar Mantenimiento</h3>
            <p class="text-gray-600 mb-8">Registre los resultados del mantenimiento realizado</p>
            
            <div class="text-left space-y-6">
                <div>
                    <label for="resMan" class="block text-sm font-bold text-gray-700 mb-3">
                        ✅ Resultado del Mantenimiento <span class="text-red-500">*</span>
                    </label>
                    <div class="relative group">
                        <textarea wire:model="resMan" id="resMan" rows="4" required maxlength="100"
                                  placeholder="Describa detalladamente el resultado del mantenimiento realizado..."
                                  class="w-full px-5 py-4 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-500 transition-all duration-300 group-hover:shadow-xl resize-none @error('resMan') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"></textarea>
                        <div class="absolute inset-0 bg-gradient-to-r from-green-500/5 to-emerald-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                    </div>
                    @error('resMan') 
                    <p class="mt-2 text-sm text-red-600 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        {{ $message }}
                    </p>
                    @enderror
                </div>

                <div>
                    <label for="obsMan" class="block text-sm font-bold text-gray-700 mb-3">
                        💭 Observaciones Adicionales
                    </label>
                    <div class="relative group">
                        <textarea wire:model="obsMan" id="obsMan" rows="3"
                                  placeholder="Observaciones, recomendaciones o notas adicionales (opcional)..."
                                  class="w-full px-5 py-4 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300 group-hover:shadow-xl resize-none"></textarea>
                        <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-purple-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                    </div>
                </div>

                <div class="flex space-x-4 pt-4">
                    <button wire:click="$set('showCompleteModal', false)" 
                            class="flex-1 px-6 py-4 bg-gradient-to-r from-gray-400 to-gray-500 hover:from-gray-500 hover:to-gray-600 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300">
                        ❌ Cancelar
                    </button>
                    <button wire:click="confirmComplete" 
                            class="flex-1 px-6 py-4 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300">
                        ✅ Completar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Script para notificaciones -->
<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('notify', (event) => {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });
            
            Toast.fire({
                icon: event.type,
                title: event.message
            });
        });
    });
</script>