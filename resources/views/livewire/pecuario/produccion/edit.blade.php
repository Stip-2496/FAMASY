<?php
use App\Models\ProduccionAnimal;
use App\Models\Animal;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component {
    public ProduccionAnimal $produccion;
    public $animales;
    public $tipProAni;
    public $canProAni;
    public $uniProAni;
    public $fecProAni;
    public $obsProAni;

    public function mount(ProduccionAnimal $produccion)
    {
        $this->produccion = $produccion;
        $this->animales = Animal::where('estAni', 'vivo')->get(['idAni', 'nomAni', 'espAni']);
        $this->tipProAni = $produccion->tipProAni;
        $this->canProAni = $produccion->canProAni;
        $this->uniProAni = $produccion->uniProAni;
        $this->fecProAni = $produccion->fecProAni->format('Y-m-d');
        $this->obsProAni = $produccion->obsProAni;
    }

    public function rules()
    {
        return [
            'tipProAni' => 'required|in:leche,huevos,carne,lana',
            'canProAni' => 'required|numeric|min:0.01|max:9999.99',
            'uniProAni' => 'nullable|string|max:20',
            'fecProAni' => 'required|date|before_or_equal:today',
            'obsProAni' => 'nullable|string|max:500'
        ];
    }

    

    public function update()
    {
        $validated = $this->validate();
        
        try {
            $this->produccion->update($validated);
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Registro actualizado correctamente'
            ]);
            
            $this->redirect(route('pecuario.produccion.show', $this->produccion->idProAni), navigate: true);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al actualizar el registro: ' . $e->getMessage()
            ]);
        }
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

<div class="max-w-4xl mx-auto px-4 py-6">
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="bg-green-600 text-white px-6 py-4">
            <h2 class="text-lg font-semibold">
                <i class="fas fa-edit"></i> Editar Registro de Producción #{{ $produccion->idProAni }}
            </h2>
        </div>

        <div class="px-6 py-4">
            <form wire:submit="update">
                <!-- Tipo de Producción -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Producción *</label>
                    <select wire:model="tipProAni" class="w-full border border-gray-300 rounded px-3 py-2" required>
                        @foreach(['leche' => 'Leche', 'huevos' => 'Huevos', 'carne' => 'Carne', 'lana' => 'Lana'] as $valor => $texto)
                        <option value="{{ $valor }}" @selected($valor == $produccion->tipProAni)>
                            {{ $texto }}
                        </option>
                        @endforeach
                    </select>
                    @error('tipProAni')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Cantidad -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cantidad *</label>
                    <div class="flex">
                        <input type="number" step="0.01" wire:model="canProAni"
                               class="w-full border border-gray-300 rounded-l px-3 py-2"
                               min="0.01" max="9999.99" required>
                        <span class="inline-flex items-center px-3 border border-l-0 border-gray-300 rounded-r bg-gray-100 text-gray-700">
                            @if($tipProAni == 'leche') litros
                            @elseif($tipProAni == 'huevos') docenas
                            @else kg
                            @endif
                        </span>
                    </div>
                    @error('canProAni')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Unidad de Medida -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Unidad de Medida</label>
                    <input type="text" wire:model="uniProAni" class="w-full border border-gray-300 rounded px-3 py-2"
                           placeholder="Ej: litros, docenas...">
                    @error('uniProAni')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Fecha -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha *</label>
                    <input type="date" wire:model="fecProAni" class="w-full border border-gray-300 rounded px-3 py-2"
                           max="{{ date('Y-m-d') }}" required>
                    @error('fecProAni')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Observaciones -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
                    <textarea wire:model="obsProAni" rows="3"
                              class="w-full border border-gray-300 rounded px-3 py-2"></textarea>
                    @error('obsProAni')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Botones -->
                <div class="flex justify-between mt-6">
                    <a href="{{ route('pecuario.produccion.show', $produccion->idProAni) }}" wire:navigate
                       class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded shadow">
                        <i class="fas fa-times mr-1"></i> Cancelar
                    </a>
                    <div class="flex gap-2">
                        <button type="submit"
                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow">
                            <i class="fas fa-save mr-1"></i> Guardar Cambios
                        </button>
                        <button wire:click="delete" type="button"
                                class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded shadow"
                                onclick="return confirm('¿Eliminar este registro?')">
                            <i class="fas fa-trash mr-1"></i> Eliminar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>