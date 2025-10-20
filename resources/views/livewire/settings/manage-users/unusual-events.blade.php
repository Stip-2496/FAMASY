<?php
use App\Models\Auditoria;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.auth')] class extends Component {
    use WithPagination;
    
    public $search = '';
    public $perPage = 10;
    
    // Propiedades para filtros específicos de eventos anómalos
    public $severidad = '';
    public $operacion = '';
    public $usuario = '';
    public $ipAddress = '';
    
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
        // Obtener fechas del primer y último registro de eventos anómalos
        $firstRecord = Auditoria::where(function($query) {
            $query->whereIn('opeAud', ['LOGIN_FAILED', 'UNAUTHORIZED_ACCESS'])
                  ->orWhere('desAud', 'like', '%intento fallido%')
                  ->orWhere('desAud', 'like', '%acceso no autorizado%');
        })->orderBy('fecAud', 'asc')->first();
        
        $lastRecord = Auditoria::where(function($query) {
            $query->whereIn('opeAud', ['LOGIN_FAILED', 'UNAUTHORIZED_ACCESS'])
                  ->orWhere('desAud', 'like', '%intento fallido%')
                  ->orWhere('desAud', 'like', '%acceso no autorizado%');
        })->orderBy('fecAud', 'desc')->first();
        
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
            ->where(function($query) {
                $query->whereIn('opeAud', ['LOGIN_FAILED', 'UNAUTHORIZED_ACCESS'])
                      ->orWhere('desAud', 'like', '%intento fallido%')
                      ->orWhere('desAud', 'like', '%acceso no autorizado%');
            })
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('opeAud', 'like', '%'.$this->search.'%')
                      ->orWhere('desAud', 'like', '%'.$this->search.'%')
                      ->orWhere('usuAud', 'like', '%'.$this->search.'%')
                      ->orWhere('ipAud', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->severidad, function($query) {
                if ($this->severidad === 'critica') {
                    $query->where(function($q) {
                        $q->where('desAud', 'like', '%crítico%')
                          ->orWhere('opeAud', 'UNAUTHORIZED_ACCESS');
                    });
                } elseif ($this->severidad === 'alta') {
                    $query->where('desAud', 'like', '%peligroso%');
                } elseif ($this->severidad === 'media') {
                    $query->where('desAud', 'like', '%advertencia%');
                } elseif ($this->severidad === 'baja') {
                    $query->where('opeAud', 'LOGIN_FAILED');
                }
            })
            ->when($this->operacion, function($query) {
                $query->where('opeAud', $this->operacion);
            })
            ->when($this->usuario, function($query) {
                $query->where('usuAud', 'like', '%'.$this->usuario.'%');
            })
            ->when($this->ipAddress, function($query) {
                $query->where('ipAud', 'like', '%'.$this->ipAddress.'%');
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
        $this->selectedLog = Auditoria::with('usuario')->find($logId);
        
        if ($this->selectedLog) {
            $this->showDetailModal = true;
        } else {
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
        $this->severidad = '';
        $this->operacion = '';
        $this->usuario = '';
        $this->ipAddress = '';
        
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

    // Método para obtener la severidad de un evento
    public function getSeveridad($log)
    {
        if (in_array($log->opeAud, ['UNAUTHORIZED_ACCESS']) || str_contains($log->desAud, 'crítico')) {
            return 'critica';
        } elseif (str_contains($log->desAud, 'peligroso')) {
            return 'alta';
        } elseif (str_contains($log->desAud, 'advertencia')) {
            return 'media';
        } else {
            return 'baja';
        }
    }
}; ?>

@section('title', 'Eventos Anómalos')

<div class="min-h-screen py-4">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-4">
            <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-xl border border-white/20 p-4 relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-red-500/5 to-red-600/5"></div>
                <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center space-x-2 mb-3 sm:mb-0">
                        <div class="p-2 bg-gradient-to-br from-red-500 to-red-600 rounded-xl shadow-lg transform rotate-3 hover:rotate-0 transition-transform duration-300">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-xl font-black bg-gradient-to-r from-gray-900 via-gray-800 to-red-800 bg-clip-text text-transparent leading-tight">
                                Eventos Anómalos
                            </h1>
                            <p class="text-gray-600 text-xs">Monitoreo de intentos fallidos y accesos no autorizados</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 p-3 mb-3">
            <form wire:submit.prevent>
                <div class="flex flex-wrap items-end gap-2">
                    <!-- Buscar -->
                    <div class="flex-1 min-w-[120px] relative">
                        <svg class="w-3.5 h-3.5 text-gray-400 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <input type="text"
                               wire:model.live.debounce.500ms="search"
                               id="search"
                               class="w-full pl-8 pr-3 py-1.5 text-xs bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                               placeholder="Palabra clave...">
                    </div>

                    <!-- Severidad -->
                    <div class="w-[110px]">
                        <label for="severidad" class="block text-xs font-medium text-gray-700 mb-1">
                            <svg class="w-2.5 h-2.5 text-gray-400 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            Severidad
                        </label>
                        <select id="severidad"
                                wire:model.live="severidad"
                                class="w-full cursor-pointer px-2 py-1.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 text-xs">
                            <option value="">Todas</option>
                            <option value="critica">Crítica</option>
                            <option value="alta">Alta</option>
                            <option value="media">Media</option>
                            <option value="baja">Baja</option>
                        </select>
                    </div>

                    <!-- Operación -->
                    <div class="w-[130px]">
                        <label for="operacion" class="block text-xs font-medium text-gray-700 mb-1">
                            <svg class="w-2.5 h-2.5 text-gray-400 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37a1.724 1.724 0 002.572-1.065z"></path>
                            </svg>
                            Operación
                        </label>
                        <select id="operacion"
                                wire:model.live="operacion"
                                class="w-full cursor-pointer px-2 py-1.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 text-xs">
                            <option value="">Todas</option>
                            <option value="LOGIN_FAILED">Login Fallido</option>
                            <option value="UNAUTHORIZED_ACCESS">Acceso No Autorizado</option>
                        </select>
                    </div>

                    <!-- Filtro de fechas -->
                    <div class="w-[200px] relative">
                        <label class="block text-xs font-medium text-gray-700 mb-1">
                            <svg class="w-2.5 h-2.5 text-gray-400 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Fechas
                        </label>
                        <button type="button"
                                wire:click="openDateModal"
                                class="w-full cursor-pointer px-2 py-1.5 bg-white border border-gray-300 rounded-lg shadow-sm flex items-center justify-between hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500 text-xs">
                            <span class="truncate mr-1">
                                @if($startDate && $endDate)
                                    {{ \Carbon\Carbon::parse($startDate)->format('d/M/Y') }} → {{ \Carbon\Carbon::parse($endDate)->format('d/M/Y') }}
                                @else
                                    Seleccionar fechas
                                @endif
                            </span>
                            <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Usuario -->
                    <div class="flex-1 min-w-[120px] relative">
                        <svg class="w-3.5 h-3.5 text-gray-400 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <input type="text"
                               wire:model.live.debounce.500ms="usuario"
                               id="usuario"
                               class="w-full pl-8 pr-3 py-1.5 text-xs bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                               placeholder="Usuario...">
                    </div>

                    <!-- IP -->
                    <div class="w-[120px] relative">
                        <svg class="w-3.5 h-3.5 text-gray-400 absolute left-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012-2v-1a2 2 0 012-2h1.945M12 3a9 9 0 100 18 9 9 0 000-18z"></path>
                        </svg>
                        <input type="text"
                               wire:model.live.debounce.500ms="ipAddress"
                               id="ipAddress"
                               class="w-full pl-8 pr-3 py-1.5 text-xs bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                               placeholder="IP...">
                    </div>

                    <!-- Botones -->
                    <div class="flex items-end">
                        <button type="button"
                                wire:click="clearFilters"
                                class="cursor-pointer inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white font-medium rounded-lg shadow hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            <span class="text-xs">Limpiar</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Modal de selección de fechas -->
        @if($showDateModal)
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-xl max-w-4xl w-full p-5 transform transition-all duration-300">
                <div class="flex justify-between items-center pb-3 border-b">
                    <h3 class="text-base font-bold text-gray-900">Seleccionar rango de fechas</h3>
                    <button wire:click="$set('showDateModal', false)" class="cursor-pointer text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <div class="flex mt-4 h-96">
                    <!-- Sidebar con opciones predefinidas -->
                    <div class="w-1/4 pr-4 border-r">
                        <ul class="space-y-2">
                            <li>
                                <button wire:click="selectRange('all_time')"
                                        class="w-full cursor-pointer text-left px-3 py-2 rounded-lg {{ $selectedRangeType === 'all_time' ? 'bg-red-100 text-red-800' : 'hover:bg-gray-50' }} text-xs">
                                    Todo el tiempo
                                </button>
                            </li>
                            <li>
                                <button wire:click="selectRange('last_24_hours')"
                                        class="w-full cursor-pointer text-left px-3 py-2 rounded-lg {{ $selectedRangeType === 'last_24_hours' ? 'bg-red-100 text-red-800' : 'hover:bg-gray-50' }} text-xs">
                                    Últimas 24 horas
                                </button>
                            </li>
                            <li>
                                <button wire:click="selectRange('last_7_days')"
                                        class="w-full cursor-pointer text-left px-3 py-2 rounded-lg {{ $selectedRangeType === 'last_7_days' ? 'bg-red-100 text-red-800' : 'hover:bg-gray-50' }} text-xs">
                                    Últimos 7 días
                                </button>
                            </li>
                            <li>
                                <button wire:click="selectRange('last_30_days')"
                                        class="w-full cursor-pointer text-left px-3 py-2 rounded-lg {{ $selectedRangeType === 'last_30_days' ? 'bg-red-100 text-red-800' : 'hover:bg-gray-50' }} text-xs">
                                    Últimos 30 días
                                </button>
                            </li>
                            @if($availableYears)
                            <li class="pt-4">
                                <div class="text-xs font-medium text-gray-500 px-3 py-1">Años</div>
                                <ul class="pl-2 mt-1 space-y-1 max-h-40 overflow-y-auto">
                                    @foreach($availableYears as $year)
                                    <li>
                                        <button wire:click="selectRange('year_{{ $year }}')"
                                                class="w-full cursor-pointer text-left px-3 py-1 rounded-lg {{ $selectedRangeType === 'year_' . $year ? 'bg-red-100 text-red-800' : 'hover:bg-gray-50' }} text-xs">
                                            {{ $year }}
                                        </button>
                                    </li>
                                    @endforeach
                                </ul>
                            </li>
                            @endif
                        </ul>
                    </div>
                    
                    <!-- Contenido principal con calendario -->
                    <div class="w-3/4 pl-4">
                        <!-- Controles de mes y año -->
                        <div class="flex justify-between items-center mb-4">
                            <button wire:click="changeCalendarMonth('prev')" class="p-2 rounded-lg hover:bg-gray-50 cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </button>
                            
                            @if($availableYears)
                            <div class="flex items-center space-x-2">
                                <select wire:model="calendarMonth" class="px-2 py-1.5 border border-gray-300 rounded-lg cursor-pointer text-xs">
                                    @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}">{{ DateTime::createFromFormat('!m', $i)->format('F') }}</option>
                                    @endfor
                                </select>
                                <select wire:model="calendarYear" class="px-2 py-1.5 border border-gray-300 rounded-lg cursor-pointer text-xs">
                                    @foreach($availableYears as $year)
                                    <option value="{{ $year }}">{{ $year }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                            
                            <button wire:click="changeCalendarMonth('next')" class="p-2 rounded-lg hover:bg-gray-50 cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        </div>
                        
                        @if($this->calendarDays)
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
                                        class="w-8 h-8 cursor-pointer rounded-full flex items-center justify-center text-xs
                                            {{ $isToday ? 'border border-red-500' : '' }}
                                            {{ $isStart || $isEnd ? 'bg-red-500 text-white' : '' }}
                                            {{ $isInRange && !$isStart && !$isEnd ? 'bg-red-100' : '' }}
                                            {{ !$isInRange && !$isStart && !$isEnd ? 'hover:bg-gray-50' : '' }}">
                                    {{ $day['day'] }}
                                </button>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        @endif
                        
                        <!-- Rango seleccionado y botones de acción -->
                        <div class="mt-4 pt-4 border-t">
                            <div class="flex justify-between items-center">
                                <div class="text-xs">
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
                                            class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg shadow hover:shadow transition-all duration-200 text-xs">
                                        Cancelar
                                    </button>
                                    <button wire:click="applyDateFilter"
                                            class="px-3 py-1.5 bg-gradient-to-r from-red-600 to-red-800 hover:from-red-700 hover:to-red-900 text-white font-medium rounded-lg shadow hover:shadow transform hover:-translate-y-0.5 transition-all duration-200 text-xs"
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
        <div class="mb-2 p-2 bg-red-50 border border-red-200 rounded-lg shadow-sm">
            <div class="flex items-center">
                <svg class="h-3 w-3 text-red-500 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <p class="text-xs text-red-800 font-medium">
                    Mostrando eventos anómalos desde {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} hasta {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
                </p>
            </div>
        </div>
        @endif

        <!-- Tabla de eventos anómalos -->
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 overflow-hidden">
            <div class="px-2 py-1.5 bg-black border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-medium text-white flex items-center">
                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                        Eventos Anómalos Detectados
                    </h3>
                    <span class="bg-red-100 text-red-800 text-xs font-medium px-1.5 py-0.5 rounded">
                        {{ $this->logs()->total() }} eventos
                    </span>
                </div>
            </div>

            @if($this->logs()->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-xs">
                        <thead class="bg-black">
                            <tr>
                                <th class="px-2 py-1.5 text-left font-medium text-white uppercase tracking-wider whitespace-nowrap">Fecha</th>
                                <th class="px-2 py-1.5 text-left font-medium text-white uppercase tracking-wider whitespace-nowrap">Usuario</th>
                                <th class="px-2 py-1.5 text-left font-medium text-white uppercase tracking-wider whitespace-nowrap">Severidad</th>
                                <th class="px-2 py-1.5 text-left font-medium text-white uppercase tracking-wider whitespace-nowrap">Operación</th>
                                <th class="px-2 py-1.5 text-left font-medium text-white uppercase tracking-wider whitespace-nowrap">IP</th>
                                <th class="px-2 py-1.5 text-left font-medium text-white uppercase tracking-wider whitespace-nowrap">Detalle</th>
                                <th class="px-2 py-1.5 text-center font-medium text-white uppercase tracking-wider whitespace-nowrap">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($this->logs() as $log)
                            <tr class="hover:bg-red-50/50 transition-colors duration-200">
                                <td class="px-2 py-1.5 whitespace-nowrap text-gray-700">
                                    {{ $log->fecAud->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-2 py-1.5 whitespace-nowrap">
                                    <div class="flex items-center gap-1">
                                        <div class="w-5 h-5 bg-red-100 rounded flex items-center justify-center flex-shrink-0">
                                            <svg class="w-2.5 h-2.5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-xs font-medium text-gray-900">{{ $log->usuAud ?? 'Sistema' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-2 py-1.5 whitespace-nowrap">
                                    @php
                                        $severidad = $this->getSeveridad($log);
                                        $colorClasses = [
                                            'baja' => 'bg-green-100 text-green-700',
                                            'media' => 'bg-yellow-100 text-yellow-700',
                                            'alta' => 'bg-orange-100 text-orange-700',
                                            'critica' => 'bg-red-100 text-red-700'
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-xs font-medium {{ $colorClasses[$severidad] ?? 'bg-gray-100 text-gray-600' }}">
                                        {{ ucfirst($severidad) }}
                                    </span>
                                </td>
                                <td class="px-2 py-1.5 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-xs font-medium
                                        @if($log->opeAud === 'LOGIN_FAILED') bg-yellow-100 text-yellow-700
                                        @elseif($log->opeAud === 'UNAUTHORIZED_ACCESS') bg-red-100 text-red-700
                                        @else bg-gray-100 text-gray-600 @endif">
                                        {{ str_replace('_', ' ', $log->opeAud) }}
                                    </span>
                                </td>
                                <td class="px-2 py-1.5 whitespace-nowrap text-gray-700">
                                    {{ $log->ipAud }}
                                </td>
                                <td class="px-2 py-1.5 text-gray-700 max-w-xs truncate" title="{{ $log->desAud }}">
                                    {{ $log->desAud }}
                                </td>
                                <td class="px-2 py-1.5 whitespace-nowrap text-center">
                                    <div class="flex justify-center space-x-1">
                                        <button wire:click="showLogDetails('{{ $log->idAud }}')"
                                                class="cursor-pointer bg-red-100 hover:bg-red-200 text-red-700 text-xs font-medium py-1 px-1.5 rounded transition duration-200">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </button>
                                        @if($log->idUsuAud && $log->usuario)
                                        <a href="{{ route('settings.manage-users.show', $log->usuario->id) }}"
                                           wire:navigate
                                           class="bg-green-100 hover:bg-green-200 text-green-700 text-xs font-medium py-1 px-1.5 rounded transition duration-200"
                                           title="Ver perfil de {{ $log->usuario->nomUsu }} {{ $log->usuario->apeUsu }}">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                        </a>
                                        @else
                                        <span class="bg-gray-100 text-gray-400 cursor-not-allowed text-xs font-medium py-1 px-1.5 rounded" title="No hay usuario asociado">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                <div class="text-center py-4 px-4">
                    <div class="w-8 h-8 bg-gray-100 rounded-full mx-auto mb-2 flex items-center justify-center">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <h3 class="text-sm font-medium text-gray-900 mb-1">No hay eventos anómalos registrados</h3>
                    <p class="text-xs text-gray-600">No se encontraron eventos anómalos que coincidan con los criterios de búsqueda.</p>
                </div>
            @endif

            @if($this->logs()->hasPages())
                <div class="bg-white px-2 py-1.5 border-t border-gray-200 sm:px-3">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 flex justify-between sm:hidden">
                            {{ $this->logs()->links() }}
                        </div>
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p class="text-xs text-gray-700">
                                    Mostrando <span class="font-medium">{{ $this->logs()->firstItem() }}</span> a 
                                    <span class="font-medium">{{ $this->logs()->lastItem() }}</span> de 
                                    <span class="font-medium">{{ $this->logs()->total() }}</span> eventos anómalos
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

        <!-- Modal de Detalles del Evento Anómalo -->
        @if($showDetailModal && $selectedLog)
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto p-5 transform transition-all duration-300">
                <div class="flex justify-between items-center pb-3 border-b">
                    <h3 class="text-base font-bold text-gray-900 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                        Detalles del Evento Anómalo
                    </h3>
                    <button wire:click="closeDetailModal" class="cursor-pointer text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                <div class="p-4 space-y-3">
                    <!-- Alerta de severidad -->
                    @php
                        $severidad = $this->getSeveridad($selectedLog);
                        $alertClasses = [
                            'baja' => 'bg-green-50 border-green-200 text-green-800',
                            'media' => 'bg-yellow-50 border-yellow-200 text-yellow-800',
                            'alta' => 'bg-orange-50 border-orange-200 text-orange-800',
                            'critica' => 'bg-red-50 border-red-200 text-red-800'
                        ];
                    @endphp
                    <div class="p-3 rounded border {{ $alertClasses[$severidad] ?? 'bg-gray-50 border-gray-200 text-gray-800' }}">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            <span class="font-medium text-xs">Nivel de severidad: {{ ucfirst($severidad) }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Fecha y Hora</label>
                            <p class="mt-1 text-xs text-gray-900">{{ $selectedLog->fecAud->format('d/m/Y H:i:s') }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Tipo de Evento</label>
                            <p class="mt-1 text-xs text-gray-900">{{ str_replace('_', ' ', $selectedLog->opeAud) }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Usuario Afectado</label>
                            <p class="mt-1 text-xs text-gray-900">{{ $selectedLog->usuAud ?? 'Sistema' }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Dirección IP</label>
                            <p class="mt-1 text-xs text-gray-900 font-mono">{{ $selectedLog->ipAud ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Tabla Afectada</label>
                            <p class="mt-1 text-xs text-gray-900">{{ $selectedLog->tablaAud ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Rol del Usuario</label>
                            <p class="mt-1 text-xs text-gray-900">{{ $selectedLog->rolAud ?? 'N/A' }}</p>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Descripción del Evento Anómalo</label>
                        <div class="mt-1 p-2 bg-red-50 border border-red-200 rounded-lg">
                            <pre class="text-xs text-gray-900 whitespace-pre-wrap">{{ $selectedLog->desAud }}</pre>
                        </div>
                    </div>
                    
                    @if($selectedLog->usuario)
                    <div class="pt-3 border-t">
                        <label class="block text-xs font-medium text-gray-700">Información del Usuario Asociado</label>
                        <div class="mt-1 grid grid-cols-1 md:grid-cols-2 gap-2 p-2 bg-gray-50 rounded-lg">
                            <p class="text-xs"><span class="font-medium">Nombre:</span> {{ $selectedLog->usuario->nomUsu }} {{ $selectedLog->usuario->apeUsu }}</p>
                            <p class="text-xs"><span class="font-medium">Email:</span> {{ $selectedLog->usuario->email }}</p>
                            <p class="text-xs"><span class="font-medium">Documento:</span> {{ $selectedLog->usuario->tipDocUsu }} {{ $selectedLog->usuario->numDocUsu }}</p>
                            <p class="text-xs"><span class="font-medium">Rol:</span> {{ $selectedLog->usuario->rol->nomRol ?? 'Sin rol' }}</p>
                            <p class="text-xs"><span class="font-medium">Estado:</span> 
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-xs font-medium {{ $selectedLog->usuario->estUsu === 'activo' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $selectedLog->usuario->estUsu }}
                                </span>
                            </p>
                        </div>
                    </div>
                    @endif

                    <!-- Recomendaciones de seguridad -->
                    <div class="pt-3 border-t">
                        <label class="block text-xs font-medium text-gray-700 mb-2">Recomendaciones de Seguridad</label>
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                            <ul class="text-xs text-blue-800 space-y-1">
                                @if($selectedLog->opeAud === 'LOGIN_FAILED')
                                <li>• Verificar si es un intento de ataque de fuerza bruta</li>
                                <li>• Considerar bloquear temporalmente la IP si hay múltiples intentos</li>
                                <li>• Revisar la fortaleza de las credenciales del usuario</li>
                                @elseif($selectedLog->opeAud === 'UNAUTHORIZED_ACCESS')
                                <li>• Revisar inmediatamente los permisos del usuario</li>
                                <li>• Verificar si la cuenta ha sido comprometida</li>
                                <li>• Considerar suspender temporalmente la cuenta</li>
                                @endif
                                <li>• Monitorear actividad adicional desde esta IP</li>
                                <li>• Documentar el incidente para análisis posterior</li>
                            </ul>
                        </div>
                    </div>
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

            /* Estilos específicos para eventos anómalos */
            .anomalous-event-row:hover {
                background-color: #fef2f2;
            }
        </style>
    </div>