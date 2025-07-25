<?php
use App\Models\Animal;
use App\Models\HistorialMedico;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Route;

new #[Layout('layouts.auth')] class extends Component {
    public $animales;
    public $idAniHis;
    public $tipHisMed;
    public $fecHisMed;
    public $desHisMed;
    public $responHisMed;
    public $obsHisMed;

    public function mount()
    {
        $this->animales = Animal::where('estAni', 'vivo')->get();
        $this->fecHisMed = date('Y-m-d');
        $this->responHisMed = auth()->user()->name;
    }

    public function rules()
    {
        return [
            'idAniHis' => 'required|exists:animales,idAni',
            'tipHisMed' => 'required|in:vacuna,tratamiento,control',
            'fecHisMed' => 'required|date',
            'desHisMed' => 'required|string|max:500',
            'responHisMed' => 'required|string|max:100',
            'obsHisMed' => 'nullable|string|max:500'
        ];
    }

    public function save()
    {
        $validated = $this->validate();
        
        try {
            HistorialMedico::create($validated);
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Registro médico creado exitosamente'
            ]);
            
            return redirect()->route('pecuario.salud-peso.index');
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al crear el registro: ' . $e->getMessage()
            ]);
        }
    }
}; ?>

@section('title', 'Nuevo Registro Médico')

<div class="container mx-auto px-4 py-6">
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <!-- Header -->
        <div class="bg-green-700 text-white px-6 py-4 rounded-t-lg flex items-center gap-2">
            <i class="fas fa-plus-circle"></i>
            <h5 class="text-lg font-semibold">Nuevo Registro Médico</h5>
        </div>
        
        <div class="p-6">
            <form wire:submit="save" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Animal <span class="text-red-500">*</span></label>
                        <select wire:model="idAniHis" class="w-full border border-gray-300 rounded px-3 py-2" required>
                            <option value="">Seleccionar animal</option>
                            @foreach($animales as $animal)
                            <option value="{{ $animal->idAni }}">
                                {{ $animal->nomAni ?? 'Animal #'.$animal->idAni }} ({{ $animal->espAni }})
                            </option>
                            @endforeach
                        </select>
                        @error('idAniHis') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Tipo de Registro <span class="text-red-500">*</span></label>
                        <select wire:model="tipHisMed" class="w-full border border-gray-300 rounded px-3 py-2" required>
                            <option value="">Seleccionar tipo</option>
                            <option value="vacuna">Vacuna</option>
                            <option value="tratamiento">Tratamiento</option>
                            <option value="control">Control de Peso</option>
                        </select>
                        @error('tipHisMed') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Campos comunes -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Fecha <span class="text-red-500">*</span></label>
                        <input type="date" wire:model="fecHisMed" class="w-full border border-gray-300 rounded px-3 py-2" required>
                        @error('fecHisMed') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Responsable <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="responHisMed" class="w-full border border-gray-300 rounded px-3 py-2" required>
                        @error('responHisMed') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label class="block mb-1 font-medium text-gray-700">Descripción <span class="text-red-500">*</span></label>
                    <textarea wire:model="desHisMed" rows="3" class="w-full border border-gray-300 rounded px-3 py-2" required></textarea>
                    @error('desHisMed') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block mb-1 font-medium text-gray-700">Observaciones</label>
                    <textarea wire:model="obsHisMed" rows="2" class="w-full border border-gray-300 rounded px-3 py-2"></textarea>
                    @error('obsHisMed') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="flex justify-between items-center">
                    <a href="{{ route('pecuario.salud-peso.index') }}" wire:navigate
                       class="inline-flex items-center gap-2 px-4 py-2 border border-green-600 rounded text-green-700 hover:bg-green-100">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>