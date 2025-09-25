
<?php
// resources/views/livewire/proveedores/show.blade.php

use App\Models\Proveedor;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component {
    public Proveedor $proveedor;
    public $showDeleteModal = false;
    

    public function mount(Proveedor $proveedor)
    {
        $this->proveedor = $proveedor;
    }

    public function confirmDelete(): void
    {
        $this->showDeleteModal = true;
    }

    public function deleteProveedor(): void
    {
        try {
            $this->proveedor->delete();
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Proveedor eliminado correctamente'
            ]);

            $this->redirect(route('proveedores.index'), navigate: true);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al eliminar el proveedor: ' . $e->getMessage()
            ]);
        }
    }
    

    // Rutas para el módulo de Proveedores
    Volt::route('proveedores', 'proveedores.index')->name('proveedores.index');
    Volt::route('proveedores/crear', 'proveedores.create')->name('proveedores.create');
    Volt::route('proveedores/{proveedor}/editar', 'proveedores.edit')->name('proveedores.edit');
    Volt::route('proveedores/{proveedor}', 'proveedores.show')->name('proveedores.show');

    
}; ?>

@section('title', 'Detalles de proveedor')

<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Detalles del Proveedor</h1>
        <div class="flex space-x-2">
            <a href="{{ route('proveedores.edit', $proveedor->idProve) }}" wire:navigate
                class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                <i class="fas fa-edit mr-2"></i>Editar
            </a>
            <a href="{{ route('proveedores.index') }}" wire:navigate
                class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                <i class="fas fa-arrow-left mr-2"></i>Volver
            </a>
        </div>
    </div>

    <!-- Información del Proveedor -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800">{{ $proveedor->nomProve }}</h2>
            <p class="text-sm text-gray-600">NIT: {{ $proveedor->nitProve }}</p>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Información de Contacto -->
                <div class="space-y-4">
                    <h3 class="text-lg font-medium text-gray-900 border-b border-gray-200 pb-2">
                        Información de Contacto
                    </h3>

                    <div>
                        <label class="text-sm font-medium text-gray-500">Contacto:</label>
                        <p class="text-gray-900">{{ $proveedor->conProve ?? 'No especificado' }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-500">Teléfono:</label>
                        <p class="text-gray-900">
                            @if($proveedor->telProve)
                            <a href="tel:{{ $proveedor->telProve }}" class="text-blue-600 hover:text-blue-800">
                                {{ $proveedor->telProve }}
                            </a>
                            @else
                            No especificado
                            @endif
                        </p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-500">Email:</label>
                        <p class="text-gray-900">
                            @if($proveedor->emailProve)
                            <a href="mailto:{{ $proveedor->emailProve }}" class="text-blue-600 hover:text-blue-800">
                                {{ $proveedor->emailProve }}
                            </a>
                            @else
                            No especificado
                            @endif
                        </p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-500">Dirección:</label>
                        <p class="text-gray-900">{{ $proveedor->dirProve ?? 'No especificada' }}</p>
                    </div>
                </div>

                <!-- Información Comercial -->
                <div class="space-y-4">
                    <h3 class="text-lg font-medium text-gray-900 border-b border-gray-200 pb-2">
                        Información Comercial
                    </h3>

                    <div>
                        <label class="text-sm font-medium text-gray-500">Tipo de Suministro:</label>
                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800 mt-1">
                            {{ $proveedor->tipSumProve ?? 'No especificado' }}
                        </span>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-500">Fecha de Registro:</label>
                        <p class="text-gray-900">{{ $proveedor->created_at->format('d/m/Y H:i') }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-500">Última Actualización:</label>
                        <p class="text-gray-900">{{ $proveedor->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>

            <!-- Observaciones -->
            @if($proveedor->obsProve)
            <div class="mt-6 pt-6 border-t border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Observaciones</h3>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-gray-700">{{ $proveedor->obsProve }}</p>
                </div>
            </div>
            @endif
        </div>

        <!-- Footer con acciones -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-between items-center">
            <div class="text-sm text-gray-500">
                ID: {{ $proveedor->idProve }}
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('proveedores.edit', $proveedor->idProve) }}" wire:navigate
                    class="text-yellow-600 hover:text-yellow-800 font-medium">
                    <i class="fas fa-edit mr-1"></i>Editar información
                </a>
                <span class="text-gray-300">|</span>
                <button wire:click="confirmDelete"
                    class="text-red-600 hover:text-red-800 font-medium">
                    <i class="fas fa-trash mr-1"></i>Eliminar proveedor
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Eliminar Proveedor -->
    @if($showDeleteModal)
        <div class="fixed inset-0 bg-black/20 bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg max-w-sm w-full">
                <h2 class="text-xl font-semibold mb-4">Confirmar eliminación</h2>
                <p class="mb-4 text-sm text-gray-600">
                    ¿Está seguro que desea eliminar este proveedor? Esta acción no se puede deshacer.
                </p>
                
                <div class="mb-4">
                    <p><strong>Proveedor:</strong> {{ $proveedor->nomProve }}</p>
                    <p><strong>NIT:</strong> {{ $proveedor->nitProve }}</p>
                    <p><strong>ID:</strong> {{ $proveedor->idProve }}</p>
                </div>
                
                <div class="flex justify-end gap-3">
                    <button wire:click="$set('showDeleteModal', false)"
                            class="cursor-pointer px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 transition">Cancelar</button>
                    <button wire:click="deleteProveedor"
                            class="cursor-pointer px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition">Confirmar Eliminación</button>
                </div>
            </div>
        </div>
    @endif
</div>

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