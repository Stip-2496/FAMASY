@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Editar Proveedor</h1>
        <div class="flex space-x-2">
            <a href="{{ route('proveedores.show', $proveedor->idProve) }}" 
               class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                <i class="fas fa-eye mr-2"></i>Ver
            </a>
            <a href="{{ route('proveedores.index') }}" 
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
        <form action="{{ route('proveedores.update', $proveedor->idProve) }}" method="POST" id="proveedorEditForm">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nombre del Proveedor -->
                <div class="md:col-span-2">
                    <label for="nomProve" class="block text-sm font-medium text-gray-700 mb-2">
                        Nombre del Proveedor <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="nomProve" 
                           name="nomProve" 
                           value="{{ old('nomProve', $proveedor->nomProve) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 @error('nomProve') border-red-500 @enderror"
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
                           name="nitProve" 
                           value="{{ old('nitProve', $proveedor->nitProve) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 @error('nitProve') border-red-500 @enderror"
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
                           name="conProve" 
                           value="{{ old('conProve', $proveedor->conProve) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 @error('conProve') border-red-500 @enderror"
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
                           name="telProve" 
                           value="{{ old('telProve', $proveedor->telProve) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 @error('telProve') border-red-500 @enderror"
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
                           name="emailProve" 
                           value="{{ old('emailProve', $proveedor->emailProve) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 @error('emailProve') border-red-500 @enderror"
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
                           name="dirProve" 
                           value="{{ old('dirProve', $proveedor->dirProve) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 @error('dirProve') border-red-500 @enderror"
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
                            name="tipSumProve" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 @error('tipSumProve') border-red-500 @enderror">
                        <option value="">Seleccione un tipo</option>
                        <option value="Alimentos concentrados" {{ old('tipSumProve', $proveedor->tipSumProve) == 'Alimentos concentrados' ? 'selected' : '' }}>Alimentos concentrados</option>
                        <option value="Medicamentos veterinarios" {{ old('tipSumProve', $proveedor->tipSumProve) == 'Medicamentos veterinarios' ? 'selected' : '' }}>Medicamentos veterinarios</option>
                        <option value="Herramientas agrícolas" {{ old('tipSumProve', $proveedor->tipSumProve) == 'Herramientas agrícolas' ? 'selected' : '' }}>Herramientas agrícolas</option>
                        <option value="Insumos ganaderos" {{ old('tipSumProve', $proveedor->tipSumProve) == 'Insumos ganaderos' ? 'selected' : '' }}>Insumos ganaderos</option>
                        <option value="Servicios veterinarios" {{ old('tipSumProve', $proveedor->tipSumProve) == 'Servicios veterinarios' ? 'selected' : '' }}>Servicios veterinarios</option>
                        <option value="Otros" {{ old('tipSumProve', $proveedor->tipSumProve) == 'Otros' ? 'selected' : '' }}>Otros</option>
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
                              name="obsProve" 
                              rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 @error('obsProve') border-red-500 @enderror"
                              placeholder="Observaciones adicionales sobre el proveedor">{{ old('obsProve', $proveedor->obsProve) }}</textarea>
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
                <a href="{{ route('proveedores.show', $proveedor->idProve) }}" 
                   class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded-lg transition duration-200">
                    Cancelar
                </a>
                <button type="button" 
                        onclick="resetForm()"
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
document.addEventListener('DOMContentLoaded', function() {
    // Validación del NIT en tiempo real
    const nitInput = document.getElementById('nitProve');
    nitInput.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9-]/g, '');
    });

    // Validación del teléfono en tiempo real
    const telInput = document.getElementById('telProve');
    telInput.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9\s\-\(\)]/g, '');
    });
});

function resetForm() {
    // Restaurar valores originales
    document.getElementById('nomProve').value = @json($proveedor->nomProve);
    document.getElementById('nitProve').value = @json($proveedor->nitProve);
    document.getElementById('conProve').value = @json($proveedor->conProve ?? '');
    document.getElementById('telProve').value = @json($proveedor->telProve ?? '');
    document.getElementById('emailProve').value = @json($proveedor->emailProve ?? '');
    document.getElementById('dirProve').value = @json($proveedor->dirProve ?? '');
    document.getElementById('tipSumProve').value = @json($proveedor->tipSumProve ?? '');
    document.getElementById('obsProve').value = @json($proveedor->obsProve ?? '');
}
</script>
@endsection