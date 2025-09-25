<?php
use App\Models\ProduccionAnimal;
use App\Models\Animal;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component {
    public ProduccionAnimal $produccion;
    public $animales;
    public $idAniPro;
    public $tipProAni;
    public $canProAni;
    public $uniProAni;
    public $fecProAni;
    public $obsProAni;
    public $canTotProAni;

    public function mount(ProduccionAnimal $produccion)
    {
        $this->produccion = $produccion;
        $this->animales = Animal::where('estAni', 'vivo')->get(['idAni', 'ideAni', 'espAni']);
        $this->idAniPro = $produccion->idAniPro;
        $this->tipProAni = $produccion->tipProAni;
        $this->canProAni = $produccion->canProAni;
        $this->uniProAni = $produccion->uniProAni;
        $this->fecProAni = $produccion->fecProAni ? $produccion->fecProAni->format('Y-m-d') : '';
        $this->obsProAni = $produccion->obsProAni;
        $this->canTotProAni = $produccion->canTotProAni;
    }

    public function rules()
    {
        return [
            'idAniPro' => 'nullable|exists:animales,idAni',
            'tipProAni' => 'required|in:leche bovina,venta en pie bovino,lana ovina,venta en pie ovino,leche ovina,venta gallinas en pie,huevo A,huevo AA,huevo AAA,huevo Jumbo,huevo B,huevo C,venta pollo engorde,otros',
            'canProAni' => 'nullable|numeric|min:0|max:99999999.99',
            'uniProAni' => 'nullable|string|max:20',
            'fecProAni' => 'nullable|date|before_or_equal:today',
            'obsProAni' => 'nullable|string',
            'canTotProAni' => 'nullable|numeric|min:0|max:999999.99'
        ];
    }

    public function update()
    {
        $validated = $this->validate();
        
        try {
            $this->produccion->update($validated);
            
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Registro actualizado correctamente'
            ]);
            
            $this->redirect(route('pecuario.produccion.show', $this->produccion->idProAni), navigate: true);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error al actualizar el registro: ' . $e->getMessage()
            ]);
        }
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

    public function getUnidadSugerida()
    {
        $unidades = [
            'leche bovina' => 'litros',
            'leche ovina' => 'litros',
            'lana ovina' => 'kg',
            'venta en pie bovino' => 'kg',
            'venta en pie ovino' => 'kg',
            'venta gallinas en pie' => 'unidades',
            'huevo A' => 'unidad',
            'huevo AA' => 'unidad',
            'huevo AAA' => 'unidad',
            'huevo Jumbo' => 'unidad',
            'huevo B' => 'unidad',
            'huevo C' => 'unidad',
            'venta pollo engorde' => 'kg',
            'otros' => ''
        ];
        
        return $unidades[$this->tipProAni] ?? '';
    }
}; ?>

