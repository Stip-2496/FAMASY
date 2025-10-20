<?php
use App\Models\PrestamoHerramienta;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component {
    public PrestamoHerramienta $prestamo;
    public $showReturnModal = false;
    public $fecDev;
    public $obsPre;

    public function mount(PrestamoHerramienta $prestamo): void
    {
        $this->prestamo = $prestamo;
        $this->fecDev = now()->format('Y-m-d\TH:i'); // Formato datetime-local
    }

    public function confirmReturn(): void
    {
        $this->validate([
            'fecDev' => 'required|date|after_or_equal:' . $this->prestamo->fecPre->format('Y-m-d'),
            'obsPre' => 'nullable|string'
        ]);

        $this->showReturnModal = true;
    }

    public function returnTool(): void
    {
        $this->prestamo->update([
            'fecDev' => $this->fecDev,
            'estPre' => 'devuelto',
            'obsPre' => $this->obsPre
        ]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Herramienta devuelta exitosamente'
        ]);

        $this->redirect(route('inventario.prestamos.index'), navigate: true);
    }

    public function deletePrestamo(): void
    {
        try {
            $this->prestamo->delete();
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Préstamo eliminado exitosamente'
            ]);

            $this->redirect(route('inventario.prestamos.index'), navigate: true);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al eliminar el préstamo: ' . $e->getMessage()
            ]);
        }
    }
}; ?>

@section('title', 'Detalles del Préstamo')

