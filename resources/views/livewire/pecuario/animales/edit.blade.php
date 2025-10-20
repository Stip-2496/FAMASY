<?php
use App\Models\Animal;
use App\Models\Proveedor;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

new #[Layout('layouts.auth')] class extends Component {
    use WithFileUploads;
    
    public Animal $animal;
    
    // Propiedades para el selector de proveedor
    public $proveedores;
    public $idProveedor = null;
    public $proveedorSeleccionado = null;
    
    // Propiedades públicas tipadas
    public string $espAni;
    public ?string $razAni = null;
    public string $sexAni;
    public ?float $pesAni = null;
    public ?string $fecNacAni = null;
    public ?string $fecComAni = null;
    public ?string $proAni = null;          // Nuevo campo
    public string $estAni;
    public string $estReproAni;
    public string $estSaludAni;
    public ?string $obsAni = null;
    public ?string $nitAni = null;
    public ?string $ubicacionAni = null;

    public function mount(Animal $animal): void
    {
        $this->animal = $animal;
        $this->fill(
            $animal->only([
                'espAni', 'razAni', 'sexAni', 'pesAni',
                'fecNacAni', 'fecComAni', 'proAni', 'estAni', 'estReproAni',
                'estSaludAni', 'obsAni', 'nitAni', 'ubicacionAni'
            ])
        );

        $this->fecNacAni = $animal->fecNacAni ? $animal->fecNacAni->format('Y-m-d') : null;
        $this->fecComAni = $animal->fecComAni ? $animal->fecComAni->format('Y-m-d') : null;
        
        // Cargar proveedores disponibles
        $this->proveedores = Proveedor::orderBy('nomProve')->get();
        
        // Buscar y establecer el proveedor si existe en el campo proAni
        $this->buscarProveedorExistente();
    }

    public function updatedIdProveedor($value)
    {
        if ($value) {
            $this->proveedorSeleccionado = Proveedor::find($value);
            // Actualizar automáticamente el campo proAni con el nombre del proveedor
            if ($this->proveedorSeleccionado) {
                $this->proAni = $this->proveedorSeleccionado->nomProve;
            }
        } else {
            $this->proveedorSeleccionado = null;
            $this->proAni = '';
        }
    }

    // Método para buscar el proveedor existente basado en el campo proAni
    private function buscarProveedorExistente(): void
    {
        if ($this->proAni) {
            $proveedor = Proveedor::where('nomProve', $this->proAni)->first();
            if ($proveedor) {
                $this->idProveedor = $proveedor->idProve;
                $this->proveedorSeleccionado = $proveedor;
            }
        }
    }

    public function rules(): array
    {
        return [
            'espAni' => 'required|string|max:100',
            'razAni' => 'nullable|string|max:100',
            'sexAni' => 'required|in:Hembra,Macho',
            'pesAni' => 'nullable|numeric|between:0,9999.99',
            'fecNacAni' => 'nullable|date',
            'fecComAni' => 'nullable|date',
            'proAni' => 'nullable|string|max:150',                        // Nueva regla
            'estAni' => 'required|in:vivo,muerto,vendido',
            'estReproAni' => 'required|in:no_aplica,ciclo,cubierta,gestacion,parida',
            'estSaludAni' => 'required|in:saludable,enfermo,tratamiento',
            'obsAni' => 'nullable|string',
            'nitAni' => 'nullable|string|max:30|unique:animales,nitAni,' . $this->animal->idAni . ',idAni', // Regla única actualizada
            'ubicacionAni' => 'nullable|string|max:100'
        ];
    }

public function update(): void
{
    $validated = $this->validate();
    
    try {
        $this->animal->update($validated);
        
        // Actualizar las propiedades con los nuevos datos
        $this->fill(
            $this->animal->only([
                'espAni', 'razAni', 'sexAni', 'pesAni',
                'proAni', 'estAni', 'estReproAni',
                'estSaludAni', 'obsAni', 'nitAni', 'ubicacionAni'
            ])
        );
        
        // Actualizar fechas
        $this->fecNacAni = $this->animal->fecNacAni ? $this->animal->fecNacAni->format('Y-m-d') : null;
        $this->fecComAni = $this->animal->fecComAni ? $this->animal->fecComAni->format('Y-m-d') : null;
        
        // Disparar evento para mostrar mensaje de éxito
        $this->dispatch('actualizacion-exitosa');
        
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Datos del animal actualizados correctamente'
        ]);
        
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
                'espAni', 'razAni', 'sexAni', 'pesAni',
                'fecNacAni', 'fecComAni', 'proAni', 'estAni', 'estReproAni',
                'estSaludAni', 'obsAni', 'nitAni', 'ubicacionAni'
            ])
        );

        $this->fecNacAni = $this->animal->fecNacAni ? $this->animal->fecNacAni->format('Y-m-d') : null;
        $this->fecComAni = $this->animal->fecComAni ? $this->animal->fecComAni->format('Y-m-d') : null;
        
        // Resetear también el selector de proveedores
        $this->buscarProveedorExistente();
        $this->resetErrorBag();
        
        // Disparar evento para mostrar mensaje
        $this->dispatch('actualizacion-cancelada');
    }
}; ?>

