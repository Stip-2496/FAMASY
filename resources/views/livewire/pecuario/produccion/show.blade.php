<?php
use App\Models\ProduccionAnimal;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component {
    public ProduccionAnimal $produccion;
    public $historial;

    public function mount(ProduccionAnimal $produccion)
    {
        $this->produccion = $produccion;
        $this->historial = ProduccionAnimal::where('idAniPro', $produccion->idAniPro)
            ->where('idProAni', '!=', $produccion->idProAni)
            ->orderBy('fecProAni', 'desc')
            ->limit(5)
            ->get();
    }

    public function delete()
    {
        try {
            $this->produccion->delete();
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Registro eliminado correctamente'
            ]);
            
            $this->redirect(route('pecuario.produccion.index'), navigate: true);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al eliminar el registro: ' . $e->getMessage()
            ]);
        }
    }

    public function getTipoProduccionFormateado($tipo)
    {
        $tipos = [
            'leche bovina' => 'Leche Bovina',
            'venta en pie bovino' => 'Venta en Pie Bovino',
            'lana ovina' => 'Lana Ovina',
            'venta en pie ovino' => 'Venta en Pie Ovino',
            'leche ovina' => 'Leche Ovina',
            'venta gallinas en pie' => 'Venta Gallinas en Pie',
            'huevo A' => 'Huevo A',
            'huevo AA' => 'Huevo AA',
            'huevo AAA' => 'Huevo AAA',
            'huevo Jumbo' => 'Huevo Jumbo',
            'huevo B' => 'Huevo B',
            'huevo C' => 'Huevo C',
            'venta pollo engorde' => 'Venta Pollo Engorde',
            'otros' => 'Otros'
        ];
        
        return $tipos[$tipo] ?? ucfirst($tipo);
    }

    public function getColorTipo($tipo)
    {
        $colores = [
            'leche bovina' => 'bg-blue-100 text-blue-800',
            'leche ovina' => 'bg-blue-100 text-blue-800',
            'venta en pie bovino' => 'bg-red-100 text-red-800',
            'venta en pie ovino' => 'bg-red-100 text-red-800',
            'venta gallinas en pie' => 'bg-red-100 text-red-800',
            'venta pollo engorde' => 'bg-red-100 text-red-800',
            'lana ovina' => 'bg-purple-100 text-purple-800',
            'huevo A' => 'bg-yellow-100 text-yellow-800',
            'huevo AA' => 'bg-yellow-100 text-yellow-800',
            'huevo AAA' => 'bg-yellow-100 text-yellow-800',
            'huevo Jumbo' => 'bg-yellow-100 text-yellow-800',
            'huevo B' => 'bg-yellow-100 text-yellow-800',
            'huevo C' => 'bg-yellow-100 text-yellow-800',
            'otros' => 'bg-gray-100 text-gray-800'
        ];
        
        return $colores[$tipo] ?? 'bg-green-100 text-green-800';
    }
}; ?>

