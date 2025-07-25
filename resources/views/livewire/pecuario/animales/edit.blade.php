<?php
use App\Models\Animal;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component {
    public Animal $animal;

    public function mount(Animal $animal)
    {
        $this->animal = $animal;
    }

    public function update()
    {
        $validated = $this->validate([
            'animal.espAni' => 'required|string|max:100',
            'animal.nomAni' => 'nullable|string|max:100',
            'animal.razAni' => 'nullable|string|max:100',
            'animal.sexAni' => 'required|in:Hembra,Macho',
            'animal.pesAni' => 'nullable|numeric|between:0,9999.99',
            'animal.fecNacAni' => 'nullable|date',
            'animal.fecComAni' => 'nullable|date',
            'animal.estAni' => 'required|in:vivo,muerto,vendido',
            'animal.estReproAni' => 'required|in:no_aplica,ciclo,cubierta,gestacion,parida',
            'animal.estSaludAni' => 'required|in:saludable,enfermo,tratamiento',
            'animal.obsAni' => 'nullable|string'
        ]);

        $this->animal->update($validated['animal']);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Datos actualizados correctamente'
        ]);

        return redirect()->route('pecuario.animales.show', $this->animal->idAni);
    }
}; ?>

@section('title', 'Dashboard pecuario')

<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
        <!-- Header -->
        <div class="bg-green-600 px-6 py-4">
            <h2 class="text-white text-xl font-semibold">
                Editar Animal: {{ $animal->nomAni ?? 'Sin nombre' }}
            </h2>
        </div>

        <!-- Formulario -->
        <form wire:submit="update" class="px-6 py-6 space-y-6">
            <!-- Sección: Identificación -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="espAni" class="block text-sm font-medium text-gray-700">Especie *</label>
                    <input type="text" id="espAni" wire:model="animal.espAni" required
                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500">
                    @error('animal.espAni') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="nomAni" class="block text-sm font-medium text-gray-700">Nombre</label>
                    <input type="text" id="nomAni" wire:model="animal.nomAni"
                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500">
                </div>
            </div>

            <!-- Sección: Características -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="razAni" class="block text-sm font-medium text-gray-700">Raza</label>
                    <input type="text" id="razAni" wire:model="animal.razAni"
                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500">
                </div>
                <div>
                    <label for="sexAni" class="block text-sm font-medium text-gray-700">Sexo *</label>
                    <select id="sexAni" wire:model="animal.sexAni" required
                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500">
                        <option value="Hembra">Hembra</option>
                        <option value="Macho">Macho</option>
                    </select>
                    @error('animal.sexAni') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="pesAni" class="block text-sm font-medium text-gray-700">Peso (kg)</label>
                    <input type="number" step="0.01" id="pesAni" wire:model="animal.pesAni"
                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500">
                </div>
            </div>

            <!-- Fechas -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="fecNacAni" class="block text-sm font-medium text-gray-700">Fecha Nacimiento</label>
                    <input type="date" id="fecNacAni" wire:model="animal.fecNacAni"
                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500">
                </div>
                <div>
                    <label for="fecComAni" class="block text-sm font-medium text-gray-700">Fecha Compra</label>
                    <input type="date" id="fecComAni" wire:model="animal.fecComAni"
                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500">
                </div>
            </div>

            <!-- Estados -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="estAni" class="block text-sm font-medium text-gray-700">Estado</label>
                    <select id="estAni" wire:model="animal.estAni"
                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500">
                        @foreach(['vivo' => 'Vivo', 'muerto' => 'Muerto', 'vendido' => 'Vendido'] as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('animal.estAni') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="estReproAni" class="block text-sm font-medium text-gray-700">Estado Reproductivo</label>
                    <select id="estReproAni" wire:model="animal.estReproAni"
                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500">
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
                    @error('animal.estReproAni') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="estSaludAni" class="block text-sm font-medium text-gray-700">Estado de Salud</label>
                    <select id="estSaludAni" wire:model="animal.estSaludAni"
                        class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500">
                        @foreach([
                            'saludable' => 'Saludable', 
                            'enfermo' => 'Enfermo', 
                            'tratamiento' => 'En tratamiento'
                        ] as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('animal.estSaludAni') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Observaciones -->
            <div>
                <label for="obsAni" class="block text-sm font-medium text-gray-700">Observaciones</label>
                <textarea id="obsAni" wire:model="animal.obsAni" rows="3"
                    class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500"></textarea>
            </div>

            <!-- Botones -->
            <div class="flex justify-between pt-4">
                <a href="{{ route('pecuario.animales.show', $animal->idAni) }}" wire:navigate
                    class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold rounded-lg shadow">
                    Cancelar
                </a>
                <button type="submit"
                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow">
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>