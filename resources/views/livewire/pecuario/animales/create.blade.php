<?php
use App\Models\Animal;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.auth')] class extends Component {
    use WithFileUploads;
    
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
    public $proAni = '';

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
            'proAni' => 'nullable|string|max:150',
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
    <!-- Header con información actual -->
    <div class="bg-white rounded-lg shadow-md border border-gray-200 p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-paw text-green-600 mr-3"></i>
                Registrar Nuevo Animal
            </h1>
            <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full flex items-center">
                <i class="fas fa-circle text-green-500 mr-1 text-xs"></i>
                Nuevo registro
            </span>
        </div>
        <p class="text-gray-600">Complete el formulario para agregar un nuevo animal al sistema</p>
    </div>

    <div class="bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden">
        <!-- Encabezado del formulario -->
        <div class="bg-green-600 text-white px-6 py-4 border-b border-green-700">
            <h2 class="text-lg font-semibold flex items-center">
                <i class="fas fa-plus-circle mr-2"></i>
                Registrar Animal
            </h2>
        </div>

        <form wire:submit="store" class="p-6 space-y-8" enctype="multipart/form-data">
            
            <!-- Sección: Información Básica -->
            <div class="space-y-4">
                <h3 class="text-lg font-medium text-gray-900 flex items-center pb-2 border-b border-gray-200">
                    <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                    Información Básica
                </h3>
                <p class="text-sm text-gray-500">Datos principales del animal</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="nitAni" class="block text-sm font-medium text-gray-700 mb-2">
                            NIT del Animal
                        </label>
                        <input type="text" id="nitAni" wire:model="nitAni" 
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        @error('nitAni') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label for="espAni" class="block text-sm font-medium text-gray-700 mb-2">
                            Especie *
                        </label>
                        <input type="text" id="espAni" wire:model="espAni" required 
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        @error('espAni') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label for="ideAni" class="block text-sm font-medium text-gray-700 mb-2">
                            Nombre del Animal
                        </label>
                        <input type="text" id="ideAni" wire:model="ideAni" 
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        @error('ideAni') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label for="razAni" class="block text-sm font-medium text-gray-700 mb-2">
                            Raza
                        </label>
                        <input type="text" id="razAni" wire:model="razAni" 
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        @error('razAni') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Sección: Características Físicas -->
            <div class="space-y-4">
                <h3 class="text-lg font-medium text-gray-900 flex items-center pb-2 border-b border-gray-200">
                    <i class="fas fa-dna text-purple-500 mr-2"></i>
                    Características Físicas
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="sexAni" class="block text-sm font-medium text-gray-700 mb-2">
                            Sexo *
                        </label>
                        <select id="sexAni" wire:model="sexAni" 
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <option value="Hembra">Hembra</option>
                            <option value="Macho">Macho</option>
                        </select>
                        @error('sexAni') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label for="pesAni" class="block text-sm font-medium text-gray-700 mb-2">
                            Peso (kg)
                        </label>
                        <input type="number" step="0.01" id="pesAni" wire:model="pesAni" 
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        @error('pesAni') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label for="fotoAni" class="block text-sm font-medium text-gray-700 mb-2">
                            Fotografía
                        </label>
                        <input type="file" id="fotoAni" wire:model="fotoAni" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        @error('fotoAni') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Sección: Procedencia -->
            <div class="space-y-4">
                <h3 class="text-lg font-medium text-gray-900 flex items-center pb-2 border-b border-gray-200">
                    <i class="fas fa-truck-loading text-teal-500 mr-2"></i>
                    Información del Proveedor
                </h3>
                <p class="text-sm text-gray-500">Proveedor que suministra este animal</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="proAni" class="block text-sm font-medium text-gray-700 mb-2">
                            Procedencia/Proveedor
                        </label>
                        <input type="text" id="proAni" wire:model="proAni" 
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        @error('proAni') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label for="ubicacionAni" class="block text-sm font-medium text-gray-700 mb-2">
                            Ubicación
                        </label>
                        <input type="text" id="ubicacionAni" wire:model="ubicacionAni" 
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        @error('ubicacionAni') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Sección: Fechas Importantes -->
            <div class="space-y-4">
                <h3 class="text-lg font-medium text-gray-900 flex items-center pb-2 border-b border-gray-200">
                    <i class="fas fa-calendar-alt text-orange-500 mr-2"></i>
                    Fechas Importantes
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="fecNacAni" class="block text-sm font-medium text-gray-700 mb-2">
                            Fecha de Nacimiento
                        </label>
                        <input type="date" id="fecNacAni" wire:model="fecNacAni" 
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        @error('fecNacAni') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label for="fecComAni" class="block text-sm font-medium text-gray-700 mb-2">
                            Fecha de Compra/Incorporación
                        </label>
                        <input type="date" id="fecComAni" wire:model="fecComAni" 
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        @error('fecComAni') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Sección: Estados del Animal -->
            <div class="space-y-4">
                <h3 class="text-lg font-medium text-gray-900 flex items-center pb-2 border-b border-gray-200">
                    <i class="fas fa-chart-line text-green-500 mr-2"></i>
                    Estados del Animal
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="estAni" class="block text-sm font-medium text-gray-700 mb-2">
                            Estado General
                        </label>
                        <select id="estAni" wire:model="estAni" 
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <option value="vivo">Vivo</option>
                            <option value="muerto">Muerto</option>
                            <option value="vendido">Vendido</option>
                        </select>
                        @error('estAni') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label for="estReproAni" class="block text-sm font-medium text-gray-700 mb-2">
                            Estado Reproductivo
                        </label>
                        <select id="estReproAni" wire:model="estReproAni" 
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <option value="no_aplica">No aplica</option>
                            <option value="ciclo">En ciclo</option>
                            <option value="cubierta">Cubierta</option>
                            <option value="gestacion">Gestación</option>
                            <option value="parida">Parida</option>
                        </select>
                        @error('estReproAni') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label for="estSaludAni" class="block text-sm font-medium text-gray-700 mb-2">
                            Estado de Salud
                        </label>
                        <select id="estSaludAni" wire:model="estSaludAni" 
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <option value="saludable">Saludable</option>
                            <option value="enfermo">Enfermo</option>
                            <option value="tratamiento">En tratamiento</option>
                        </select>
                        @error('estSaludAni') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Sección: Observaciones -->
            <div class="space-y-4">
                <h3 class="text-lg font-medium text-gray-900 flex items-center pb-2 border-b border-gray-200">
                    <i class="fas fa-comment-alt text-yellow-500 mr-2"></i>
                    Información Adicional
                </h3>
                <p class="text-sm text-gray-500">Observaciones especiales, características específicas, cuidados requeridos, etc.</p>
                
                <div>
                    <label for="obsAni" class="block text-sm font-medium text-gray-700 mb-2">
                        Observaciones
                    </label>
                    <textarea id="obsAni" wire:model="obsAni" rows="4" 
                              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                              placeholder="Escriba aquí cualquier observación importante sobre el animal..."></textarea>
                    @error('obsAni') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="flex justify-between pt-6 border-t border-gray-200">
                <a href="{{ route('pecuario.animales.index') }}" wire:navigate 
                   class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2.5 px-5 rounded-lg transition duration-200 flex items-center">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </a>
                <button type="submit" 
                        class="bg-green-600 hover:bg-green-700 text-white font-bold py-2.5 px-5 rounded-lg transition duration-200 flex items-center">
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