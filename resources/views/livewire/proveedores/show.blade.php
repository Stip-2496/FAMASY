<?php
// resources/views/livewire/proveedores/show.blade.php

use App\Models\Proveedor;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component {
    public Proveedor $proveedor;
}; ?>

@section('title', 'Detalles de Proveedor')

<div class="flex items-center justify-center min-h-screen py-3">
    <div class="w-full max-w-7xl mx-auto bg-white/80 backdrop-blur-xl shadow rounded-3xl p-3 relative border border-white/20">
        <!-- Encabezado -->
        <div class="text-center mb-3">
            <div class="flex justify-center mb-1">
                <div class="p-2 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl shadow-xl transform rotate-3 hover:rotate-0 transition-transform duration-300">
                    <div class="w-4 h-4 text-white flex items-center justify-center">
                        <i class="fas fa-truck text-base"></i>
                    </div>
                </div>
            </div>
            <h1 class="text-base font-black bg-gradient-to-r from-gray-900 via-gray-800 to-green-800 bg-clip-text text-transparent mb-1">
                Detalles del Proveedor
            </h1>
            <p class="text-gray-600 text-xs">Información completa del proveedor</p>
        </div>

        <!-- Botones -->
        <div class="absolute top-2 right-2 flex space-x-2">
            <a href="{{ route('proveedores.edit', $proveedor->idProve) }}" wire:navigate
               class="group relative inline-flex items-center px-2 py-1 bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                <svg class="w-3 h-3 mr-1 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                <span class="relative z-10 text-xs">Editar</span>
            </a>
            <a href="{{ route('proveedores.index') }}" wire:navigate
               class="group relative inline-flex items-center px-2 py-1 bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                <svg class="w-3 h-3 mr-1 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span class="relative z-10 text-xs">Volver</span>
            </a>
        </div>

        <!-- Tarjeta de información principal -->
        <div class="border border-gray-300 rounded-3xl overflow-hidden bg-gradient-to-br from-white to-gray-50">
            <div class="h-1.5 bg-gradient-to-r from-[#000000] to-[#39A900]"></div>

            <div class="p-2">
                <!-- Header del perfil -->
                <div class="flex items-start space-x-2 mb-2 pb-2 border-b border-gray-200">
                    <div class="relative">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl shadow-lg flex items-center justify-center">
                            <span class="text-white text-lg font-bold">
                                {{ strtoupper(substr($proveedor->nomProve, 0, 1)) }}{{ strtoupper(substr($proveedor->apeProve, 0, 1)) }}
                            </span>
                        </div>
                        <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 border-2 border-white rounded-full"></div>
                    </div>
                    <div class="flex-1">
                        <h2 class="text-xs font-bold text-gray-900 mb-1">{{ $proveedor->nomProve }} {{ $proveedor->apeProve }}</h2>
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="inline-flex items-center text-[10px] font-medium px-1.5 py-0.5 rounded-full bg-gray-100 text-gray-700 border border-gray-300">
                                <svg class="w-2.5 h-2.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                NIT: {{ $proveedor->nitProve }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Primera fila: Información de Contacto y Ubicación -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    <!-- Información de Contacto -->
                    <div class="bg-white rounded-2xl p-2 border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-300">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl shadow-md">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xs font-bold text-gray-900">Información de Contacto</h3>
                        </div>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between py-1 border-b border-gray-100">
                                <div class="flex items-center space-x-2">
                                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    <span class="text-[10px] text-gray-500 font-medium">Celular</span>
                                </div>
                                <p class="text-xs text-gray-900 font-semibold">{{ $proveedor->conProve ?? 'No especificado' }}</p>
                            </div>
                            <div class="flex items-center justify-between py-1 border-b border-gray-100">
                                <div class="flex items-center space-x-2">
                                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                    </svg>
                                    <span class="text-[10px] text-gray-500 font-medium">Teléfono</span>
                                </div>
                                <p class="text-xs text-gray-900 font-semibold">
                                    @if($proveedor->telProve)
                                    <a href="tel:{{ $proveedor->telProve }}" class="text-blue-600 hover:text-blue-800 hover:underline transition-colors duration-200">
                                        {{ $proveedor->telProve }}
                                    </a>
                                    @else
                                    <span class="inline-flex items-center text-xs text-gray-400 italic">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                        </svg>
                                        No especificado
                                    </span>
                                    @endif
                                </p>
                            </div>
                            <div class="flex items-center justify-between py-1 border-b border-gray-100">
                                <div class="flex items-center space-x-2">
                                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                                    </svg>
                                    <span class="text-[10px] text-gray-500 font-medium">Email</span>
                                </div>
                                <p class="text-xs text-gray-900 font-semibold">
                                    @if($proveedor->emailProve)
                                    <a href="mailto:{{ $proveedor->emailProve }}" class="text-blue-600 hover:text-blue-800 hover:underline transition-colors duration-200">
                                        {{ $proveedor->emailProve }}
                                    </a>
                                    @else
                                    <span class="inline-flex items-center text-xs text-gray-400 italic">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                        </svg>
                                        No especificado
                                    </span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Ubicación -->
                    <div class="bg-white rounded-2xl p-2 border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-300">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-orange-500 to-red-600 rounded-xl shadow-md">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xs font-bold text-gray-900">Ubicación</h3>
                        </div>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between py-1 border-b border-gray-100">
                                <div class="flex items-center space-x-2">
                                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <span class="text-[10px] text-gray-500 font-medium">Dirección</span>
                                </div>
                                <p class="text-xs text-gray-900 font-semibold">{{ $proveedor->dirProve ?? 'No especificada' }}</p>
                            </div>
                            <div class="flex items-center justify-between py-1 border-b border-gray-100">
                                <div class="flex items-center space-x-2">
                                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553-2.276A1 1 0 0021 13.382V6.618a1 1 0 00-1.447-.894L15 8"></path>
                                    </svg>
                                    <span class="text-[10px] text-gray-500 font-medium">Ciudad</span>
                                </div>
                                <p class="text-xs text-gray-900 font-semibold">{{ $proveedor->ciuProve ?? 'No especificada' }}</p>
                            </div>
                            <div class="flex items-center justify-between py-1">
                                <div class="flex items-center space-x-2">
                                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                    </svg>
                                    <span class="text-[10px] text-gray-500 font-medium">Departamento</span>
                                </div>
                                <p class="text-xs text-gray-900 font-semibold">{{ $proveedor->depProve ?? 'No especificado' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Segunda fila: Información Comercial y Observaciones -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mt-2">
                    <!-- Información Comercial -->
                    <div class="bg-white rounded-2xl p-2 border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-300">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl shadow-md">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xs font-bold text-gray-900">Información Comercial</h3>
                        </div>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between py-1">
                                <div class="flex items-center space-x-2">
                                    <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                    <span class="text-[10px] text-gray-500 font-medium">Tipo de Suministro</span>
                                </div>
                                <span class="inline-flex items-center text-[10px] font-semibold px-1.5 py-0.5 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 text-white shadow-sm">
                                    {{ $proveedor->tipSumProve ?? 'No especificado' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Observaciones -->
                    <div class="bg-white rounded-2xl p-2 border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-300">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-gray-500 to-gray-600 rounded-xl shadow-md">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xs font-bold text-gray-900">Observaciones</h3>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-2 border border-gray-100">
                            <p class="text-xs text-gray-900 font-semibold">{{ $proveedor->obsProve ?? 'No especificado' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script para notificaciones -->
<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('notify', (event) => {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });
            
            Toast.fire({
                icon: event.type,
                title: event.message
            });
        });
    });
</script>
```