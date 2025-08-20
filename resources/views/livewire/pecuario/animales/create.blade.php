<?php
use App\Models\Animal;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component {
    public $espAni = '';
    public $ideAni = '';
    public $razAni = '';
    public $sexAni = 'Hembra';
    public $pesAni = '';
    public $fecNacAni = '';
    public $fecComAni = '';
    public $estAni = 'vivo';
    public $estReproAni = 'no_aplica';
    public $estSaludAni = 'saludable';
    public $obsAni = '';
    public $nitAni = '';
    public $fotoAni;
    public $ubicacionAni = '';

    public function store()
    {
        $validated = $this->validate([
            'espAni' => 'required|string|max:100',
            'ideAni' => 'nullable|string|max:100',
            'razAni' => 'nullable|string|max:100',
            'sexAni' => 'required|in:Hembra,Macho',
            'pesAni' => 'nullable|numeric|between:0,9999.99',
            'fecNacAni' => 'nullable|date',
            'fecComAni' => 'nullable|date',
            'estAni' => 'required|in:vivo,muerto,vendido',
            'estReproAni' => 'required|in:no_aplica,ciclo,cubierta,gestacion,parida',
            'estSaludAni' => 'required|in:saludable,enfermo,tratamiento',
            'obsAni' => 'nullable|string',
            'nitAni' => 'nullable|string|max:30|unique:animales,nitAni',
            'fotoAni' => 'nullable|image|max:2048',
            'ubicacionAni' => 'nullable|string|max:100',
        ]);

        if ($this->fotoAni) {
            $validated['fotoAni'] = $this->fotoAni->store('animales', 'public');
        }

        Animal::create($validated);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Animal registrado correctamente'
        ]);

        return redirect()->route('pecuario.animales.index');
    }
}; ?>

@section('title', 'Registrar Animal')

<div class="container mx-auto px-4 py-6">
    <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-gray-800 mb-2">Registrar Nuevo Animal</h1>
        <p class="text-gray-600">Complete el formulario para agregar un nuevo animal al sistema</p>
    </div>

    <div class="bg-white rounded-lg shadow-md border-2 border-gray-200 overflow-hidden">
        <div class="bg-green-600 text-white px-6 py-4">
            <h2 class="text-lg font-semibold">Información del Animal</h2>
        </div>

        <form wire:submit="store" class="p-6 space-y-6" enctype="multipart/form-data">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="espAni" class="block text-sm font-medium text-gray-700 mb-2">Especie *</label>
                    <input type="text" id="espAni" wire:model="espAni" required class="w-full px-3 py-2 border border-gray-400 rounded-md">
                    @error('espAni') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="ideAni" class="block text-sm font-medium text-gray-700 mb-2">Identificación</label>
                    <input type="text" id="ideAni" wire:model="ideAni" class="w-full px-3 py-2 border border-gray-400 rounded-md">
                    @error('ideAni') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="razAni" class="block text-sm font-medium text-gray-700 mb-2">Raza</label>
                    <input type="text" id="razAni" wire:model="razAni" class="w-full px-3 py-2 border border-gray-400 rounded-md">
                    @error('razAni') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="sexAni" class="block text-sm font-medium text-gray-700 mb-2">Sexo *</label>
                    <select id="sexAni" wire:model="sexAni" class="w-full px-3 py-2 border border-gray-400 rounded-md">
                        <option value="Hembra">Hembra</option>
                        <option value="Macho">Macho</option>
                    </select>
                    @error('sexAni') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="pesAni" class="block text-sm font-medium text-gray-700 mb-2">Peso (kg)</label>
                    <input type="number" step="0.01" id="pesAni" wire:model="pesAni" class="w-full px-3 py-2 border border-gray-400 rounded-md">
                    @error('pesAni') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="fecNacAni" class="block text-sm font-medium text-gray-700 mb-2">Fecha Nacimiento</label>
                    <input type="date" id="fecNacAni" wire:model="fecNacAni" class="w-full px-3 py-2 border border-gray-400 rounded-md">
                    @error('fecNacAni') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="fecComAni" class="block text-sm font-medium text-gray-700 mb-2">Fecha Compra</label>
                    <input type="date" id="fecComAni" wire:model="fecComAni" class="w-full px-3 py-2 border border-gray-400 rounded-md">
                    @error('fecComAni') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="estAni" class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                    <select id="estAni" wire:model="estAni" class="w-full px-3 py-2 border border-gray-400 rounded-md">
                        <option value="vivo">Vivo</option>
                        <option value="muerto">Muerto</option>
                        <option value="vendido">Vendido</option>
                    </select>
                    @error('estAni') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="estReproAni" class="block text-sm font-medium text-gray-700 mb-2">Estado Reproductivo</label>
                    <select id="estReproAni" wire:model="estReproAni" class="w-full px-3 py-2 border border-gray-400 rounded-md">
                        <option value="no_aplica">No aplica</option>
                        <option value="ciclo">En ciclo</option>
                        <option value="cubierta">Cubierta</option>
                        <option value="gestacion">Gestación</option>
                        <option value="parida">Parida</option>
                    </select>
                    @error('estReproAni') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="estSaludAni" class="block text-sm font-medium text-gray-700 mb-2">Estado Salud</label>
                    <select id="estSaludAni" wire:model="estSaludAni" class="w-full px-3 py-2 border border-gray-400 rounded-md">
                        <option value="saludable">Saludable</option>
                        <option value="enfermo">Enfermo</option>
                        <option value="tratamiento">En tratamiento</option>
                    </select>
                    @error('estSaludAni') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="nitAni" class="block text-sm font-medium text-gray-700 mb-2">NIT</label>
                    <input type="text" id="nitAni" wire:model="nitAni" class="w-full px-3 py-2 border border-gray-400 rounded-md">
                    @error('nitAni') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="fotoAni" class="block text-sm font-medium text-gray-700 mb-2">Foto</label>
                    <input type="file" id="fotoAni" wire:model="fotoAni" class="w-full px-3 py-2 border border-gray-400 rounded-md">
                    @error('fotoAni') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <div>
                <label for="ubicacionAni" class="block text-sm font-medium text-gray-700 mb-2">Ubicación</label>
                <input type="text" id="ubicacionAni" wire:model="ubicacionAni" class="w-full px-3 py-2 border border-gray-400 rounded-md">
                @error('ubicacionAni') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="obsAni" class="block text-sm font-medium text-gray-700 mb-2">Observaciones</label>
                <textarea id="obsAni" wire:model="obsAni" rows="3" class="w-full px-3 py-2 border border-gray-400 rounded-md"></textarea>
                @error('obsAni') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="flex justify-end space-x-3 pt-4">
                <a href="{{ route('pecuario.animales.index') }}" wire:navigate class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </a>
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg">
                    <i class="fas fa-save mr-2"></i>Guardar Animal
                </button>
            </div>
        </form>
    </div>
</div>

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