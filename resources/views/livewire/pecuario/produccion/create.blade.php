<?php
use App\Models\Animal;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component {
    public $animales;
    public $idAniPro = '';
    public $tipProAni = 'leche';
    public $canProAni = '';
    public $uniProAni = '';
    public $fecProAni = '';
    public $obsProAni = '';

    public function mount()
    {
        $this->animales = Animal::where('estAni', 'vivo')->get(['idAni', 'nomAni', 'espAni']);
        $this->fecProAni = now()->format('Y-m-d');
    }

    public function rules()
    {
        return [
            'idAniPro' => 'required|exists:animales,idAni',
            'tipProAni' => 'required|in:leche,huevos,carne,lana',
            'canProAni' => 'required|numeric|min:0.01|max:9999.99',
            'uniProAni' => 'nullable|string|max:20',
            'fecProAni' => 'required|date|before_or_equal:today',
            'obsProAni' => 'nullable|string|max:500'
        ];
    }

    public function save()
    {
        $validated = $this->validate();
        
        try {
            $produccion = ProduccionAnimal::create($validated);
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Registro creado correctamente'
            ]);
            
            $this->redirect(route('pecuario.produccion.index'), navigate: true);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al crear el registro: ' . $e->getMessage()
            ]);
        }
    }
}; ?>

<div class="max-w-4xl mx-auto px-4 py-6">
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="bg-green-600 text-white px-6 py-4">
            <h2 class="text-lg font-semibold">
                <i class="fas fa-plus-circle"></i> Nuevo Registro de Producción
            </h2>
        </div>

        <div class="px-6 py-4">
            <form wire:submit="save">
                <!-- Selección de Animal y Tipo -->
                <div class="grid md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Animal *</label>
                        <select wire:model="idAniPro" class="w-full border border-gray-300 rounded px-3 py-2" required>
                            <option value="">Seleccione un animal</option>
                            @foreach($animales as $animal)
                            <option value="{{ $animal->idAni }}">
                                {{ $animal->nomAni ?: 'Animal #'.$animal->idAni }} ({{ $animal->espAni }})
                            </option>
                            @endforeach
                        </select>
                        @error('idAniPro')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Producción *</label>
                        <select wire:model="tipProAni" class="w-full border border-gray-300 rounded px-3 py-2" required>
                            @foreach(['leche' => 'Leche', 'huevos' => 'Huevos', 'carne' => 'Carne', 'lana' => 'Lana'] as $valor => $texto)
                            <option value="{{ $valor }}">
                                {{ $texto }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Cantidad y Unidad -->
                <div class="grid md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cantidad *</label>
                        <div class="flex">
                            <input type="number" step="0.01" wire:model="canProAni"
                                   class="w-full border border-gray-300 rounded-l px-3 py-2"
                                   min="0.01" max="9999.99" required>
                            <span id="unidad-medida"
                                  class="inline-flex items-center px-3 border border-l-0 border-gray-300 rounded-r bg-gray-100 text-gray-700">
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

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unidad de Medida</label>
                        <input type="text" wire:model="uniProAni" class="w-full border border-gray-300 rounded px-3 py-2"
                               placeholder="Ej: litros, docenas...">
                    </div>
                </div>

                <!-- Fecha -->
                <div class="grid md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha *</label>
                        <input type="date" wire:model="fecProAni" class="w-full border border-gray-300 rounded px-3 py-2"
                               max="{{ date('Y-m-d') }}" required>
                    </div>
                </div>

                <!-- Observaciones -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
                    <textarea wire:model="obsProAni" rows="2"
                              class="w-full border border-gray-300 rounded px-3 py-2"></textarea>
                </div>

                <!-- Botones -->
                <div class="flex justify-between mt-6">
                    <a href="{{ route('pecuario.produccion.index') }}" wire:navigate
                       class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded shadow">
                        <i class="fas fa-times mr-1"></i> Cancelar
                    </a>
                    <button type="submit"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow">
                        <i class="fas fa-save mr-1"></i> Guardar Registro
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>