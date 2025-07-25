<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\DatabaseBackup;

new #[Layout('layouts.auth')] class extends Component {
    use WithFileUploads;

    public bool $dbExists = false;
    public bool $showExportModal = false;
    public bool $showExportConfirmModal = false;
    public bool $showDeleteModal = false;
    public bool $showDeleteConfirmModal = false;
    public string $status = '';
    public string $observation = '';
    public ?TemporaryUploadedFile $import_file = null;

    public function mount()
    {
        $this->checkDatabase();
    }

    public function checkDatabase(): void
    {
        $this->dbExists = DB::select("SHOW DATABASES LIKE 'famasy'") ? true : false;
    }

    protected function formatFileSize($bytes): string
    {
        if ($bytes === 0) return '0 bytes';
        $units = ['bytes', 'KB', 'MB', 'GB', 'TB'];
        $index = floor(log($bytes, 1024));
        $size = round($bytes / pow(1024, $index), 2);
        return $size . ' ' . $units[$index];
    }

public function export(): void
{
    $timestamp = now()->format('Ymd_His');
    $filename = "famasy_{$timestamp}.sql";
    $fullPath = storage_path("app/backups/{$filename}");

    // Asegurar que el directorio existe
    if (!file_exists(dirname($fullPath))) {
        mkdir(dirname($fullPath), 0777, true);
    }

    // Verificar que mysqldump.exe existe
    $mysqldumpPath = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';
    if (!file_exists($mysqldumpPath)) {
        $this->status = "❌ Error: No se encontró mysqldump.exe";
        return;
    }

    // Ejecutar el comando con manejo de errores
    $command = "\"$mysqldumpPath\" --user=root famasy > \"$fullPath\" 2>&1";
    exec($command, $output, $returnVar);

    if ($returnVar !== 0) {
        $this->status = "❌ Error en la exportación: " . implode("\n", $output);
        return;
    }

    // Esperar hasta que el archivo tenga contenido
    $attempts = 0;
    $fileSize = 0;
    while ($attempts < 10) {
        if (file_exists($fullPath)) {
            $fileSize = filesize($fullPath);
            if ($fileSize > 0) break;
        }
        $attempts++;
        usleep(500000); // 0.5 segundos
    }

    $formattedSize = $this->formatFileSize($fileSize);

    // Registrar en la base de datos
    DatabaseBackup::create([
        'nomBac' => 'famasy',
        'verBac' => $timestamp,
        'arcBac' => $filename,
        'obsBac' => $this->observation,
        'tamBac' => $formattedSize,
        'tipBac' => 'export',
        'idUsuBac' => Auth::id()
    ]);

    $this->status = "✅ Exportación completada: " . $this->observation;
    $this->dispatch('download-export', ['filename' => $filename]);
    $this->reset(['observation', 'showExportModal', 'showExportConfirmModal']);
    $this->checkDatabase();
}

    public function download(string $filename)
    {
        if (!Storage::exists("backups/{$filename}")) {
            $this->status = "❌ El archivo solicitado no existe";
            return null;
        }
        return Storage::download("backups/{$filename}");
    }

    public function import(): void
    {
        if (!$this->import_file) {
            $this->status = "Debes seleccionar un archivo válido.";
            return;
        }

        $extension = strtolower($this->import_file->getClientOriginalExtension());
        if (!in_array($extension, ['sql'])) {
            $this->status = "Error: Solo se permiten archivos .sql";
            return;
        }

        // Verificar que la base de datos existe
        if (!$this->dbExists) {
            $this->status = "Error: La base de datos famasy no existe";
            return;
        }

        try {
            $filePath = $this->import_file->storeAs('imports', $this->import_file->getClientOriginalName());
            $fullPath = storage_path("app/{$filePath}");
            $mysql = 'C:\\xampp\\mysql\\bin\\mysql.exe';
            $command = "\"$mysql\" -u root famasy < \"{$fullPath}\"";
            exec($command);

            $fileSize = $this->formatFileSize(filesize($fullPath));

            DatabaseBackup::create([
                'nomBac' => 'famasy',
                'verBac' => now()->format('Ymd_His'),
                'arcBac' => $this->import_file->getClientOriginalName(),
                'tamBac' => $fileSize,
                'obsBac' => 'Importación desde archivo',
                'tipBac' => 'import',
                'idUsuBac' => Auth::id()
            ]);

            $this->status = "✅ Base de datos importada correctamente";
            $this->observation = '';
            $this->checkDatabase();
        } catch (\Exception $e) {
            $this->status = "❌ Error al importar: " . $e->getMessage();
        }
    }

