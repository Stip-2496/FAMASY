<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

new #[Layout('layouts.auth')] class extends Component {
    use WithPagination;
    
    // Propiedades del componente
    public $filtros = [
        'tipo' => '',
        'fecha_desde' => '',
        'fecha_hasta' => '',
        'categoria_id' => '',
        'search' => ''
    ];
    
    public $modalAbierto = false;
    public $movimientoId = null;
    public $tipo = '';
    public $descripcion = '';
    public $monto = '';
    public $categoria_id = '';
    public $fecha = '';
    
    // Categorías disponibles (simuladas - en producción vendrían de la BD)
    public $categorias = [
        ['id' => 1, 'nombre' => 'Ventas'],
        ['id' => 2, 'nombre' => 'Servicios'],
        ['id' => 3, 'nombre' => 'Gastos de Oficina'],
        ['id' => 4, 'nombre' => 'Marketing'],
        ['id' => 5, 'nombre' => 'Otros']
    ];
    
    // Datos simulados - en producción serían consultas a la BD
    public $movimientos = [];
    public $totales = [
        'ingresos' => 0,
        'egresos' => 0,
        'balance' => 0
    ];

    public function mount()
    {
        $this->fecha = date('Y-m-d');
        $this->cargarDatos();
    }

    public function cargarDatos()
    {
        try {
            // Simulación de datos - en producción serían consultas a la BD con los filtros aplicados
            $this->movimientos = collect([
                (object)[
                    'id' => 1,
                    'fecha' => '2024-01-15',
                    'descripcion' => 'Venta de servicios profesionales',
                    'categoria' => 'Servicios',
                    'tipo' => 'ingreso',
                    'monto' => 1500.00
                ],
                (object)[
                    'id' => 2,
                    'fecha' => '2024-01-14',
                    'descripcion' => 'Pago de servicios públicos',
                    'categoria' => 'Servicios',
                    'tipo' => 'egreso',
                    'monto' => 350.00
                ],
                (object)[
                    'id' => 3,
                    'fecha' => '2024-01-13',
                    'descripcion' => 'Compra de materiales',
                    'categoria' => 'Materiales',
                    'tipo' => 'egreso',
                    'monto' => 850.00
                ]
            ]);
            
            // Calcular totales
            $this->totales['ingresos'] = $this->movimientos->where('tipo', 'ingreso')->sum('monto');
            $this->totales['egresos'] = $this->movimientos->where('tipo', 'egreso')->sum('monto');
            $this->totales['balance'] = $this->totales['ingresos'] - $this->totales['egresos'];
            
        } catch (\Exception $e) {
            Log::error('Error al cargar movimientos: ' . $e->getMessage());
            $this->dispatch('notify-error', message: 'Error al cargar los movimientos');
        }
    }

    public function aplicarFiltros()
    {
        $this->resetPage();
        $this->cargarDatos();
    }

    public function resetFiltros()
    {
        $this->reset('filtros');
        $this->aplicarFiltros();
    }

    public function abrirModal($id = null)
    {
        $this->resetErrorBag();
        $this->movimientoId = $id;
        
        if ($id) {
            // Edición - cargar datos del movimiento
            $movimiento = $this->movimientos->firstWhere('id', $id);
            if ($movimiento) {
                $this->tipo = $movimiento->tipo;
                $this->descripcion = $movimiento->descripcion;
                $this->monto = $movimiento->monto;
                $this->categoria_id = array_search($movimiento->categoria, array_column($this->categorias, 'nombre'));
                $this->fecha = $movimiento->fecha;
            }
        } else {
            // Nuevo - resetear valores
            $this->reset(['tipo', 'descripcion', 'monto', 'categoria_id']);
            $this->fecha = date('Y-m-d');
        }
        
        $this->modalAbierto = true;
    }

    public function cerrarModal()
    {
        $this->modalAbierto = false;
        $this->reset(['movimientoId', 'tipo', 'descripcion', 'monto', 'categoria_id']);
    }

    public function guardarMovimiento()
    {
        $this->validate([
            'tipo' => 'required|in:ingreso,egreso',
            'descripcion' => 'required|string|max:255',
            'monto' => 'required|numeric|min:0.01',
            'fecha' => 'required|date',
            'categoria_id' => 'nullable|integer'
        ], [
            'tipo.required' => 'Selecciona el tipo de movimiento',
            'tipo.in' => 'Tipo de movimiento inválido',
            'descripcion.required' => 'La descripción es obligatoria',
            'descripcion.max' => 'La descripción no puede exceder 255 caracteres',
            'monto.required' => 'El monto es obligatorio',
            'monto.numeric' => 'El monto debe ser un número válido',
            'monto.min' => 'El monto debe ser mayor a 0',
            'fecha.required' => 'La fecha es obligatoria',
            'fecha.date' => 'Formato de fecha inválido',
            'categoria_id.integer' => 'Categoría inválida'
        ]);

        try {
            // Aquí iría la lógica para guardar en la base de datos
            Log::info('Movimiento ' . ($this->movimientoId ? 'actualizado' : 'creado'), [
                'tipo' => $this->tipo,
                'descripcion' => $this->descripcion,
                'monto' => $this->monto,
                'categoria_id' => $this->categoria_id,
                'fecha' => $this->fecha
            ]);
            
            $this->cerrarModal();
            $this->cargarDatos();
            
            $this->dispatch('notify-success', message: 'Movimiento ' . ($this->movimientoId ? 'actualizado' : 'creado') . ' correctamente');
            
        } catch (\Exception $e) {
            Log::error('Error al guardar movimiento: ' . $e->getMessage());
            $this->dispatch('notify-error', message: 'Error al guardar el movimiento');
        }
    }

    public function confirmarEliminar($id)
    {
        $this->dispatch('confirm-delete', [
            'id' => $id,
            'message' => '¿Estás seguro de eliminar este movimiento? Esta acción no se puede deshacer.'
        ]);
    }

    public function eliminarMovimiento($id)
    {
        try {
            // Aquí iría la lógica para eliminar de la base de datos
            Log::info('Movimiento eliminado', ['id' => $id]);
            
            $this->cargarDatos();
            $this->dispatch('notify-success', message: 'Movimiento eliminado correctamente');
            
        } catch (\Exception $e) {
            Log::error('Error al eliminar movimiento: ' . $e->getMessage());
            $this->dispatch('notify-error', message: 'Error al eliminar el movimiento');
        }
    }

    public function exportarExcel()
    {
        try {
            // Aquí iría la lógica para exportar a Excel
            Log::info('Exportando movimientos a Excel');
            
            $this->dispatch('notify-success', message: 'Exportación iniciada. Recibirás un correo con el archivo.');
            
        } catch (\Exception $e) {
            Log::error('Error al exportar movimientos: ' . $e->getMessage());
            $this->dispatch('notify-error', message: 'Error al exportar los movimientos');
        }
    }
}; ?>

