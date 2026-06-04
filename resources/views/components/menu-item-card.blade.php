@props(['item' => \App\Models\MenuItem::class])

@php
    $avg = round($item->reviews->avg('rating') ?? 0, 1);
    $reviewCount = $item->reviews->count();
    $imageSrc = $item->image_path
        ? (str_starts_with($item->image_path, 'http') ? $item->image_path : asset('images/menu/' . $item->image_path))
        : null;
@endphp

<div @class([
    'group flex flex-col bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden transition-all duration-300 hover:-translate-y-2 hover:shadow-xl hover:shadow-amber-100/70 hover:border-amber-200',
    'opacity-60' => ! $item->is_available,
])>
    {{-- Clickable top section → opens detail modal (disabled when item unavailable) --}}
    <button
        @if ($item->is_available) wire:click="openItem({{ $item->id }})" @endif
        type="button"
        @class([
            'w-full text-left focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400 focus-visible:ring-inset',
            'cursor-not-allowed pointer-events-none' => ! $item->is_available,
        ])
    >
        {{-- Image area — inner overflow-hidden clips the zoom effect --}}
        <div class="overflow-hidden">
            @if ($imageSrc)
                <img
                    src="{{ $imageSrc }}"
                    alt="{{ $item->name }}"
                    class="w-full h-40 object-cover transition-transform duration-500 group-hover:scale-110"
                    loading="lazy"
                    onerror="this.style.display='none';this.nextElementSibling.style.display='flex'"
                />
                <x-menu-category-placeholder
                    :category="$item->category->name ?? ''"
                    class="h-40 transition-transform duration-500 group-hover:scale-110"
                    style="display:none"
                />
            @else
                <x-menu-category-placeholder
                    :category="$item->category->name ?? ''"
                    class="h-40 transition-transform duration-500 group-hover:scale-110"
                />
            @endif
        </div>

        <div class="p-4 pb-2">
            <div class="flex items-start justify-between gap-2">
                <h3 class="font-semibold text-gray-900 leading-snug group-hover:text-amber-700 transition-colors duration-200">
                    {{ $item->name }}
                </h3>
                <div class="flex flex-col items-end gap-0.5 shrink-0">
                    <span class="text-xs text-gray-400 uppercase tracking-wide">{{ $item->category->name }}</span>
                    <span class="text-sm font-semibold text-amber-700">
                        Rp {{ number_format($item->price_cents, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            @if ($item->description)
                <p class="text-sm text-gray-500 line-clamp-2 mt-1">{{ $item->description }}</p>
            @endif

            {{-- Rating row --}}
            @if ($reviewCount > 0)
                <div class="flex items-center gap-1.5 mt-2" aria-label="Rated {{ $avg }} out of 5">
                    <div class="flex" aria-hidden="true">
                        @for ($i = 1; $i <= 5; $i++)
                            <svg viewBox="0 0 20 20" aria-hidden="true" @class(['w-3.5 h-3.5 fill-current', $i <= $avg ? 'text-amber-400' : 'text-gray-200'])>
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        @endfor
                    </div>
                    <span class="text-xs text-gray-400">{{ $avg }} ({{ $reviewCount }})</span>
                </div>
            @endif
        </div>
    </button>

    {{-- Add to Cart — separate from the clickable area above --}}
    <div class="px-4 pb-4 pt-2 mt-auto">
        @if ($item->is_available)
            <button
                wire:click="addToCart({{ $item->id }})"
                wire:loading.attr="disabled"
                wire:loading.class="opacity-60 cursor-wait"
                wire:target="addToCart({{ $item->id }})"
                type="button"
                class="w-full bg-gradient-to-r from-amber-500 to-orange-400
                       hover:from-amber-600 hover:to-orange-500
                       hover:shadow-lg hover:shadow-amber-300/50 hover:-translate-y-0.5
                       active:translate-y-0 active:shadow-sm active:scale-[0.97]
                       text-white text-sm font-semibold py-2 px-4 rounded-lg
                       transition-all duration-200
                       focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400 focus-visible:ring-offset-1
                       disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0 disabled:hover:shadow-none disabled:active:scale-100"
            >
                <span wire:loading.remove wire:target="addToCart({{ $item->id }})">Add to Cart</span>
                <span wire:loading wire:target="addToCart({{ $item->id }})" class="flex items-center justify-center gap-1.5">
                    <svg class="animate-spin h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 22 6.477 22 12h-4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                    </svg>
                    Adding...
                </span>
            </button>
        @else
            <button disabled type="button"
                class="w-full bg-gray-100 text-gray-400 text-sm font-semibold py-2 px-4 rounded-lg
                       cursor-not-allowed opacity-60">
                Not Available
            </button>
        @endif
    </div>
</div>
