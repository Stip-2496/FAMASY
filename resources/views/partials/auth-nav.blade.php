<aside class="flex flex-col h-full select-none sidebar-navigation">
    <!-- Contenedor principal que alinea el encabezado con el sidebar -->
    <!-- Barra del logo y nombre del proyecto - ahora con el mismo ancho que el sidebar -->
    <div class="flex w-[180px] h-10 items-center px-2 bg-white">
        <!-- Logo - ahora con el mismo tamaño que los íconos del sidebar -->
        <img src="/assets/images/FAMASY-logo.jpg" alt="Logo del SENA" class="w-6 h-6" />
        <!-- Línea divisoria - alineada con la línea del sidebar -->
        <div class="h-6 w-px bg-black mx-2"></div>
        <!-- Nombre del proyecto -->
        <h1 class="text-lg font-semibold text-black whitespace-nowrap">FAMASY</h1>
    </div>

    <!-- Perfil del usuario -->
    <div class="flex flex-col items-center py-4 bg-white border-t border-b border-gray-700">
        @auth
            <div class="flex items-center justify-center w-8 h-8 rounded-full bg-gray-200 text-gray-700 font-medium mb-2">
                {{ Auth::user()->initials() }}
            </div>
            <div class="text-center">
                <h2 class="text-xs font-medium text-gray-700">
                    {{ Auth::user()->nomUsu }} {{ Auth::user()->apeUsu }}
                </h2>
                <p class="text-xs text-teal-600 bg-teal-50 px-2 py-1 rounded-full">
                    {{ Auth::user()->rol->nomRol ?? 'Usuario' }}
                </p>
            </div>
        @endauth
    </div>