public function importFromHistory(string $filename): void
{
    try {
        $filePath = storage_path("app/backups/{$filename}");
        
        if (!file_exists($filePath)) {
            throw new \Exception("El archivo de backup no existe");
        }

        // Verificar que mysql.exe existe
        $mysqlPath = 'C:\\xampp\\mysql\\bin\\mysql.exe';
        if (!file_exists($mysqlPath)) {
            throw new \Exception("No se encontró mysql.exe");
        }

        $fileSize = filesize($filePath);
        $command = "\"$mysqlPath\" -u root famasy < \"{$filePath}\" 2>&1";
        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new \Exception("Error en la importación: " . implode("\n", $output));
        }

        DatabaseBackup::create([
            'nomBac' => 'famasy',
            'verBac' => now()->format('Ymd_His'),
            'arcBac' => $filename,
            'tamBac' => $this->formatFileSize($fileSize),
            'obsBac' => 'Importado desde historial',
            'tipBac' => 'import',
            'idUsuBac' => Auth::id()
        ]);

        $this->status = "✅ Base de datos importada correctamente desde el historial";
        $this->checkDatabase();
    } catch (\Exception $e) {
        $this->status = "❌ Error al importar: " . $e->getMessage();
    }
}

public function safeDelete(): void
{
    $protectedTables = [
        'cache', 'cache_locks', 'contacto', 'direccion', 
        'failed_jobs', 'job_batches', 'jobs', 'migrations',
        'password_reset_tokens', 'rol', 'sessions', 'users',
        'database_backups'
    ];
    
    try {
        $tables = DB::select('SHOW TABLES');
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        foreach ($tables as $table) {
            $tableName = reset($table);
            
            if (!in_array($tableName, $protectedTables)) {
                DB::table($tableName)->truncate();
            }
        }
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        DatabaseBackup::create([
            'nomBac' => 'famasy',
            'verBac' => now()->format('Ymd_His'),
            'arcBac' => 'N/A',
            'tamBac' => '0 bytes',
            'obsBac' => $this->observation,
            'tipBac' => 'clean',
            'idUsuBac' => Auth::id()
        ]);
        
        $this->status = "✅ Datos limpiados (tablas esenciales protegidas). Observación: " . $this->observation;
        $this->reset(['observation', 'showDeleteModal', 'showDeleteConfirmModal']);
        
    } catch (\Exception $e) {
        $this->status = "❌ Error durante la limpieza: " . $e->getMessage();
    }
}

public function getBackups()
{
    return DatabaseBackup::with(['user' => function($query) {
            $query->select('id', 'nomUsu', 'apeUsu');
        }])
        ->orderByDesc('created_at')
        ->get()
        ->map(function ($backup) {
            return [
                'name' => $backup->nomBac,
                'version' => $backup->verBac,
                'filename' => $backup->arcBac,
                'fecha' => $backup->created_at->format('Y-m-d H:i:s'),
                'tamano' => $backup->tamBac ?? 'N/A',
                'observacion' => $backup->obsBac,
                'responsable' => $backup->user ? "{$backup->user->nomUsu} {$backup->user->apeUsu}" : 'N/A',
                'downloadable' => $backup->tipBac === 'export' && Storage::exists("backups/{$backup->arcBac}"),
                'tipBac' => $backup->tipBac
            ];
        })
        ->toArray();
}
};
?>

