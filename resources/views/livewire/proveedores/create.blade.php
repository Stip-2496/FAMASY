<?php
// resources/views/livewire/proveedores/create.blade.php

use App\Models\Proveedor;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component {
    public Proveedor $proveedor;
    
    public string $nomProve = '';
    public string $nitProve = '';
    public ?string $conProve = null;
    public ?string $telProve = null;
    public ?string $emailProve = null;
    public ?string $dirProve = null;
    public ?string $tipSumProve = null;
    public ?string $obsProve = null;

    public function mount(): void
    {
        $this->proveedor = new Proveedor();
    }

    public function rules(): array
    {
        return $this->proveedor->getRules();
    }

    public function save(): void
    {
        $validated = $this->validate();
        
        try {
            Proveedor::create($validated);
            session()->flash('success', 'Proveedor registrado exitosamente');
            $this->redirect(route('proveedores.index'), navigate: true);
        } catch (\Exception $e) {
            session()->flash('error', 'Error al registrar el proveedor: ' . $e->getMessage());
        }
    }

    public function clear(): void
    {
        $this->reset();
        $this->resetErrorBag();
    }
}; ?>

@section('title', 'Crear proveedor')

<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Registrar Nuevo Proveedor</h1>
        <a href="{{ route('proveedores.index') }}" wire:navigate
            class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
            <i class="fas fa-arrow-left mr-2"></i>Volver
        </a>
    </div>

    <!-- Formulario -->
    <div class="bg-white rounded-lg shadow-md border-2 border-gray-400 p-6">
        <form wire:submit="save">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nombre del Proveedor -->
                <div class="md:col-span-2">
                    <label for="nomProve" class="block text-sm font-medium text-gray-700 mb-2">
                        Nombre del Proveedor <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                        id="nomProve"
                        wire:model="nomProve"
                        class="w-full px-4 py-2 border border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white @error('nomProve') border-red-500 @enderror"
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
                        class="w-full px-4 py-2 border border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white @error('nitProve') border-red-500 @enderror"
                        placeholder="Ej: 900123456-7"
                        required>
                    @error('nitProve')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Contacto -->
                <div>
                    <label for="conProve" class="block text-sm font-medium text-gray-700 mb-2">
                        Celular
                    </label>
                    <input type="text"
                        id="conProve"
                        wire:model="conProve"
                        class="w-full px-4 py-2 border border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white @error('conProve') border-red-500 @enderror"
                        placeholder="Ej: 3001234567"
                        maxlength="10"
                        pattern="[0-9]{10}"
                        title="Debe contener exactamente 10 dígitos">
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
                        class="w-full px-4 py-2 border border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white @error('telProve') border-red-500 @enderror"
                        placeholder="Ej: 000-00-00">
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
                        class="w-full px-4 py-2 border border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white @error('emailProve') border-red-500 @enderror"
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
                        class="w-full px-4 py-2 border border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white @error('dirProve') border-red-500 @enderror"
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
                        class="w-full px-4 py-2 border border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white @error('tipSumProve') border-red-500 @enderror">
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
                        class="w-full px-4 py-2 border border-gray-400 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 text-gray-900 bg-white @error('obsProve') border-red-500 @enderror"
                        placeholder="Observaciones adicionales sobre el proveedor"></textarea>
                    @error('obsProve')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Botones -->
            <div class="flex justify-end space-x-4 mt-6 pt-6 border-t border-gray-200">
                <a href="{{ route('proveedores.index') }}" wire:navigate
                    class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded-lg transition duration-200">
                    Cancelar
                </a>
                <button type="button"
                    wire:click="clear"
                    class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-6 rounded-lg transition duration-200">
                    Limpiar
                </button>
                <button type="submit"
                    class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-lg transition duration-200">
                    <i class="fas fa-save mr-2"></i>Guardar Proveedor
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