<div class="flex flex-1 overflow-hidden">
    <!-- Barra de íconos de acceso rápido -->
    <div class="flex flex-col items-center justify-center w-10 py-4 space-y-6 bg-white border-r border-gray-700 quick-access">

        <!-- Ícono de inicio 
        <a href="#home" class="p-1 text-gray-500 hover:bg-gray-100 rounded-lg">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>
            </svg>
        </a>-->

        <!-- Ícono de perfil -->
        <a href="{{ route('settings.profile') }}" wire:navigate class="p-1 text-gray-500 hover:bg-gray-100 rounded-lg">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </a>

        <!-- Ícono de notificaciones 
        <a href="#notifications" class="p-1 text-gray-500 hover:bg-gray-100 rounded-lg">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
            </svg>
        </a> -->

        <!-- Ícono de ajustes -->
        <a href="{{ route('settings.password') }}" wire:navigate class="p-1 text-gray-500 hover:bg-gray-100 rounded-lg">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </a>

        <!-- Ícono de logout -->
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="p-1 text-gray-500 hover:bg-gray-100 rounded-lg cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/>
                </svg>
            </button>
        </form>
    </div>
    
    <!-- Barra de módulos -->
    <div class="py-4 overflow-y-auto bg-white w-[140px] custom-scrollbar">
        <h2 class="px-2 text-sm font-medium text-gray-800">MÓDULOS</h2>

        <div class="mt-3 space-y-1">
            <!-- Módulo de Gestión de usuarios -->
            @can('manage-users')
            <div class="mb-1">
                <button class="module-button flex items-center justify-between w-full px-2 py-1 text-xs hover:bg-gray-100 cursor-pointer">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-1 text-gray-500">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                        </svg>
                        <span class="text-gray-700">Panel de suarios</span>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-gray-500 arrow-icon">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </button>
                <div class="submenu pl-6">
                    <a href="{{ route('settings.manage-users.dashboard') }}" wire:navigate class="block px-2 py-0.5 text-xs text-gray-600 hover:bg-gray-100">Dashboard</a>
                    <a href="{{ route('settings.manage-users') }}" wire:navigate class="block px-2 py-0.5 text-xs text-gray-600 hover:bg-gray-100">Lista</a>
                    <a href="{{ route('settings.manage-users.create') }}" wire:navigate class="block px-2 py-0.5 text-xs text-gray-600 hover:bg-gray-100">Agregar</a>
                </div>
            </div>
            @endcan

            @can('aprendiz')
                <!-- Módulo Inventario - Aprendiz -->
            <div class="mb-1">
                <button class="module-button flex items-center justify-between w-full px-2 py-1 text-xs hover:bg-gray-100 cursor-pointer">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-1 text-gray-500">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
                        </svg>
                        <span class="text-gray-700">Inventario</span>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-gray-500 arrow-icon">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </button>
                <div class="submenu pl-6">
                    <a href="{{ route('inventario.prestamos.index') }}" wire:navigate class="block px-2 py-0.5 text-xs text-gray-600 hover:bg-gray-100">Préstamos</a>
                </div>
            </div>
            @endcan

            @can('admin')
            <!-- Módulo Inventario -->
            <div class="mb-1">
                <button class="module-button flex items-center justify-between w-full px-2 py-1 text-xs hover:bg-gray-100 cursor-pointer">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-1 text-gray-500">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
                        </svg>
                        <span class="text-gray-700">Inventario</span>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-gray-500 arrow-icon">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </button>
                <div class="submenu pl-6">
                    <a href="{{ route('inventario.dashboard') }}" wire:navigate class="block px-2 py-0.5 text-xs text-gray-600 hover:bg-gray-100">Dashboard</a>
                    <a href="{{ route('inventario.herramientas.index') }}" wire:navigate class="block px-2 py-0.5 text-xs text-gray-600 hover:bg-gray-100">Herramientas</a>
                    <a href="{{ route('inventario.insumos.index') }}" wire:navigate class="block px-2 py-0.5 text-xs text-gray-600 hover:bg-gray-100">Insumos</a>
                    <a href="{{ route('inventario.movimientos.index') }}" wire:navigate class="block px-2 py-0.5 text-xs text-gray-600 hover:bg-gray-100">Movimientos</a>
                    <a href="{{ route('inventario.prestamos.index') }}" wire:navigate class="block px-2 py-0.5 text-xs text-gray-600 hover:bg-gray-100">Préstamos</a>
                    <a href="{{ route('inventario.mantenimientos.index') }}" wire:navigate class="block px-2 py-0.5 text-xs text-gray-600 hover:bg-gray-100">Mantenimientos</a>
                    <!--<a href="#" class="block px-2 py-0.5 text-xs text-gray-600 hover:bg-gray-100">Reportes</a>-->
                </div>
            </div>

            <!-- Módulo Pecuario -->
            <div class="mb-1">
                <button class="module-button flex items-center justify-between w-full px-2 py-1 text-xs hover:bg-gray-100 cursor-pointer">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-1 text-gray-500">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/>
                        </svg>
                        <span class="text-gray-700">Pecuario</span>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-gray-500 arrow-icon">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </button>
                <div class="submenu pl-6">
                    <a href="{{ route('pecuario.dashboard') }}" wire:navigate class="block px-2 py-0.5 text-xs text-gray-600 hover:bg-gray-100">Dashboard</a>
                    <a href="{{ route('pecuario.animales.index') }}" wire:navigate class="block px-2 py-0.5 text-xs text-gray-600 hover:bg-gray-100">Animales</a>
                    <a href="{{ route('pecuario.produccion.index') }}" wire:navigate class="block px-2 py-0.5 text-xs text-gray-600 hover:bg-gray-100">Producción</a>
                    <a href="{{ route('pecuario.salud-peso.index') }}" wire:navigate class="block px-2 py-0.5 text-xs text-gray-600 hover:bg-gray-100">Historial médico</a>
                </div>
            </div>

            <!-- Módulo Proveedor -->
            <div class="mb-1">
                <button class="module-button flex items-center justify-between w-full px-2 py-1 text-xs hover:bg-gray-100 cursor-pointer">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-1 text-gray-500">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-5.679 5.687 9 9 0 003 3m8.5 0a3 3 0 00-9-1.551m5.632 3.801a7.963 7.963 0 01-4.553 1.463 8.037 8.037 0 01-7.951-1.843M12 6.75a3 3 0 013-3h3.75a3 3 0 013 3v1.5M12 6.75a3 3 0 00-3 3v1.5m0 0h3.75m-3.75 0h-3.75"/>
                        </svg>
                        <span class="text-gray-700">Proveedor</span>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-gray-500 arrow-icon">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </button>
                <div class="submenu pl-6">
                    <!--<a href="#" class="block px-2 py-0.5 text-xs text-gray-600 hover:bg-gray-100">Dashboard</a>-->
                    <a href="{{ route('proveedores.create') }}" wire:navigate class="block px-2 py-0.5 text-xs text-gray-600 hover:bg-gray-100">Crear</a>
                    <a href="{{ route('proveedores.index') }}" wire:navigate class="block px-2 py-0.5 text-xs text-gray-600 hover:bg-gray-100">Ver</a>
                </div>
            </div>
            @endcan
        </div>
    </div>
</div>
</aside>