<?php
use App\Models\Animal;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component {
    public Animal $animal;
    
    // Propiedades públicas tipadas
    public string $espAni;
    public ?string $nomAni = null;
    public ?string $razAni = null;
    public string $sexAni;
    public ?float $pesAni = null;
    public ?string $fecNacAni = null;
    public ?string $fecComAni = null;
    public string $estAni;
    public string $estReproAni;
    public string $estSaludAni;
    public ?string $obsAni = null;

    public function mount(Animal $animal): void
    {
        $this->animal = $animal;
        $this->fill(
            $animal->only([
                'espAni', 'nomAni', 'razAni', 'sexAni', 'pesAni',
                'fecNacAni', 'fecComAni', 'estAni', 'estReproAni',
                'estSaludAni', 'obsAni'
            ])
        );
    }

    public function rules(): array
    {
        return [
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
        ];
    }

    public function update(): void
    {
        $validated = $this->validate();
        
        try {
            $this->animal->update($validated);
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Datos del animal actualizados correctamente'
            ]);
            
            $this->redirect(route('pecuario.animales.show', $this->animal->idAni), navigate: true);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al actualizar el animal: ' . $e->getMessage()
            ]);
        }
    }

    public function resetForm(): void
    {
        $this->fill(
            $this->animal->only([
                'espAni', 'nomAni', 'razAni', 'sexAni', 'pesAni',
                'fecNacAni', 'fecComAni', 'estAni', 'estReproAni',
                'estSaludAni', 'obsAni'
            ])
        );
        $this->resetErrorBag();
    }
}; ?>


@section('title', 'Editar Animal')

<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-gray-800 mb-2">Editar Animal</h1>
        <p class="text-gray-600">{{ $animal->nomAni ?? 'Animal #' . $animal->idAni }}</p>
        <nav class="text-sm text-gray-500 mt-2">
            <ol class="flex justify-center space-x-2">
                <li><a href="{{ route('pecuario.dashboard') }}" wire:navigate class="hover:underline">Pecuario</a> /</li>
                <li><a href="{{ route('pecuario.animales.index') }}" wire:navigate class="hover:underline">Animales</a> /</li>
                <li><a href="{{ route('pecuario.animales.show', $animal->idAni) }}" wire:navigate class="hover:underline">Detalle</a> /</li>
                <li class="text-gray-600">Editar</li>
            </ol>
        </nav>
    </div>

    <!-- Formulario -->
    <div class="bg-white rounded-lg shadow-md border-2 border-gray-200 overflow-hidden">
        <div class="bg-green-600 text-white px-6 py-4">
            <h2 class="text-lg font-semibold">Modificar Información del Animal</h2>
        </div>

        <form wire:submit="update" class="p-6 space-y-6">
            <!-- Sección: Identificación -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-id-card text-blue-500 mr-2"></i>
                    Identificación
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="espAni" class="block text-sm font-medium text-gray-700 mb-2">Especie *</label>
                        <input type="text" id="espAni" wire:model="espAni" required
                            class="w-full px-3 py-2 border border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
                        @error('animal.espAni') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="nomAni" class="block text-sm font-medium text-gray-700 mb-2">Nombre</label>
                        <input type="text" id="nomAni" wire:model="nomAni"
                            class="w-full px-3 py-2 border border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
                        @error('animal.nomAni') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Sección: Características -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-dna text-purple-500 mr-2"></i>
                    Características Físicas
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="razAni" class="block text-sm font-medium text-gray-700 mb-2">Raza</label>
                        <input type="text" id="razAni" wire:model="razAni"
                            class="w-full px-3 py-2 border border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
                        @error('animal.razAni') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="sexAni" class="block text-sm font-medium text-gray-700 mb-2">Sexo *</label>
                        <select id="sexAni" wire:model="sexAni" required
                            class="w-full px-3 py-2 border border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
                            <option value="Hembra">Hembra</option>
                            <option value="Macho">Macho</option>
                        </select>
                        @error('animal.sexAni') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="pesAni" class="block text-sm font-medium text-gray-700 mb-2">Peso (kg)</label>
                        <input type="number" step="0.01" id="pesAni" wire:model="pesAni"
                            class="w-full px-3 py-2 border border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
                        @error('animal.pesAni') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Fechas -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-calendar-alt text-orange-500 mr-2"></i>
                    Fechas Importantes
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="fecNacAni" class="block text-sm font-medium text-gray-700 mb-2">Fecha Nacimiento</label>
                        <input type="date" id="fecNacAni" wire:model="fecNacAni"
                            class="w-full px-3 py-2 border border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
                        @error('animal.fecNacAni') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="fecComAni" class="block text-sm font-medium text-gray-700 mb-2">Fecha Compra</label>
                        <input type="date" id="fecComAni" wire:model="fecComAni"
                            class="w-full px-3 py-2 border border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
                        @error('animal.fecComAni') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Estados -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-chart-line text-green-500 mr-2"></i>
                    Estados del Animal
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="estAni" class="block text-sm font-medium text-gray-700 mb-2">Estado General</label>
                        <select id="estAni" wire:model="estAni"
                            class="w-full px-3 py-2 border border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
                            @foreach(['vivo' => 'Vivo', 'muerto' => 'Muerto', 'vendido' => 'Vendido'] as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('animal.estAni') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="estReproAni" class="block text-sm font-medium text-gray-700 mb-2">Estado Reproductivo</label>
                        <select id="estReproAni" wire:model="estReproAni"
                            class="w-full px-3 py-2 border border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
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
                        <label for="estSaludAni" class="block text-sm font-medium text-gray-700 mb-2">Estado de Salud</label>
                        <select id="estSaludAni" wire:model="estSaludAni"
                            class="w-full px-3 py-2 border border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white">
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
            </div>

            <!-- Observaciones -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-comment-alt text-yellow-500 mr-2"></i>
                    Observaciones Adicionales
                </h3>
                <div>
                    <label for="obsAni" class="block text-sm font-medium text-gray-700 mb-2">Observaciones</label>
                    <textarea id="obsAni" wire:model="obsAni" rows="4"
                        placeholder="Escriba aquí cualquier observación importante sobre el animal..."
                        class="w-full px-3 py-2 border border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white"></textarea>
                    @error('animal.obsAni') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Botones -->
            <div class="flex justify-between pt-6 border-t border-gray-200">
                <a href="{{ route('pecuario.animales.show', $animal->idAni) }}" wire:navigate
                    class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </a>
                <button type="submit"
                    class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                    <i class="fas fa-save mr-2"></i>Guardar Cambios
                </button>
            </div>
        </form>
    </div>
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