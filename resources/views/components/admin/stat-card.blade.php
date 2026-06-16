@props([
    'label',
    'value',
    'sub' => null,
    'href' => null,
    'tint' => 'bg-gray-100 text-gray-500',
    'icon',
])

<div @class([
        'group relative rounded-2xl bg-white border border-gray-100 shadow-xs p-5 flex items-center gap-4',
        'transition hover:-translate-y-0.5 hover:shadow-md hover:border-amber-200' => $href,
    ])>
    @if ($href)
        {{-- Stretched link: the whole card is clickable, no dynamic tag needed. --}}
        <a href="{{ $href }}" wire:navigate class="absolute inset-0 rounded-2xl focus:outline-hidden focus-visible:ring-2 focus-visible:ring-amber-400">
            <span class="sr-only">{{ $label }}</span>
        </a>
    @endif

    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl {{ $tint }}">
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" />
        </svg>
    </span>
    <div class="min-w-0">
        <p class="text-sm font-medium text-gray-500">{{ $label }}</p>
        <p class="mt-0.5 text-2xl font-bold text-gray-900">{{ $value }}</p>
        @if ($sub)
            <p class="text-xs text-gray-400">{{ $sub }}</p>
        @endif
    </div>
</div>
