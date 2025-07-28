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

<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                        @php
                            $iconoColor = match($mantenimiento->estMan) {
                                'pendiente' => 'text-red-600',
                                'en proceso' => 'text-yellow-600',
                                'completado' => 'text-green-600',
                                default => 'text-green-600'
                            };
                        @endphp
                        <svg class="w-8 h-8 {{ $iconoColor }} mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Mantenimiento #{{ $mantenimiento->idMan }}
                    </h1>
                    <p class="mt-1 text-sm text-gray-600">Detalles completos del mantenimiento</p>
                </div>
                <div class="mt-4 sm:mt-0 flex space-x-3">
                    <a href="{{ route('inventario.mantenimientos.index') }}" wire:navigate
                       class="inline-flex items-center px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium rounded-lg shadow-sm transition duration-150 ease-in-out">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Volver
                    </a>
                    @if($mantenimiento->estMan !== 'completado')
                    <a href="{{ route('inventario.mantenimientos.edit', $mantenimiento) }}" wire:navigate
                       class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg shadow-sm transition duration-150 ease-in-out">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Editar
                    </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Información Principal -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Datos del Mantenimiento -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 bg-green-600 rounded-t-lg">
                        <h3 class="text-lg font-medium text-white flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            Información del Mantenimiento
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Tipo de Mantenimiento -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Mantenimiento</label>
                                <div class="flex items-center">
                                    @php
                                        $tipoColors = [
                                            'preventivo' => 'bg-green-100 text-green-800',
                                            'correctivo' => 'bg-red-100 text-red-800',
                                            'predictivo' => 'bg-blue-100 text-blue-800'
                                        ];
                                        $colorClass = $tipoColors[$mantenimiento->tipMan] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $colorClass }}">
                                        {{ ucfirst($mantenimiento->tipMan) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Estado -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                                <div class="flex items-center">
                                    @php
                                        $estadoColors = [
                                            'pendiente' => 'bg-red-100 text-red-800',
                                            'en proceso' => 'bg-yellow-100 text-yellow-800',
                                            'completado' => 'bg-green-100 text-green-800'
                                        ];
                                        $colorClass = $estadoColors[$mantenimiento->estMan] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $colorClass }}">
                                        {{ ucfirst($mantenimiento->estMan) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Fecha del Mantenimiento -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Programada</label>
                                <div class="flex items-center text-gray-900">
                                    <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <div>
                                        <p class="font-medium">{{ $mantenimiento->fecMan->format('d/m/Y') }}</p>
                                        <p class="text-sm text-gray-500">{{ $mantenimiento->fecMan->diffForHumans() }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Fecha de creación -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Fecha de Registro</label>
                                <div class="flex items-center text-gray-900">
                                    <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <div>
                                        <p class="font-medium">{{ $mantenimiento->created_at->format('d/m/Y H:i') }}</p>
                                        <p class="text-sm text-gray-500">{{ $mantenimiento->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Descripción -->
                        @if($mantenimiento->desMan)
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-gray-900">{{ $mantenimiento->desMan }}</p>
                            </div>
                        </div>
                        @endif

                        <!-- Resultado (solo si está completado) -->
                        @if($mantenimiento->resMan && $mantenimiento->estMan === 'completado')
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Resultado del Mantenimiento</label>
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-green-600 mt-0.5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <p class="text-green-900">{{ $mantenimiento->resMan }}</p>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Observaciones -->
                        @if($mantenimiento->obsMan)
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Observaciones</label>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-gray-900">{{ $mantenimiento->obsMan }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Movimientos Relacionados -->
                @if($mantenimiento->movimientos && $mantenimiento->movimientos->count() > 0)
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 bg-blue-600 rounded-t-lg">
                        <h3 class="text-lg font-medium text-white flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                            </svg>
                            Movimientos de Inventario Relacionados
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            @foreach($mantenimiento->movimientos as $movimiento)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ ucfirst($movimiento->tipMovInv) }}</p>
                                        <p class="text-sm text-gray-500">{{ $movimiento->fecMovInv->format('d/m/Y H:i') }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-medium text-gray-900">{{ number_format($movimiento->cantMovInv, 2) }} {{ $movimiento->uniMovInv }}</p>
                                        @if($movimiento->costoTotInv)
                                        <p class="text-sm text-gray-500">${{ number_format($movimiento->costoTotInv, 2) }}</p>
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
            <div class="lg:col-span-1 space-y-6">
                <!-- Herramienta -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 bg-blue-600 rounded-t-lg">
                        <h3 class="text-lg font-medium text-white flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 8.172V5L8 4z"></path>
                            </svg>
                            Herramienta
                        </h3>
                    </div>
                    <div class="p-6">
                        @if($mantenimiento->herramienta)
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 8.172V5L8 4z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="font-medium text-gray-900">{{ $mantenimiento->herramienta->nomHer }}</p>
                                <p class="text-sm text-gray-500">{{ $mantenimiento->herramienta->catHer ?? 'Sin categoría' }}</p>
                            </div>
                        </div>

                        <div class="mt-4 space-y-3">
                            @if($mantenimiento->herramienta->marHer)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Marca</label>
                                <p class="text-gray-900">{{ $mantenimiento->herramienta->marHer }}</p>
                            </div>
                            @endif

                            @if($mantenimiento->herramienta->modHer)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Modelo</label>
                                <p class="text-gray-900">{{ $mantenimiento->herramienta->modHer }}</p>
                            </div>
                            @endif

                            @if($mantenimiento->herramienta->estHer)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                                <p class="text-gray-900">{{ ucfirst($mantenimiento->herramienta->estHer) }}</p>
                            </div>
                            @endif
                        </div>

                        <div class="mt-4">
                            <a href="{{ route('inventario.herramientas.show', $mantenimiento->herramienta) }}" wire:navigate
                               class="w-full inline-flex items-center justify-center px-3 py-2 border border-blue-300 rounded-md shadow-sm text-sm font-medium text-blue-700 bg-white hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Ver Herramienta
                            </a>
                        </div>
                        @else
                        <p class="text-gray-500">Herramienta no encontrada</p>
                        @endif
                    </div>
                </div>

                <!-- Acciones Rápidas -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 bg-green-600 rounded-t-lg">
                        <h3 class="text-lg font-medium text-white flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            Acciones
                        </h3>
                    </div>
                    <div class="p-6 space-y-3">
                        @if($mantenimiento->estMan !== 'completado')
                        <a href="{{ route('inventario.mantenimientos.edit', $mantenimiento) }}" wire:navigate
                           class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition duration-150 ease-in-out">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Editar Mantenimiento
                        </a>
                        @endif

                        @if($mantenimiento->estMan === 'en proceso')
                        <button wire:click="$set('showCompleteModal', true)" 
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-150 ease-in-out">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Completar Mantenimiento
                        </button>
                        @endif

                        @if($mantenimiento->estMan === 'pendiente')
                        <button wire:click="deleteMantenimiento" 
                                onclick="return confirm('¿Está seguro de eliminar este mantenimiento? Esta acción no se puede deshacer.')"
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition duration-150 ease-in-out">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Eliminar Mantenimiento
                        </button>
                        @endif

                        <a href="{{ route('inventario.mantenimientos.create') }}" wire:navigate
                           class="w-full inline-flex items-center justify-center px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium rounded-lg transition duration-150 ease-in-out">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Nuevo Mantenimiento
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para completar mantenimiento -->
@if($showCompleteModal)
<div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center" style="z-index: 1000;">
    <div class="relative bg-white p-6 rounded-lg shadow-lg max-w-md w-full">
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">Completar Mantenimiento</h3>
            
            <div class="mt-4 text-left">
                <div class="mb-4">
                    <label for="resMan" class="block text-sm font-medium text-gray-700 mb-2">
                        Resultado del Mantenimiento <span class="text-red-500">*</span>
                    </label>
                    <textarea wire:model="resMan" id="resMan" rows="3" required maxlength="100"
                              placeholder="Describa el resultado del mantenimiento realizado..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"></textarea>
                    @error('resMan') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="mb-4">
                    <label for="obsMan" class="block text-sm font-medium text-gray-700 mb-2">
                        Observaciones Adicionales
                    </label>
                    <textarea wire:model="obsMan" id="obsMan" rows="2"
                              placeholder="Observaciones adicionales (opcional)..."
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"></textarea>
                </div>

                <div class="flex space-x-3">
                    <button wire:click="$set('showCompleteModal', false)" 
                            class="flex-1 px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium rounded-lg transition duration-150 ease-in-out">
                        Cancelar
                    </button>
                    <button wire:click="confirmComplete" 
                            class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition duration-150 ease-in-out">
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