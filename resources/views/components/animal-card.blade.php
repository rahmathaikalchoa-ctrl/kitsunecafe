@props(['animal' => \App\Models\Animal::class])

@php
    $meta = array_filter([
        $animal->gender,
        $animal->age ? $animal->age . ' ' . Str::plural('yr', $animal->age) : null,
        $animal->color,
    ]);
@endphp

<button type="button" wire:click="openFox({{ $animal->id }})"
   aria-label="View {{ $animal->name }}'s profile"
   class="group flex flex-col text-left w-full bg-white rounded-2xl shadow-xs border border-gray-100 overflow-hidden transition-all duration-300 hover:-translate-y-2 hover:shadow-xl hover:shadow-amber-100/70 hover:border-amber-200 focus:outline-hidden focus-visible:ring-2 focus-visible:ring-amber-400 focus-visible:ring-offset-2">

    {{-- Image area — inner overflow-hidden clips the zoom --}}
    <div class="overflow-hidden">
        @if ($animal->image_path)
            <img
                src="{{ $animal->imageUrl() }}"
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
            <h3 class="font-semibold text-gray-900 text-lg group-hover:text-amber-700 transition-colors duration-200">{{ $animal->name }}</h3>
            <span class="text-xs font-medium text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full capitalize">
                {{ $animal->species->value }}
            </span>
        </div>
        @if (count($meta))
            <p class="text-xs text-gray-400 mb-1.5">{{ implode(' · ', $meta) }}</p>
        @endif
        <p class="text-sm text-gray-500 line-clamp-3">{{ $animal->description }}</p>
        <p class="mt-3 text-sm font-medium text-amber-600 flex items-center gap-1 group-hover:gap-2 transition-all">
            View profile
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
            </svg>
        </p>
    </div>
</button>
