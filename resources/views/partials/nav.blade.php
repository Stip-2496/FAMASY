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
    <div class="w-full p-2 sm:p-4 h-16 sm:h-[72px] flex items-center justify-end relative z-10 pl-[180px] sm:pl-[300px]">
      <div class="flex-grow"></div> <!-- Este div empuja el contenido a la derecha -->
      <a href="{{ route('login') }}" class="cursor-pointer text-white bg-[#007832] hover:bg-[#007832]/70 focus:outline-none focus:ring-[#3b5998]/50 font-medium rounded-lg text-xs sm:text-sm px-3 py-1.5 sm:px-5 sm:py-2.5 text-center inline-flex items-center dark:focus:ring-[#3b5998]/55 me-2">
        Iniciar sesión
      </a>
    </div>
  </div>
</nav>
