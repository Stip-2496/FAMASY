@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-6 mt-10">

    @if ($errors->any())
    <div class="bg-red-100 text-red-600 p-4 rounded mb-4 shadow">
        <ul class="list-disc pl-5">
            @foreach ($errors->all() as $error)
            <li>- {{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="bg-white border border-green-300 shadow-lg rounded-xl p-8">
        <div class="text-center mb-10">
            <h2 class="text-4xl font-extrabold text-gray-700">Registrar Proveedor</h2>
            <p class="mt-2 text-lg text-gray-500">Ingresa los datos completos del proveedor</p>
        </div>

        <form action="{{ route('proveedores.store') }}" method="POST" class="space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre</label>
                    <input type="text" name="nombre" id="nombre" required
                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:ring-green-600 focus:border-green-600 sm:text-sm">
                </div>
                <div>
                    <label for="nit" class="block text-sm font-medium text-gray-700">NIT</label>
                    <input type="text" name="nit" id="nit" required
                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:ring-green-600 focus:border-green-600 sm:text-sm">
                </div>
                <div>
                    <label for="contacto" class="block text-sm font-medium text-gray-700">Contacto</label>
                    <input type="text" name="contacto" id="contacto"
                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:ring-green-600 focus:border-green-600 sm:text-sm">
                </div>
                <div>
                    <label for="telefono" class="block text-sm font-medium text-gray-700">Teléfono</label>
                    <input type="text" name="telefono" id="telefono"
                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:ring-green-600 focus:border-green-600 sm:text-sm">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="email" id="email"
                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:ring-green-600 focus:border-green-600 sm:text-sm">
                </div>
                <div>
                    <label for="direccion" class="block text-sm font-medium text-gray-700">Dirección</label>
                    <input type="text" name="direccion" id="direccion"
                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:ring-green-600 focus:border-green-600 sm:text-sm">
                </div>
                <div>
                    <label for="tipoSuministro" class="block text-sm font-medium text-gray-700">Tipo de Suministro</label>
                    <input type="text" name="tipoSuministro" id="tipoSuministro"
                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:ring-green-600 focus:border-green-600 sm:text-sm">
                </div>
                <div class="md:col-span-2">
                    <label for="observaciones" class="block text-sm font-medium text-gray-700">Observaciones</label>
                    <textarea name="observaciones" id="observaciones" rows="4"
                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:ring-green-600 focus:border-green-600 sm:text-sm"></textarea>
                </div>
            </div>

            <div class="mt-8 flex justify-center space-x-4">
                <a href="{{ route('proveedores.index') }}"
                    class="px-5 py-2 rounded-md border border-green-600 text-green-600 font-semibold hover:bg-green-50">
                    ← Atrás
                </a>
                <button type="submit"
                    class="px-6 py-2 bg-green-600 text-white rounded-md font-semibold hover:bg-green-700 shadow">
                    Actualizar
                </button>
            </div>
        </form>
    </div>
</div>
@endsection