<div class="flex items-center justify-center min-h-screen py-3">
    <div class="w-full max-w-7xl mx-auto bg-white/80 backdrop-blur-xl shadow rounded-3xl p-3 relative border border-white/20">
        <!-- Encabezado -->
        <div class="text-center mb-3">
            <div class="flex justify-center mb-1">
                <div class="p-2 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl shadow-xl transform rotate-3 hover:rotate-0 transition-transform duration-300">
                    <div class="w-4 h-4 text-white flex items-center justify-center">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <h1 class="text-base font-black bg-gradient-to-r from-gray-900 via-gray-800 to-blue-800 bg-clip-text text-transparent mb-1">
                Detalles del Préstamo
            </h1>
            <p class="text-gray-600 text-xs">Información completa del préstamo</p>
        </div>

        <!-- Botones -->
        <div class="absolute top-2 right-2 flex space-x-2">
            @can('admin')
            @if($prestamo->estPre !== 'devuelto')
            <a href="{{ route('inventario.prestamos.edit', $prestamo) }}" wire:navigate
               class="group relative inline-flex items-center px-2 py-1 bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                <svg class="w-3 h-3 mr-1 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                <span class="relative z-10 text-xs">Editar</span>
            </a>
            @endif
            @endcan
            <a href="{{ route('inventario.prestamos.index') }}" wire:navigate
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
            <div class="h-1.5 bg-gradient-to-r from-[#000000] to-[#0066CC]"></div>
            
            <div class="p-2">
                <!-- Grid de información -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-2">
                    <!-- Información del Préstamo -->
                    <div class="bg-white rounded-2xl p-2 border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-300">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl shadow-md">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xs font-bold text-gray-900">Información del Préstamo</h3>
                        </div>
                        <div class="bg-blue-50/50 border border-blue-200 rounded-xl p-2">
                            <div class="space-y-2">
                                <!-- Estado -->
                                <div class="flex items-center justify-between py-1 border-b border-blue-100">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span class="text-xs text-black font-medium">Estado</span>
                                    </div>
                                    @php
                                        $estadoClasses = match($prestamo->estPre) {
                                            'prestado' => 'bg-gradient-to-r from-blue-500 to-blue-600 text-white',
                                            'devuelto' => 'bg-gradient-to-r from-green-500 to-green-600 text-white',
                                            'vencido' => 'bg-gradient-to-r from-red-500 to-red-600 text-white',
                                            default => 'bg-gradient-to-r from-gray-500 to-gray-600 text-white'
                                        };
                                    @endphp
                                    <span class="inline-flex items-center text-xs font-semibold px-1.5 py-0.5 rounded-full {{ $estadoClasses }} shadow-sm">
                                        {{ ucfirst($prestamo->estPre) }}
                                        @if($prestamo->estPre === 'vencido')
                                        <svg class="w-3 h-3 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        @endif
                                    </span>
                                </div>

                                <!-- Fecha de Préstamo -->
                                <div class="flex items-center justify-between py-1 border-b border-blue-100">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <span class="text-xs text-black font-medium">Fecha de Préstamo</span>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs text-black font-semibold">{{ $prestamo->fecPre->format('d/m/Y H:i') }}</p>
                                        <p class="text-2xs text-gray-500">{{ $prestamo->fecPre->diffForHumans() }}</p>
                                    </div>
                                </div>

                                <!-- Fecha de Devolución -->
                                <div class="flex items-center justify-between py-1 border-b border-blue-100">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            @if($prestamo->estPre === 'devuelto')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            @endif
                                        </svg>
                                        <span class="text-xs text-black font-medium">
                                            @if($prestamo->estPre === 'devuelto')
                                                Fecha de Devolución
                                            @else
                                                Devolución Programada
                                            @endif
                                        </span>
                                    </div>
                                    @if($prestamo->fecDev)
                                    <div class="text-right">
                                        <p class="text-xs text-black font-semibold">{{ $prestamo->fecDev->format('d/m/Y H:i') }}</p>
                                        <p class="text-2xs text-gray-500">{{ $prestamo->fecDev->diffForHumans() }}</p>
                                    </div>
                                    @else
                                    <div class="flex items-center text-gray-500">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span class="text-xs">No programada</span>
                                    </div>
                                    @endif
                                </div>

                                <!-- Tiempo Transcurrido -->
                                <div class="flex items-center justify-between py-1 border-b border-blue-100">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span class="text-xs text-black font-medium">Tiempo Transcurrido</span>
                                    </div>
                                    <div class="text-right">
                                        @php
                                            $dias = $prestamo->fecPre->diffInDays(now());
                                            $horas = $prestamo->fecPre->diffInHours(now()) % 24;
                                            $minutos = $prestamo->fecPre->diffInMinutes(now()) % 60;
                                        @endphp
                                        <p class="text-xs text-black font-semibold">
                                            @if($dias > 0)
                                                {{ $dias }}d 
                                            @endif
                                            @if($horas > 0)
                                                {{ $horas }}h 
                                            @endif
                                            {{ $minutos }}m
                                        </p>
                                        <p class="text-2xs text-gray-500">{{ $prestamo->fecPre->diffForHumans() }}</p>
                                    </div>
                                </div>

                                @if($prestamo->obsPre)
                                <div class="py-1">
                                    <div class="flex items-center space-x-2 mb-1">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                                        </svg>
                                        <span class="text-xs text-black font-medium">Observaciones</span>
                                    </div>
                                    <p class="text-xs text-black">{{ $prestamo->obsPre }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Información de Personas -->
                    <div class="bg-white rounded-2xl p-2 border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-300">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-md">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xs font-bold text-gray-900">Información de Personas</h3>
                        </div>
                        <div class="bg-purple-50/50 border border-purple-200 rounded-xl p-2">
                            <!-- Solicitante -->
                            <div class="mb-3 p-2 bg-white rounded-lg border border-purple-100">
                                <div class="flex items-center mb-2">
                                    <div class="w-6 h-6 bg-purple-100 rounded-full flex items-center justify-center">
                                        <svg class="w-3 h-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                    <span class="ml-2 text-xs font-medium text-gray-900">Solicitante</span>
                                </div>
                                @if($prestamo->solicitante)
                                <div class="space-y-1">
                                    <p class="text-xs font-semibold text-gray-900">
                                        {{ $prestamo->solicitante->nomUsu ?? '' }} {{ $prestamo->solicitante->apeUsu ?? '' }}
                                    </p>
                                    <p class="text-2xs text-gray-500">
                                        {{ $prestamo->solicitante->numDocUsu ?? 'Sin documento' }}
                                    </p>
                                </div>
                                @else
                                <p class="text-xs text-gray-500">Solicitante no encontrado</p>
                                @endif
                            </div>

                            <!-- Encargado -->
                            <div class="p-2 bg-white rounded-lg border border-purple-100">
                                <div class="flex items-center mb-2">
                                    <div class="w-6 h-6 bg-orange-100 rounded-full flex items-center justify-center">
                                        <svg class="w-3 h-3 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                    <span class="ml-2 text-xs font-medium text-gray-900">Encargado</span>
                                </div>
                                @if($prestamo->usuario)
                                <div class="space-y-1">
                                    <p class="text-xs font-semibold text-gray-900">
                                        {{ $prestamo->usuario->nomUsu ?? '' }} {{ $prestamo->usuario->apeUsu ?? '' }}
                                    </p>
                                    <p class="text-2xs text-gray-500">{{ $prestamo->usuario->email ?? '' }}</p>
                                    @if($prestamo->usuario->telefono)
                                    <p class="text-2xs text-gray-500">{{ $prestamo->usuario->telefono }}</p>
                                    @endif
                                    @if($prestamo->usuario->departamento)
                                    <p class="text-2xs text-gray-500">{{ $prestamo->usuario->departamento }}</p>
                                    @endif
                                </div>
                                @else
                                <p class="text-xs text-gray-500">Usuario no encontrado</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Herramienta Prestada -->
                    <div class="bg-white rounded-2xl p-2 border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-300">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl shadow-md">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 8.172V5L8 4z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xs font-bold text-gray-900">Herramienta Prestada</h3>
                        </div>
                        <div class="bg-green-50/50 border border-green-200 rounded-xl p-2">
                            @if($prestamo->herramienta)
                            <div class="flex items-center mb-3 p-2 bg-white rounded-lg border border-green-100">
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 8.172V5L8 4z"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-xs font-semibold text-gray-900">{{ $prestamo->herramienta->nomHer }}</p>
                                    <p class="text-2xs text-gray-500">{{ $prestamo->herramienta->catHer ?? 'Sin categoría' }}</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                @if($prestamo->herramienta->marHer)
                                <div class="text-center p-1.5 bg-white rounded border border-green-100">
                                    <div class="text-2xs text-gray-600">Marca</div>
                                    <div class="text-xs font-semibold text-gray-900">{{ $prestamo->herramienta->marHer }}</div>
                                </div>
                                @endif

                                @if($prestamo->herramienta->modHer)
                                <div class="text-center p-1.5 bg-white rounded border border-green-100">
                                    <div class="text-2xs text-gray-600">Modelo</div>
                                    <div class="text-xs font-semibold text-gray-900">{{ $prestamo->herramienta->modHer }}</div>
                                </div>
                                @endif

                                @if($prestamo->herramienta->estHer)
                                <div class="text-center p-1.5 bg-white rounded border border-green-100">
                                    <div class="text-2xs text-gray-600">Estado</div>
                                    <div class="text-xs font-semibold text-gray-900">{{ ucfirst($prestamo->herramienta->estHer) }}</div>
                                </div>
                                @endif

                                @if($prestamo->herramienta->ubiHer)
                                <div class="text-center p-1.5 bg-white rounded border border-green-100">
                                    <div class="text-2xs text-gray-600">Ubicación</div>
                                    <div class="text-xs font-semibold text-gray-900">{{ $prestamo->herramienta->ubiHer }}</div>
                                </div>
                                @endif
                            </div>

                            @can('admin')
                            <div class="mt-3">
                                <a href="{{ route('inventario.herramientas.show', $prestamo->herramienta) }}" wire:navigate
                                   class="w-full inline-flex items-center justify-center px-2 py-1 border border-green-300 rounded-lg text-xs font-medium text-green-700 bg-white hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    Ver Herramienta Completa
                                </a>
                            </div>
                            @endcan
                            @else
                            <p class="text-xs text-gray-500 text-center py-2">Herramienta no encontrada</p>
                            @endif
                        </div>
                    </div>

                    <!-- Acciones -->
                    @can('admin')
                    <div class="bg-white rounded-2xl p-2 border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-300">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl shadow-md">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xs font-bold text-gray-900">Acciones</h3>
                        </div>
                        <div class="bg-yellow-50/50 border border-yellow-200 rounded-xl p-2">
                            <div class="grid grid-cols-1 gap-2">
                                
                                @if($prestamo->estPre !== 'devuelto')
                                <button wire:click="confirmReturn" 
                                        class="flex items-center justify-center p-2 bg-green-50 hover:bg-green-100 rounded-lg border border-green-100 transition duration-150 ease-in-out">
                                    <svg class="w-4 h-4 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-xs font-medium text-green-800">Devolver Herramienta</span>
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para devolver préstamo -->
@if($showReturnModal)
<div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">Devolver Herramienta</h3>
            <div class="mt-4">
                <div class="mb-4">
                    <label for="fecDev" class="block text-sm font-medium text-gray-700 mb-2 text-left">
                        Fecha y Hora de Devolución <span class="text-red-500">*</span>
                    </label>
                    <input type="datetime-local" wire:model="fecDev" id="fecDev" required
                           min="{{ $prestamo->fecPre->format('Y-m-d\TH:i') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    @error('fecDev') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="mb-4">
                    <label for="obsPre" class="block text-sm font-medium text-gray-700 mb-2 text-left">
                        Observaciones de Devolución
                    </label>
                    <textarea wire:model="obsPre" id="obsPre" rows="3"
                              placeholder="Estado de la herramienta al momento de la devolución..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"></textarea>
                </div>

                <div class="flex space-x-3">
                    <button wire:click="$set('showReturnModal', false)" 
                            class="flex-1 px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium rounded-lg transition duration-150 ease-in-out">
                        Cancelar
                    </button>
                    <button wire:click="returnTool" 
                            class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition duration-150 ease-in-out">
                        Devolver
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