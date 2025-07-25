<?php
use App\Models\ProduccionAnimal;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component {
    public ProduccionAnimal $produccion;
    public $historial;

    public function mount(ProduccionAnimal $produccion)
    {
        $this->produccion = $produccion;
        $this->historial = ProduccionAnimal::where('idAniPro', $produccion->idAniPro)
            ->where('idProAni', '!=', $produccion->idProAni)
            ->orderBy('fecProAni', 'desc')
            ->limit(5)
            ->get();
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
}; ?>

<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header bg-success text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-clipboard-list"></i> Detalles de Producción #{{ $produccion->idProAni }}
                </h5>
                <div class="btn-group">
                    <a href="{{ route('pecuario.produccion.edit', $produccion->idProAni) }}" wire:navigate
                       class="btn btn-sm btn-warning">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <button wire:click="delete" class="btn btn-sm btn-danger ms-2" 
                            onclick="return confirm('¿Eliminar este registro?')">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body">
            <!-- Datos principales -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tr>
                                <th width="40%">Animal</th>
                                <td>
                                    @if($produccion->animal)
                                        {{ $produccion->animal->nomAni }} 
                                        <small class="text-muted">({{ $produccion->animal->espAni }})</small>
                                    @else
                                        N/A
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Tipo de Producción</th>
                                <td>
                                    <span class="badge bg-success">
                                        {{ ucfirst($produccion->tipProAni) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Fecha</th>
                                <td>{{ $produccion->fecProAni->format('d/m/Y') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <tr>
                                <th width="40%">Cantidad</th>
                                <td>
                                    {{ $produccion->canProAni }} 
                                    {{ $produccion->uniProAni ?? (
                                        $produccion->tipProAni == 'leche' ? 'litros' : 'kg'
                                    ) }}
                                </td>
                            </tr>
                            <tr>
                                <th>Registrado por</th>
                                <td>{{ $produccion->user->name ?? 'Sistema' }}</td>
                            </tr>
                            <tr>
                                <th>Última actualización</th>
                                <td>{{ $produccion->updated_at->diffForHumans() }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Observaciones -->
            @if($produccion->obsProAni)
            <div class="mb-4">
                <h5 class="border-bottom pb-2">
                    <i class="fas fa-comment-dots"></i> Observaciones
                </h5>
                <div class="card bg-success bg-opacity-10">
                    <div class="card-body">
                        {{ $produccion->obsProAni }}
                    </div>
                </div>
            </div>
            @endif

            <!-- Historial relacionado -->
            <div class="mt-4">
                <h5 class="border-bottom pb-2">
                    <i class="fas fa-history"></i> Historial Reciente
                </h5>
                <div class="list-group">
                    @forelse($historial as $registro)
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between">
                            <strong>{{ $registro->fecProAni->format('d/m/Y') }}</strong>
                            <span class="badge bg-success">
                                {{ $registro->canProAni }} 
                                {{ $registro->uniProAni ?? (
                                    $registro->tipProAni == 'leche' ? 'L' : 'kg'
                                ) }}
                            </span>
                        </div>
                        <small class="text-muted">
                            Actualizado: {{ $registro->updated_at->diffForHumans() }}
                        </small>
                    </div>
                    @empty
                    <div class="list-group-item text-center text-muted">
                        No hay registros históricos
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>