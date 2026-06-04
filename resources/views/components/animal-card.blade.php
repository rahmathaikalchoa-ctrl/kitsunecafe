@props(['animal' => \App\Models\Animal::class])

@php
    $imageSrc = $animal->image_path
        ? (str_starts_with($animal->image_path, 'http') ? $animal->image_path : asset('images/animals/' . $animal->image_path))
        : null;
@endphp

<a href="{{ route('animals.show', $animal) }}" wire:navigate
   aria-label="View {{ $animal->name }}'s profile"
   class="group flex flex-col bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden transition-all duration-300 hover:-translate-y-2 hover:shadow-xl hover:shadow-orange-100/70 hover:border-orange-200">

    {{-- Image area — inner overflow-hidden clips the zoom --}}
    <div class="overflow-hidden">
        @if ($imageSrc)
            <img
                src="{{ $imageSrc }}"
                alt="{{ $animal->name }}"
                class="w-full h-48 object-cover transition-transform duration-500 group-hover:scale-110"
                loading="lazy"
                onerror="this.style.display='none';this.nextElementSibling.style.display='flex'"
            />
            <x-fox-placeholder class="h-48 transition-transform duration-500 group-hover:scale-110" style="display:none" />
        @else
            <x-fox-placeholder class="h-48 transition-transform duration-500 group-hover:scale-110" />
        @endif
    </div>

    <div class="p-4">
        <div class="flex items-center gap-2 mb-1">
            <h3 class="font-semibold text-gray-900 text-lg group-hover:text-orange-700 transition-colors duration-200">{{ $animal->name }}</h3>
            <span class="text-xs font-medium text-orange-700 bg-orange-50 px-2 py-0.5 rounded-full capitalize">
                {{ $animal->species->value }}
            </span>
        </div>
        <p class="text-sm text-gray-500 line-clamp-3">{{ $animal->description }}</p>
    </div>
</a>
