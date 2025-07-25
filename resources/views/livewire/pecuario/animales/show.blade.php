<?php
use App\Models\Animal;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component {
    public Animal $animal;

    public function mount(Animal $animal)
    {
        $this->animal = $animal;
    }
}; ?>

@section('title', 'Dashboard pecuario')

<div class="px-6 py-4">
    <!-- Encabezado -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-paw text-green-600"></i>
                {{ $animal->nomAni ?? 'Animal #' . $animal->idAni }}
            </h1>
            <nav class="text-sm text-gray-500 mt-1">
                <ol class="flex space-x-2">
                    <li><a href="{{ route('pecuario.dashboard') }}" wire:navigate class="hover:underline">Pecuario</a> /</li>
                    <li><a href="{{ route('pecuario.animales.index') }}" wire:navigate class="hover:underline">Animales</a> /</li>
                    <li class="text-gray-600">Detalle</li>
                </ol>
            </nav>
        </div>

        <!-- Acciones -->
        <div class="flex gap-2">
            <a href="{{ route('pecuario.animales.index') }}" wire:navigate
               class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded flex items-center">
                <i class="fas fa-arrow-left mr-2"></i> Volver
            </a>
            <a href="{{ route('pecuario.animales.edit', $animal->idAni) }}" wire:navigate
               class="bg-yellow-400 hover:bg-yellow-500 text-black px-4 py-2 rounded flex items-center">
                <i class="fas fa-edit mr-2"></i> Editar
            </a>
            <button wire:click="$dispatch('confirm-delete', { id: {{ $animal->idAni }} })"
                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded flex items-center">
                <i class="fas fa-trash mr-2"></i> Eliminar
            </button>
        </div>
    </div>

    <!-- Información del Animal -->
    <div class="bg-white shadow rounded-lg p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <!-- Tabla 1 -->
            <table class="w-full text-sm text-left border rounded">
                <tr><th class="bg-gray-100 px-4 py-2">ID</th><td class="px-4 py-2">{{ $animal->idAni }}</td></tr>
                <tr><th class="bg-gray-100 px-4 py-2">Especie</th><td class="px-4 py-2">{{ $animal->espAni }}</td></tr>
                <tr><th class="bg-gray-100 px-4 py-2">Raza</th><td class="px-4 py-2">{{ $animal->razAni ?? 'N/A' }}</td></tr>
                <tr>
                    <th class="bg-gray-100 px-4 py-2">Sexo</th>
                    <td class="px-4 py-2">
                        <span class="px-2 py-1 rounded text-white text-xs {{ $animal->sexAni === 'Hembra' ? 'bg-pink-500' : 'bg-blue-500' }}">
                            {{ $animal->sexAni }}
                        </span>
                    </td>
                </tr>
            </table>

            <!-- Tabla 2 -->
            <table class="w-full text-sm text-left border rounded">
                <tr>
                    <th class="bg-gray-100 px-4 py-2">Estado</th>
                    <td class="px-4 py-2">
                        @switch($animal->estAni)
                            @case('vivo') <span class="text-green-700 bg-green-100 px-2 py-1 rounded text-xs">Vivo</span> @break
                            @case('muerto') <span class="text-red-700 bg-red-100 px-2 py-1 rounded text-xs">Muerto</span> @break
                            @case('vendido') <span class="text-yellow-800 bg-yellow-200 px-2 py-1 rounded text-xs">Vendido</span> @break
                            @default <span class="text-gray-700 bg-gray-200 px-2 py-1 rounded text-xs">Desconocido</span>
                        @endswitch
                    </td>
                </tr>
                <tr>
                    <th class="bg-gray-100 px-4 py-2">Fecha Nacimiento</th>
                    <td class="px-4 py-2">
                        @if($animal->fecNacAni)
                            {{ \Carbon\Carbon::parse($animal->fecNacAni)->format('d/m/Y') }}
                            ({{ \Carbon\Carbon::parse($animal->fecNacAni)->age }} años)
                        @else
                            N/A
                        @endif
                    </td>
                </tr>
                <tr>
                    <th class="bg-gray-100 px-4 py-2">Peso Actual</th>
                    <td class="px-4 py-2">{{ $animal->pesAni ? $animal->pesAni . ' kg' : 'N/A' }}</td>
                </tr>
                <tr>
                    <th class="bg-gray-100 px-4 py-2">Estado Reproductivo</th>
                    <td class="px-4 py-2">
                        @php
                            $estadosRepro = [
                                'no_aplica' => 'No aplica',
                                'ciclo' => 'En ciclo',
                                'cubierta' => 'Cubierta',
                                'gestacion' => 'Gestación',
                                'parida' => 'Parida'
                            ];
                        @endphp
                        {{ $estadosRepro[$animal->estReproAni] ?? 'N/A' }}
                    </td>
                </tr>
            </table>
        </div>

        <!-- Observaciones -->
        @if($animal->obsAni)
            <div class="mt-6">
                <h4 class="font-semibold mb-2 flex items-center">
                    <i class="fas fa-comment mr-2"></i> Observaciones
                </h4>
                <div class="bg-gray-100 p-4 rounded text-gray-700 whitespace-pre-line">
                    {{ $animal->obsAni }}
                </div>
            </div>
        @endif
    </div>
</div>