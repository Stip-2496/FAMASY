<?php
// resources/views/livewire/inventario/prestamos/create.blade.php

use App\Models\PrestamoHerramienta;
use App\Models\Herramienta;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component {
    public PrestamoHerramienta $prestamo;
    public $herramientas;
    public $usuarios;
    
    public $idHerPre = '';
    public $idUsuPre = '';
    public $fecPre = '';
    public $fecDev = '';
    public $estPre = 'prestado';
    public $obsPre = '';

    public function mount(): void
    {
        $this->prestamo = new PrestamoHerramienta();
        $this->herramientas = Herramienta::where('canHer', '>', 0)->get();
        $this->usuarios = User::all();
        $this->fecPre = now()->format('Y-m-d');
    }

    public function rules(): array
    {
        return [
            'idHerPre' => 'required|exists:herramientas,idHer',
            'idUsuPre' => 'required|exists:users,id',
            'fecPre' => 'required|date',
            'fecDev' => 'nullable|date|after_or_equal:fecPre',
            'estPre' => 'required|in:prestado,devuelto,vencido',
            'obsPre' => 'nullable|string'
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();
        
        try {
            PrestamoHerramienta::create($validated);
            session()->flash('success', 'Préstamo registrado exitosamente');
            $this->redirect(route('inventario.prestamos.index'), navigate: true);
        } catch (\Exception $e) {
            session()->flash('error', 'Error al registrar el préstamo: ' . $e->getMessage());
        }
    }

    public function clear(): void
    {
        $this->reset();
        $this->resetErrorBag();
        $this->fecPre = now()->format('Y-m-d');
        $this->estPre = 'prestado';
    }
}; ?>

@section('title', 'Registrar Nuevo Préstamo')

<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                        <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                        Registrar Nuevo Préstamo
                    </h1>
                    <p class="mt-1 text-sm text-gray-600">Complete la información para registrar un préstamo de herramienta</p>
                </div>
                <a href="{{ route('inventario.prestamos.index') }}" wire:navigate
                   class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition duration-200 ease-in-out transform hover:scale-105">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Volver
                </a>
            </div>
        </div>

        <!-- Formulario -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
            <!-- Header del Card -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                <h2 class="text-xl font-semibold text-white flex items-center">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Información del Préstamo
                </h2>
            </div>

            <!-- Body del Card -->
            <div class="p-6">
                <form wire:submit="save" id="prestamoForm">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Herramienta -->
                        <div class="space-y-2">
                            <label for="idHerPre" class="block text-sm font-semibold text-gray-700">
                                <svg class="w-4 h-4 inline mr-1 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                Herramienta *
                            </label>
                            <select id="idHerPre" wire:model="idHerPre" required
                                    class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200 @error('idHerPre') border-red-500 @enderror">
                                <option value="">Seleccione una herramienta...</option>
                                @foreach($herramientas as $herramienta)
                                    <option value="{{ $herramienta->idHer }}" 
                                            data-disponible="{{ $herramienta->canHer }}"
                                            data-codigo="{{ $herramienta->codHer }}">
                                        {{ $herramienta->codHer }} - {{ $herramienta->nomHer }}
                                        (Disponibles: {{ $herramienta->canHer }})
                                    </option>
                                @endforeach
                            </select>
                            @error('idHerPre')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-gray-500 flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                                Solo se muestran herramientas con stock disponible
                            </p>
                        </div>

                        <!-- Usuario -->
                        <div class="space-y-2">
                            <label for="idUsuPre" class="block text-sm font-semibold text-gray-700">
                                <svg class="w-4 h-4 inline mr-1 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                Usuario *
                            </label>
                            <select id="idUsuPre" wire:model="idUsuPre" required
                                    class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200 @error('idUsuPre') border-red-500 @enderror">
                                <option value="">Seleccione un usuario...</option>
                                @foreach($usuarios as $usuario)
                                    <option value="{{ $usuario->id }}">
                                        {{ $usuario->name }} - {{ $usuario->email }}
                                    </option>
                                @endforeach
                            </select>
                            @error('idUsuPre')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Fecha de Préstamo -->
                        <div class="space-y-2">
                            <label for="fecPre" class="block text-sm font-semibold text-gray-700">
                                <svg class="w-4 h-4 inline mr-1 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                Fecha de Préstamo *
                            </label>
                            <input type="date" id="fecPre" wire:model="fecPre" required
                                   class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200 @error('fecPre') border-red-500 @enderror">
                            @error('fecPre')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Fecha de Devolución Esperada -->
                        <div class="space-y-2">
                            <label for="fecDev" class="block text-sm font-semibold text-gray-700">
                                <svg class="w-4 h-4 inline mr-1 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Fecha de Devolución Esperada
                            </label>
                            <input type="date" id="fecDev" wire:model="fecDev" min="{{ $fecPre }}"
                                   class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200 @error('fecDev') border-red-500 @enderror">
                            @error('fecDev')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-gray-500 flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                                Opcional - Fecha estimada de devolución
                            </p>
                        </div>

                        <!-- Estado -->
                        <div class="space-y-2">
                            <label for="estPre" class="block text-sm font-semibold text-gray-700">
                                <svg class="w-4 h-4 inline mr-1 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
                                </svg>
                                Estado *
                            </label>
                            <select id="estPre" wire:model="estPre" required
                                    class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200 @error('estPre') border-red-500 @enderror">
                                <option value="prestado">🤝 Prestado</option>
                                <option value="devuelto">✅ Devuelto</option>
                                <option value="vencido">⚠️ Vencido</option>
                            </select>
                            @error('estPre')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Observaciones -->
                    <div class="mt-6 space-y-2">
                        <label for="obsPre" class="block text-sm font-semibold text-gray-700">
                            <svg class="w-4 h-4 inline mr-1 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Observaciones
                        </label>
                        <textarea id="obsPre" wire:model="obsPre" rows="4"
                                  placeholder="Escriba cualquier observación adicional sobre el préstamo..."
                                  class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200 resize-none @error('obsPre') border-red-500 @enderror"></textarea>
                        @error('obsPre')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Botones -->
                    <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-end">
                        <a href="{{ route('inventario.prestamos.index') }}" wire:navigate
                           class="inline-flex items-center justify-center px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition duration-200 ease-in-out transform hover:scale-105">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Cancelar
                        </a>
                        <button type="submit" id="btnSubmit"
                                class="inline-flex items-center justify-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition duration-200 ease-in-out transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h11a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            Registrar Préstamo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('livewire:initialized', () => {
    // Validación de fechas
    const fecPre = document.getElementById('fecPre');
    const fecDev = document.getElementById('fecDev');
    
    fecPre.addEventListener('change', function() {
        fecDev.min = this.value;
        if (fecDev.value && fecDev.value < this.value) {
            fecDev.value = '';
        }
    });
    
    // Información de herramientas
    const selectHerramienta = document.getElementById('idHerPre');
    selectHerramienta.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const disponibles = selectedOption.dataset.disponible;
        
        if (disponibles && parseInt(disponibles) <= 0) {
            alert('⚠️ Esta herramienta no tiene stock disponible');
            this.value = '';
            @this.set('idHerPre', '');
        }
    });
    
    // Confirmación antes de enviar
    document.getElementById('prestamoForm').addEventListener('submit', function(e) {
        const herramienta = document.getElementById('idHerPre');
        const usuario = document.getElementById('idUsuPre');
        
        if (!herramienta.value || !usuario.value) {
            e.preventDefault();
            alert('⚠️ Por favor complete todos los campos obligatorios');
            return;
        }
        
        const btnSubmit = document.getElementById('btnSubmit');
        btnSubmit.innerHTML = `
            <svg class="w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Registrando...
        `;
        btnSubmit.disabled = true;
    });
});
</script>