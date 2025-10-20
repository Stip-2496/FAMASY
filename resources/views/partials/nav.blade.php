<nav class="bg-white">
  <div class="bg-[#39A900] w-full relative">
    <!-- Fondo blanco diagonal con logo-->
    <div class="absolute inset-y-0 left-0 bg-white clip-diagonal w-[180px] sm:w-[300px]">
      <div class="h-full flex items-center pl-2 sm:pl-4">
        <a href="{{ route('welcome') }}">
          <img src="/assets/images/FAMASY-logo.jpg" alt="Logo del SENA" class="w-8 h-8 sm:w-[50px] sm:h-[50px] hover:cursor.pointer"/>
        </a>
        <div class="w-px h-6 sm:h-10 bg-black mx-2 sm:m-3"></div>
        <h1 class="text-lg sm:text-2xl font-semibold whitespace-nowrap text-black ml-1 sm:ml-3">FAMASY</h1>
      </div>
    </div>
    <!-- Contenedor principal con padding izquierdo para no superponerse con la diagonal -->
    <div class="w-full p-2 sm:p-4 h-16 sm:h-[72px] flex items-center justify-end relative z-10 pl-[180px] sm:pl-[300px]">
      <div class="flex-grow"></div> <!-- Este div empuja el contenido a la derecha -->
        <a href="{{ route('login') }}" wire:navigate 
   class="cursor-pointer group relative inline-flex items-center justify-center px-3 py-1.5 sm:px-5 sm:py-2.5 bg-[#007832] hover:bg-[#007832]/70 text-white font-medium rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden text-xs sm:text-sm me-2">
    <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
    <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1.5 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
    </svg>
    <span class="relative z-10">Iniciar sesión</span>
</a>
    </div>
  </div>
</nav>
