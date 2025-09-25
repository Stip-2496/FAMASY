@section('title', 'Equipo de desarrollo')
<x-app-layout>
  <!-- Banner principal mejorado -->
  <div class="w-full h-64 sm:h-[400px] bg-gradient-to-r from-gray-900 to-black relative overflow-hidden">
    <!-- Efecto de fondo con formas geométricas -->
    <div class="absolute inset-0 opacity-10">
      <div class="absolute top-10 left-10 w-32 h-32 bg-white rounded-full"></div>
      <div class="absolute bottom-10 right-10 w-40 h-40 bg-white rounded-lg transform rotate-45"></div>
      <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-48 h-48 bg-white rounded-full"></div>
    </div>
    
    <div class="flex justify-center items-center h-full w-full px-4 relative z-10">
      <div class="text-center">
        <h1 class="text-white font-bold text-3xl sm:text-5xl mb-4 drop-shadow-lg">Nuestro Equipo</h1>
        <p class="text-white text-xl sm:text-2xl font-medium">FAMASY Technologies</p>
        <div class="mt-6 flex justify-center">
          <div class="w-16 h-1 bg-white rounded-full"></div>
        </div>
      </div>
    </div>
    
    <!-- Flecha indicadora de scroll -->
    <div class="absolute bottom-6 left-1/2 transform -translate-x-1/2 animate-bounce">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
      </svg>
    </div>
  </div>

  <!-- Sección Quiénes Somos mejorada -->
  <div class="container mx-auto py-16 px-4">
    <div class="max-w-4xl mx-auto text-center mb-16">
      <h2 class="text-4xl sm:text-5xl font-bold text-gray-800 mb-6 text-center">
        ¿Quiénes Somos?
      </h2>
      <div class="flex justify-center mb-8">
        <div class="w-20 h-1 bg-[#39A900] rounded-full"></div>
      </div>
      <div class="bg-white p-8 rounded-2xl shadow-lg border border-gray-100">
        <p class="text-lg text-gray-700 leading-relaxed">Somos un equipo comprometido con el desarrollo tecnológico. En FAMASY Technologies, trabajamos para crear soluciones digitales efectivas que apoyan diversos sectores como educación, salud, entretenimiento y procesos industriales, ofreciendo herramientas que facilitan y optimizan tanto tareas cotidianas o empresariales.</p>
      </div>
    </div>

    <!-- Equipo -->
    <div class="max-w-6xl mx-auto">
      <h3 class="text-3xl sm:text-4xl font-bold text-center text-gray-800 mb-4">Nuestro Equipo</h3>
      <div class="flex justify-center mb-12">
        <div class="w-24 h-1 bg-[#39A900] rounded-full"></div>
      </div>
      
      <!-- Imagen del equipo completo mejorada -->
      <div class="mb-16 max-w-5xl mx-auto rounded-2xl overflow-hidden shadow-2xl transform hover:scale-[1.01] transition-transform duration-500">
        <img src="assets/images/backgrounds/dev-team.jpeg" alt="Equipo FAMASY Technologies" class="w-full h-80 object-cover">
      </div>
      
      <!-- Grid de miembros del equipo mejorado -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-10 max-w-6xl mx-auto">
        <!-- Miembro 1 -->
        <div class="team-member bg-white rounded-2xl shadow-lg overflow-hidden group transform hover:-translate-y-2 transition-all duration-300 h-full flex flex-col">
          <div class="p-6 text-center flex flex-col flex-grow">
            <div class="mb-4">
              <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-r from-[#39A900] to-[#007832] mb-4">
                <span class="text-2xl font-bold text-white">KS</span>
              </div>
              <h4 class="text-xl font-bold text-gray-800">Kevin Stip Pama Campo</h4>
              <p class="text-[#39A900] font-semibold mt-1">Desarrollador Back-End</p>
            </div>
            <p class="text-gray-600 mt-3 flex-grow">Con experiencia en servidores y bases de datos, se encarga de construir la estructura que soporta las aplicaciones.
Su labor se orienta a mantener un buen rendimiento y estabilidad en los proyectos.
También procura que cada desarrollo sea escalable y seguro, adaptándose a las necesidades del equipo.</p>
            <div class="mt-6 pt-4 border-t border-gray-200">
              <p class="text-sm font-medium text-gray-700 mb-2">Contacto:</p>
              <div class="space-y-2">
                <div class="flex items-center justify-center space-x-2">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#39A900]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                  </svg>
                  <a href="mailto:kevin.pama@famasy.com" class="text-sm text-gray-600 hover:text-[#39A900] transition-colors">pamacampokevinstip@gmail.com</a>
                </div>
                <div class="flex items-center justify-center space-x-2">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#39A900]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                  </svg>
                  <span class="text-sm text-gray-600">+57 315 488 9618</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Miembro 2 -->
        <div class="team-member bg-white rounded-2xl shadow-lg overflow-hidden group transform hover:-translate-y-2 transition-all duration-300 h-full flex flex-col">
          <div class="p-6 text-center flex flex-col flex-grow">
            <div class="mb-4">
              <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-r from-[#39A900] to-[#007832] mb-4">
                <span class="text-2xl font-bold text-white">KT</span>
              </div>
              <h4 class="text-xl font-bold text-gray-800">Keyli Daniela Troyano Beltrán</h4>
              <p class="text-[#39A900] font-semibold mt-1">Diseñadora UX/UI</p>
            </div>
            <p class="text-gray-600 mt-3 flex-grow">Creativa y detallista en el diseño de interfaces digitales, busca que las aplicaciones sean claras y fáciles de usar.
Su trabajo se centra en la accesibilidad, priorizando que diferentes tipos de usuarios puedan interactuar sin dificultad.
Además, incorpora criterios de usabilidad que ayudan a mejorar la experiencia en cada proyecto.</p>
            <div class="mt-6 pt-4 border-t border-gray-200">
              <p class="text-sm font-medium text-gray-700 mb-2">Contacto:</p>
              <div class="space-y-2">
                <div class="flex items-center justify-center space-x-2">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#39A900]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                  </svg>
                  <a href="mailto:keyli.troyano@famasy.com" class="text-sm text-gray-600 hover:text-[#39A900] transition-colors">troyanodani1809@gmail.com</a>
                </div>
                <div class="flex items-center justify-center space-x-2">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#39A900]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                  </svg>
                  <span class="text-sm text-gray-600">+57 313 352 7458</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Miembro 3 -->
        <div class="team-member bg-white rounded-2xl shadow-lg overflow-hidden group transform hover:-translate-y-2 transition-all duration-300 h-full flex flex-col">
          <div class="p-6 text-center flex flex-col flex-grow">
            <div class="mb-4">
              <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-r from-[#39A900] to-[#007832] mb-4">
                <span class="text-2xl font-bold text-white">WN</span>
              </div>
              <h4 class="text-xl font-bold text-gray-800">Willian Yovanny Nievas Carlosama</h4>
              <p class="text-[#39A900] font-semibold mt-1">Analista</p>
            </div>
            <p class="text-gray-600 mt-3 flex-grow">Especialista en identificar requerimientos y procesos para transformarlos en propuestas tecnológicas útiles.
Su función es comprender las necesidades de los usuarios y transmitirlas al equipo de desarrollo.
De esta manera, facilita la comunicación y asegura que las soluciones respondan a lo que realmente se requiere.</p>
            <div class="mt-6 pt-4 border-t border-gray-200">
              <p class="text-sm font-medium text-gray-700 mb-2">Contacto:</p>
              <div class="space-y-2">
                <div class="flex items-center justify-center space-x-2">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#39A900]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                  </svg>
                  <a href="mailto:willian.nievas@famasy.com" class="text-sm text-gray-600 hover:text-[#39A900] transition-colors">williann.adso@gmail.com</a>
                </div>
                <div class="flex items-center justify-center space-x-2">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#39A900]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                  </svg>
                  <span class="text-sm text-gray-600">+57 316 621 5181</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Contenedor especial para las dos últimas tarjetas centradas -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-10 max-w-4xl mx-auto justify-items-center mt-8">
        <!-- Miembro 4 -->
        <div class="team-member bg-white rounded-2xl shadow-lg overflow-hidden group transform hover:-translate-y-2 transition-all duration-300 h-full flex flex-col w-full">
          <div class="p-6 text-center flex flex-col flex-grow">
            <div class="mb-4">
              <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-r from-[#39A900] to-[#007832] mb-4">
                <span class="text-2xl font-bold text-white">PL</span>
              </div>
              <h4 class="text-xl font-bold text-gray-800">Pablo José López Nequipo</h4>
              <p class="text-[#39A900] font-semibold mt-1">Documentador</p>
            </div>
            <p class="text-gray-600 mt-3 flex-grow">Responsable de crear documentación técnica y educativa que sirva de apoyo al uso de las soluciones desarrolladas.
Elabora manuales y guías que ayudan tanto a usuarios como a equipos técnicos en sus tareas.
Organiza la información de forma clara y estructurada para facilitar el mantenimiento de los proyectos.</p>
            <div class="mt-6 pt-4 border-t border-gray-200">
              <p class="text-sm font-medium text-gray-700 mb-2">Contacto:</p>
              <div class="space-y-2">
                <div class="flex items-center justify-center space-x-2">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#39A900]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                  </svg>
                  <a href="mailto:pablo.lopez@famasy.com" class="text-sm text-gray-600 hover:text-[#39A900] transition-colors">pablolopez.adso@gmail.com</a>
                </div>
                <div class="flex items-center justify-center space-x-2">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#39A900]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                  </svg>
                  <span class="text-sm text-gray-600">+57 314 317 2173</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Miembro 5 -->
        <div class="team-member bg-white rounded-2xl shadow-lg overflow-hidden group transform hover:-translate-y-2 transition-all duration-300 h-full flex flex-col w-full">
          <div class="p-6 text-center flex flex-col flex-grow">
            <div class="mb-4">
              <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-r from-[#39A900] to-[#007832] mb-4">
                <span class="text-2xl font-bold text-white">VC</span>
              </div>
              <h4 class="text-xl font-bold text-gray-800">Victor Manuel Castro Valencia</h4>
              <p class="text-[#39A900] font-semibold mt-1">Tester</p>
            </div>
            <p class="text-gray-600 mt-3 flex-grow">Encargado de revisar y probar las aplicaciones para detectar errores antes de su entrega final.
Se asegura de que las soluciones funcionen de manera estable y cumplan con los objetivos definidos.
También aporta retroalimentación constante al equipo para mejorar la calidad del producto.</p>
            <div class="mt-6 pt-4 border-t border-gray-200">
              <p class="text-sm font-medium text-gray-700 mb-2">Contacto:</p>
              <div class="space-y-2">
                <div class="flex items-center justify-center space-x-2">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#39A900]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                  </svg>
                  <a href="mailto:victor.castro@famasy.com" class="text-sm text-gray-600 hover:text-[#39A900] transition-colors">vimacast963@gmail.com</a>
                </div>
                <div class="flex items-center justify-center space-x-2">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#39A900]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                  </svg>
                  <span class="text-sm text-gray-600">+57 322 854 5241</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Sección Nuestra Misión mejorada -->
  <div class="bg-gradient-to-br from-gray-50 to-gray-100 py-20 relative overflow-hidden">
    <!-- Elementos decorativos de fondo -->
    <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-r from-[#39A900]/10 to-[#007832]/10 rounded-full -translate-y-32 translate-x-32"></div>
    <div class="absolute bottom-0 left-0 w-80 h-80 bg-gradient-to-r from-[#39A900]/10 to-[#007832]/10 rounded-full -translate-x-40 translate-y-40"></div>
    
    <div class="container mx-auto px-4 relative z-10">
      <div class="max-w-4xl mx-auto text-center bg-white p-10 rounded-2xl shadow-lg border border-gray-100">
        <h2 class="text-4xl sm:text-5xl font-bold text-gray-800 mb-6 text-center">
          Nuestra Misión
        </h2>
        <div class="flex justify-center mb-8">
          <div class="w-20 h-1 bg-[#39A900] rounded-full"></div>
        </div>
        <div class="space-y-6">
          <p class="text-lg text-gray-700 leading-relaxed">Nuestra misión es transformar y mejorar la vida de las personas mediante soluciones tecnológicas innovadoras que potencien el desarrollo humano en todos sus aspectos: educativo, sanitario, cultural, social y económico.</p>
          <p class="text-lg text-gray-700 leading-relaxed">En FAMASY Technologies, nos comprometemos a crear tecnología accesible e inclusiva que resuelva problemas reales, facilite la toma de decisiones basadas en datos y contribuya a construir un mundo más conectado, eficiente y sostenible para todos.</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Sección de botón para volver al inicio mejorada -->
  <div class="py-16 bg-white">
    <div class="container mx-auto px-4 text-center">
      <a href="{{ route('welcome') }}" class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-[#39A900] to-[#007832] hover:from-[#007832] hover:to-[#39A900] text-white font-semibold text-lg rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M9.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L7.414 9H15a1 1 0 110 2H7.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd" />
        </svg>
        Volver a la página principal
      </a>
    </div>
  </div>
</x-app-layout>