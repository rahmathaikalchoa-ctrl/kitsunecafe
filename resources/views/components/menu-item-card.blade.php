@props(['item'])

<div @class([
    'flex flex-col bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden transition hover:shadow-md',
    'opacity-60' => ! $item->is_available,
])>
    <div class="h-40 bg-amber-50 flex items-center justify-center text-5xl">
        🍜
    </div>

    <div class="flex flex-col flex-1 p-4 gap-2">
        <div class="flex items-start justify-between gap-2">
            <h3 class="font-semibold text-gray-900 leading-snug">{{ $item->name }}</h3>
            <span class="shrink-0 text-sm font-medium text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full">
                Rp {{ number_format($item->price_cents / 100, 0, ',', '.') }}
            </span>
        </div>

        @if ($item->description)
            <p class="text-sm text-gray-500 line-clamp-2">{{ $item->description }}</p>
        @endif

        <div class="mt-auto pt-3">
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
</div>
