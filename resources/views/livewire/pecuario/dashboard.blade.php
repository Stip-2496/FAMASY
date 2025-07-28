<?php
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;


new #[Layout('layouts.auth')] class extends Component {

}; ?>

@section('title', 'Dashboard pecuario')

<div class="container mx-auto px-4 py-6">
    <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-gray-800 mb-2">Módulo Pecuario</h1>
        <p class="text-gray-600">Gestión de animales, producción y salud</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        
        <!-- Animales -->
        <div class="bg-white rounded-lg shadow-md border-2 border-gray-200 hover:shadow-lg transition-shadow duration-300">
            <div class="bg-green-700 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-white text-lg font-bold">Animales</h3>
                        <p class="text-green-200 text-sm">Listado y control de animales</p>
                    </div>
                    <i class="fas fa-dog text-white text-2xl"></i>
                </div>
            </div>
            <div class="p-4">
                <p class="text-gray-600 mb-4">Registrar, editar y dar seguimiento a los animales.</p>
                <div class="flex gap-2">
                    <a href="{{ route('pecuario.animales.index') }}" class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded text-sm">
                        Ver Lista
                    </a>
                    <a href="{{ route('pecuario.animales.create') }}" class="bg-green-800 hover:bg-green-900 text-white px-3 py-2 rounded text-sm">
                        Agregar
                    </a>
                </div>
            </div>
        </div>

        <!-- Producción -->
        <div class="bg-white rounded-lg shadow-md border-2 border-gray-200 hover:shadow-lg transition-shadow duration-300">
            <div class="bg-green-500 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-white text-lg font-bold">Producción</h3>
                        <p class="text-green-100 text-sm">Datos de producción animal</p>
                    </div>
                    <i class="fas fa-egg text-white text-2xl"></i>
                </div>
            </div>
            <div class="p-4">
                <p class="text-gray-600 mb-4">Registrar producción como leche, huevos o carne.</p>
                <div class="flex gap-2">
                    <a href="{{ route('pecuario.produccion.index') }}" class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded text-sm">
                        Ver Lista
                    </a>
                    <a href="{{ route('pecuario.produccion.create') }}" class="bg-green-700 hover:bg-green-800 text-white px-3 py-2 rounded text-sm">
                        Registrar
                    </a>
                </div>
            </div>
        </div>

        <!-- Salud e Historial Médico -->
        <div class="bg-white rounded-lg shadow-md border-2 border-gray-200 hover:shadow-lg transition-shadow duration-300">
            <div class="bg-green-600 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-white text-lg font-bold">Historial Médico</h3>
                        <p class="text-green-100 text-sm">Controles, vacunas, tratamientos</p>
                    </div>
                    <i class="fas fa-notes-medical text-white text-2xl"></i>
                </div>
            </div>
            <div class="p-4">
                <p class="text-gray-600 mb-4">Registrar y ver registros médicos por animal.</p>
                <div class="flex gap-2">
                    <a href="{{ route('pecuario.salud-peso.index') }}" class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded text-sm">
                        Ver Historial
                    </a>
                    <a href="{{ route('pecuario.salud-peso.create') }}" class="bg-green-800 hover:bg-green-900 text-white px-3 py-2 rounded text-sm">
                        Nuevo Registro
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>

