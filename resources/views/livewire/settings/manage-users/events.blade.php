<?php
use App\Models\Auditoria;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.auth')] class extends Component {
    use WithPagination;
    
    public $search = '';
    public $operacion = '';
    public $usuario = '';
    public $rol = '';
    public $tabla = '';
    public $perPage = 10;
    
    // Propiedades para el filtro de fechas
    public $startDate = '';
    public $endDate = '';
    public $showDateModal = false;
    public $selectedRangeType = 'all_time';
    public $tempStartDate = '';
    public $tempEndDate = '';
    public $calendarMonth;
    public $calendarYear;
    public $availableYears = [];
    public $firstRecordDate;
    public $lastRecordDate;

    // Propiedades para el modal de detalles
    public $showDetailModal = false;
    public $selectedLog = null;

    public function mount()
    {
        // Obtener fechas del primer y último registro
        $firstRecord = Auditoria::orderBy('fecAud', 'asc')->first();
        $lastRecord = Auditoria::orderBy('fecAud', 'desc')->first();
        
        if ($firstRecord && $lastRecord) {
            $this->firstRecordDate = $firstRecord->fecAud;
            $this->lastRecordDate = $lastRecord->fecAud;
            
            $this->startDate = $firstRecord->fecAud->format('Y-m-d');
            $this->endDate = $lastRecord->fecAud->format('Y-m-d');
            
            // Generar años disponibles
            $firstYear = $firstRecord->fecAud->year;
            $lastYear = $lastRecord->fecAud->year;
            
            for ($year = $firstYear; $year <= $lastYear; $year++) {
                $this->availableYears[] = $year;
            }
            
            // Inicializar calendario con el mes y año actual
            $this->calendarMonth = now()->month;
            $this->calendarYear = now()->year;
        }
    }
    
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
                      ->orWhere('rolAud', 'like', '%'.$this->search.'%')
                      ->orWhere('tablaAud', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->operacion, function($query) {
                $query->where('opeAud', $this->operacion);
            })
            ->when($this->usuario, function($query) {
                $query->where('usuAud', 'like', '%'.$this->usuario.'%');
            })
            ->when($this->rol, function($query) {
                $query->where('rolAud', $this->rol);
            })
            ->when($this->tabla, function($query) {
                $query->where('tablaAud', 'like', '%'.$this->tabla.'%');
            })
            ->when($this->startDate && $this->endDate, function($query) {
                $query->whereBetween('fecAud', [
                    $this->startDate . ' 00:00:00',
                    $this->endDate . ' 23:59:59'
                ]);
            })
            ->orderBy('fecAud', 'desc')
            ->paginate($this->perPage);
    }

    public function openDateModal()
    {
        $this->tempStartDate = $this->startDate;
        $this->tempEndDate = $this->endDate;
        $this->showDateModal = true;
    }
    
    public function applyDateFilter()
    {
        $this->startDate = $this->tempStartDate;
        $this->endDate = $this->tempEndDate;
        $this->showDateModal = false;
        $this->resetPage();
    }
    
    public function cancelDateFilter()
    {
        $this->tempStartDate = $this->startDate;
        $this->tempEndDate = $this->endDate;
        $this->showDateModal = false;
    }
    
    public function selectRange($rangeType)
    {
        $this->selectedRangeType = $rangeType;
        
        $today = now();
        
        switch ($rangeType) {
            case 'all_time':
                $this->tempStartDate = $this->firstRecordDate->format('Y-m-d');
                $this->tempEndDate = $this->lastRecordDate->format('Y-m-d');
                break;
            case 'last_24_hours':
                $this->tempStartDate = $today->copy()->subHours(24)->format('Y-m-d');
                $this->tempEndDate = $today->format('Y-m-d');
                break;
            case 'last_7_days':
                $this->tempStartDate = $today->copy()->subDays(7)->format('Y-m-d');
                $this->tempEndDate = $today->format('Y-m-d');
                break;
            case 'last_30_days':
                $this->tempStartDate = $today->copy()->subDays(30)->format('Y-m-d');
                $this->tempEndDate = $today->format('Y-m-d');
                break;
            default:
                // Para años específicos
                if (strpos($rangeType, 'year_') === 0) {
                    $year = substr($rangeType, 5);
                    $this->tempStartDate = $year . '-01-01';
                    $this->tempEndDate = $year . '-12-31';
                }
                break;
        }
    }
    
    public function selectDate($date)
    {
        if (!$this->tempStartDate || ($this->tempStartDate && $this->tempEndDate)) {
            // Si no hay fecha seleccionada o ya hay un rango completo, empezar nuevo rango
            $this->tempStartDate = $date;
            $this->tempEndDate = '';
        } else if ($this->tempStartDate && !$this->tempEndDate) {
            // Si ya hay una fecha de inicio, establecer fecha de fin
            if ($date < $this->tempStartDate) {
                // Si la nueva fecha es anterior, intercambiar
                $this->tempEndDate = $this->tempStartDate;
                $this->tempStartDate = $date;
            } else {
                $this->tempEndDate = $date;
            }
        }
    }
    
    public function changeCalendarMonth($direction)
    {
        if ($direction === 'next') {
            if ($this->calendarMonth == 12) {
                $this->calendarMonth = 1;
                $this->calendarYear++;
            } else {
                $this->calendarMonth++;
            }
        } else {
            if ($this->calendarMonth == 1) {
                $this->calendarMonth = 12;
                $this->calendarYear--;
            } else {
                $this->calendarMonth--;
            }
        }
    }

    // Métodos para el modal de detalles
    public function showLogDetails($logId)
    {
        // Buscar usando el campo ID correcto (probablemente 'id' en lugar de 'idAud')
        $this->selectedLog = Auditoria::with('usuario')->find($logId);
        
        if ($this->selectedLog) {
            $this->showDetailModal = true;
        } else {
            // Opcional: mostrar mensaje de error o log para debugging
            session()->flash('error', 'No se pudo encontrar el evento solicitado.');
        }
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->selectedLog = null;
    }
    
    public function clearFilters(): void
    {
        $this->search = '';
        $this->operacion = '';
        $this->usuario = '';
        $this->rol = '';
        $this->tabla = '';
        
        // Restablecer fechas a los valores por defecto
        if ($this->firstRecordDate && $this->lastRecordDate) {
            $this->startDate = $this->firstRecordDate->format('Y-m-d');
            $this->endDate = $this->lastRecordDate->format('Y-m-d');
        }
        
        $this->resetPage();
    }
    
    public function updatedStartDate()
    {
        $this->resetPage();
    }
    
    public function updatedEndDate()
    {
        $this->resetPage();
    }
    
    // Generar días del mes para el calendario
    public function getCalendarDaysProperty()
    {
        if (!$this->calendarMonth || !$this->calendarYear) {
            return [];
        }
        
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $this->calendarMonth, $this->calendarYear);
        $firstDay = date('N', strtotime($this->calendarYear . '-' . $this->calendarMonth . '-01'));
        
        $days = [];
        
        // Días vacíos al inicio
        for ($i = 1; $i < $firstDay; $i++) {
            $days[] = ['day' => '', 'date' => null];
        }
        
        // Días del mes
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = $this->calendarYear . '-' . str_pad($this->calendarMonth, 2, '0', STR_PAD_LEFT) . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);
            $days[] = ['day' => $day, 'date' => $date];
        }
        
        return $days;
    }
}; ?>

