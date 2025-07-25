<?php
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component {
    public User $user;
}; ?>

@section('title', 'Detalles del Usuario')

<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Detalles del Usuario</h1>
        <div class="flex space-x-2">
            <a href="{{ route('settings.manage-users.edit', $user->id) }}" wire:navigate
                class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                <i class="fas fa-edit mr-2"></i>Editar
            </a>
            <a href="{{ route('settings.manage-users') }}" wire:navigate
                class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                <i class="fas fa-arrow-left mr-2"></i>Volver
            </a>
        </div>
    </div>

    <!-- Información del Usuario -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800">{{ $user->nomUsu }} {{ $user->apeUsu }}</h2>
            <p class="text-sm text-gray-600">
                {{ $user->rol->nomRol ?? 'Sin rol asignado' }} • 
                {{ $user->tipDocUsu }} {{ $user->numDocUsu }}
            </p>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Información Personal -->
                <div class="space-y-4">
                    <h3 class="text-lg font-medium text-gray-900 border-b border-gray-200 pb-2">
                        Información Personal
                    </h3>

                    <div>
                        <label class="text-sm font-medium text-gray-500">Nombre Completo:</label>
                        <p class="text-gray-900">{{ $user->nomUsu }} {{ $user->apeUsu }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-500">Documento:</label>
                        <p class="text-gray-900">{{ $user->tipDocUsu }} {{ $user->numDocUsu }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-500">Fecha de Nacimiento:</label>
                        <p class="text-gray-900">{{ \Carbon\Carbon::parse($user->fecNacUsu)->format('d/m/Y') }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-500">Sexo:</label>
                        <p class="text-gray-900">{{ $user->sexUsu }}</p>
                    </div>
                </div>

                <!-- Información de Contacto -->
                <div class="space-y-4">
                    <h3 class="text-lg font-medium text-gray-900 border-b border-gray-200 pb-2">
                        Información de Contacto
                    </h3>

                    <div>
                        <label class="text-sm font-medium text-gray-500">Email:</label>
                        <p class="text-gray-900">
                            <a href="mailto:{{ $user->email }}" class="text-blue-600 hover:text-blue-800">
                                {{ $user->email }}
                            </a>
                        </p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-500">Teléfono:</label>
                        <p class="text-gray-900">
                            @if($user->contacto && $user->contacto->celCon)
                            <a href="tel:{{ $user->contacto->celCon }}" class="text-blue-600 hover:text-blue-800">
                                {{ $user->contacto->celCon }}
                            </a>
                            @else
                            No especificado
                            @endif
                        </p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-500">Rol:</label>
                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800 mt-1">
                            {{ $user->rol->nomRol ?? 'Sin rol asignado' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Dirección -->
            @if($user->direccion)
            <div class="mt-6 pt-6 border-t border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Dirección</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500">Calle:</label>
                        <p class="text-gray-700">{{ $user->direccion->calDir }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Barrio:</label>
                        <p class="text-gray-700">{{ $user->direccion->barDir }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Ciudad:</label>
                        <p class="text-gray-700">{{ $user->direccion->ciuDir }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Departamento:</label>
                        <p class="text-gray-700">{{ $user->direccion->depDir }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">Código Postal:</label>
                        <p class="text-gray-700">{{ $user->direccion->codPosDir }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-500">País:</label>
                        <p class="text-gray-700">{{ $user->direccion->paiDir }}</p>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Footer con acciones -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-between items-center">
            <div class="text-sm text-gray-500">
                ID: {{ $user->id }} • Registrado: {{ $user->created_at->format('d/m/Y H:i') }}
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('settings.manage-users.edit', $user->id) }}" wire:navigate
                    class="text-yellow-600 hover:text-yellow-800 font-medium">
                    <i class="fas fa-edit mr-1"></i>Editar información
                </a>
                <span class="text-gray-300">|</span>
                <a href="{{ route('settings.manage-users') }}" wire:navigate
                    class="text-gray-600 hover:text-gray-800 font-medium">
                    <i class="fas fa-users mr-1"></i>Volver a la lista
                </a>
            </div>
        </div>
    </div>
</div>