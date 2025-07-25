<?php
// resources/views/livewire/proveedores/edit.blade.php

use App\Models\Proveedor;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component {
    public Proveedor $proveedor;
    
    // Propiedades públicas tipadas correctamente
    public string $nomProve;
    public string $nitProve;
    public ?string $conProve = null;
    public ?string $telProve = null;
    public ?string $emailProve = null;
    public ?string $dirProve = null;
    public ?string $tipSumProve = null;
    public ?string $obsProve = null;

    // Método público para las reglas
    public function rules(): array
    {
        return $this->proveedor->getRules($this->proveedor->idProve);
    }

    public function mount(Proveedor $proveedor): void
    {
        $this->proveedor = $proveedor;
        $this->fill(
            $proveedor->only([
                'nomProve', 'nitProve', 'conProve', 'telProve',
                'emailProve', 'dirProve', 'tipSumProve', 'obsProve'
            ])
        );
    }

    public function update(): void
    {
        $validated = $this->validate();
        
        try {
            $this->proveedor->update($validated);
            session()->flash('success', 'Información del proveedor actualizada correctamente');
            $this->redirect(route('proveedores.index'), navigate: true);
        } catch (\Exception $e) {
            session()->flash('error', 'Error al actualizar el proveedor: ' . $e->getMessage());
        }
    }

    public function resetForm(): void
    {
        $this->fill(
            $this->proveedor->only([
                'nomProve', 'nitProve', 'conProve', 'telProve',
                'emailProve', 'dirProve', 'tipSumProve', 'obsProve'
            ])
        );
        $this->resetErrorBag();
    }
}; ?>

@section('title', 'Editar proveedores')