@section('title', 'Configuración: Base de datos')

<div x-data @download-export.window="window.open(`/storage/backups/${$event.detail.filename}`, '_blank')"
     class="flex items-center justify-center p-4 min-h-screen">
    
    @include('partials.sidebar', [
        'active' => 'database',
        'items' => [
            ['id' => 'password', 'label' => 'Contraseña', 'route' => 'settings.password'],
            ['id' => 'database', 'label' => 'Base de datos', 'route' => 'settings.database'],
        ]
    ])

    <div class="w-full max-w-5xl space-y-8">

        <!-- Configuración de BD -->
        <div class="bg-white shadow rounded-lg p-6 border border-gray-300">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Configuración de base de datos</h2>

            <table class="w-full text-sm">
                <thead class="bg-gray-100 text-left">
                    <tr>
                        <th class="p-2">Nombre</th>
                        <th class="p-2">Versión</th>
                        <th class="p-2">Estado</th>
                        <th class="p-2">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="p-2 font-medium text-gray-800">famasy</td>
                        <td class="p-2 text-gray-600">famasy_{{ now()->format('Ymd_His') }}</td>
                        <td class="p-2">
                            <span class="font-semibold {{ $dbExists ? 'text-green-600' : 'text-red-600' }}">
                                {{ $dbExists ? 'Existe' : 'No existe' }}
                            </span>
                        </td>
                        <td class="p-2">
                            @if ($dbExists)
                                <button wire:click="$set('showExportModal', true)" class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">Exportar</button>
                                <button wire:click="$set('showDeleteModal', true)" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 ml-2">Limpiar Datos</button>
                            @else
                                <form wire:submit.prevent="import" class="flex items-center gap-2">
                                    <input type="file" wire:model="import_file" accept=".sql,.txt" class="border rounded px-2 py-1" />
                                    <button type="submit" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700">Importar</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <!-- Mensaje de estado -->
            @if($status)
                <div class="mt-4 p-3 rounded {{ str_contains($status, '✅') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    {{ $status }}
                </div>
            @endif
        </div>

        <!-- Historial de versiones -->
        <div class="bg-white shadow rounded-lg p-6 border border-gray-300">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Historial de versiones</h2>

            <table class="w-full text-sm">
                <thead class="bg-gray-100 text-left">
                    <tr>
                        <th class="p-2">Nombre</th>
                        <th class="p-2">Versión</th>
                        <th class="p-2">Fecha</th>
                        <th class="p-2">Tamaño</th>
                        <th class="p-2">Observación</th>
                        <th class="p-2">Responsable</th>
                        <th class="p-2">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($this->getBackups() as $backup)
                        <tr>
                            <td class="p-2">{{ $backup['name'] }}</td>
                            <td class="p-2">{{ $backup['version'] }}</td>
                            <td class="p-2">{{ $backup['fecha'] }}</td>
                            <td class="p-2">{{ $backup['tamano'] }}</td>
                            <td class="p-2 italic text-gray-500">{{ $backup['observacion'] }}</td>
                            <td class="p-2">{{ $backup['responsable'] }}</td>
                            <td class="p-2">
    @if ($backup['downloadable'])
        <a href="{{ route('download.backup', ['filename' => $backup['filename']]) }}" 
           class="text-blue-600 hover:underline mr-2">Descargar</a>
        @if(!$dbExists)
            <button wire:click="importFromHistory('{{ $backup['filename'] }}')" 
                    class="text-green-600 hover:underline">Importar</button>
        @endif
    @else
        <span class="text-gray-400">{{ $backup['tipBac'] === 'clean' ? 'Limpieza' : ($backup['tipBac'] === 'import' ? 'Importación' : 'Exportación') }}</span>
    @endif
