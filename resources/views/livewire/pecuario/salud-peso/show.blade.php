<?php
use App\Models\HistorialMedico;
use App\Models\Animal;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component {
    public HistorialMedico $historial;
    public Animal $animal;

    public function mount(HistorialMedico $historial)
    {
    $this->historial = $historial;
    $this->animal = Animal::findOrFail($historial->idAniHis);
    }
}; ?>

@section('title', 'Detalles del Registro Médico')

<div class="container mx-auto px-4 py-6">
    <div class="bg-white shadow-lg rounded-lg">
        <!-- Header -->
        <div class="bg-green-700 text-white px-6 py-4 rounded-t-lg flex items-center gap-2">
            <i class="fas fa-eye"></i>
            <h5 class="text-lg font-semibold mb-0">Detalles del Registro Médico</h5>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Información del Animal -->
                <div>
                    <h5 class="text-lg font-semibold mb-3">Información del Animal</h5>
                    <ul class="border border-gray-200 rounded-md divide-y divide-gray-200">
                        <li class="px-4 py-2 flex justify-between">
                            <span class="font-semibold">Nombre:</span>
                            <span>{{ $animal->nomAni ?? 'Sin nombre' }}</span>
                        </li>
                        <li class="px-4 py-2 flex justify-between">
                            <span class="font-semibold">Especie:</span>
                            <span>{{ $animal->espAni }}</span>
                        </li>
                        <li class="px-4 py-2 flex justify-between">
                            <span class="font-semibold">Peso actual:</span>
                            <span>{{ $animal->pesAni }} kg</span>
                        </li>
                        <li class="px-4 py-2 flex justify-between">
                            <span class="font-semibold">Estado salud:</span>
                            <span>{{ ucfirst($animal->estSaludAni) }}</span>
                        </li>
                    </ul>
                </div>

                <!-- Detalles del Registro -->
                <div>
                    <h5 class="text-lg font-semibold mb-3">Detalles del Registro</h5>
                    <ul class="border border-gray-200 rounded-md divide-y divide-gray-200">
                        <li class="px-4 py-2 flex justify-between">
                            <span class="font-semibold">Tipo:</span>
                            <span>{{ ucfirst($historial->tipHisMed) }}</span>
                        </li>
                        <li class="px-4 py-2 flex justify-between">
                            <span class="font-semibold">Fecha:</span>
                            <span>{{ \Carbon\Carbon::parse($historial->fecHisMed)->format('d/m/Y') }}</span>
                        </li>
                        <li class="px-4 py-2 flex justify-between">
                            <span class="font-semibold">Responsable:</span>
                            <span>{{ $historial->responHisMed }}</span>
                        </li>
                        <li class="px-4 py-2 flex justify-between">
                            <span class="font-semibold">Registrado:</span>
                            <span>{{ $historial->created_at->format('d/m/Y H:i') }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Descripción -->
            <div class="border border-gray-200 rounded-md mb-6">
                <div class="bg-gray-100 px-4 py-3 rounded-t-md">
                    <h5 class="font-semibold text-gray-700 mb-0">Descripción</h5>
                </div>
                <div class="px-4 py-4 text-gray-700">
                    <p>{{ $historial->desHisMed }}</p>
                </div>
            </div>

            <!-- Observaciones (opcional) -->
            @if($historial->obsHisMed)
            <div class="border border-gray-200 rounded-md mb-6">
                <div class="bg-gray-100 px-4 py-3 rounded-t-md">
                    <h5 class="font-semibold text-gray-700 mb-0">Observaciones</h5>
                </div>
                <div class="px-4 py-4 text-gray-700">
                    <p>{{ $historial->obsHisMed }}</p>
                </div>
            </div>
            @endif

            <!-- Botón volver -->
            <div>
                <a href="{{ route('pecuario.salud-peso.index') }}" wire:navigate
                   class="inline-flex items-center px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded shadow">
                   <i class="fas fa-arrow-left mr-2"></i> Volver
                </a>
            </div>
        </div>
    </div>
</div>