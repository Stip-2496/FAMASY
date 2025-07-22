@section('title', 'Proveedores')

<x-auth-layout>
    <div class="container mx-auto px-4 py-6">
    <!-- Header centrado como en la imagen 4 -->
    <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-gray-800 mb-2">Lista de Proveedores</h1>
        <p class="text-gray-600">Gestiona y administra todos tus proveedores</p>
    </div>

    <!-- Mensajes de éxito/error -->
    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        {{ session('error') }}
    </div>
    @endif

    <!-- Barra de búsqueda con botón integrado -->
    <div class="bg-white rounded-lg shadow-md border-2 border-gray-200 p-4 mb-6">
        <form action="{{ route('proveedores.search') }}" method="GET">
            <div class="flex gap-4 items-end">
                <div class="flex-1">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Buscar por NIT</label>
                    <input type="text"
                        id="search"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Ingrese el NIT a buscar..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                </div>
                <div class="flex gap-2">
                    <button type="submit"
                        class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-lg transition duration-200">
                        <i class="fas fa-search mr-2"></i>Buscar
                    </button>
                    <a href="{{ route('proveedores.create') }}"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                        <i class="fas fa-plus mr-2"></i>Registrar Proveedor
                    </a>
                    @if(request('search'))
                    <a href="{{ route('proveedores.index') }}"
                        class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                        <i class="fas fa-times mr-2"></i>Limpiar
                    </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Tabla de proveedores -->
    <div class="bg-white rounded-lg shadow-md border-2 border-gray-200 overflow-hidden"></div>

    <!-- Tabla de proveedores -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        @if($proveedores->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-green-600">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Nombre</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">NIT</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Contacto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Teléfono</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($proveedores as $proveedor)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $proveedor->idProve }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $proveedor->nomProve }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $proveedor->nitProve }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $proveedor->conProve ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $proveedor->telProve ?? '000000' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $proveedor->emailProve ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <div class="flex justify-center space-x-2">
                                <a href="{{ route('proveedores.show', $proveedor->idProve) }}"
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded text-sm transition duration-200">
                                    Detalles
                                </a>
                                <a href="{{ route('proveedores.edit', $proveedor->idProve) }}"
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded text-sm transition duration-200">
                                    Editar
                                </a>
                                <form action="{{ route('proveedores.destroy', $proveedor->idProve) }}"
                                    method="POST"
                                    class="inline-block"
                                    onsubmit="return confirm('¿Está seguro de eliminar este proveedor?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded text-sm transition duration-200">
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        <div class="px-6 py-4 bg-gray-50">
            {{ $proveedores->links() }}
        </div>
        @else
        <div class="px-6 py-12 text-center">
            <i class="fas fa-users text-gray-400 text-4xl mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No hay proveedores registrados</h3>
            <p class="text-gray-500 mb-4">Comience agregando su primer proveedor.</p>
            <a href="{{ route('proveedores.create') }}"
                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                <i class="fas fa-plus mr-2"></i>Agregar Proveedor
            </a>
        </div>
        @endif
    </div>
</div>
</x-auth-layout>