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

@section('title', 'Detalles del Mantenimiento')

<div class="flex items-center justify-center min-h-screen py-3">
    <div class="w-full max-w-7xl mx-auto bg-white/80 backdrop-blur-xl shadow rounded-3xl p-3 relative border border-white/20">
        <!-- Encabezado -->
        <div class="text-center mb-3">
            <div class="flex justify-center mb-1">
                <div class="p-2 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl shadow-xl transform rotate-3 hover:rotate-0 transition-transform duration-300">
                    <div class="w-4 h-4 text-white flex items-center justify-center">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <h1 class="text-base font-black bg-gradient-to-r from-gray-900 via-gray-800 to-green-800 bg-clip-text text-transparent mb-1">
                Mantenimiento
            </h1>
            <p class="text-gray-600 text-xs">Vista detallada del mantenimiento</p>
        </div>

        <!-- Botones -->
        <div class="absolute top-2 right-2 flex space-x-2">
            @if($mantenimiento->estMan !== 'completado')
            <a href="{{ route('inventario.mantenimientos.edit', $mantenimiento) }}" wire:navigate
               class="group relative inline-flex items-center px-2 py-1 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                <svg class="w-3 h-3 mr-1 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                <span class="relative z-10 text-xs">Editar</span>
            </a>
            @endif
            <a href="{{ route('inventario.mantenimientos.index') }}" wire:navigate
               class="group relative inline-flex items-center px-2 py-1 bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                <svg class="w-3 h-3 mr-1 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span class="relative z-10 text-xs">Volver</span>
            </a>
        </div>

        <!-- Tarjeta de información principal -->
        <div class="border border-gray-300 rounded-3xl overflow-hidden bg-gradient-to-br from-white to-gray-50">
            <div class="h-1.5 bg-gradient-to-r from-green-500 to-emerald-600"></div>
            
            <div class="p-2">
                <!-- Grid de información -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-2">
                    <!-- Información del Mantenimiento -->
                    <div class="bg-white rounded-2xl p-2 border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-300">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl shadow-md">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xs font-bold text-gray-900">Información General</h3>
                        </div>
                        <div class="bg-green-50/50 border border-green-200 rounded-xl p-2">
                            <div class="space-y-2">
                                <div class="flex items-center justify-between py-1 border-b border-green-100">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                        </svg>
                                        <span class="text-xs text-black font-medium">Tipo</span>
                                    </div>
                                    @php
                                        $tipoConfig = [
                                            'preventivo' => ['bg' => 'from-green-500 to-emerald-500', 'text' => 'text-white'],
                                            'correctivo' => ['bg' => 'from-red-500 to-pink-500', 'text' => 'text-white'],
                                            'predictivo' => ['bg' => 'from-blue-500 to-indigo-500', 'text' => 'text-white']
                                        ];
                                        $config = $tipoConfig[$mantenimiento->tipMan] ?? ['bg' => 'from-gray-500 to-gray-600', 'text' => 'text-white'];
                                    @endphp
                                    <span class="inline-flex items-center text-xs font-semibold px-1.5 py-0.5 rounded-full bg-gradient-to-r {{ $config['bg'] }} {{ $config['text'] }} shadow-sm">
                                        {{ ucfirst($mantenimiento->tipMan) }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-green-100">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span class="text-xs text-black font-medium">Estado</span>
                                    </div>
                                    @php
                                        $estadoConfig = [
                                            'pendiente' => ['bg' => 'from-orange-500 to-red-500', 'text' => 'text-white'],
                                            'en proceso' => ['bg' => 'from-yellow-500 to-amber-500', 'text' => 'text-white'],
                                            'completado' => ['bg' => 'from-green-500 to-emerald-500', 'text' => 'text-white']
                                        ];
                                        $config = $estadoConfig[$mantenimiento->estMan] ?? ['bg' => 'from-gray-500 to-gray-600', 'text' => 'text-white'];
                                    @endphp
                                    <span class="inline-flex items-center text-xs font-semibold px-1.5 py-0.5 rounded-full bg-gradient-to-r {{ $config['bg'] }} {{ $config['text'] }} shadow-sm">
                                        {{ ucfirst($mantenimiento->estMan) }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-green-100">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <span class="text-xs text-black font-medium">Fecha Programada</span>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs text-black font-semibold">{{ $mantenimiento->fecMan->format('d/m/Y') }}</p>
                                        <p class="text-2xs text-gray-600">{{ $mantenimiento->fecMan->diffForHumans() }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between py-1">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span class="text-xs text-black font-medium">Fecha de Registro</span>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs text-black font-semibold">{{ $mantenimiento->created_at->format('d/m/Y H:i') }}</p>
                                        <p class="text-2xs text-gray-600">{{ $mantenimiento->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Herramienta Asociada -->
                    <div class="bg-white rounded-2xl p-2 border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-300">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl shadow-md">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 8.172V5L8 4z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xs font-bold text-gray-900">Herramienta</h3>
                        </div>

                        @if($mantenimiento->herramienta)
                        <div class="bg-blue-50/50 border border-blue-200 rounded-xl p-2">
                            <div class="flex items-center space-x-3 mb-3 pb-2 border-b border-blue-100">
                                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-500 rounded-xl flex items-center justify-center shadow-md">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 8.172V5L8 4z"></path>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <p class="font-bold text-sm text-blue-900">{{ $mantenimiento->herramienta->nomHer }}</p>
                                    <p class="text-xs text-blue-700">{{ $mantenimiento->herramienta->catHer ?? 'Sin categoría' }}</p>
                                </div>
                            </div>

                            <div class="space-y-2">
                                @if($mantenimiento->herramienta->marHer)
                                <div class="flex items-center justify-between py-1 border-b border-blue-100">
                                    <span class="text-xs text-blue-700 font-medium">Marca</span>
                                    <p class="text-xs text-blue-900 font-semibold">{{ $mantenimiento->herramienta->marHer }}</p>
                                </div>
                                @endif
                                @if($mantenimiento->herramienta->modHer)
                                <div class="flex items-center justify-between py-1 border-b border-blue-100">
                                    <span class="text-xs text-blue-700 font-medium">Modelo</span>
                                    <p class="text-xs text-blue-900 font-semibold">{{ $mantenimiento->herramienta->modHer }}</p>
                                </div>
                                @endif
                                @if($mantenimiento->herramienta->estHer)
                                <div class="flex items-center justify-between py-1">
                                    <span class="text-xs text-blue-700 font-medium">Estado</span>
                                    <p class="text-xs text-blue-900 font-semibold">{{ ucfirst($mantenimiento->herramienta->estHer) }}</p>
                                </div>
                                @endif
                            </div>

                            <a href="{{ route('inventario.herramientas.show', $mantenimiento->herramienta) }}" wire:navigate
                               class="mt-3 w-full group relative inline-flex items-center justify-center px-3 py-2 bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                                <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                                <svg class="w-3 h-3 mr-1 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <span class="relative z-10 text-xs">Ver Herramienta</span>
                            </a>
                        </div>
                        @else
                        <div class="bg-gray-50 border border-gray-200 rounded-xl p-3 text-center">
                            <svg class="w-8 h-8 text-gray-400 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.314 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            <p class="text-gray-600 font-medium text-xs">Herramienta no encontrada</p>
                        </div>
                        @endif
                    </div>

                    <!-- Descripción -->
                    @if($mantenimiento->desMan)
                    <div class="bg-white rounded-2xl p-2 border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-300">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-gray-500 to-gray-600 rounded-xl shadow-md">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path>
                                </svg>
                            </div>
                            <h3 class="text-xs font-bold text-gray-900">Descripción</h3>
                        </div>
                        <div class="bg-gray-50/50 border border-gray-200 rounded-xl p-2">
                            <p class="text-xs text-gray-900 leading-relaxed">{{ $mantenimiento->desMan }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Resultado (solo si está completado) -->
                    @if($mantenimiento->resMan && $mantenimiento->estMan === 'completado')
                    <div class="bg-white rounded-2xl p-2 border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-300">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl shadow-md">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <h3 class="text-xs font-bold text-gray-900">Resultado</h3>
                        </div>
                        <div class="bg-green-50/50 border border-green-200 rounded-xl p-2">
                            <div class="flex items-start space-x-2">
                                <div class="p-1 bg-green-500 rounded-lg">
                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <p class="text-xs text-green-900 leading-relaxed flex-1">{{ $mantenimiento->resMan }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Observaciones -->
                    @if($mantenimiento->obsMan)
                    <div class="bg-white rounded-2xl p-2 border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-300">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-amber-500 to-yellow-600 rounded-xl shadow-md">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xs font-bold text-gray-900">Observaciones</h3>
                        </div>
                        <div class="bg-amber-50/50 border border-amber-200 rounded-xl p-2">
                            <p class="text-xs text-amber-900 leading-relaxed">{{ $mantenimiento->obsMan }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Movimientos Relacionados -->
        @if($mantenimiento->movimientos && $mantenimiento->movimientos->count() > 0)
        <div class="mt-2 border border-gray-300 rounded-3xl overflow-hidden bg-gradient-to-br from-white to-gray-50">
            <div class="h-1.5 bg-gradient-to-r from-blue-500 to-indigo-500"></div>
            
            <div class="p-2">
                <div class="bg-white rounded-2xl p-2 border border-gray-200 shadow-sm">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center space-x-2">
                            <div class="p-1.5 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl shadow-md">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                </svg>
                            </div>
                            <h3 class="text-xs font-bold text-gray-900">Movimientos de Inventario</h3>
                        </div>
                        <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2 py-0.5 rounded-full">
                            {{ $mantenimiento->movimientos->count() }} movimientos
                        </span>
                    </div>

                    <div class="bg-blue-50/50 border border-blue-200 rounded-xl p-2">
                        <div class="space-y-2">
                            @foreach($mantenimiento->movimientos as $movimiento)
                            <div class="bg-white border border-blue-200 rounded-xl p-2 hover:shadow-md transition-shadow duration-300">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-2">
                                        <div class="p-1.5 bg-gradient-to-br from-blue-500 to-indigo-500 rounded-lg">
                                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="font-bold text-xs text-gray-900">{{ ucfirst($movimiento->tipMovInv) }}</p>
                                            <p class="text-2xs text-gray-600">{{ $movimiento->fecMovInv->format('d/m/Y H:i') }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs font-bold text-gray-900">{{ number_format($movimiento->cantMovInv, 2) }} {{ $movimiento->uniMovInv }}</p>
                                        @if($movimiento->costoTotInv)
                                        <p class="text-2xs text-green-600 font-medium">${{ number_format($movimiento->costoTotInv, 2) }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Modal para completar mantenimiento -->
@if($showCompleteModal)
<div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 z-50" wire:click.self="$set('showCompleteModal', false)">
    <div class="bg-white/95 backdrop-blur-xl rounded-3xl shadow-2xl max-w-lg w-full p-6 transform transition-all duration-300 border border-white/20">
        <div class="text-center">
            <div class="p-3 bg-gradient-to-br from-green-500 to-emerald-600 rounded-full w-16 h-16 mx-auto mb-4 flex items-center justify-center shadow-xl">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Completar Mantenimiento</h3>
            <p class="text-gray-600 mb-6 text-sm">Registre los resultados del mantenimiento realizado</p>
            
            <div class="text-left space-y-4">
                <div>
                    <label for="resMan" class="block text-xs font-bold text-gray-700 mb-2">
                        Resultado del Mantenimiento <span class="text-red-500">*</span>
                    </label>
                    <div class="relative group">
                        <textarea wire:model="resMan" id="resMan" rows="3" required maxlength="100"
                                  placeholder="Describa detalladamente el resultado del mantenimiento realizado..."
                                  class="w-full px-3 py-2 bg-white/50 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-500 transition-all duration-300 group-hover:shadow-xl resize-none text-xs @error('resMan') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"></textarea>
                        <div class="absolute inset-0 bg-gradient-to-r from-green-500/5 to-emerald-500/5 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                    </div>
                    @error('resMan') 
                    <p class="mt-1 text-xs text-red-600 flex items-center">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        {{ $message }}
                    </p>
                    @enderror
                </div>

                <div>
                    <label for="obsMan" class="block text-xs font-bold text-gray-700 mb-2">
                        Observaciones Adicionales
                    </label>
                    <div class="relative group">
                        <textarea wire:model="obsMan" id="obsMan" rows="2"
                                  placeholder="Observaciones, recomendaciones o notas adicionales (opcional)..."
                                  class="w-full px-3 py-2 bg-white/50 border-2 border-gray-200 rounded-xl shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-300 group-hover:shadow-xl resize-none text-xs"></textarea>
                        <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-purple-500/5 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                    </div>
                </div>

                <div class="flex space-x-3 pt-3">
                    <button wire:click="$set('showCompleteModal', false)" 
                            class="flex-1 px-4 py-2 bg-gradient-to-r from-gray-400 to-gray-500 hover:from-gray-500 hover:to-gray-600 text-white font-bold rounded-xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 text-xs">
                        Cancelar
                    </button>
                    <button wire:click="confirmComplete" 
                            class="flex-1 px-4 py-2 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-bold rounded-xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 text-xs">
                        Completar
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