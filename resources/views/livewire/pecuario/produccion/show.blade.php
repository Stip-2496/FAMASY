<?php
use App\Models\ProduccionAnimal;
use App\Models\Animal;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.auth')] class extends Component {
    use WithPagination;

    public ProduccionAnimal $produccion;
    public Animal $animal;

    public function mount(ProduccionAnimal $produccion)
    {
        $this->produccion = $produccion;
        $this->animal = $produccion->animal ?? new Animal();
    }

    public function with(): array
    {
        return [
            'historial' => ProduccionAnimal::where('idAniPro', $this->produccion->idAniPro)
                ->where('idProAni', '!=', $this->produccion->idProAni)
                ->orderBy('fecProAni', 'desc')
                ->paginate(1, ['*'], 'historialPage')
        ];
    }

    public function delete()
    {
        try {
            $this->produccion->delete();
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Registro eliminado correctamente'
            ]);
            
            $this->redirect(route('pecuario.produccion.index'), navigate: true);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al eliminar el registro: ' . $e->getMessage()
            ]);
        }
    }

    public function getTipoProduccionFormateado($tipo)
    {
        $tipos = [
            'leche bovina' => 'Leche Bovina',
            'venta en pie bovino' => 'Venta en Pie Bovino',
            'lana ovina' => 'Lana Ovina',
            'venta en pie ovino' => 'Venta en Pie Ovino',
            'leche ovina' => 'Leche Ovina',
            'venta gallinas en pie' => 'Venta Gallinas en Pie',
            'huevo A' => 'Huevo A',
            'huevo AA' => 'Huevo AA',
            'huevo AAA' => 'Huevo AAA',
            'huevo Jumbo' => 'Huevo Jumbo',
            'huevo B' => 'Huevo B',
            'huevo C' => 'Huevo C',
            'venta pollo engorde' => 'Venta Pollo Engorde',
            'otros' => 'Otros'
        ];
        
        return $tipos[$tipo] ?? ucfirst($tipo);
    }

    public function getColorTipo($tipo)
    {
        $colores = [
            'leche bovina' => 'bg-gradient-to-r from-blue-500 to-indigo-600 text-white',
            'leche ovina' => 'bg-gradient-to-r from-blue-500 to-indigo-600 text-white',
            'venta en pie bovino' => 'bg-gradient-to-r from-red-500 to-red-600 text-white',
            'venta en pie ovino' => 'bg-gradient-to-r from-red-500 to-red-600 text-white',
            'venta gallinas en pie' => 'bg-gradient-to-r from-red-500 to-red-600 text-white',
            'venta pollo engorde' => 'bg-gradient-to-r from-red-500 to-red-600 text-white',
            'lana ovina' => 'bg-gradient-to-r from-purple-500 to-purple-600 text-white',
            'huevo A' => 'bg-gradient-to-r from-yellow-500 to-yellow-600 text-white',
            'huevo AA' => 'bg-gradient-to-r from-yellow-500 to-yellow-600 text-white',
            'huevo AAA' => 'bg-gradient-to-r from-yellow-500 to-yellow-600 text-white',
            'huevo Jumbo' => 'bg-gradient-to-r from-yellow-500 to-yellow-600 text-white',
            'huevo B' => 'bg-gradient-to-r from-yellow-500 to-yellow-600 text-white',
            'huevo C' => 'bg-gradient-to-r from-yellow-500 to-yellow-600 text-white',
            'otros' => 'bg-gradient-to-r from-gray-500 to-gray-600 text-white'
        ];
        
        return $colores[$tipo] ?? 'bg-gradient-to-r from-green-500 to-emerald-600 text-white';
    }

    public function paginationView()
    {
        return 'vendor.livewire.simple-tailwind';
    }
}; ?>

@section('title', 'Detalles de Producción')

