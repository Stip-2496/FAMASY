@extends('layouts.app')

@section('content')
<div class="flex justify-center items-center py-12">
    <div class="w-full max-w-4xl bg-white p-10 rounded-xl shadow-xl border border-green-300">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-extrabold text-gray-700">Editar Proveedor</h2>
            <p class="mt-2 text-gray-500 text-base">Modifica los datos del proveedor</p>
        </div>

        <form action="{{ route('proveedores.update', $proveedor->idProve) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="nomProve" class="block text-sm font-medium text-gray-700">Nombre</label>
                    <input type="text" name="nomProve" id="nomProve" value="{{ $proveedor->nomProve }}" required
                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:ring-green-600 focus:border-green-600 sm:text-sm">
                </div>

                <div>
                    <label for="nitProve" class="block text-sm font-medium text-gray-700">NIT</label>
                    <input type="text" name="nitProve" id="nitProve" value="{{ $proveedor->nitProve }}"
                       class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:ring-green-600 focus:border-green-600 sm:text-sm">
                </div>

                <div>
                    <label for="conProve" class="block text-sm font-medium text-gray-700">Contacto</label>
                    <input type="text" name="conProve" id="conProve" value="{{ $proveedor->conProve }}"
                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:ring-green-600 focus:border-green-600 sm:text-sm">
                </div>

                <div>
                    <label for="telProve" class="block text-sm font-medium text-gray-700">Teléfono</label>
                    <input type="text" name="telProve" id="telProve" value="{{ $proveedor->telProve }}"
                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:ring-green-600 focus:border-green-600 sm:text-sm">
                </div>

                <div>
                    <label for="emailProve" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" name="emailProve" id="emailProve" value="{{ $proveedor->emailProve }}"
                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:ring-green-600 focus:border-green-600 sm:text-sm">
                </div>

                <div>
                    <label for="dirProve" class="block text-sm font-medium text-gray-700">Dirección</label>
                    <input type="text" name="dirProve" id="dirProve" value="{{ $proveedor->dirProve }}"
                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:ring-green-600 focus:border-green-600 sm:text-sm">
                </div>

                <div>
                    <label for="tipSumProve" class="block text-sm font-medium text-gray-700">Tipo de Suministro</label>
                    <input type="text" name="tipSumProve" id="tipSumProve" value="{{ $proveedor->tipSumProve }}"
                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:ring-green-600 focus:border-green-600 sm:text-sm">
                </div>

                <div class="md:col-span-2">
                    <label for="obsProve" class="block text-sm font-medium text-gray-700">Observaciones</label>
                    <textarea name="obsProve" id="obsProve" rows="3"
                        class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:ring-green-600 focus:border-green-600 sm:text-sm">{{ $proveedor->obsProve }}</textarea>
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
