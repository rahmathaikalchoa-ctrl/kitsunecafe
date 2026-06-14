@props(['type', 'table' => null])

@php
    // Accept either an OrderType enum or its string value.
    $enum = $type instanceof \App\Enums\OrderType
        ? $type
        : \App\Enums\OrderType::tryFrom((string) $type);

    $label = $enum?->label() ?? ucfirst(str_replace('_', ' ', (string) $type));
    $showTable = $enum === \App\Enums\OrderType::DineIn && $table;
@endphp

<span {{ $attributes->class('inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full border bg-gray-50 text-gray-600 border-gray-200') }}>
    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
        @if ($showTable)
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8.25V18m0-9.75h18M3 8.25 4.5 6h15L21 8.25M21 8.25V18M7.5 18v-2.25m9 2.25v-2.25"/>
        @else
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z"/>
        @endif
    </svg>
    {{ $label }}@if ($showTable) · Table {{ $table }}@endif
</span>