</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Exportar (Paso 1) -->
    @if ($showExportModal)
        <div class="fixed inset-0 bg-black/20 bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg max-w-sm w-full">
                <h2 class="text-xl font-semibold mb-4">Exportar base de datos</h2>
                <p class="mb-4 text-sm text-gray-600">
                    Se creará un respaldo completo de la base de datos <strong>famasy</strong>.
                </p>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Observación</label>
                    <input type="text" wire:model="observation" placeholder="Motivo de la exportación..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-blue-500" />
                </div>
                
                <div class="flex justify-end gap-3">
                    <button wire:click="$set('showExportModal', false)"
                            class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 transition">Cancelar</button>
                    <button wire:click="$set('showExportConfirmModal', true)"
                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">Continuar</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Confirmación Exportar (Paso 2) -->
    @if ($showExportConfirmModal)
        <div class="fixed inset-0 bg-black/20 bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg max-w-sm w-full">
                <h2 class="text-xl font-semibold mb-4">Confirmar exportación</h2>
                <p class="mb-4 text-sm text-gray-600">
                    Se exportará la base de datos con los siguientes detalles:
                </p>
                
                <div class="mb-4 space-y-2">
                    <p><strong>Nombre:</strong> famasy</p>
                    <p><strong>Versión:</strong> famasy_{{ now()->format('Ymd_His') }}</p>
                    <p><strong>Observación:</strong> {{ $observation ?: 'Ninguna' }}</p>
                </div>
                
                <div class="flex justify-end gap-3">
                    <button wire:click="$set('showExportConfirmModal', false)"
                            class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 transition">Atrás</button>
                    <button wire:click="export"
                            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">Confirmar Exportación</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Limpiar Datos (Paso 1) -->
    @if ($showDeleteModal)
        <div class="fixed inset-0 bg-black/20 bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg max-w-sm w-full">
                <h2 class="text-xl font-semibold mb-4">Limpiar base de datos</h2>
                <p class="mb-4 text-sm text-gray-600">
                    Se eliminarán los datos de la base de datos <strong>famasy</strong> excepto:
                </p>
                
                <ul class="list-disc pl-5 mb-4 text-sm">
                    <li>Usuarios y credenciales</li>
                    <li>Datos de contacto y dirección</li>
                    <li>Sesiones activas</li>
                    <li>Roles y permisos</li>
                    <li>Estructura de la base de datos</li>
                </ul>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Observación</label>
                    <input type="text" wire:model="observation" placeholder="Motivo de la limpieza..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-red-500" />
                </div>
                
                <div class="flex justify-end gap-3">
                    <button wire:click="$set('showDeleteModal', false)"
                            class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 transition">Cancelar</button>
                    <button wire:click="$set('showDeleteConfirmModal', true)"
                            class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition">Continuar</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Confirmación Limpiar Datos (Paso 2) -->
    @if ($showDeleteConfirmModal)
        <div class="fixed inset-0 bg-black/20 bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg max-w-sm w-full">
                <h2 class="text-xl font-semibold mb-4">Confirmar limpieza</h2>
                <p class="mb-4 text-sm text-gray-600">
                    Se eliminarán TODOS los datos excepto las tablas esenciales:
                </p>
                
                <div class="mb-4 space-y-2">
                    <p><strong>Nombre:</strong> famasy</p>
                    <p><strong>Versión actual:</strong> famasy_{{ now()->format('Ymd_His') }}</p>
                    <p><strong>Observación:</strong> {{ $observation ?: 'Ninguna' }}</p>
                </div>
                
                <div class="flex justify-end gap-3">
                    <button wire:click="$set('showDeleteConfirmModal', false)"
                            class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 transition">Atrás</button>
                    <button wire:click="safeDelete"
                            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">Confirmar Limpieza</button>
                </div>
            </div>
        </div>
    @endif
</div>