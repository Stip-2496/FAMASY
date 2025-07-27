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
}; ?>

<div class="max-w-6xl mx-auto px-4 py-6">
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="bg-green-600 text-white px-6 py-4">
            <div class="flex justify-between items-center">
                <h2 class="text-lg font-semibold">
                    <i class="fas fa-clipboard-list mr-2"></i> Detalles de Producción #{{ $produccion->idProAni }}
                </h2>
                <div class="flex gap-2">
                    <a href="{{ route('pecuario.produccion.edit', $produccion->idProAni) }}" wire:navigate
                       class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-sm font-medium">
                        <i class="fas fa-edit mr-1"></i> Editar
                    </a>
                    <button wire:click="delete" 
                            onclick="return confirm('¿Eliminar este registro?')"
                            class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm font-medium">
                        <i class="fas fa-trash mr-1"></i> Eliminar
                    </button>
                </div>
            </div>
        </div>

        <div class="px-6 py-4">
            <!-- Datos principales -->
            <div class="grid md:grid-cols-2 gap-6 mb-6">
                <div class="space-y-4">
                    <h3 class="text-md font-medium text-gray-900 border-b border-gray-200 pb-2">
                        Información Básica
                    </h3>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Animal</label>
                        <p class="text-gray-900">
                            @if($produccion->animal)
                                {{ $produccion->animal->nomAni }} 
                                <span class="text-gray-500 text-sm">({{ $produccion->animal->espAni }})</span>
                            @else
                                N/A
                            @endif
                        </p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Tipo de Producción</label>
                        <p class="text-gray-900">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                {{ ucfirst($produccion->tipProAni) }}
                            </span>
                        </p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Fecha</label>
                        <p class="text-gray-900">{{ $produccion->fecProAni->format('d/m/Y') }}</p>
                    </div>
                </div>
                
                <div class="space-y-4">
                    <h3 class="text-md font-medium text-gray-900 border-b border-gray-200 pb-2">
                        Detalles de Producción
                    </h3>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Cantidad</label>
                        <p class="text-gray-900">
                            {{ $produccion->canProAni }} 
                            {{ $produccion->uniProAni ?? ($produccion->tipProAni == 'leche' ? 'litros' : 'kg') }}
                        </p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Registrado por</label>
                        <p class="text-gray-900">{{ $produccion->user->name ?? 'Sistema' }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Última actualización</label>
                        <p class="text-gray-900">{{ $produccion->updated_at->diffForHumans() }}</p>
                    </div>
                </div>
            </div>

            <!-- Observaciones -->
            @if($produccion->obsProAni)
            <div class="mb-6">
                <h3 class="text-md font-medium text-gray-900 border-b border-gray-200 pb-2">
                    <i class="fas fa-comment-dots text-green-500 mr-2"></i>Observaciones
                </h3>
                <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded">
                    <p class="text-gray-700 whitespace-pre-line">{{ $produccion->obsProAni }}</p>
                </div>
            </div>
            @endif

            <!-- Historial relacionado -->
            <div class="mt-6">
                <h3 class="text-md font-medium text-gray-900 border-b border-gray-200 pb-2">
                    <i class="fas fa-history text-green-500 mr-2"></i>Historial Reciente
                </h3>
                <div class="space-y-2 mt-2">
                    @forelse($historial as $registro)
                    <div class="bg-white border border-gray-200 rounded p-3 hover:bg-gray-50">
                        <div class="flex justify-between items-center">
                            <div>
                                <span class="font-medium">{{ $registro->fecProAni->format('d/m/Y') }}</span>
                                <span class="text-sm text-gray-500 ml-2">
                                    ({{ $registro->updated_at->diffForHumans() }})
                                </span>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                {{ $registro->canProAni }} 
                                {{ $registro->uniProAni ?? ($registro->tipProAni == 'leche' ? 'L' : 'kg') }}
                            </span>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4 text-gray-500">
                        No hay registros históricos disponibles
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>