@section('title', 'Editar Animal')

<div class="flex items-center justify-center min-h-screen py-3">
    <div class="w-full max-w-7xl mx-auto bg-white/80 backdrop-blur-xl shadow rounded-3xl p-3 relative border border-white/20">
        <!-- Botón Volver -->
        <div class="absolute top-2 right-2">
            <a href="{{ route('pecuario.animales.index', $animal->idAni) }}" wire:navigate
               class="group relative inline-flex items-center px-2 py-1 bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                <svg class="w-3 h-3 mr-1 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span class="relative z-10 text-xs">Volver</span>
            </a>
        </div>

        <!-- Encabezado -->
        <div class="text-center mb-3"
             x-data="{ showSuccess: false, showCancel: false }"
             x-on:actualizacion-exitosa.window="showSuccess = true; showCancel = false; setTimeout(() => showSuccess = false, 3000)"
             x-on:actualizacion-cancelada.window="showCancel = true; showSuccess = false; setTimeout(() => showCancel = false, 3000)">
            <div class="flex justify-center mb-1">
                <div class="p-2 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl shadow-xl transform rotate-3 hover:rotate-0 transition-transform duration-300">
                    <div class="w-4 h-4 text-white flex items-center justify-center">
                        <i class="fas fa-paw text-base"></i>
                    </div>
                </div>
            </div>
            <h1 class="text-base font-black bg-gradient-to-r from-gray-900 via-gray-800 to-green-800 bg-clip-text text-transparent mb-1">
                Editar Animal
            </h1>
            
            <template x-if="showSuccess">
                <div class="rounded bg-green-100 px-2 py-1 text-green-800 border border-green-400 text-xs mb-1 font-semibold">
                    ¡Animal actualizado exitosamente!
                </div>
            </template>

            <template x-if="showCancel">
                <div class="rounded bg-yellow-100 px-2 py-1 text-yellow-800 border border-yellow-400 text-xs mb-1 font-semibold">
                    Formulario limpiado. Puede editar nuevamente.
                </div>
            </template>

            <template x-if="!showSuccess && !showCancel">
                <p class="text-gray-600 text-xs">Actualiza los datos del animal</p>
            </template>
        </div>

        <form wire:submit.prevent="update" class="space-y-2">
            <!-- Sección: Información Básica -->
            <div class="flex flex-col md:flex-row gap-2">
                <div class="flex-1 border border-gray-300 rounded-3xl overflow-hidden">
                    <div class="h-1.5 bg-gradient-to-r from-[#000000] to-[#39A900]"></div>
                    <div class="p-2">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl shadow-lg">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xs font-bold text-gray-900">Información Básica</h2>
                                <p class="text-gray-600 text-[10px]">Datos principales del animal</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                            <div>
                                <label for="nitAni" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    NIT del Animal
                                </label>
                                <div class="relative group">
                                    <input type="text" wire:model="nitAni" id="nitAni"
                                           class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('nitAni') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror">
                                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-indigo-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('nitAni')
                                    <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                        <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>
                            <div>
                                <label for="espAni" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Especie <span class="text-red-500">*</span>
                                </label>
                                <div class="relative group">
                                    <input type="text" wire:model="espAni" id="espAni" required
                                           class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('espAni') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror">
                                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-indigo-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('espAni')
                                    <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                        <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>
                            <div>
                                <label for="razAni" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Raza
                                </label>
                                <div class="relative group">
                                    <input type="text" wire:model="razAni" id="razAni"
                                           class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('razAni') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror">
                                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-indigo-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('razAni')
                                    <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                        <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Características Físicas -->
                <div class="flex-1 border border-gray-300 rounded-3xl overflow-hidden">
                    <div class="h-1.5 bg-gradient-to-r from-[#39A900] to-[#000000]"></div>
                    <div class="p-2">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl shadow-lg">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xs font-bold text-gray-900">Características Físicas</h2>
                                <p class="text-gray-600 text-[10px]">Datos físicos del animal</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            <div>
                                <label for="sexAni" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Sexo <span class="text-red-500">*</span>
                                </label>
                                <div class="relative group">
                                    <select wire:model="sexAni" id="sexAni"
                                            class="cursor-pointer w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl appearance-none text-xs @error('sexAni') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror">
                                        <option value="Hembra">Hembra</option>
                                        <option value="Macho">Macho</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-1.5 pointer-events-none">
                                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </div>
                                @error('sexAni')
                                    <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                        <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>
                            <div>
                                <label for="pesAni" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Peso (kg)
                                </label>
                                <div class="relative group">
                                    <input type="number" step="0.01" wire:model="pesAni" id="pesAni"
                                           class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('pesAni') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror">
                                    <div class="absolute inset-0 bg-gradient-to-r from-purple-500/5 to-pink-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('pesAni')
                                    <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                        <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección: Procedencia -->
            <div class="flex flex-col md:flex-row gap-2">
                <div class="flex-1 border border-gray-300 rounded-3xl overflow-hidden">
                    <div class="h-1.5 bg-gradient-to-r from-[#000000] to-[#39A900]"></div>
                    <div class="p-2">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-orange-500 to-red-600 rounded-xl shadow-lg">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xs font-bold text-gray-900">Información del Proveedor</h2>
                                <p class="text-gray-600 text-[10px]">Proveedor que suministra este animal</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Procedencia/Proveedor
                                </label>
                                <div class="relative group">
                                    <select 
                                        wire:model.live="idProveedor" 
                                        class="cursor-pointer w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl appearance-none text-xs @error('idProveedor') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"
                                    >
                                        <option value="">Seleccionar proveedor</option>
                                        @foreach($proveedores as $proveedor)
                                            <option value="{{ $proveedor->idProve }}">
                                                {{ $proveedor->nomProve }} ({{ $proveedor->tipSumProve ?? 'Sin tipo' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-1.5 pointer-events-none">
                                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                    <div class="absolute inset-0 bg-gradient-to-r from-orange-500/5 to-red-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('idProveedor')
                                    <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                        <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>
                            <div>
                                <label for="ubicacionAni" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Ubicación
                                </label>
                                <div class="relative group">
                                    <input type="text" wire:model="ubicacionAni" id="ubicacionAni"
                                           class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('ubicacionAni') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror">
                                    <div class="absolute inset-0 bg-gradient-to-r from-orange-500/5 to-red-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('ubicacionAni')
                                    <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                        <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>
                        </div>

                        <!-- Información del proveedor seleccionado -->
                        @if($proveedorSeleccionado)
                            <div class="bg-blue-50/50 border border-blue-200 rounded-2xl p-2 mt-2">
                                <h4 class="text-[10px] font-bold text-blue-900 mb-1 flex items-center gap-1">
                                    <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Información del Proveedor Seleccionado
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-[10px]">
                                    <div>
                                        <span class="font-medium text-gray-700">Nombre:</span>
                                        <span class="text-gray-900 ml-1">{{ $proveedorSeleccionado->nomProve }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-700">Tipo:</span>
                                        <span class="text-gray-900 ml-1">{{ $proveedorSeleccionado->tipSumProve ?? 'No especificado' }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-700">Contacto:</span>
                                        <span class="text-gray-900 ml-1">{{ $proveedorSeleccionado->conProve ?? 'No especificado' }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-700">Teléfono:</span>
                                        <span class="text-gray-900 ml-1">{{ $proveedorSeleccionado->telProve ?? 'No especificado' }}</span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Fechas Importantes -->
                <div class="flex-1 border border-gray-300 rounded-3xl overflow-hidden">
                    <div class="h-1.5 bg-gradient-to-r from-[#39A900] to-[#000000]"></div>
                    <div class="p-2">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-orange-500 to-red-600 rounded-xl shadow-lg">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xs font-bold text-gray-900">Fechas Importantes</h2>
                                <p class="text-gray-600 text-[10px]">Fechas clave del animal</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            <div>
                                <label for="fecNacAni" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Fecha de Nacimiento
                                </label>
                                <div class="relative group">
                                    <input type="date" wire:model="fecNacAni" id="fecNacAni"
                                           class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('fecNacAni') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror">
                                    <div class="absolute inset-0 bg-gradient-to-r from-orange-500/5 to-red-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('fecNacAni')
                                    <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                        <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>
                            <div>
                                <label for="fecComAni" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Fecha de Compra/Incorporación
                                </label>
                                <div class="relative group">
                                    <input type="date" wire:model="fecComAni" id="fecComAni"
                                           class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('fecComAni') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror">
                                    <div class="absolute inset-0 bg-gradient-to-r from-orange-500/5 to-red-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                </div>
                                @error('fecComAni')
                                    <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                        <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección: Estados del Animal -->
            <div class="flex flex-col md:flex-row gap-2">
                <div class="flex-1 border border-gray-300 rounded-3xl overflow-hidden">
                    <div class="h-1.5 bg-gradient-to-r from-[#000000] to-[#39A900]"></div>
                    <div class="p-2">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl shadow-lg">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2M9 19"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xs font-bold text-gray-900">Estados del Animal</h2>
                                <p class="text-gray-600 text-[10px]">Estado actual del animal</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                            <div>
                                <label for="estAni" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Estado General
                                </label>
                                <div class="relative group">
                                    <select wire:model="estAni" id="estAni"
                                            class="cursor-pointer w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl appearance-none text-xs @error('estAni') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror">
                                        <option value="vivo">Vivo</option>
                                        <option value="muerto">Muerto</option>
                                        <option value="vendido">Vendido</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-1.5 pointer-events-none">
                                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </div>
                                @error('estAni')
                                    <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                        <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>
                            <div>
                                <label for="estReproAni" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Estado Reproductivo
                                </label>
                                <div class="relative group">
                                    <select wire:model="estReproAni" id="estReproAni"
                                            class="cursor-pointer w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl appearance-none text-xs @error('estReproAni') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror">
                                        <option value="no_aplica">No aplica</option>
                                        <option value="ciclo">En ciclo</option>
                                        <option value="cubierta">Cubierta</option>
                                        <option value="gestacion">Gestación</option>
                                        <option value="parida">Parida</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-1.5 pointer-events-none">
                                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </div>
                                @error('estReproAni')
                                    <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                        <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>
                            <div>
                                <label for="estSaludAni" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                    Estado de Salud
                                </label>
                                <div class="relative group">
                                    <select wire:model="estSaludAni" id="estSaludAni"
                                            class="cursor-pointer w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl appearance-none text-xs @error('estSaludAni') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror">
                                        <option value="saludable">Saludable</option>
                                        <option value="enfermo">Enfermo</option>
                                        <option value="tratamiento">En tratamiento</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-1.5 pointer-events-none">
                                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </div>
                                @error('estSaludAni')
                                    <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                        <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección: Observaciones -->
            <div class="flex flex-col md:flex-row gap-2">
                <div class="flex-1 border border-gray-300 rounded-3xl overflow-hidden">
                    <div class="h-1.5 bg-gradient-to-r from-[#39A900] to-[#000000]"></div>
                    <div class="p-2">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-yellow-500 to-orange-600 rounded-xl shadow-lg">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xs font-bold text-gray-900">Información Adicional</h2>
                                <p class="text-gray-600 text-[10px]">Observaciones especiales, características específicas, cuidados requeridos, etc.</p>
                            </div>
                        </div>

                        <div>
                            <label for="obsAni" class="block text-[10px] font-bold text-gray-700 mb-0.5">
                                Observaciones
                            </label>
                            <div class="relative group">
                                <textarea wire:model="obsAni" id="obsAni" rows="4"
                                          placeholder="Escriba aquí cualquier observación importante sobre el animal..."
                                          class="w-full px-1.5 py-1 bg-white/50 border-2 border-gray-200 rounded-2xl shadow-lg focus:outline-none focus:ring-4 focus:ring-green-500/20 focus:border-green-700 transition-all duration-300 group-hover:shadow-xl text-xs @error('obsAni') border-red-400 focus:ring-red-500/20 focus:border-red-500 @enderror"></textarea>
                                <div class="absolute inset-0 bg-gradient-to-r from-yellow-500/5 to-orange-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                            </div>
                            @error('obsAni')
                                <p class="mt-0.5 text-[10px] text-red-600 flex items-center">
                                    <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones -->
            <div class="flex justify-center space-x-2 pt-2">
                <button type="button" wire:click="resetForm"
                        class="cursor-pointer group relative inline-flex items-center justify-center px-2.5 py-1 bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                    <svg class="w-3 h-3 mr-1.5 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    <span class="relative z-10 text-xs">Cancelar</span>
                </button>
                <button type="submit"
                        class="cursor-pointer group relative inline-flex items-center justify-center px-2.5 py-1 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                    <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                    <svg class="w-3 h-3 mr-1.5 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="relative z-10 text-xs">Actualizar Animal</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('livewire:initialized', () => {
    Livewire.on('notify', (event) => {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: event.type,
            title: event.message,
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            customClass: {
                popup: 'text-xs'
            },
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });
    });
});
</script>