@section('title', 'Eventos del Sistema')

<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                        <svg class="w-6 h-6 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Eventos del Sistema
                    </h1>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="bg-white shadow rounded-lg mb-4">
            <div class="px-4 py-3">
                <form wire:submit.prevent>
                    <div class="flex flex-wrap items-end gap-2">
                        <!-- Buscar (expandible) -->
                        <div class="flex-1 min-w-[120px]">
                            <label for="search" class="block text-xs font-medium text-gray-700 mb-1">
                                <i class="fa fa-search text-gray-400 mr-1"></i> Buscar
                            </label>
                            <input type="text"
                                wire:model.live.debounce.500ms="search"
                                id="search"
                                class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500 text-xs"
                                placeholder="Palabra clave...">
                        </div>

                        <!-- Operación (compacto) -->
                        <div class="w-[110px]">
                            <label for="operacion" class="block text-xs font-medium text-gray-700 mb-1">
                                <i class="fa fa-cogs text-gray-400 mr-1"></i> Operación
                            </label>
                            <select id="operacion"
                                    wire:model.live="operacion"
                                    class="w-full cursor-pointer px-2 py-1 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500 text-xs">
                                <option value="">Todas</option>
                                <option value="INSERT">Insert</option>
                                <option value="UPDATE">Update</option>
                                <option value="DELETE">Delete</option>
                                <option value="LOGIN">Login</option>
                                <option value="LOGOUT">Logout</option>
                            </select>
                        </div>

                        <!-- Filtro de fechas (ancho fijo) -->
                        <div class="w-[200px]">
                            <label class="block text-xs font-medium text-gray-700 mb-1">
                                <i class="fa fa-calendar text-gray-400 mr-1"></i> Fechas
                            </label>
                            
                            <!-- Botón para abrir el modal de fechas -->
                            <button type="button" 
                                    wire:click="openDateModal"
                                    class="w-full cursor-pointer px-2 py-1 bg-white border border-gray-300 rounded shadow-sm flex items-center justify-between hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 text-xs">
                                <span class="truncate mr-1">
                                    {{ \Carbon\Carbon::parse($startDate)->format('d/M/Y') }} → {{ \Carbon\Carbon::parse($endDate)->format('d/M/Y') }}
                                </span>
                                <svg class="w-3 h-3 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                        </div>

                        <!-- Usuario (expandible) -->
                        <div class="flex-1 min-w-[120px]">
                            <label for="usuario" class="block text-xs font-medium text-gray-700 mb-1">
                                <i class="fa fa-user text-gray-400 mr-1"></i> Usuario
                            </label>
                            <input type="text"
                                wire:model.live.debounce.500ms="usuario"
                                id="usuario"
                                class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500 text-xs"
                                placeholder="Usuario...">
                        </div>

                        <!-- Rol (compacto) -->
                        <div class="w-[100px] ">
                            <label for="rol" class="block text-xs font-medium text-gray-700 mb-1">
                                <i class="fa fa-users text-gray-400 mr-1"></i> Rol
                            </label>
                            <select id="rol"
                                    wire:model.live="rol"
                                    class="w-full cursor-pointer px-2 py-1 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500 text-xs">
                                <option value="">Todos</option>
                                <option value="Superusuario">Superusuario</option>
                                <option value="Administrador">Administrador</option>
                                <option value="Aprendiz">Aprendiz</option>
                            </select>
                        </div>

                        <!-- Botones (sin expansión) -->
                        <div class="flex items-end">
                            <button type="button"
                                    wire:click="clearFilters"
                                    class="cursor-pointer px-2 py-1 bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium rounded transition duration-150 ease-in-out text-xs whitespace-nowrap">
                                <i class="fa fa-eraser mr-1"></i> Limpiar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal de selección de fechas -->
        @if($showDateModal)
        <div class="fixed inset-0 bg-black/20 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-4 border w-full max-w-4xl shadow-lg rounded-md bg-white">
                <div class="flex justify-between items-center pb-3 border-b">
                    <h3 class="text-xl font-semibold text-gray-900">Seleccionar rango de fechas</h3>
                </div>
                
                <div class="flex mt-4 h-96">
                    <!-- Sidebar con opciones predefinidas -->
                    <div class="w-1/4 pr-4 border-r">
                        <ul class="space-y-2">
                            <li>
                                <button wire:click="selectRange('all_time')" 
                                        class="w-full cursor-pointer text-left px-3 py-2 rounded {{ $selectedRangeType === 'all_time' ? 'bg-blue-100 text-blue-800' : 'hover:bg-gray-100' }}">
                                    Todo el tiempo
                                </button>
                            </li>
                            <li>
                                <button wire:click="selectRange('last_24_hours')" 
                                        class="w-full cursor-pointer text-left px-3 py-2 rounded {{ $selectedRangeType === 'last_24_hours' ? 'bg-blue-100 text-blue-800' : 'hover:bg-gray-100' }}">
                                    Últimas 24 horas
                                </button>
                            </li>
                            <li>
                                <button wire:click="selectRange('last_7_days')" 
                                        class="w-full cursor-pointer text-left px-3 py-2 rounded {{ $selectedRangeType === 'last_7_days' ? 'bg-blue-100 text-blue-800' : 'hover:bg-gray-100' }}">
                                    Últimos 7 días
                                </button>
                            </li>
                            <li>
                                <button wire:click="selectRange('last_30_days')" 
                                        class="w-full cursor-pointer text-left px-3 py-2 rounded {{ $selectedRangeType === 'last_30_days' ? 'bg-blue-100 text-blue-800' : 'hover:bg-gray-100' }}">
                                    Últimos 30 días
                                </button>
                            </li>
                            <li class="pt-4">
                                <div class="text-sm font-medium text-gray-500 px-3 py-1">Años</div>
                                <ul class="pl-2 mt-1 space-y-1 max-h-40 overflow-y-auto">
                                    @foreach($availableYears as $year)
                                    <li>
                                        <button wire:click="selectRange('year_{{ $year }}')" 
                                                class="w-full cursor-pointer text-left px-3 py-1 rounded {{ $selectedRangeType === 'year_' . $year ? 'bg-blue-100 text-blue-800' : 'hover:bg-gray-100' }}">
                                            {{ $year }}
                                        </button>
                                    </li>
                                    @endforeach
                                </ul>
                            </li>
                        </ul>
                    </div>
                    
                    <!-- Contenido principal con calendario -->
                    <div class="w-3/4 pl-4">
                        <!-- Controles de mes y año -->
                        <div class="flex justify-between items-center mb-4">
                            <button wire:click="changeCalendarMonth('prev')" class="p-2 rounded hover:bg-gray-100 cursor-pointer">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </button>
                            
                            <div class="flex items-center space-x-2">
                                <select wire:model="calendarMonth" class="px-2 py-1 border rounded cursor-pointer">
                                    @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}">{{ DateTime::createFromFormat('!m', $i)->format('F') }}</option>
                                    @endfor
                                </select>
                                <select wire:model="calendarYear" class="px-2 py-1 border rounded cursor-pointer">
                                    @foreach($availableYears as $year)
                                    <option value="{{ $year }}">{{ $year }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <button wire:click="changeCalendarMonth('next')" class="p-2 rounded hover:bg-gray-100 cursor-pointer">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        </div>
                        
                        <!-- Calendario -->
                        <div class="grid grid-cols-7 gap-1 text-center text-xs font-medium text-gray-500 mb-1">
                            <div>Lun</div>
                            <div>Mar</div>
                            <div>Mié</div>
                            <div>Jue</div>
                            <div>Vie</div>
                            <div>Sáb</div>
                            <div>Dom</div>
                        </div>
                        
                        <div class="grid grid-cols-7 gap-1">
                            @foreach($this->calendarDays as $day)
                            <div class="h-8 flex items-center justify-center">
                                @if($day['date'])
                                @php
                                    $isInRange = false;
                                    $isStart = false;
                                    $isEnd = false;
                                    
                                    if ($tempStartDate && $tempEndDate) {
                                        $isInRange = $day['date'] >= $tempStartDate && $day['date'] <= $tempEndDate;
                                        $isStart = $day['date'] == $tempStartDate;
                                        $isEnd = $day['date'] == $tempEndDate;
                                    } else if ($tempStartDate) {
                                        $isStart = $day['date'] == $tempStartDate;
                                    }
                                    
                                    $isToday = $day['date'] == now()->format('Y-m-d');
                                @endphp
                                <button wire:click="selectDate('{{ $day['date'] }}')"
                                        class="w-8 h-8 cursor-pointer rounded-full flex items-center justify-center text-sm
                                            {{ $isToday ? 'border border-blue-500' : '' }}
                                            {{ $isStart || $isEnd ? 'bg-blue-500 text-white' : '' }}
                                            {{ $isInRange && !$isStart && !$isEnd ? 'bg-blue-100' : '' }}
                                            {{ !$isInRange && !$isStart && !$isEnd ? 'hover:bg-gray-100' : '' }}">
                                    {{ $day['day'] }}
                                </button>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        
                        <!-- Rango seleccionado y botones de acción -->
                        <div class="mt-4 pt-4 border-t">
                            <div class="flex justify-between items-center">
                                <div class="text-sm">
                                    <span class="font-medium">Rango seleccionado:</span>
                                    <span>
                                        @if($tempStartDate && $tempEndDate)
                                            {{ \Carbon\Carbon::parse($tempStartDate)->format('d/M/Y') }} → {{ \Carbon\Carbon::parse($tempEndDate)->format('d/M/Y') }}
                                        @elseif($tempStartDate)
                                            {{ \Carbon\Carbon::parse($tempStartDate)->format('d/M/Y') }} → Seleccione fecha final
                                        @else
                                            Seleccione un rango de fechas
                                        @endif
                                    </span>
                                </div>
                                <div class="space-x-2">
                                    <button wire:click="cancelDateFilter" 
                                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 cursor-pointer">
                                        Cancelar
                                    </button>
                                    <button wire:click="applyDateFilter" 
                                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 cursor-pointer"
                                            {{ !$tempStartDate || !$tempEndDate ? 'disabled' : '' }}>
                                        Aplicar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Información del rango seleccionado -->
        @if($startDate && $endDate)
        <div class="mb-3 bg-blue-50 border border-blue-200 rounded p-2">
            <div class="flex items-center">
                <svg class="w-4 h-4 text-blue-600 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span class="text-blue-800 text-xs font-medium">
                    Mostrando eventos desde {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} hasta {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
                </span>
            </div>
        </div>
        @endif

        <!-- Tabla de eventos -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-4 py-2 bg-blue-600 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-medium text-white flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                        </svg>
                        Registro de Eventos
                    </h3>
                    <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2 py-0.5 rounded">
                        {{ $this->logs()->total() }} eventos
                    </span>
                </div>
            </div>

            @if($this->logs()->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-xs">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Operación</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">IP</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Detalle</th>
                                <th class="px-3 py-2 text-center font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($this->logs() as $log)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 whitespace-nowrap text-gray-500">
                                    {{ $log->fecAud->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-gray-900">
                                    {{ $log->usuAud ?? 'Sistema' }}
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-gray-500">
                                    {{ $log->rolAud }}
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap">
                                    <span class="px-1.5 py-0.5 text-xs rounded-full 
                                        @if(in_array($log->opeAud, ['INSERT', 'LOGIN'])) bg-green-100 text-green-800
                                        @elseif(in_array($log->opeAud, ['UPDATE'])) bg-blue-100 text-blue-800
                                        @elseif(in_array($log->opeAud, ['DELETE'])) bg-red-100 text-red-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ $log->opeAud }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-gray-500">
                                    {{ $log->ipAud }}
                                </td>
                                <td class="px-3 py-2 text-gray-500 max-w-xs truncate" title="{{ $log->desAud }}">
                                    {{ $log->desAud }}
                                </td>
                                <!-- Nueva columna de Acción -->
                                <td class="px-3 py-2 whitespace-nowrap text-center font-medium">
                                    <div class="flex justify-center space-x-1">
                                        <!-- Icono de ojo para ver detalles - CAMBIO: usar $log->id en lugar de $log->idAud -->
                                        <button wire:click="showLogDetails('{{ $log->idAud }}')"
                                                class="cursor-pointer text-blue-600 hover:text-blue-900 transition-colors duration-200"
                                                title="Ver detalles del evento">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </button>
                                        
                                        <!-- Icono de perfil para ver usuario (solo si hay usuario asociado) -->
                                        @if($log->idUsuAud && $log->usuario)
                                        <a href="{{ route('settings.manage-users.show', $log->usuario->id) }}"
                                        wire:navigate
                                        class="text-green-600 hover:text-green-900 transition-colors duration-200"
                                        title="Ver perfil de {{ $log->usuario->nomUsu }} {{ $log->usuario->apeUsu }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                        </a>
                                        @else
                                        <span class="text-gray-400 cursor-not-allowed" title="No hay usuario asociado">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                        </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No hay eventos registrados</h3>
                    <p class="mt-1 text-xs text-gray-500">No se encontraron eventos que coincidan con los criterios de búsqueda.</p>
                </div>
            @endif

            @if($this->logs()->hasPages())
                <div class="bg-white px-3 py-2 border-t border-gray-200 sm:px-4">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 flex justify-between sm:hidden">
                            {{ $this->logs()->links() }}
                        </div>
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p class="text-xs text-gray-700">
                                    Mostrando <span class="font-medium">{{ $this->logs()->firstItem() }}</span> a 
                                    <span class="font-medium">{{ $this->logs()->lastItem() }}</span> de 
                                    <span class="font-medium">{{ $this->logs()->total() }}</span> eventos
                                </p>
                            </div>
                            <div>
                                {{ $this->logs()->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

<!-- Modal de Detalles del Evento -->
@if($showDetailModal && $selectedLog)
<div class="fixed inset-0 bg-black/20 bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center p-4 border-b">
            <h3 class="text-base font-semibold text-gray-900">Detalles del Evento</h3>
            <button wire:click="closeDetailModal" class="cursor-pointer text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <div class="p-4 space-y-3">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-700">Fecha y Hora</label>
                    <p class="mt-1 text-xs text-gray-900">{{ $selectedLog->fecAud->format('d/m/Y H:i:s') }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700">Operación</label>
                    <p class="mt-1 text-xs text-gray-900">{{ $selectedLog->opeAud }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700">Usuario</label>
                    <p class="mt-1 text-xs text-gray-900">{{ $selectedLog->usuAud ?? 'Sistema' }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700">Rol</label>
                    <p class="mt-1 text-xs text-gray-900">{{ $selectedLog->rolAud }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700">Tabla Afectada</label>
                    <p class="mt-1 text-xs text-gray-900">{{ $selectedLog->tablaAud }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700">IP del Cliente</label>
                    <p class="mt-1 text-xs text-gray-900">{{ $selectedLog->ipAud ?? 'N/A' }}</p>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-700">Registro ID</label>
                    <p class="mt-1 text-xs text-gray-900">{{ $selectedLog->regAud }}</p>
                </div>
            </div>
            
            <div>
                <label class="block text-xs font-medium text-gray-700">Descripción Completa</label>
                <div class="mt-1 p-2 bg-gray-50 rounded">
                    <pre class="text-xs text-gray-900 whitespace-pre-wrap">{{ $selectedLog->desAud }}</pre>
                </div>
            </div>
            
            <!-- Análisis de la descripción para mostrar información estructurada -->
            @php
                $description = $selectedLog->desAud;
                $module = '';
                $action = '';
                $details = '';
                
                // Extraer información estructurada de la descripción
                if (str_contains($description, 'módulo')) {
                    $parts = explode('.', $description);
                    if (count($parts) >= 2) {
                        $modulePart = $parts[0];
                        $detailsPart = implode('.', array_slice($parts, 1));
                        
                        // Extraer módulo
                        if (preg_match('/módulo (.*)$/', $modulePart, $matches)) {
                            $module = $matches[1];
                        }
                        
                        // Determinar acción
                        if (str_contains($modulePart, 'Creación')) {
                            $action = 'Creación';
                        } elseif (str_contains($modulePart, 'Actualización')) {
                            $action = 'Actualización';
                        } elseif (str_contains($modulePart, 'Eliminación')) {
                            $action = 'Eliminación';
                        }
                        
                        $details = trim($detailsPart);
                    }
                }
            @endphp
            
            @if($module && $action)
            <div class="pt-3 border-t">
                <label class="block text-xs font-medium text-gray-700">Resumen del Evento</label>
                <div class="mt-1 grid grid-cols-1 gap-2">
                    <p class="text-xs"><span class="font-medium">Módulo:</span> {{ $module }}</p>
                    <p class="text-xs"><span class="font-medium">Acción:</span> {{ $action }}</p>
                    <p class="text-xs"><span class="font-medium">Detalles:</span> {{ $details }}</p>
                </div>
            </div>
            @endif
            
            @if($selectedLog->usuario)
            <div class="pt-3 border-t">
                <label class="block text-xs font-medium text-gray-700">Información del encargado</label>
                <div class="mt-1 grid grid-cols-1 md:grid-cols-2 gap-2">
                    <p class="text-xs"><span class="font-medium">Nombre:</span> {{ $selectedLog->usuario->nomUsu }} {{ $selectedLog->usuario->apeUsu }}</p>
                    <p class="text-xs"><span class="font-medium">Email:</span> {{ $selectedLog->usuario->email }}</p>
                    <p class="text-xs"><span class="font-medium">Documento:</span> {{ $selectedLog->usuario->tipDocUsu }} {{ $selectedLog->usuario->numDocUsu }}</p>
                    <p class="text-xs"><span class="font-medium">Rol:</span> {{ $selectedLog->usuario->rol->nomRol ?? 'Sin rol' }}</p>
                    <p class="text-xs"><span class="font-medium">Estado:</span> 
                        <span class="px-1.5 py-0.5 text-xs font-semibold rounded-full {{ $selectedLog->usuario->estUsu === 'activo' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $selectedLog->usuario->estUsu }}
                        </span>
                    </p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endif
    </div>
    <style>
    .grid.grid-cols-7 {
        grid-template-columns: repeat(7, minmax(0, 1fr));
    }
    
    /* Estilos para hacer la tabla más compacta */
    table.text-xs {
        font-size: 0.75rem;
        line-height: 1rem;
    }
    
    .px-3.py-2 {
        padding-left: 0.75rem;
        padding-right: 0.75rem;
        padding-top: 0.5rem;
        padding-bottom: 0.5rem;
    }
    
    /* Ajustar paginación para mostrar 10 registros sin scroll */
    .min-h-screen {
        min-height: calc(100vh - 2rem);
    }
</style>
</div>