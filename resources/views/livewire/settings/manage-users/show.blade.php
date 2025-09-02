<?php
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component {
    public User $user;
}; ?>

@section('title', 'Detalles del Usuario')

<div class="container mx-auto px-4 py-4">
    <!-- Header compacto -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mb-4">
        <h1 class="text-xl font-bold text-gray-800">Detalles del Usuario</h1>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('settings.manage-users.edit', $user->id) }}" wire:navigate
                class="bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-medium py-1.5 px-3 rounded transition duration-200">
                <i class="fas fa-edit mr-1"></i>Editar
            </a>
            <a href="{{ route('settings.manage-users') }}" wire:navigate
                class="bg-gray-500 hover:bg-gray-600 text-white text-sm font-medium py-1.5 px-3 rounded transition duration-200">
                <i class="fas fa-arrow-left mr-1"></i>Volver
            </a>
        </div>
    </div>

    <!-- Tarjeta de información compacta -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <!-- Encabezado compacto -->
        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">{{ $user->nomUsu }} {{ $user->apeUsu }}</h2>
            <div class="flex flex-wrap items-center gap-2 mt-1">
                <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-blue-100 text-blue-800">
                    {{ $user->rol->nomRol ?? 'Sin rol asignado' }}
                </span>
                <span class="text-xs text-gray-600">
                    {{ $user->tipDocUsu }} {{ $user->numDocUsu }}
                </span>
            </div>
        </div>

        <!-- Contenido compacto -->
        <div class="p-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Información Personal compacta -->
                <div class="space-y-3">
                    <h3 class="text-base font-medium text-gray-900 pb-1 border-b border-gray-200">
                        Información Personal
                    </h3>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <p class="text-xs text-gray-500">Nombre completo</p>
                            <p class="text-sm text-gray-900">{{ $user->nomUsu }} {{ $user->apeUsu }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Documento</p>
                            <p class="text-sm text-gray-900">{{ $user->tipDocUsu }} {{ $user->numDocUsu }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Nacimiento</p>
                            <p class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($user->fecNacUsu)->format('d/m/Y') }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Sexo</p>
                            <p class="text-sm text-gray-900">{{ $user->sexUsu }}</p>
                        </div>
                    </div>
                </div>

                <!-- Información de Contacto compacta -->
                <div class="space-y-3">
                    <h3 class="text-base font-medium text-gray-900 pb-1 border-b border-gray-200">
                        Información de Contacto
                    </h3>

                    <div class="space-y-2">
                        <div>
                            <p class="text-xs text-gray-500">Email</p>
                            <p class="text-sm">
                                <a href="mailto:{{ $user->email }}" class="text-blue-600 hover:text-blue-800 truncate block">
                                    {{ $user->email }}
                                </a>
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Teléfono</p>
                            <p class="text-sm">
                                @if($user->contacto && $user->contacto->celCon)
                                <a href="tel:{{ $user->contacto->celCon }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $user->contacto->celCon }}
                                </a>
                                @else
                                <span class="text-gray-500">No especificado</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dirección compacta -->
            @if($user->direccion)
            <div class="mt-4 pt-4 border-t border-gray-200">
                <h3 class="text-base font-medium text-gray-900 mb-2">Dirección</h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    <div>
                        <p class="text-xs text-gray-500">Calle</p>
                        <p class="text-sm text-gray-700">{{ $user->direccion->calDir }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Barrio</p>
                        <p class="text-sm text-gray-700">{{ $user->direccion->barDir }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Ciudad</p>
                        <p class="text-sm text-gray-700">{{ $user->direccion->ciuDir }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Departamento</p>
                        <p class="text-sm text-gray-700">{{ $user->direccion->depDir }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Código Postal</p>
                        <p class="text-sm text-gray-700">{{ $user->direccion->codPosDir }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">País</p>
                        <p class="text-sm text-gray-700">{{ $user->direccion->paiDir }}</p>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Footer compacto -->
        <div class="px-4 py-3 bg-gray-50 border-t border-gray-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
            <div class="text-xs text-gray-500">
                ID: {{ $user->id }} • Registrado: {{ $user->created_at->format('d/m/Y H:i') }}
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('settings.manage-users.edit', $user->id) }}" wire:navigate
                    class="text-xs text-yellow-600 hover:text-yellow-800 font-medium">
                    <i class="fas fa-edit mr-1"></i>Editar
                </a>
                <a href="{{ route('settings.manage-users') }}" wire:navigate
                    class="text-xs text-gray-600 hover:text-gray-800 font-medium">
                    <i class="fas fa-users mr-1"></i>Lista de usuarios
                </a>
            </div>
        </div>
    </div>
</div>