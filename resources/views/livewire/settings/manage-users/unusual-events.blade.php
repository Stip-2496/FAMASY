<?php
use App\Models\Auditoria;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.auth')] class extends Component {
    use WithPagination;
    
    public $search = '';
    public $perPage = 10;
    public $filters = [
        'severidad' => null
    ];
    
    public function logs()
    {
        return Auditoria::query()
            ->with('usuario')
            ->where(function($query) {
                $query->whereIn('opeAud', ['LOGIN_FAILED', 'UNAUTHORIZED_ACCESS'])
                      ->orWhere('desAud', 'like', '%intento fallido%')
                      ->orWhere('desAud', 'like', '%acceso no autorizado%');
            })
            ->when($this->filters['severidad'], function($query) {
                $query->where(function($q) {
                    if ($this->filters['severidad'] === 'critica') {
                        $q->where('desAud', 'like', '%crítico%')
                          ->orWhere('opeAud', 'UNAUTHORIZED_ACCESS');
                    } elseif ($this->filters['severidad'] === 'alta') {
                        $q->where('desAud', 'like', '%peligroso%');
                    } elseif ($this->filters['severidad'] === 'media') {
                        $q->where('desAud', 'like', '%advertencia%');
                    } elseif ($this->filters['severidad'] === 'baja') {
                        $q->where('opeAud', 'LOGIN_FAILED');
                    }
                });
            })
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('opeAud', 'like', '%'.$this->search.'%')
                      ->orWhere('desAud', 'like', '%'.$this->search.'%')
                      ->orWhere('usuAud', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy('fecAud', 'desc')
            ->paginate($this->perPage);
    }
}; ?>

@section('title', 'Eventos Anómalos')

<div class="bg-gray-100 min-h-full p-4">
    <div class="max-w-7xl mx-auto">
        <!-- Header y filtros (similar al anterior pero con severidad) -->
        
        <!-- Tabla adaptada para eventos anómalos -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Severidad</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Operación</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP</th>
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
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $severidad = $log->getSeveridadAttribute();
                                    $colorClasses = [
                                        'baja' => 'bg-green-100 text-green-800',
                                        'media' => 'bg-yellow-100 text-yellow-800',
                                        'alta' => 'bg-orange-100 text-orange-800',
                                        'critica' => 'bg-red-100 text-red-800'
                                    ];
                                @endphp
                                <span class="px-2 py-1 text-xs rounded-full {{ $colorClasses[$severidad] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ ucfirst($severidad) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $log->opeAud }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $log->ipAud }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate" title="{{ $log->desAud }}">
                                {{ $log->desAud }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                No se encontraron eventos anómalos
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