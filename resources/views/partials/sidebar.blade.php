<div class="bg-white fixed right-0 z-50 px-2 py-6 shadow-md border-r border-gray-300" x-data="{ active: '{{ $active ?? '' }}' }">
    <nav class="flex flex-col gap-2">
        @foreach ($items as $item)
            <a href="{{ route($item['route']) }}" wire:navigate @click="active = '{{ $item['id'] }}'" :class="active === '{{ $item['id'] }}' ? 'bg-blue-100 text-blue-700 font-semibold border-l-4 border-blue-600' : 'text-gray-700 hover:bg-blue-50 hover:text-blue-600 border-l-4 border-transparent'" class="block px-4 py-2 rounded-md transition-all duration-200">
                {{ $item['label'] }}
            </a>
        @endforeach
    </nav>
</div>

