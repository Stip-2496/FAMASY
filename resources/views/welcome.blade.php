@extends('layouts.app')

@section('title', 'Inicio')

@section('content')
<div class="w-full h-64 sm:h-[500px] bg-[linear-gradient(rgba(0,0,0,0.7),rgba(0,0,0,0.7)),url('/assets/images/backgrounds/livestock-background.jpg')] bg-cover bg-center bg-no-repeat bg-opacity-70 relative">
  <div class="flex justify-center items-center h-full w-full px-4">
    <p class="text-white font-bold text-xl sm:text-3xl text-center">Gestiona tu producción con FAMASY</p>
  </div>
</div>
    <div class="w-full h-auto sm:h-[500px] bg-[linear-gradient(rgba(0,0,0,0.6),rgba(0,0,0,0.6)),url('/assets/images/backgrounds/who-famasy.jpg')] bg-cover bg-center bg-no-repeat bg-opacity-70 relative py-8 sm:py-0">
  <h1 class="w-full text-white font-bold text-2xl sm:text-4xl flex justify-center items-center mb-4 sm:mb-0 sm:absolute sm:top-8">¿QUÉ ES FAMASY?</h1>
  <div class="sm:absolute sm:inset-0 flex items-center justify-center">
    <div class="flex flex-col sm:flex-row justify-center gap-4 sm:gap-1 text-center px-4 sm:px-0"> 
      <a href="#" class="block w-full sm:w-48 md:w-56 lg:w-64 h-auto sm:h-80 p-4 sm:p-6 bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700 flex flex-col items-center text-center gap-2 card-abbr" data-abbr="FA" data-full="FARM">
        <h5 class="mb-2 text-xl sm:text-2xl font-bold tracking-tight text-gray-900 dark:text-white abbr-text">FA</h5>
        <img src="/assets/images/icons/farm-icon.png" class="w-16 h-16 sm:w-[100px] sm:h-[100px] rounded" alt="Icono de granja automizada">
        <p class="font-normal text-sm sm:text-base text-gray-700 dark:text-gray-400">Facilidad de adaptación a tus necesidades agropecuarias, con la automatización de procesos agropecuarios con precisión.</p>
      </a>
      <a href="#" class="block w-full sm:w-48 md:w-56 lg:w-64 h-auto sm:h-80 p-4 sm:p-6 bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700 flex flex-col items-center text-center gap-2 card-abbr" data-abbr="MA" data-full="MANAGEMENT">
        <h5 class="mb-2 text-xl sm:text-2xl font-bold tracking-tight text-gray-900 dark:text-white abbr-text">MA</h5>
        <img src="/assets/images/icons/manage-icon.png" class="w-16 h-16 sm:w-[100px] sm:h-[100px] rounded" alt="Icono de trazabilidad de ganado.">
        <p class="font-normal text-sm sm:text-base text-gray-700 dark:text-gray-400">Máximo rendimiento con mínimo esfuerzo sobre gestión de inventarios, producción y trazabilidad.</p>
      </a>
      <a href="#" class="block w-full sm:w-48 md:w-56 lg:w-64 h-auto sm:h-80 p-4 sm:p-6 bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700 flex flex-col items-center text-center gap-2 card-abbr" data-abbr="SY" data-full="SYSTEM">
        <h5 class="mb-2 text-xl sm:text-2xl font-bold tracking-tight text-gray-900 dark:text-white abbr-text">SY</h5>
        <img src="/assets/images/icons/system-icon.png" class="w-16 h-16 sm:w-[100px] sm:h-[100px] rounded" alt="Icono de sistema escalable">
        <p class="font-normal text-sm sm:text-base text-gray-700 dark:text-gray-400">Sistema con arquitectura adaptable a cualquier tamaño de operación.</p>
      </a>
    </div>
  </div>
</div>
<div class="w-full h-auto sm:h-[400px] flex flex-col sm:flex-row">
  <div class="w-full sm:w-1/2 h-64 sm:h-full">
    <iframe src="https://www.google.com/maps/embed?pb=!1m17!1m12!1m3!1d1993.0794747140058!2d-75.76485751316588!3d2.454129553876159!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m2!1m1!2zMsKwMjcnMTMuOSJOIDc1wrA0NSc1NC40Ilc!5e0!3m2!1ses!2sus!4v1745980594206!5m2!1ses!2sus" class="w-full h-full border-0" allowfullscreenloading="lazy" title="Ubicación en Google Maps"></iframe>
  </div>
  <div class="w-full sm:w-1/2 bg-gray-50 flex flex-col bg-[linear-gradient(rgba(0,0,0,0.6),rgba(0,0,0,0.6)),url('/assets/images/backgrounds/juan-bosco.jpg')] bg-cover bg-center bg-no-repeat bg-opacity-70 relative text-white py-8 sm:py-0">  
    <h1 class="font-bold text-2xl sm:text-4xl text-center mt-0 sm:mt-8">Granja Experimental San Juan Bosco</h1>
    <div class="w-32 sm:w-150 h-1 bg-white m-3 mx-auto"></div>
    <p class="text-base sm:text-lg text-center px-4">Encuéntranos</p>
  </div>
</div>
@endsection