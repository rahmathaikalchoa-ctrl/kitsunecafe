@props(['animal'])

<a href="{{ route('animals.show', $animal) }}" wire:navigate
   class="group flex flex-col bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden transition hover:shadow-md hover:-translate-y-0.5">
    <div class="h-48 bg-orange-50 flex items-center justify-center text-7xl">
        🦊
    </div>

    <div class="p-4">
        <div class="flex items-center gap-2 mb-1">
            <h3 class="font-semibold text-gray-900 text-lg">{{ $animal->name }}</h3>
            <span class="text-xs font-medium text-orange-700 bg-orange-50 px-2 py-0.5 rounded-full capitalize">
                {{ $animal->species->value }}
            </span>
        </div>
        <p class="text-sm text-gray-500 line-clamp-3">{{ $animal->description }}</p>
    </div>
</a>