<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Editar Proveedor</h1>
        <div class="flex space-x-2">
            <a href="{{ route('proveedores.show', $proveedor->idProve) }}" wire:navigate
               class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                <i class="fas fa-eye mr-2"></i>Ver
            </a>
            <a href="{{ route('proveedores.index') }}" wire:navigate
               class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                <i class="fas fa-arrow-left mr-2"></i>Volver
            </a>
        </div>
    </div>

    <!-- Información actual -->
    <div class="bg-blue-50 border-2 border-blue-300 rounded-lg p-4 mb-6">
        <h2 class="text-lg font-medium text-blue-800 mb-2">Editando: {{ $proveedor->nomProve }}</h2>
        <p class="text-sm text-blue-600">NIT: {{ $proveedor->nitProve }} | Registrado: {{ $proveedor->created_at->format('d/m/Y') }}</p>
    </div>

    <!-- Formulario -->
    <div class="bg-white rounded-lg shadow-md border-2 border-gray-400 p-6">
        <form wire:submit="update">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nombre del Proveedor -->
                <div class="md:col-span-2">
                    <label for="nomProve" class="block text-sm font-medium text-gray-700 mb-2">
                        Nombre del Proveedor <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="nomProve" 
                           wire:model="nomProve" 
                           class="w-full px-4 py-2 border border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500 text-gray-900 bg-white @error('nomProve') border-red-500 @enderror"
                           placeholder="Ingrese el nombre del proveedor"
                           required>
                    @error('nomProve')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- NIT -->
                <div>
                    <label for="nitProve" class="block text-sm font-medium text-gray-700 mb-2">
                        NIT <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="nitProve" 
                           wire:model="nitProve" 
                           class="w-full px-4 py-2 border border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500 text-gray-900 bg-white @error('nitProve') border-red-500 @enderror"
                           placeholder="Ej: 900123456-7"
                           required>
                    @error('nitProve')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Contacto -->
                <div>
                    <label for="conProve" class="block text-sm font-medium text-gray-700 mb-2">
                        Contacto
                    </label>
                    <input type="text" 
                           id="conProve" 
                           wire:model="conProve" 
                           class="w-full px-4 py-2 border border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500 text-gray-900 bg-white @error('conProve') border-red-500 @enderror"
                           placeholder="Nombre del contacto">
                    @error('conProve')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Teléfono -->
                <div>
                    <label for="telProve" class="block text-sm font-medium text-gray-700 mb-2">
                        Teléfono
                    </label>
                    <input type="text" 
                           id="telProve" 
                           wire:model="telProve" 
                           class="w-full px-4 py-2 border border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500 text-gray-900 bg-white @error('telProve') border-red-500 @enderror"
                           placeholder="Ej: 3001234567">
                    @error('telProve')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="emailProve" class="block text-sm font-medium text-gray-700 mb-2">
                        Email
                    </label>
                    <input type="email" 
                           id="emailProve" 
                           wire:model="emailProve" 
                           class="w-full px-4 py-2 border border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500 text-gray-900 bg-white @error('emailProve') border-red-500 @enderror"
                           placeholder="correo@ejemplo.com">
                    @error('emailProve')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Dirección -->
                <div class="md:col-span-2">
                    <label for="dirProve" class="block text-sm font-medium text-gray-700 mb-2">
                        Dirección
                    </label>
                    <input type="text" 
                           id="dirProve" 
                           wire:model="dirProve" 
                           class="w-full px-4 py-2 border border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500 text-gray-900 bg-white @error('dirProve') border-red-500 @enderror"
                           placeholder="Dirección completa">
                    @error('dirProve')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tipo de Suministro -->
                <div class="md:col-span-2">
                    <label for="tipSumProve" class="block text-sm font-medium text-gray-700 mb-2">
                        Tipo de Suministro
                    </label>
                    <select id="tipSumProve" 
                            wire:model="tipSumProve" 
                            class="w-full px-4 py-2 border border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500 text-gray-900 bg-white @error('tipSumProve') border-red-500 @enderror">
                        <option value="">Seleccione un tipo</option>
                        <option value="Alimentos concentrados">Alimentos concentrados</option>
                        <option value="Medicamentos veterinarios">Medicamentos veterinarios</option>
                        <option value="Herramientas agrícolas">Herramientas agrícolas</option>
                        <option value="Insumos ganaderos">Insumos ganaderos</option>
                        <option value="Servicios veterinarios">Servicios veterinarios</option>
                        <option value="Otros">Otros</option>
                    </select>
                    @error('tipSumProve')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Observaciones -->
                <div class="md:col-span-2">
                    <label for="obsProve" class="block text-sm font-medium text-gray-700 mb-2">
                        Observaciones
                    </label>
                    <textarea id="obsProve" 
                              wire:model="obsProve" 
                              rows="3"
                              class="w-full px-4 py-2 border border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-yellow-500 text-gray-900 bg-white @error('obsProve') border-red-500 @enderror"
                              placeholder="Observaciones adicionales sobre el proveedor"></textarea>
                    @error('obsProve')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Información de cambios -->
            <div class="mt-6 p-4 bg-gray-50 rounded-lg border-2 border-gray-300">
                <h3 class="text-sm font-medium text-gray-700 mb-2">Información de registro</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
                    <div>
                        <span class="font-medium">Creado:</span> {{ $proveedor->created_at->format('d/m/Y H:i') }}
                    </div>
                    <div>
                        <span class="font-medium">Última actualización:</span> {{ $proveedor->updated_at->format('d/m/Y H:i') }}
                    </div>
                </div>
            </div>

            <!-- Botones -->
            <div class="flex justify-end space-x-4 mt-6 pt-6 border-t border-gray-200">
                <a href="{{ route('proveedores.show', $proveedor->idProve) }}" wire:navigate
                   class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded-lg transition duration-200">
                    Cancelar
                </a>
                <button type="button" 
                        wire:click="resetForm"
                        class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-6 rounded-lg transition duration-200">
                    Restaurar
                </button>
                <button type="submit" 
                        class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-6 rounded-lg transition duration-200">
                    <i class="fas fa-save mr-2"></i>Actualizar Proveedor
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('livewire:initialized', () => {
    // Validación del NIT en tiempo real
    Livewire.on('nitProve', (value) => {
        document.getElementById('nitProve').value = value.replace(/[^0-9-]/g, '');
    });

    // Validación del teléfono en tiempo real
    Livewire.on('telProve', (value) => {
        document.getElementById('telProve').value = value.replace(/[^0-9\s\-\(\)]/g, '');
    });

    // Validación del celular - solo números, máximo 10 dígitos
    Livewire.on('conProve', (value) => {
        const input = document.getElementById('conProve');
        input.value = value.replace(/[^0-9]/g, '');
        
        if (input.value.length > 10) {
            input.value = input.value.slice(0, 10);
        }
    });
});
</script>