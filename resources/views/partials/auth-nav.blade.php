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
    <div class="flex flex-col items-center py-4 bg-gradient-to-b from-white to-gray-50 border-b border-gray-200">
        @auth
            <div class="flex items-center justify-center w-8 h-8 rounded-full bg-gradient-to-br from-green-500 to-emerald-600 text-white font-bold shadow-lg mb-2 transform hover:scale-110 transition-transform duration-300">
                {{ Auth::user()->initials() }}
            </div>
            <div class="text-center">
                <h2 class="text-xs font-bold text-gray-800 mb-2">
                    {{ Auth::user()->nomUsu }} {{ Auth::user()->apeUsu }}
                </h2>
                <p class="text-xs font-semibold text-emerald-700 bg-gradient-to-r from-emerald-50 to-teal-50 px-2 py-1 rounded-full border border-emerald-200 shadow-sm">
                    {{ Auth::user()->rol->nomRol ?? 'Usuario' }}
                </p>
            </div>
        @endauth
    </div>

<div class="flex flex-1 overflow-hidden">
    <!-- Barra de íconos de acceso rápido -->
    <div class="flex flex-col items-center justify-center w-10 py-4 space-y-6 bg-gradient-to-b from-white to-gray-50 border-r border-black quick-access">

        <!-- Ícono de perfil -->
        <a href="{{ route('settings.profile') }}" wire:navigate 
           class="group relative p-1.5 text-gray-500 hover:text-green-600 bg-white hover:bg-gradient-to-br hover:from-green-50 hover:to-emerald-50 rounded-xl shadow-sm hover:shadow-lg transform hover:scale-110 transition-all duration-300 border border-transparent hover:border-green-200">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </a>

        <!-- Ícono de ajustes -->
        <a href="{{ route('settings.password') }}" wire:navigate 
           class="group relative p-1.5 text-gray-500 hover:text-green-600 bg-white hover:bg-gradient-to-br hover:from-green-50 hover:to-emerald-50 rounded-xl shadow-sm hover:shadow-lg transform hover:scale-110 transition-all duration-300 border border-transparent hover:border-green-200">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </a>

        <!-- Ícono de logout -->
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" 
                    class="group relative p-1.5 text-gray-500 hover:text-red-600 bg-white hover:bg-gradient-to-br hover:from-red-50 hover:to-pink-50 rounded-xl shadow-sm hover:shadow-lg transform hover:scale-110 transition-all duration-300 cursor-pointer border border-transparent hover:border-red-200">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/>
                </svg>
            </button>
        </form>
    </div>
    
    <!-- Barra de módulos -->
    <div class="py-4 overflow-y-auto bg-gradient-to-b from-white to-gray-50 w-[140px] custom-scrollbar">
        <h2 class="px-2 text-sm font-black text-gray-800 mb-1">MÓDULOS</h2>

        <div class="mt-3 space-y-1">
            <!-- Módulo de Gestión de usuarios -->
            @can('manage-users')
            <div class="mb-1">
                <button class="module-button group flex items-center justify-between w-full px-2 py-1 text-xs hover:bg-gradient-to-r hover:from-green-50 hover:to-emerald-50 rounded-lg transition-all duration-300 cursor-pointer border border-transparent hover:border-green-200 hover:shadow-sm">
                    <div class="flex items-center">
                        <div class="w-4 h-4 flex items-center justify-center mr-1">
                            <i class="fa-solid fa-users text-gray-500 group-hover:text-green-600 transition-colors duration-300 text-[13px]"></i>
                        </div>
                        <span class="text-gray-700 font-medium group-hover:text-green-700 transition-colors duration-300">Panel de usuarios</span>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-gray-500 group-hover:text-green-600 arrow-icon transition-all duration-300">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </button>
                <div class="submenu pl-6">
                    <a href="{{ route('settings.manage-users.dashboard') }}" wire:navigate class="block px-2 py-0.5 text-xs text-gray-600 hover:text-green-700 hover:bg-green-50 rounded transition-colors duration-200">Dashboard</a>
                    <a href="{{ route('settings.manage-users') }}" wire:navigate class="block px-2 py-0.5 text-xs text-gray-600 hover:text-green-700 hover:bg-green-50 rounded transition-colors duration-200">Lista</a>
                    <a href="{{ route('settings.manage-users.create') }}" wire:navigate class="block px-2 py-0.5 text-xs text-gray-600 hover:text-green-700 hover:bg-green-50 rounded transition-colors duration-200">Registrar</a>
                </div>
            </div>
            @endcan

            @can('aprendiz')
                <!-- Módulo Inventario - Aprendiz -->
            <div class="mb-1">
                <button class="module-button group flex items-center justify-between w-full px-2 py-1 text-xs hover:bg-gradient-to-r hover:from-green-50 hover:to-emerald-50 rounded-lg transition-all duration-300 cursor-pointer border border-transparent hover:border-green-200 hover:shadow-sm">
                    <div class="flex items-center">
                        <div class="w-4 h-4 flex items-center justify-center mr-1">
                            <i class="fa-solid fa-warehouse text-gray-500 group-hover:text-green-600 transition-colors duration-300 text-[13px]"></i>
                        </div>
                        <span class="text-gray-700 font-medium group-hover:text-green-700 transition-colors duration-300">Inventario</span>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-gray-500 group-hover:text-green-600 arrow-icon transition-all duration-300">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </button>
                <div class="submenu pl-6">
                    <a href="{{ route('inventario.prestamos.index') }}" wire:navigate class="block px-2 py-0.5 text-xs text-gray-600 hover:text-green-700 hover:bg-green-50 rounded transition-colors duration-200">Préstamos</a>
                </div>
            </div>
            @endcan

            @can('admin')
            <!-- Módulo Inventario -->
            <div class="mb-1">
                <button class="module-button group flex items-center justify-between w-full px-2 py-1 text-xs hover:bg-gradient-to-r hover:from-green-50 hover:to-emerald-50 rounded-lg transition-all duration-300 cursor-pointer border border-transparent hover:border-green-200 hover:shadow-sm">
                    <div class="flex items-center">
                        <div class="w-4 h-4 flex items-center justify-center mr-1">
                            <i class="fa-solid fa-warehouse text-gray-500 group-hover:text-green-600 transition-colors duration-300 text-[13px]"></i>
                        </div>
                        <span class="text-gray-700 font-medium group-hover:text-green-700 transition-colors duration-300">Inventario</span>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-gray-500 group-hover:text-green-600 arrow-icon transition-all duration-300">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </button>
                <div class="submenu pl-6">
                    <a href="{{ route('inventario.dashboard') }}" wire:navigate class="block px-2 py-0.5 text-xs text-gray-600 hover:text-green-700 hover:bg-green-50 rounded transition-colors duration-200">Dashboard</a>
                    <a href="{{ route('inventario.herramientas.index') }}" wire:navigate class="block px-2 py-0.5 text-xs text-gray-600 hover:text-green-700 hover:bg-green-50 rounded transition-colors duration-200">Herramientas</a>
                    <a href="{{ route('inventario.insumos.index') }}" wire:navigate class="block px-2 py-0.5 text-xs text-gray-600 hover:text-green-700 hover:bg-green-50 rounded transition-colors duration-200">Insumos</a>
                    <a href="{{ route('inventario.movimientos.index') }}" wire:navigate class="block px-2 py-0.5 text-xs text-gray-600 hover:text-green-700 hover:bg-green-50 rounded transition-colors duration-200">Movimientos</a>
                    <a href="{{ route('inventario.prestamos.index') }}" wire:navigate class="block px-2 py-0.5 text-xs text-gray-600 hover:text-green-700 hover:bg-green-50 rounded transition-colors duration-200">Préstamos</a>
                    <a href="{{ route('inventario.mantenimientos.index') }}" wire:navigate class="block px-2 py-0.5 text-xs text-gray-600 hover:text-green-700 hover:bg-green-50 rounded transition-colors duration-200">Mantenimientos</a>
                </div>
            </div>

            <!-- Módulo Pecuario -->
            <div class="mb-1">
                <button class="module-button group flex items-center justify-between w-full px-2 py-1 text-xs hover:bg-gradient-to-r hover:from-green-50 hover:to-emerald-50 rounded-lg transition-all duration-300 cursor-pointer border border-transparent hover:border-green-200 hover:shadow-sm">
                    <div class="flex items-center">
                        <div class="w-4 h-4 flex items-center justify-center mr-1">
                            <i class="fas fa-paw text-gray-500 group-hover:text-green-600 transition-colors duration-300 text-[13px]"></i>
                        </div>
                        <span class="text-gray-700 font-medium group-hover:text-green-700 transition-colors duration-300">Pecuario</span>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-gray-500 group-hover:text-green-600 arrow-icon transition-all duration-300">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </button>
                <div class="submenu pl-6">
                    <a href="{{ route('pecuario.dashboard') }}" wire:navigate class="block px-2 py-0.5 text-xs text-gray-600 hover:text-green-700 hover:bg-green-50 rounded transition-colors duration-200">Dashboard</a>
                    <a href="{{ route('pecuario.animales.index') }}" wire:navigate class="block px-2 py-0.5 text-xs text-gray-600 hover:text-green-700 hover:bg-green-50 rounded transition-colors duration-200">Animales</a>
                    <a href="{{ route('pecuario.produccion.index') }}" wire:navigate class="block px-2 py-0.5 text-xs text-gray-600 hover:text-green-700 hover:bg-green-50 rounded transition-colors duration-200">Producción</a>
                    <a href="{{ route('pecuario.salud-peso.index') }}" wire:navigate class="block px-2 py-0.5 text-xs text-gray-600 hover:text-green-700 hover:bg-green-50 rounded transition-colors duration-200">Historial médico</a>
                </div>
            </div>

            <!-- Módulo Proveedor -->
            <div class="mb-1">
                <button class="module-button group flex items-center justify-between w-full px-2 py-1 text-xs hover:bg-gradient-to-r hover:from-green-50 hover:to-emerald-50 rounded-lg transition-all duration-300 cursor-pointer border border-transparent hover:border-green-200 hover:shadow-sm">
                    <div class="flex items-center">
                    <div class="w-4 h-4 flex items-center justify-center mr-1">
                        <i class="fa-solid fa-truck text-gray-500 group-hover:text-green-600 transition-colors duration-300 text-[13px]"></i>
                    </div>    
                        <span class="text-gray-700 font-medium group-hover:text-green-700 transition-colors duration-300">Proveedor</span>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-gray-500 group-hover:text-green-600 arrow-icon transition-all duration-300">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </button>
                <div class="submenu pl-6">
                    <a href="{{ route('proveedores.create') }}" wire:navigate class="block px-2 py-0.5 text-xs text-gray-600 hover:text-green-700 hover:bg-green-50 rounded transition-colors duration-200">Registrar</a>
                    <a href="{{ route('proveedores.index') }}" wire:navigate class="block px-2 py-0.5 text-xs text-gray-600 hover:text-green-700 hover:bg-green-50 rounded transition-colors duration-200">Lista</a>
                </div>
            </div>
            @endcan
        </div>
    </div>
</div>
</aside>