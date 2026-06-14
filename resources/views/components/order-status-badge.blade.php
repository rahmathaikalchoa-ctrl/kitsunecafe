@props(['status'])

@php
    // Accept either an OrderStatus enum or its string value; fall back gracefully for unknowns.
    $enum = $status instanceof \App\Enums\OrderStatus
        ? $status
        : \App\Enums\OrderStatus::tryFrom((string) $status);

    $label = $enum?->label() ?? ucfirst((string) $status);
    $dot = $enum?->dotClass() ?? 'bg-gray-400';
    $chip = $enum?->chipClass() ?? 'bg-gray-100 text-gray-500 border-gray-200';
@endphp

<span {{ $attributes->class("inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full border {$chip}") }}>
    <span class="h-1.5 w-1.5 rounded-full {{ $dot }}"></span>
    {{ $label }}
</span>
