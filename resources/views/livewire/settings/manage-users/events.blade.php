<?php
use App\Models\Auditoria;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.auth')] class extends Component {
    use WithPagination;
    
    public $search = '';
    public $perPage = 10;
    
    public function logs()
    {
        return Auditoria::query()
            ->with('usuario')
            ->whereNotIn('opeAud', ['LOGIN_FAILED', 'UNAUTHORIZED_ACCESS'])
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('opeAud', 'like', '%'.$this->search.'%')
                      ->orWhere('desAud', 'like', '%'.$this->search.'%')
                      ->orWhere('usuAud', 'like', '%'.$this->search.'%')
                      ->orWhere('rolAud', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy('fecAud', 'desc')
            ->paginate($this->perPage);
    }
}; ?>

@section('title', 'Eventos del Sistema')

<div class="bg-gray-100 min-h-full p-4">
    <div class="max-w-7xl mx-auto">
        <!-- Header y filtros (igual que antes) -->
        
        <!-- Tabla adaptada -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Operación</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tabla</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Detalle</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($this->logs() as $log)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $log->fecAud->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $log->usuAud ?? 'Sistema' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $log->rolAud }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded-full 
                                    @if(in_array($log->opeAud, ['INSERT', 'LOGIN'])) bg-green-100 text-green-800
                                    @elseif(in_array($log->opeAud, ['UPDATE'])) bg-blue-100 text-blue-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ $log->opeAud }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $log->tablaAud }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate" title="{{ $log->desAud }}">
                                {{ $log->desAud }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                No se encontraron eventos registrados
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <!-- Paginación -->
        </div>
    </div>
</div>