<div class="flex items-center justify-center min-h-screen py-3">
    <div class="w-full max-w-7xl mx-auto bg-white/80 backdrop-blur-xl shadow rounded-3xl p-3 relative border border-white/20">
        <!-- Encabezado -->
        <div class="text-center mb-3">
            <div class="flex justify-center mb-1">
                <div class="p-2 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl shadow-xl transform rotate-3 hover:rotate-0 transition-transform duration-300">
                    <div class="w-4 h-4 text-white flex items-center justify-center">
                        <i class="fas fa-chart-line text-base"></i>
                    </div>
                </div>
            </div>
            <h1 class="text-base font-black bg-gradient-to-r from-gray-900 via-gray-800 to-green-800 bg-clip-text text-transparent mb-1">
                Detalles de Producción
            </h1>
            <p class="text-gray-600 text-xs">Información completa de la producción</p>
        </div>

        <!-- Botones -->
        <div class="absolute top-2 right-2 flex space-x-2">
            <a href="{{ route('pecuario.produccion.edit', $produccion->idProAni) }}" wire:navigate
               class="group relative inline-flex items-center px-2 py-1 bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                <svg class="w-3 h-3 mr-1 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                <span class="relative z-10 text-xs">Editar</span>
            </a>
            <a href="{{ route('pecuario.produccion.index') }}" wire:navigate
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
                <!-- Grid de información -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-2">
                    <!-- Información del Animal -->
                    <div class="bg-white rounded-2xl p-2 border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-300">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center space-x-2">
                                <div class="p-1.5 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl shadow-md">
                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4c2.21 0 4 1.79 4 4s-1.79 4-4 4-4-1.79-4-4 1.79-4 4-4zm0 12c-3.31 0-6 2.69-6 6h12c0-3.31-2.69-6-6-6z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-xs font-bold text-gray-900">Información del Animal</h3>
                            </div>
                            @if($produccion->animal)
                                <a href="{{ route('pecuario.animales.show', $produccion->animal->idAni) }}" wire:navigate class="group relative inline-flex items-center px-2 py-1 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white font-bold rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                                    <div class="absolute inset-0 bg-gradient-to-r from-white/0 to-white/20 transform translate-x-full group-hover:translate-x-0 transition-transform duration-300"></div>
                                    <svg class="w-3 h-3 mr-1 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    <span class="relative z-10 text-xs">Ver Animal</span>
                                </a>
                            @endif
                        </div>
                        <div class="bg-blue-50/50 border border-blue-200 rounded-xl p-2">
                            <div class="space-y-2">
                                <div class="flex items-center justify-between py-1 border-b border-blue-100">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <span class="text-xs text-black font-medium">NIT del Animal</span>
                                    </div>
                                    <p class="text-xs text-black font-semibold">{{ $animal->nitAni ?? 'No registrado' }}</p>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-blue-100">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <span class="text-xs text-black font-medium">Especie</span>
                                    </div>
                                    <p class="text-xs text-black font-semibold">{{ $animal->espAni ?? 'No registrado' }}</p>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-blue-100">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4c2.21 0 4 1.79 4 4s-1.79 4-4 4-4-1.79-4-4 1.79-4 4-4zm0 12c-3.31 0-6 2.69-6 6h12c0-3.31-2.69-6-6-6z"></path>
                                        </svg>
                                        <span class="text-xs text-black font-medium">Raza</span>
                                    </div>
                                    <p class="text-xs text-black font-semibold">{{ $animal->razAni ?? 'No especificada' }}</p>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-blue-100">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                        </svg>
                                        <span class="text-xs text-black font-medium">Estado General</span>
                                    </div>
                                    <p class="text-xs text-green-900 font-semibold">
                                        @switch($animal->estAni)
                                            @case('vivo')
                                                <span class="inline-flex items-center text-xs font-semibold px-1.5 py-0.5 rounded-full bg-gradient-to-r from-green-500 to-emerald-600 text-white shadow-sm">
                                                    <svg class="w-2.5 h-2.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                                    </svg>
                                                    Vivo
                                                </span>
                                                @break
                                            @case('muerto')
                                                <span class="inline-flex items-center text-xs font-semibold px-1.5 py-0.5 rounded-full bg-gradient-to-r from-red-500 to-red-600 text-white shadow-sm">
                                                    <svg class="w-2.5 h-2.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                    Muerto
                                                </span>
                                                @break
                                            @default
                                                <span class="inline-flex items-center text-xs font-semibold px-1.5 py-0.5 rounded-full bg-gradient-to-r from-yellow-500 to-yellow-600 text-white shadow-sm">
                                                    <svg class="w-2.5 h-2.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                                    </svg>
                                                    Vendido
                                                </span>
                                        @endswitch
                                    </p>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-blue-100">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                        </svg>
                                        <span class="text-xs text-black font-medium">Estado de Salud</span>
                                    </div>
                                    <p class="text-xs text-green-900 font-semibold">
                                        @switch($animal->estSaludAni)
                                            @case('saludable')
                                                <span class="inline-flex items-center text-xs font-semibold px-1.5 py-0.5 rounded-full bg-gradient-to-r from-green-500 to-emerald-600 text-white shadow-sm">
                                                    Saludable
                                                </span>
                                                @break
                                            @case('enfermo')
                                                <span class="inline-flex items-center text-xs font-semibold px-1.5 py-0.5 rounded-full bg-gradient-to-r from-red-500 to-red-600 text-white shadow-sm">
                                                    Enfermo
                                                </span>
                                                @break
                                            @default
                                                <span class="inline-flex items-center text-xs font-semibold px-1.5 py-0.5 rounded-full bg-gradient-to-r from-yellow-500 to-yellow-600 text-white shadow-sm">
                                                    En tratamiento
                                                </span>
                                        @endswitch
                                    </p>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-blue-100">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                        </svg>
                                        <span class="text-xs text-black font-medium">Estado Reproductivo</span>
                                    </div>
                                    <p class="text-xs text-green-900 font-semibold">
                                        @php
                                            $estadosRepro = [
                                                'no_aplica' => ['label' => 'No aplica', 'color' => 'gray', 'icon' => 'M18.364 5.636l-1.414-1.414M5.636 18.364l1.414 1.414M5.636 5.636l1.414 1.414M18.364 18.364l-1.414-1.414'],
                                                'ciclo' => ['label' => 'En ciclo', 'color' => 'blue', 'icon' => 'M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 01-8 8h4l-3 3 3 3h-4a8 8 0 01-8-8z'],
                                                'cubierta' => ['label' => 'Cubierta', 'color' => 'purple', 'icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z'],
                                                'gestacion' => ['label' => 'Gestación', 'color' => 'pink', 'icon' => 'M21 12a9 9 0 01-9 9c-.663 0-1.315-.071-1.95-.21C7.987 18.837 6 15.85 6 12c0-3.85 1.987-6.837 4.05-8.79.635-.14 1.287-.21 1.95-.21a9 9 0 019 9z'],
                                                'parida' => ['label' => 'Parida', 'color' => 'green', 'icon' => 'M9 9l3-3m0 0l3 3m-3-3v12m-6-9H3m18 0h-6']
                                            ];
                                            $estadoActual = $estadosRepro[$animal->estReproAni] ?? $estadosRepro['no_aplica'];
                                        @endphp
                                        <span class="inline-flex items-center text-xs font-semibold px-1.5 py-0.5 rounded-full bg-gradient-to-r from-{{ $estadoActual['color'] }}-500 to-{{ $estadoActual['color'] }}-600 text-white shadow-sm">
                                            {{ $estadoActual['label'] }}
                                        </span>
                                    </p>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-blue-100">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                        </svg>
                                        <span class="text-xs text-black font-medium">Sexo</span>
                                    </div>
                                    <p class="text-xs text-black font-semibold">
                                        @if($animal->sexAni === 'Hembra')
                                            <span class="inline-flex items-center text-xs font-semibold px-1.5 py-0.5 rounded-full bg-gradient-to-r from-pink-500 to-pink-600 text-white shadow-sm">
                                                Hembra
                                            </span>
                                        @else
                                            <span class="inline-flex items-center text-xs font-semibold px-1.5 py-0.5 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 text-white shadow-sm">
                                                Macho
                                            </span>
                                        @endif
                                    </p>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-blue-100">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-4m6 2l3.975 1.425M21 6l-3 9a5.002 5.002 0 01-6.001 0m3-9l-3 9"></path>
                                        </svg>
                                        <span class="text-xs text-black font-medium">Peso</span>
                                    </div>
                                    <p class="text-xs text-black font-semibold">{{ $animal->pesAni ? $animal->pesAni.' kg' : 'No registrado' }}</p>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-blue-100">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <span class="text-xs text-black font-medium">Fecha de Nacimiento</span>
                                    </div>
                                    <p class="text-xs text-black font-semibold">
                                        @if($animal->fecNacAni)
                                            {{ \Carbon\Carbon::parse($animal->fecNacAni)->format('d/m/Y') }}
                                            <span class="text-xs text-black">({{ \Carbon\Carbon::parse($animal->fecNacAni)->age }} años)</span>
                                        @else
                                            No registrada
                                        @endif
                                    </p>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-blue-100">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        <span class="text-xs text-black font-medium">Ubicación</span>
                                    </div>
                                    <p class="text-xs text-black font-semibold">{{ $animal->ubicacionAni ?? 'No registrada' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contenedor para Información de Producción e Historial Relacionado -->
                    <div class="flex flex-col gap-2">
                        <!-- Información de Producción -->
                        <div class="bg-white rounded-2xl p-2 border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-300">
                            <div class="flex items-center space-x-2 mb-2">
                                <div class="p-1.5 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl shadow-md">
                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-xs font-bold text-gray-900">Información de Producción</h3>
                            </div>
                            <div class="bg-green-50/50 border border-green-200 rounded-xl p-2">
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between py-1 border-b border-green-100">
                                        <div class="flex items-center space-x-2">
                                            <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            <span class="text-xs text-black font-medium">Categoría</span>
                                        </div>
                                        <p class="text-xs text-black font-semibold">
                                            @if($produccion->tipProAni)
                                                <span class="inline-flex items-center text-xs font-semibold px-1.5 py-0.5 rounded-full {{ $this->getColorTipo($produccion->tipProAni) }} shadow-sm">
                                                    {{ $this->getTipoProduccionFormateado($produccion->tipProAni) }}
                                                </span>
                                            @else
                                                <span class="text-xs text-black italic">No especificada</span>
                                            @endif
                                        </p>
                                    </div>
                                    <div class="flex items-center justify-between py-1 border-b border-green-100">
                                        <div class="flex items-center space-x-2">
                                            <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                            </svg>
                                            <span class="text-xs text-black font-medium">Cantidad</span>
                                        </div>
                                        <p class="text-xs text-black font-semibold">
                                            @if($produccion->canProAni)
                                                {{ number_format($produccion->canProAni, 2) }}
                                                <span class="text-xs text-black">{{ $produccion->uniProAni ?: 'unidades' }}</span>
                                            @else
                                                <span class="text-xs text-black italic">No especificada</span>
                                            @endif
                                        </p>
                                    </div>
                                    <div class="flex items-center justify-between py-1 border-b border-green-100">
                                        <div class="flex items-center space-x-2">
                                            <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                            </svg>
                                            <span class="text-xs text-black font-medium">Unidad</span>
                                        </div>
                                        <p class="text-xs text-black font-semibold">
                                            @if($produccion->uniProAni)
                                                <span class="inline-flex items-center text-xs font-semibold px-1.5 py-0.5 rounded-full bg-gradient-to-r from-gray-500 to-gray-600 text-white shadow-sm">
                                                    {{ ucfirst($produccion->uniProAni) }}
                                                </span>
                                            @else
                                                <span class="text-xs text-black italic">No especificada</span>
                                            @endif
                                        </p>
                                    </div>
                                    <div class="flex items-center justify-between py-1 border-b border-green-100">
                                        <div class="flex items-center space-x-2">
                                            <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                            </svg>
                                            <span class="text-xs text-black font-medium">Cantidad Total</span>
                                        </div>
                                        <p class="text-xs text-black font-semibold">
                                            @if($produccion->canTotProAni)
                                                {{ number_format($produccion->canTotProAni, 2) }}
                                                <span class="text-xs text-black">{{ $produccion->uniProAni ?: 'unidades' }}</span>
                                            @else
                                                <span class="text-xs text-black italic">No especificada</span>
                                            @endif
                                        </p>
                                    </div>
                                    <div class="flex items-center justify-between py-1 border-b border-green-100">
                                        <div class="flex items-center space-x-2">
                                            <svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            <span class="text-xs text-black font-medium">Fecha</span>
                                        </div>
                                        <p class="text-xs text-black font-semibold">
                                            @if($produccion->fecProAni)
                                                {{ $produccion->fecProAni->format('d/m/Y') }}
                                                <span class="text-xs text-black">({{ $produccion->fecProAni->diffForHumans() }})</span>
                                            @else
                                                <span class="text-xs text-black italic">No especificada</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <!-- Historial Relacionado -->
                    <div class="bg-white rounded-2xl p-2 border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-300">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-teal-500 to-teal-600 rounded-xl shadow-md">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xs font-bold text-gray-900">
                                Historial reciente de producción
                                <span class="text-[10px] text-teal-600 font-medium">({{ $produccion->animal->nitAni ?? 'Animal' }})</span>
                            </h3>
                        </div>
                        <div class="bg-teal-50/50 border border-teal-200 rounded-xl p-2">
                            @if($produccion->idAniPro)
                                @forelse($historial as $registro)
                                    <div class="bg-teal-50 rounded-xl p-2 border border-teal-100 hover:bg-teal-100 transition-colors duration-300 mb-2">
                                        <div class="grid grid-cols-6 gap-2 items-center text-xs"> <!-- Cambiado de 5 a 6 columnas -->
                                            <!-- Fecha -->
                                            <div class="text-teal-900 font-semibold">
                                                {{ $registro->fecProAni ? $registro->fecProAni->format('d/m/Y') : 'Sin fecha' }}
                                            </div>
                                            <!-- Tipo -->
                                            <div class="text-center">
                                                <span class="inline-flex items-center text-[10px] font-semibold px-1.5 py-0.5 rounded-full {{ $this->getColorTipo($registro->tipProAni) }} shadow-sm">
                                                    {{ $this->getTipoProduccionFormateado($registro->tipProAni) }}
                                                </span>
                                            </div>
                                            <!-- Cantidad -->
                                            <div class="text-center text-teal-900 font-semibold">
                                                {{ $registro->canProAni ? number_format($registro->canProAni, 2) : '0' }}
                                            </div>
                                            <!-- Total -->
                                            <div class="text-right text-[10px] text-teal-600">
                                                {{ $registro->canTotProAni ? 'Total: ' . number_format($registro->canTotProAni, 2) : '-' }}
                                            </div>
                                            <!-- Unidad -->
                                            <div class="text-center text-[10px] text-teal-600">
                                                {{ $registro->uniProAni ?: 'un.' }}
                                            </div>
                                            <!-- Acción Ver -->
                                            <div class="text-right">
                                                <a href="{{ route('pecuario.produccion.show', $registro->idProAni) }}" wire:navigate
                                                class="bg-blue-100 hover:bg-blue-200 text-blue-600 text-xs font-medium py-1 px-1.5 rounded transition duration-200 inline-flex items-center">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                    <span class="text-[10px]">Ver</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-4 text-teal-600">
                                        <svg class="w-6 h-6 mx-auto text-teal-300 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                        </svg>
                                        <p class="text-[10px] italic">No hay registros históricos disponibles para este animal</p>
                                    </div>
                                @endforelse
                                @if($historial->hasPages())
                                    <div class="mt-3 pt-2 border-t border-teal-200 flex justify-end">
                                        {{ $historial->links() }}
                                    </div>
                                @endif
                            @else
                                <div class="text-center py-4 text-teal-600">
                                    <svg class="w-6 h-6 mx-auto text-teal-300 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <p class="text-[10px] italic">Este registro no está asociado a un animal específico</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                    <!-- Información Adicional -->
                    <div class="lg:col-span-2 bg-white rounded-2xl p-2 border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-300">
                        <div class="flex items-center space-x-2 mb-2">
                            <div class="p-1.5 bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl shadow-md">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xs font-bold text-gray-900">Información Adicional</h3>
                        </div>
                        <div class="bg-yellow-50/50 border border-yellow-200 rounded-xl p-2">
                            @if($produccion->obsProAni)
                                <p class="text-xs text-black whitespace-pre-line">{{ $produccion->obsProAni }}</p>
                            @else
                                <p class="text-xs text-black italic">No hay observaciones disponibles</p>
                            @endif
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