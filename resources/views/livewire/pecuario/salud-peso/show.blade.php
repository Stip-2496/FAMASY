<?php
use App\Models\HistorialMedico;
use App\Models\Animal;
use App\Models\Proveedor;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component {
    public HistorialMedico $historial;
    public Animal $animal;
    public $proveedor;

    public function mount(HistorialMedico $historial)
    {
        $this->historial = $historial;
        $this->animal = Animal::findOrFail($historial->idAniHis);
        $this->proveedor = $historial->idProveedor ? Proveedor::find($historial->idProveedor) : null;
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
                            <span class="font-semibold">ID:</span>
                            <span>{{ $animal->idAni }}</span>
                        </li>
                        <li class="px-4 py-2 flex justify-between">
                            <span class="font-semibold">Nombre:</span>
                            <span>{{ $animal->nomAni ?? 'Sin nombre' }}</span>
                        </li>
                        <li class="px-4 py-2 flex justify-between">
                            <span class="font-semibold">Especie:</span>
                            <span>{{ $animal->espAni }}</span>
                        </li>
                        <li class="px-4 py-2 flex justify-between">
                            <span class="font-semibold">Raza:</span>
                            <span>{{ $animal->razAni ?? 'No especificada' }}</span>
                        </li>
                        <li class="px-4 py-2 flex justify-between">
                            <span class="font-semibold">Sexo:</span>
                            <span>{{ $animal->sexAni }}</span>
                        </li>
                        <li class="px-4 py-2 flex justify-between">
                            <span class="font-semibold">Peso actual:</span>
                            <span>{{ $animal->pesAni }} kg</span>
                        </li>
                        <li class="px-4 py-2 flex justify-between">
                            <span class="font-semibold">Estado salud:</span>
                            <span>{{ ucfirst($animal->estSaludAni) }}</span>
                        </li>
                        @if($animal->fecNacAni)
                        <li class="px-4 py-2 flex justify-between">
                            <span class="font-semibold">Fecha nacimiento:</span>
                            <span>{{ \Carbon\Carbon::parse($animal->fecNacAni)->format('d/m/Y') }}</span>
                        </li>
                        @endif
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
                        @if($proveedor)
                        <li class="px-4 py-2 flex justify-between">
                            <span class="font-semibold">Proveedor:</span>
                            <span>{{ $proveedor->nomProve }}</span>
                        </li>
                        <li class="px-4 py-2 flex justify-between">
                            <span class="font-semibold">Tipo proveedor:</span>
                            <span>{{ $proveedor->tipSumProve ?? 'No especificado' }}</span>
                        </li>
                        @endif
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

            <!-- Información del Proveedor (si existe) -->
            @if($proveedor)
            <div class="border border-gray-200 rounded-md mb-6">
                <div class="bg-gray-100 px-4 py-3 rounded-t-md">
                    <h5 class="font-semibold text-gray-700 mb-0">Información del Proveedor</h5>
                </div>
                <div class="px-4 py-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm"><span class="font-semibold">ID:</span> {{ $proveedor->idProve }}</p>
                            <p class="text-sm"><span class="font-semibold">NIT:</span> {{ $proveedor->nitProve ?? 'No especificado' }}</p>
                            <p class="text-sm"><span class="font-semibold">Contacto:</span> {{ $proveedor->conProve ?? 'No especificado' }}</p>
                        </div>
                        <div>
                            <p class="text-sm"><span class="font-semibold">Teléfono:</span> {{ $proveedor->telProve ?? 'No especificado' }}</p>
                            <p class="text-sm"><span class="font-semibold">Email:</span> {{ $proveedor->emailProve ?? 'No especificado' }}</p>
                            <p class="text-sm"><span class="font-semibold">Dirección:</span> {{ $proveedor->dirProve ?? 'No especificado' }}</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Botones de acción -->
            <div class="flex justify-between items-center">
                <a href="{{ route('pecuario.salud-peso.index') }}" wire:navigate
                   class="inline-flex items-center px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded shadow">
                   <i class="fas fa-arrow-left mr-2"></i> Volver
                </a>
                
                <div class="flex gap-2">
                    <a href="{{ route('pecuario.salud-peso.edit', $historial->idHisMed) }}" wire:navigate
                       class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded shadow">
                       <i class="fas fa-edit mr-2"></i> Editar
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>