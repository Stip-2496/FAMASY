@php
if (! isset($scrollTo)) {
    $scrollTo = 'body';
}

$scrollIntoViewJsSnippet = ($scrollTo !== false)
    ? <<<JS
       (\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView()
    JS
    : '';
@endphp

<div>
    @if ($paginator->hasPages())
        <nav role="navigation" aria-label="Navegación de paginación" class="inline-flex items-center p-1 rounded bg-white space-x-2">
            {{-- Botón Anterior --}}
            @if ($paginator->onFirstPage())
                <span class="p-1 rounded border text-gray-400 bg-white cursor-default rounded-xl shadow-lg" aria-disabled="true" aria-label="Anterior">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z" />
                    </svg>
                </span>
            @else
                <button wire:click="previousPage" wire:loading.attr="disabled" class="p-1 rounded border text-black bg-white cursor-pointer rounded-xl shadow-lg hover:shadow-xl hover:-translate-y-0.5 hover:text-white hover:bg-blue-600 hover:border-blue-600 transition-all duration-200" aria-label="Anterior">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z" />
                    </svg>
                </button>
            @endif

            {{-- Información de página --}}
            <p class="text-xs text-gray-500">
                Página {{ $paginator->currentPage() }} de {{ $paginator->lastPage() }}
            </p>

            {{-- Botón Siguiente --}}
            @if ($paginator->hasMorePages())
                <button wire:click="nextPage" wire:loading.attr="disabled" class="p-1 rounded border text-black bg-white cursor-pointer rounded-xl shadow-lg hover:shadow-xl hover:-translate-y-0.5 hover:text-white hover:bg-blue-600 hover:border-blue-600 transition-all duration-200" aria-label="Siguiente">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z" />
                    </svg>
                </button>
            @else
                <span class="p-1 rounded border text-gray-400 bg-white cursor-default rounded-xl shadow-lg" aria-disabled="true" aria-label="Siguiente">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z" />
                    </svg>
                </span>
            @endif
        </nav>
    @endif
</div>