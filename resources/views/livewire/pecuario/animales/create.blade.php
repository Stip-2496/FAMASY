<?php
use App\Models\Animal;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component {
    public $espAni = '';
    public $nomAni = '';
    public $razAni = '';
    public $sexAni = 'Hembra';
    public $pesAni = '';
    public $fecNacAni = '';
    public $fecComAni = '';
    public $estAni = 'vivo';
    public $estReproAni = 'no_aplica';
    public $estSaludAni = 'saludable';
    public $obsAni = '';

    public function store()
    {
        $validated = $this->validate([
            'espAni' => 'required|string|max:100',
            'nomAni' => 'nullable|string|max:100',
            'razAni' => 'nullable|string|max:100',
            'sexAni' => 'required|in:Hembra,Macho',
            'pesAni' => 'nullable|numeric|between:0,9999.99',
            'fecNacAni' => 'nullable|date',
            'fecComAni' => 'nullable|date',
            'estAni' => 'required|in:vivo,muerto,vendido',
            'estReproAni' => 'required|in:no_aplica,ciclo,cubierta,gestacion,parida',
            'estSaludAni' => 'required|in:saludable,enfermo,tratamiento',
            'obsAni' => 'nullable|string'
        ]);

        Animal::create($validated);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Animal registrado correctamente'
        ]);

        return redirect()->route('pecuario.animales.index');
    }
}; ?>

@section('title', 'Dashboard pecuario')
<div class="max-w-5xl mx-auto px-4 py-8">
    <div class="bg-white shadow-md rounded-lg overflow-hidden border border-green-500">
        <!-- Encabezado -->
        <div class="bg-green-600 text-white px-6 py-4">
            <h2 class="text-lg font-semibold">Registrar Nuevo Animal</h2>
        </div>

        <form wire:submit="store" class="p-6 space-y-6">
            <!-- Identificación -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="espAni" class="block text-sm font-medium text-gray-700">Especie *</label>
                    <input type="text" id="espAni" wire:model="espAni" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500">
                    @error('espAni') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="nomAni" class="block text-sm font-medium text-gray-700">Nombre</label>
                    <input type="text" id="nomAni" wire:model="nomAni"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500">
                </div>
            </div>

            <!-- Características -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="razAni" class="block text-sm font-medium text-gray-700">Raza</label>
                    <input type="text" id="razAni" wire:model="razAni"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500">
                </div>
                <div>
                    <label for="sexAni" class="block text-sm font-medium text-gray-700">Sexo *</label>
                    <select id="sexAni" wire:model="sexAni" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500">
                        <option value="Hembra">Hembra</option>
                        <option value="Macho">Macho</option>
                    </select>
                    @error('sexAni') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="pesAni" class="block text-sm font-medium text-gray-700">Peso (kg)</label>
                    <input type="number" step="0.01" id="pesAni" wire:model="pesAni"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500">
                </div>
            </div>

            <!-- Fechas -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="fecNacAni" class="block text-sm font-medium text-gray-700">Fecha Nacimiento</label>
                    <input type="date" id="fecNacAni" wire:model="fecNacAni"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500">
                </div>
                <div>
                    <label for="fecComAni" class="block text-sm font-medium text-gray-700">Fecha Compra</label>
                    <input type="date" id="fecComAni" wire:model="fecComAni"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500">
                </div>
            </div>

            <!-- Estados -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="estAni" class="block text-sm font-medium text-gray-700">Estado</label>
                    <select id="estAni" wire:model="estAni"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500">
                        <option value="vivo">Vivo</option>
                        <option value="muerto">Muerto</option>
                        <option value="vendido">Vendido</option>
                    </select>
                    @error('estAni') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="estReproAni" class="block text-sm font-medium text-gray-700">Estado Reproductivo</label>
                    <select id="estReproAni" wire:model="estReproAni"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500">
                        @foreach([
                            'no_aplica' => 'No aplica',
                            'ciclo' => 'En ciclo',
                            'cubierta' => 'Cubierta',
                            'gestacion' => 'Gestación',
                            'parida' => 'Parida'
                        ] as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('estReproAni') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="estSaludAni" class="block text-sm font-medium text-gray-700">Estado Salud</label>
                    <select id="estSaludAni" wire:model="estSaludAni"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500">
                        @foreach([
                            'saludable' => 'Saludable',
                            'enfermo' => 'Enfermo',
                            'tratamiento' => 'En tratamiento'
                        ] as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('estSaludAni') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Observaciones -->
            <div>
                <label for="obsAni" class="block text-sm font-medium text-gray-700">Observaciones</label>
                <textarea id="obsAni" wire:model="obsAni" rows="3"
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500"></textarea>
            </div>

            <!-- Botones -->
            <div class="flex justify-end space-x-3 pt-4">
                <a href="{{ route('pecuario.animales.index') }}" wire:navigate
                   class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Cancelar
                </a>
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700">
                    <i class="fas fa-save mr-2"></i> Guardar Animal
                </button>
            </div>
        </form>
    </div>
</div>