@section('title', 'Movimientos Contables')

<div class="w-full px-6 py-6 mx-auto">
    <!-- Header -->
    <div class="flex flex-wrap -mx-3 mb-6">
        <div class="w-full px-3">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                <div class="mb-4 md:mb-0">
                    <nav class="text-sm text-gray-600 mb-2">
                        <a href="{{ route('contabilidad.index') }}" class="hover:text-blue-600">Dashboard</a>
                        <span class="mx-2">/</span>
                        <span class="text-gray-900">Movimientos</span>
                    </nav>
                    <h1 class="text-3xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-exchange-alt mr-3 text-blue-600"></i> 
                        Movimientos Contables
                    </h1>
                    <p class="text-gray-600 mt-1">Gestión de ingresos y egresos</p>
                </div>
                <div class="flex space-x-3">
                    <button wire:click="abrirModal" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center transition duration-200">
                        <i class="fas fa-plus mr-2"></i> Nuevo Movimiento
                    </button>
                    <a href="{{ route('contabilidad.reportes.index') }}" 
                       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center transition duration-200">
                        <i class="fas fa-chart-line mr-2"></i> Reportes
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="flex flex-wrap -mx-3 mb-6">
        <div class="w-full px-3">
            <div class="bg-white shadow-lg rounded-lg p-6">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipo</label>
                        <select wire:model="filtros.tipo" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Todos</option>
                            <option value="ingreso">Ingresos</option>
                            <option value="egreso">Egresos</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Desde</label>
                        <input type="date" wire:model="filtros.fecha_desde" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Hasta</label>
                        <input type="date" wire:model="filtros.fecha_hasta" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Categoría</label>
                        <select wire:model="filtros.categoria_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Todas</option>
                            @foreach($categorias as $categoria)
                            <option value="{{ $categoria['id'] }}">{{ $categoria['nombre'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end space-x-2">
                        <button wire:click="aplicarFiltros" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-200">
                            <i class="fas fa-search mr-2"></i> Filtrar
                        </button>
                        <button wire:click="resetFiltros" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-2 rounded-lg transition duration-200">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
                    <div class="relative">
                        <input type="text" wire:model.live.debounce.300ms="filtros.search" 
                               class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" 
                               placeholder="Buscar movimientos...">
                        <div class="absolute left-3 top-2 text-gray-400">
                            <i class="fas fa-search"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen Rápido -->
    <div class="flex flex-wrap -mx-3 mb-6">
        <div class="w-full md:w-1/3 px-3 mb-6">
            <div class="bg-white shadow-lg rounded-lg p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-green-600 uppercase tracking-wide mb-1">Total Ingresos</p>
                        <p class="text-2xl font-bold text-gray-800">${{ number_format($totales['ingresos'], 2) }}</p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-arrow-up text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/3 px-3 mb-6">
            <div class="bg-white shadow-lg rounded-lg p-6 border-l-4 border-red-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-red-600 uppercase tracking-wide mb-1">Total Egresos</p>
                        <p class="text-2xl font-bold text-gray-800">${{ number_format($totales['egresos'], 2) }}</p>
                    </div>
                    <div class="bg-red-100 p-3 rounded-full">
                        <i class="fas fa-arrow-down text-red-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/3 px-3 mb-6">
            <div class="bg-white shadow-lg rounded-lg p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-blue-600 uppercase tracking-wide mb-1">Balance</p>
                        <p class="text-2xl font-bold text-gray-800">${{ number_format($totales['balance'], 2) }}</p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-balance-scale text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Movimientos -->
    <div class="flex flex-wrap -mx-3">
        <div class="w-full px-3">
            <div class="bg-white shadow-lg rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <div>
                            <h6 class="text-lg font-semibold text-gray-800">Lista de Movimientos</h6>
                            <p class="text-sm text-gray-600">Historial completo de transacciones</p>
                        </div>
                        <div class="flex space-x-2">
                            <button wire:click="exportarExcel" 
                                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm transition duration-200">
                                <i class="fas fa-file-excel mr-1"></i> Exportar
                            </button>
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoría</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($movimientos as $movimiento)
                            <tr class="hover:bg-gray-50 transition duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ date('d/m/Y', strtotime($movimiento->fecha)) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $movimiento->descripcion }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                        {{ $movimiento->categoria ?? 'Sin categoría' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                               {{ $movimiento->tipo == 'ingreso' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        <i class="fas fa-{{ $movimiento->tipo == 'ingreso' ? 'arrow-up' : 'arrow-down' }} mr-1"></i>
                                        {{ ucfirst($movimiento->tipo) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium 
                                        {{ $movimiento->tipo == 'ingreso' ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $movimiento->tipo == 'ingreso' ? '+' : '-' }}${{ number_format($movimiento->monto, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <button wire:click="abrirModal({{ $movimiento->id }})" 
                                                class="text-blue-600 hover:text-blue-900">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button wire:click="confirmarEliminar({{ $movimiento->id }})" 
                                                class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                    <div class="py-8">
                                        <i class="fas fa-inbox text-4xl text-gray-300 mb-4"></i>
                                        <p class="text-lg font-medium mb-2">No hay movimientos registrados</p>
                                        <p class="text-sm text-gray-400 mb-4">Comienza registrando tu primer movimiento contable</p>
                                        <button wire:click="abrirModal" 
                                                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition duration-200">
                                            <i class="fas fa-plus mr-2"></i> Crear Primer Movimiento
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Paginación 
                @if($movimientos->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $movimientos->links() }}
                </div>
                @endif-->
            </div>
        </div>
    </div>
</div>

<!-- Modal Nuevo/Editar Movimiento -->
<x-modal wire:model="modalAbierto">
    <div class="mt-3">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900">
                {{ $movimientoId ? 'Editar' : 'Nuevo' }} Movimiento Contable
            </h3>
            <button wire:click="cerrarModal" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <form wire:submit="guardarMovimiento" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Movimiento</label>
                <select wire:model="tipo" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Seleccionar...</option>
                    <option value="ingreso">💰 Ingreso</option>
                    <option value="egreso">💸 Egreso</option>
                </select>
                @error('tipo') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
                <input type="text" wire:model="descripcion" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" 
                       placeholder="Ej: Venta de producto, pago de servicios...">
                @error('descripcion') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Monto</label>
                <div class="relative">
                    <span class="absolute left-3 top-2 text-gray-500">$</span>
                    <input type="number" wire:model="monto" step="0.01" class="w-full border border-gray-300 rounded-lg pl-8 pr-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" 
                           placeholder="0.00">
                </div>
                @error('monto') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Categoría</label>
                <select wire:model="categoria_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Sin categoría</option>
                    @foreach($categorias as $categoria)
                    <option value="{{ $categoria['id'] }}">{{ $categoria['nombre'] }}</option>
                    @endforeach
                </select>
                @error('categoria_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Fecha</label>
                <input type="date" wire:model="fecha" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('fecha') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            
            <div class="flex justify-end space-x-3 pt-4">
                <button type="button" wire:click="cerrarModal" 
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition duration-200">
                    Cancelar
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200">
                    <i class="fas fa-save mr-2"></i> {{ $movimientoId ? 'Actualizar' : 'Guardar' }} Movimiento
                </button>
            </div>
        </form>
    </div>
</x-modal>

<!-- Scripts para confirmación de eliminación -->
<script>
document.addEventListener('livewire:initialized', () => {
    Livewire.on('confirm-delete', (data) => {
        Swal.fire({
            title: '¿Estás seguro?',
            text: data.message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Livewire.dispatch('eliminarMovimiento', {id: data.id});
            }
        });
    });
    
    // Notificaciones
    Livewire.on('notify-success', (data) => {
        Swal.fire({
            position: 'top-end',
            icon: 'success',
            title: data.message,
            showConfirmButton: false,
            timer: 3000
        });
    });
    
    Livewire.on('notify-error', (data) => {
        Swal.fire({
            position: 'top-end',
            icon: 'error',
            title: data.message,
            showConfirmButton: false,
            timer: 3000
        });
    });
});
</script>