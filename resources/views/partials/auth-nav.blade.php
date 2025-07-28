<nav class="bg-white">
  <div class="bg-[#39A900] w-full relative">
    <!-- Fondo blanco diagonal con logo -->
    <div class="absolute inset-y-0 left-0 bg-white clip-diagonal w-[180px] sm:w-[300px]">
      <div class="h-full flex items-center pl-2 sm:pl-4">
        <img src="/assets/images/logo-sena.jpg" alt="Logo del SENA" class="w-8 h-8 sm:w-[50px] sm:h-[50px]"/>
        <div class="w-px h-6 sm:h-10 bg-black mx-2 sm:m-3"></div>
        <h1 class="text-lg sm:text-2xl font-semibold whitespace-nowrap text-black ml-1 sm:ml-3">FAMASY</h1>
      </div>
    </div>
    
    <!-- Contenedor principal con padding izquierdo para no superponerse con la diagonal -->
    <div class="w-full p-2 sm:p-4 h-16 sm:h-[72px] flex items-center justify-between relative z-10 pl-[180px] sm:pl-[300px]">

      <!-- Contenedor para los 5 botones -->
      <div class="flex-grow flex justify-center space-x-2 sm:space-x-4">

  <!-- Botón 1: Contabilidad -->
  <a href="{{ route('contabilidad.index') }}" wire:navigate class="group relative cursor-pointer text-white transition-all duration-300 focus:outline-none focus:ring-[#3b5998]/50 font-medium rounded-lg text-xs sm:text-sm px-3 py-1.5 sm:px-5 sm:py-2.5 text-center inline-flex items-center dark:focus:ring-[#3b5998]/55 overflow-hidden">
    Contabilidad
    <span class="absolute left-1/2 bottom-0 h-0.5 w-0 -translate-x-1/2 bg-white transition-all duration-500 group-hover:w-full"></span>
  </a>

  <!-- Botón 2: Inventario -->
  <a href="{{ route('inventario.dashboard') }}" class="group relative cursor-pointer text-white transition-all duration-300 focus:outline-none focus:ring-[#3b5998]/50 font-medium rounded-lg text-xs sm:text-sm px-3 py-1.5 sm:px-5 sm:py-2.5 text-center inline-flex items-center dark:focus:ring-[#3b5998]/55 overflow-hidden">
    Inventario
    <span class="absolute left-1/2 bottom-0 h-0.5 w-0 -translate-x-1/2 bg-white transition-all duration-500 group-hover:w-full"></span>
  </a>

  
  <!-- Botón 3: Pecuario -->
  <a href="{{ route('pecuario.dashboard') }}" class="group relative cursor-pointer text-white transition-all duration-300 focus:outline-none focus:ring-[#3b5998]/50 font-medium rounded-lg text-xs sm:text-sm px-3 py-1.5 sm:px-5 sm:py-2.5 text-center inline-flex items-center dark:focus:ring-[#3b5998]/55 overflow-hidden">
    Pecuario
    <span class="absolute left-1/2 bottom-0 h-0.5 w-0 -translate-x-1/2 bg-white transition-all duration-500 group-hover:w-full"></span>
  </a>

  <!-- Botón 4: Proveedor -->
  <a href="{{ route('proveedores.index') }}" wire:navigate class="group relative cursor-pointer text-white transition-all duration-300 focus:outline-none focus:ring-[#3b5998]/50 font-medium rounded-lg text-xs sm:text-sm px-3 py-1.5 sm:px-5 sm:py-2.5 text-center inline-flex items-center dark:focus:ring-[#3b5998]/55 overflow-hidden">
    Proveedor
    <span class="absolute left-1/2 bottom-0 h-0.5 w-0 -translate-x-1/2 bg-white transition-all duration-500 group-hover:w-full"></span>
  </a>

  <!-- Botón 5: Administrador -->
  <button class="group relative cursor-pointer text-white transition-all duration-300 focus:outline-none focus:ring-[#3b5998]/50 font-medium rounded-lg text-xs sm:text-sm px-3 py-1.5 sm:px-5 sm:py-2.5 text-center inline-flex items-center dark:focus:ring-[#3b5998]/55 overflow-hidden">
    Administrador
    <span class="absolute left-1/2 bottom-0 h-0.5 w-0 -translate-x-1/2 bg-white transition-all duration-500 group-hover:w-full"></span>
  </button>
</div>

<!-- Avatar con menú desplegable -->
  <div class="relative group">
    <button class="flex items-center focus:outline-none">
    <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-white flex items-center justify-center overflow-hidden cursor-pointer">
      <!-- Icono de usuario -->
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#39A900]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
      </svg>
    </div>
    </button>
        
<!-- Menú desplegable - se muestra al hacer hover en el grupo padre -->
  <div class="invisible opacity-0 group-hover:visible group-hover:opacity-100 absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 transition-all duration-200 transform group-hover:translate-y-0 translate-y-1">
    <a href="{{ route('settings.profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" wire:navigate>Mi perfil</a>
    @can('manage-users')
    <a href="{{ route('settings.manage-users') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" wire:navigate>Gestionar usuarios</a>
    @endcan
    <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" wire:navigate>Dashboard</a>
    <a href="{{ route('settings.password') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" wire:navigate>Configuración</a>
  <form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer">Cerrar sesión</button>
  </form>
  </div>

    </div>
  </div>
</nav>