<div class="max-w-4xl mx-auto px-4 py-6">
    <!-- Información Actual -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
        <div class="bg-green-600 text-white px-6 py-4">
            <h2 class="text-lg font-semibold flex items-center">
                <i class="fas fa-info-circle mr-2"></i> Información Actual
            </h2>
        </div>
        <div class="px-6 py-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600">ID #{{ $produccion->idProAni }}</p>
                    <p class="text-sm text-gray-600">Última Actualización: {{ $produccion->updated_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulario de Edición -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="bg-green-600 text-white px-6 py-4">
            <h2 class="text-lg font-semibold flex items-center">
                <i class="fas fa-edit mr-2"></i> Actualizar Información
            </h2>
        </div>

        <div class="px-6 py-4">
            <form wire:submit="update">
                <!-- Información Básica -->
                <div class="mb-6">
                    <h3 class="text-md font-medium text-gray-700 mb-4 flex items-center">
                        <i class="fas fa-tools mr-2"></i> Información Básica
                    </h3>
                    
                    <!-- Animal -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1 flex items-center">
                            <i class="fas fa-cow mr-1 text-gray-500"></i> Animal
                        </label>
                        <select wire:model="idAniPro" class="w-full border border-gray-300 rounded px-3 py-2">
                            <option value="">Seleccionar animal (opcional)</option>
                            @foreach($animales as $animal)
                            <option value="{{ $animal->idAni }}">
                                {{ $animal->ideAni }} - {{ ucfirst($animal->espAni) }}
                            </option>
                            @endforeach
                        </select>
                        @error('idAniPro')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tipo de Producción -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1 flex items-center">
                            <i class="fas fa-list-alt mr-1 text-gray-500"></i> Tipo de Producción *
                        </label>
                        <select wire:model.live="tipProAni" class="w-full border border-gray-300 rounded px-3 py-2" required>
                            <option value="">Seleccionar tipo de producción</option>
                            <optgroup label="Producción Bovina">
                                <option value="leche bovina" @selected('leche bovina' == $produccion->tipProAni)>Leche Bovina</option>
                                <option value="venta en pie bovino" @selected('venta en pie bovino' == $produccion->tipProAni)>Venta en Pie Bovino</option>
                            </optgroup>
                            <optgroup label="Producción Ovina">
                                <option value="lana ovina" @selected('lana ovina' == $produccion->tipProAni)>Lana Ovina</option>
                                <option value="venta en pie ovino" @selected('venta en pie ovino' == $produccion->tipProAni)>Venta en Pie Ovino</option>
                                <option value="leche ovina" @selected('leche ovina' == $produccion->tipProAni)>Leche Ovina</option>
                            </optgroup>
                            <optgroup label="Producción Avícola">
                                <option value="venta gallinas en pie" @selected('venta gallinas en pie' == $produccion->tipProAni)>Venta Gallinas en Pie</option>
                                <option value="huevo A" @selected('huevo A' == $produccion->tipProAni)>Huevo A</option>
                                <option value="huevo AA" @selected('huevo AA' == $produccion->tipProAni)>Huevo AA</option>
                                <option value="huevo AAA" @selected('huevo AAA' == $produccion->tipProAni)>Huevo AAA</option>
                                <option value="huevo Jumbo" @selected('huevo Jumbo' == $produccion->tipProAni)>Huevo Jumbo</option>
                                <option value="huevo B" @selected('huevo B' == $produccion->tipProAni)>Huevo B</option>
                                <option value="huevo C" @selected('huevo C' == $produccion->tipProAni)>Huevo C</option>
                                <option value="venta pollo engorde" @selected('venta pollo engorde' == $produccion->tipProAni)>Venta Pollo Engorde</option>
                            </optgroup>
                            <option value="otros" @selected('otros' == $produccion->tipProAni)>Otros</option>
                        </select>
                        @error('tipProAni')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Control de Producción -->
                <div class="mb-6">
                    <h3 class="text-md font-medium text-gray-700 mb-4 flex items-center">
                        <i class="fas fa-calculator mr-2"></i> Control de Producción
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Cantidad -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cantidad</label>
                            <div class="flex">
                                <input type="number" step="0.01" wire:model="canProAni"
                                       class="w-full border border-gray-300 rounded-l px-3 py-2"
                                       min="0" max="99999999.99" placeholder="0.00">
                                <span class="inline-flex items-center px-3 border border-l-0 border-gray-300 rounded-r bg-gray-100 text-gray-700">
                                    {{ $this->getUnidadSugerida() ?: 'unidad' }}
                                </span>
                            </div>
                            @error('canProAni')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Unidad de Medida -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Unidad de Medida</label>
                            <input type="text" wire:model="uniProAni" class="w-full border border-gray-300 rounded px-3 py-2"
                                   placeholder="{{ $this->getUnidadSugerida() ?: 'Ej: litros, kg, docenas...' }}"
                                   maxlength="20">
                            @error('uniProAni')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Cantidad Total -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cantidad Total</label>
                        <input type="number" step="0.01" wire:model="canTotProAni"
                               class="w-full border border-gray-300 rounded px-3 py-2"
                               min="0" max="999999.99" placeholder="0.00">
                        <p class="text-sm text-gray-500 mt-1 flex items-center">
                            <i class="fas fa-info-circle mr-1"></i> Cantidad total acumulada o procesada
                        </p>
                        @error('canTotProAni')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Fecha -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1 flex items-center">
                            <i class="far fa-calendar-alt mr-1 text-gray-500"></i> Fecha
                        </label>
                        <input type="date" wire:model="fecProAni" class="w-full border border-gray-300 rounded px-3 py-2"
                               max="{{ date('Y-m-d') }}">
                        @error('fecProAni')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Información Adicional -->
                <div class="mb-6">
                    <h3 class="text-md font-medium text-gray-700 mb-4 flex items-center">
                        <i class="fas fa-clipboard-list mr-2"></i> Información Adicional
                    </h3>
                    
                    <!-- Observaciones -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
                        <textarea wire:model="obsProAni" rows="4"
                                  class="w-full border border-gray-300 rounded px-3 py-2"
                                  placeholder="Detalles adicionales sobre la producción..."></textarea>
                        @error('obsProAni')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Botones -->
                <div class="flex justify-between mt-6 pt-4 border-t border-gray-200">
                    <a href="{{ route('pecuario.produccion.show', $produccion->idProAni) }}" wire:navigate
                       class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded shadow flex items-center">
                        <i class="fas fa-times mr-2"></i> Cancelar
                    </a>
                    <div class="flex gap-2">
                        <button type="submit"
                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow flex items-center">
                            <i class="fas fa-save mr-2"></i> Guardar Cambios
                        </button>
                        <button wire:click="delete" type="button"
                                class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded shadow flex items-center"
                                onclick="return confirm('¿Eliminar este registro?')">
                            <i class="fas fa-trash mr-2"></i> Eliminar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>