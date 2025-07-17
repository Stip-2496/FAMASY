@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-10">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Listado de Proveedores</h2>
        <a href="{{ route('proveedores.create') }}" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
            + Nuevo Proveedor
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-100 text-green-700 p-4 rounded mb-4">
        {{ session('success') }}
    </div>
    @endif
    <div class="mb-4 flex justify-end space-x-2">
        <!-- Formulario de búsqueda -->
        <form action="{{ route('proveedores.index') }}" method="GET" class="flex">
            <input type="text" name="nit" value="{{ request('nit') }}"
                placeholder="Buscar por NIT"
                class="px-4 py-2 border rounded-l-md text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
            <button type="submit"
                class="bg-green-600 text-white px-4 py-2 rounded-r-md hover:bg-green-700 text-sm">
                Buscar
            </button>
        </form>

        <!-- Botón limpiar -->
        <a href="{{ route('proveedores.index') }}"
            class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400 text-sm h-full">
            Limpiar
        </a>
    </div>


    <div class="overflow-x-auto bg-white shadow rounded">


        <!-- Tabla -->
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-100 text-left">
                <tr>
                    <th class="px-4 py-2">Nombre</th>
                    <th class="px-4 py-2">NIT</th>
                    <th class="px-4 py-2">Contacto</th>
                    <th class="px-4 py-2">Teléfono</th>
                    <th class="px-4 py-2">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @if ($proveedores->isEmpty())
                <tr>
                    <td colspan="5" class="px-4 py-4 text-center text-gray-500">
                        No se encontraron proveedores.
                    </td>
                </tr>
                @else
                @foreach ($proveedores as $proveedor)
                <tr>
                    <td class="px-4 py-2">{{ $proveedor->nomProve }}</td>
                    <td class="px-4 py-2">{{ $proveedor->nitProve }}</td>
                    <td class="px-4 py-2">{{ $proveedor->conProve }}</td>
                    <td class="px-4 py-2">{{ $proveedor->telProve }}</td>
                    <td class="px-4 py-2 space-x-2">
                        <a href="{{ route('proveedores.edit', $proveedor->idProve) }}" class="text-blue-600 hover:underline">Editar</a>
                        <form action="{{ route('proveedores.destroy', $proveedor->idProve) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Estás seguro de eliminar este proveedor?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline">Eliminar</button>
                        </form>
                    </td>
                </tr>
                @endforeach
                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection