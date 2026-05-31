@props(['item'])

@php
    $placeholderConfig = match(strtolower($item->category->name ?? '')) {
        'food'     => ['bg' => 'bg-amber-100',  'text' => 'text-amber-400',  'label' => 'FOOD'],
        'drinks'   => ['bg' => 'bg-sky-100',    'text' => 'text-sky-400',    'label' => 'DRINKS'],
        'desserts' => ['bg' => 'bg-pink-100',   'text' => 'text-pink-400',   'label' => 'DESSERTS'],
        'snacks'   => ['bg' => 'bg-orange-100', 'text' => 'text-orange-400', 'label' => 'SNACKS'],
        default    => ['bg' => 'bg-gray-100',   'text' => 'text-gray-400',   'label' => 'MENU'],
    };

    $avg = round($item->reviews->avg('rating') ?? 0, 1);
    $reviewCount = $item->reviews->count();
@endphp

<div @class([
    'flex flex-col bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden transition hover:shadow-md',
    'opacity-60' => ! $item->is_available,
])>
    {{-- Clickable top section → opens detail modal --}}
    <button wire:click="openItem({{ $item->id }})" class="w-full text-left focus:outline-none group">
        <div @class(['h-40 flex items-center justify-center transition group-hover:brightness-95', $placeholderConfig['bg']])>
            <span @class(['text-xs font-semibold tracking-widest uppercase', $placeholderConfig['text']])>
                {{ $placeholderConfig['label'] }}
            </span>
        </div>

        <div class="p-4 pb-2">
            <div class="flex items-start justify-between gap-2">
                <h3 class="font-semibold text-gray-900 leading-snug group-hover:text-amber-700 transition">
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
                <div class="flex items-center gap-1.5 mt-2">
                    <div class="flex">
                        @for ($i = 1; $i <= 5; $i++)
                            <svg viewBox="0 0 20 20" @class(['w-3.5 h-3.5 fill-current', $i <= $avg ? 'text-amber-400' : 'text-gray-200'])>
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        @endfor
                    </div>
                    <span class="text-xs text-gray-400">{{ $avg }} ({{ $reviewCount }})</span>
                </div>
            @endif
        </div>
    </button>

    {{-- Add to Cart — separate from clickable area --}}
    <div class="px-4 pb-4 pt-2 mt-auto">
        @if ($item->is_available)
            <flux:button
                wire:click="addToCart({{ $item->id }})"
                variant="primary"
                size="sm"
                class="w-full"
            >
                Add to Cart
            </flux:button>
        @else
            <flux:button disabled size="sm" class="w-full">
                Not Available
            </flux:button>
        @endif
    </div>
</div>