<div class="max-w-6xl mx-auto px-4 py-6">
    <!-- Información Actual -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
        <div class="bg-green-600 text-white px-6 py-4">
            <h2 class="text-lg font-semibold flex items-center">
                <i class="fas fa-info-circle mr-2"></i> Información Actual
            </h2>
        </div>
        <div class="px-6 py-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600">ID #{{ $produccion->idProAni }}</p>
                    <p class="text-sm text-gray-600">Última Actualización: {{ $produccion->updated_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Detalles de Producción -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="bg-green-600 text-white px-6 py-4">
            <div class="flex justify-between items-center">
                <h2 class="text-lg font-semibold flex items-center">
                    <i class="fas fa-clipboard-list mr-2"></i> Detalles de Producción #{{ $produccion->idProAni }}
                </h2>
                <div class="flex gap-2">
                    <a href="{{ route('pecuario.produccion.edit', $produccion->idProAni) }}" wire:navigate
                       class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-sm font-medium flex items-center">
                        <i class="fas fa-edit mr-1"></i> Editar
                    </a>
                    <button wire:click="delete" 
                            onclick="return confirm('¿Eliminar este registro?')"
                            class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm font-medium flex items-center">
                        <i class="fas fa-trash mr-1"></i> Eliminar
                    </button>
                </div>
            </div>
        </div>

        <div class="px-6 py-4">
            <!-- Información Básica -->
            <div class="mb-6">
                <h3 class="text-md font-medium text-gray-700 mb-4 flex items-center">
                    <i class="fas fa-tools mr-2"></i> Información Básica
                </h3>
                
                <div class="grid lg:grid-cols-3 md:grid-cols-2 gap-6">
                    <!-- Animal -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1 flex items-center">
                            <i class="fas fa-cow mr-1 text-gray-500"></i> Animal
                        </label>
                        <p class="text-gray-900">
                            @if($produccion->animal)
                                <span class="font-medium">{{ $produccion->animal->ideAni }}</span>
                                <span class="text-gray-500 text-sm ml-1">({{ ucfirst($produccion->animal->espAni) }})</span>
                            @else
                                <span class="text-gray-400 italic">No asignado</span>
                            @endif
                        </p>
                    </div>
                    
                    <!-- Tipo de Producción -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1 flex items-center">
                            <i class="fas fa-list-alt mr-1 text-gray-500"></i> Tipo de Producción
                        </label>
                        <p class="text-gray-900">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $this->getColorTipo($produccion->tipProAni) }}">
                                {{ $this->getTipoProduccionFormateado($produccion->tipProAni) }}
                            </span>
                        </p>
                    </div>
                    
                    <!-- Fecha de Producción -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1 flex items-center">
                            <i class="far fa-calendar-alt mr-1 text-gray-500"></i> Fecha de Producción
                        </label>
                        <p class="text-gray-900">
                            @if($produccion->fecProAni)
                                <i class="fas fa-calendar-alt text-green-500 mr-1"></i>
                                {{ $produccion->fecProAni->format('d/m/Y') }}
                                <span class="text-gray-500 text-sm ml-1">
                                    ({{ $produccion->fecProAni->diffForHumans() }})
                                </span>
                            @else
                                <span class="text-gray-400 italic">No especificada</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Control de Producción -->
            <div class="mb-6">
                <h3 class="text-md font-medium text-gray-700 mb-4 flex items-center">
                    <i class="fas fa-calculator mr-2"></i> Control de Producción
                </h3>
                
                <div class="grid lg:grid-cols-3 md:grid-cols-2 gap-6">
                    <!-- Cantidad -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cantidad</label>
                        <p class="text-gray-900">
                            @if($produccion->canProAni)
                                <span class="text-lg font-semibold text-green-600">{{ number_format($produccion->canProAni, 2) }}</span>
                                <span class="text-gray-600 ml-1">{{ $produccion->uniProAni ?: 'unidades' }}</span>
                            @else
                                <span class="text-gray-400 italic">No especificada</span>
                            @endif
                        </p>
                    </div>
                    
                    <!-- Cantidad Total -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cantidad Total</label>
                        <p class="text-gray-900">
                            @if($produccion->canTotProAni)
                                <span class="text-lg font-semibold text-blue-600">{{ number_format($produccion->canTotProAni, 2) }}</span>
                                <span class="text-gray-600 ml-1">{{ $produccion->uniProAni ?: 'unidades' }}</span>
                                <span class="text-xs text-gray-500 block flex items-center">
                                    <i class="fas fa-info-circle mr-1"></i> Total acumulado
                                </span>
                            @else
                                <span class="text-gray-400 italic">No especificada</span>
                            @endif
                        </p>
                    </div>
                    
                    <!-- Unidad de Medida -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unidad de Medida</label>
                        <p class="text-gray-900">
                            @if($produccion->uniProAni)
                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-700">
                                    {{ ucfirst($produccion->uniProAni) }}
                                </span>
                            @else
                                <span class="text-gray-400 italic">No especificada</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Información de Registro -->
            <div class="mb-6">
                <h3 class="text-md font-medium text-gray-700 mb-4 flex items-center">
                    <i class="fas fa-clock mr-2"></i> Información de Registro
                </h3>
                
                <div class="grid lg:grid-cols-3 md:grid-cols-2 gap-6">
                    <!-- Registrado por -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1 flex items-center">
                            <i class="fas fa-user mr-1 text-gray-500"></i> Registrado por
                        </label>
                        <p class="text-gray-900">
                            <i class="fas fa-user text-green-500 mr-1"></i>
                            {{ $produccion->user->name ?? 'Sistema' }}
                        </p>
                    </div>
                    
                    <!-- Fecha de Registro -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1 flex items-center">
                            <i class="fas fa-plus-circle mr-1 text-gray-500"></i> Fecha de Registro
                        </label>
                        <p class="text-gray-900">
                            <i class="fas fa-plus-circle text-green-500 mr-1"></i>
                            {{ $produccion->created_at->format('d/m/Y H:i') }}
                            <span class="text-gray-500 text-sm block">
                                {{ $produccion->created_at->diffForHumans() }}
                            </span>
                        </p>
                    </div>
                    
                    <!-- Última Actualización -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1 flex items-center">
                            <i class="fas fa-edit mr-1 text-gray-500"></i> Última Actualización
                        </label>
                        <p class="text-gray-900">
                            <i class="fas fa-edit text-green-500 mr-1"></i>
                            {{ $produccion->updated_at->format('d/m/Y H:i') }}
                            <span class="text-gray-500 text-sm block">
                                {{ $produccion->updated_at->diffForHumans() }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Observaciones -->
            @if($produccion->obsProAni)
            <div class="mb-6">
                <h3 class="text-md font-medium text-gray-700 mb-4 flex items-center">
                    <i class="fas fa-clipboard-list mr-2"></i> Información Adicional
                </h3>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1 flex items-center">
                        <i class="fas fa-comment-dots mr-1 text-gray-500"></i> Observaciones
                    </label>
                    <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-r-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-sticky-note text-green-500"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-gray-700 whitespace-pre-line">{{ $produccion->obsProAni }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Historial relacionado -->
            @if($produccion->idAniPro)
            <div class="mb-6">
                <h3 class="text-md font-medium text-gray-700 mb-4 flex items-center">
                    <i class="fas fa-history mr-2"></i> Historial Reciente del Animal
                    <span class="text-sm font-normal text-gray-500 ml-2">
                        ({{ $produccion->animal->ideAni ?? 'Animal' }})
                    </span>
                </h3>
                <div class="space-y-3">
                    @forelse($historial as $registro)
                    <div class="bg-white border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="flex items-center mb-2">
                                    <span class="font-medium text-gray-900">{{ $registro->fecProAni ? $registro->fecProAni->format('d/m/Y') : 'Sin fecha' }}</span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $this->getColorTipo($registro->tipProAni) }} ml-2">
                                        {{ $this->getTipoProduccionFormateado($registro->tipProAni) }}
                                    </span>
                                </div>
                                <div class="flex items-center text-sm text-gray-500">
                                    <i class="fas fa-clock mr-1"></i>
                                    {{ $registro->updated_at->diffForHumans() }}
                                    @if($registro->obsProAni)
                                        <span class="ml-3">
                                            <i class="fas fa-comment text-gray-400"></i>
                                            Con observaciones
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-semibold text-gray-900">
                                    {{ $registro->canProAni ? number_format($registro->canProAni, 2) : '0' }}
                                    <span class="text-sm font-normal text-gray-600">
                                        {{ $registro->uniProAni ?: 'un.' }}
                                    </span>
                                </div>
                                @if($registro->canTotProAni)
                                <div class="text-xs text-gray-500">
                                    Total: {{ number_format($registro->canTotProAni, 2) }}
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-inbox text-3xl text-gray-300 mb-2"></i>
                        <p>No hay registros históricos disponibles para este animal</p>
                    </div>
                    @endforelse
                    
                    @if($historial->count() >= 5)
                    <div class="text-center pt-4">
                        <a href="{{ route('pecuario.produccion.index', ['animal' => $produccion->idAniPro]) }}" 
                           wire:navigate
                           class="text-green-600 hover:text-green-700 text-sm font-medium flex items-center justify-center">
                            <i class="fas fa-eye mr-1"></i>
                            Ver historial completo
                        </a>
                    </div>
                    @endif
                </div>
            </div>
            @else
            <div class="mb-6 bg-gray-50 border border-gray-200 rounded-lg p-4">
                <div class="text-center text-gray-500">
                    <i class="fas fa-info-circle text-2xl mb-2"></i>
                    <p>Este registro no está asociado a un animal específico</p>
                </div>
            </div>
            @endif

            <!-- Botón volver -->
            <div class="mt-8 pt-6 border-t border-gray-200">
                <a href="{{ route('pecuario.produccion.index') }}" wire:navigate
                   class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Volver al listado
                </a>
            </div>
        </div>
    </div>
</div>