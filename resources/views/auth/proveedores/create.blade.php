@section('title', 'Crear proveedores')

<x-auth-layout>
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Registrar Nuevo Proveedor</h1>
        <a href="{{ route('proveedores.index') }}"
            class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
            <i class="fas fa-arrow-left mr-2"></i>Volver
        </a>
    </div>

    <!-- Formulario -->
    <div class="bg-white rounded-lg shadow-md border-2 border-gray-400 p-6">
        <form action="{{ route('proveedores.store') }}" method="POST" id="proveedorForm">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nombre del Proveedor -->
                <div class="md:col-span-2">
                    <label for="nomProve" class="block text-sm font-medium text-gray-700 mb-2">
                        Nombre del Proveedor <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                        id="nomProve"
                        name="nomProve"
                        value="{{ old('nomProve') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('nomProve') border-red-500 @enderror"
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
                        value="{{ old('nitProve') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('nitProve') border-red-500 @enderror"
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
                        name="conProve"
                        value="{{ old('conProve') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('conProve') border-red-500 @enderror"
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
                        name="telProve"
                        value="{{ old('telProve') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('telProve') border-red-500 @enderror"
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
                        name="emailProve"
                        value="{{ old('emailProve') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('emailProve') border-red-500 @enderror"
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
                        value="{{ old('dirProve') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('dirProve') border-red-500 @enderror"
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
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('tipSumProve') border-red-500 @enderror">
                        <option value="">Seleccione un tipo</option>
                        <option value="Alimentos concentrados" {{ old('tipSumProve') == 'Alimentos concentrados' ? 'selected' : '' }}>Alimentos concentrados</option>
                        <option value="Medicamentos veterinarios" {{ old('tipSumProve') == 'Medicamentos veterinarios' ? 'selected' : '' }}>Medicamentos veterinarios</option>
                        <option value="Herramientas agrícolas" {{ old('tipSumProve') == 'Herramientas agrícolas' ? 'selected' : '' }}>Herramientas agrícolas</option>
                        <option value="Insumos ganaderos" {{ old('tipSumProve') == 'Insumos ganaderos' ? 'selected' : '' }}>Insumos ganaderos</option>
                        <option value="Servicios veterinarios" {{ old('tipSumProve') == 'Servicios veterinarios' ? 'selected' : '' }}>Servicios veterinarios</option>
                        <option value="Otros" {{ old('tipSumProve') == 'Otros' ? 'selected' : '' }}>Otros</option>
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
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('obsProve') border-red-500 @enderror"
                        placeholder="Observaciones adicionales sobre el proveedor">{{ old('obsProve') }}</textarea>
                    @error('obsProve')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Botones -->
            <div class="flex justify-end space-x-4 mt-6 pt-6 border-t border-gray-200">
                <a href="{{ route('proveedores.index') }}"
                    class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded-lg transition duration-200">
                    Cancelar
                </a>
                <button type="reset"
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

    // Validación del celular - solo números, máximo 10 dígitos
    const celularInput = document.getElementById('conProve');
    celularInput.addEventListener('input', function() {
        // Eliminar cualquier caracter que no sea número
        this.value = this.value.replace(/[^0-9]/g, '');
        
        // Limitar a 10 dígitos
        if (this.value.length > 10) {
            this.value = this.value.slice(0, 10);
        }
    });
});
</script>
</x-auth-layout>