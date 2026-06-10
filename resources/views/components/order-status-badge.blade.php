@props(['status'])

@php
    // Accept either an OrderStatus enum or its string value.
    $value = $status instanceof \App\Enums\OrderStatus ? $status->value : (string) $status;

    $styles = [
        'pending'   => ['label' => 'Pending',   'dot' => 'bg-amber-400', 'chip' => 'bg-amber-50 text-amber-700 border-amber-100'],
        'confirmed' => ['label' => 'Confirmed', 'dot' => 'bg-blue-400',  'chip' => 'bg-blue-50 text-blue-700 border-blue-100'],
        'completed' => ['label' => 'Completed', 'dot' => 'bg-green-500', 'chip' => 'bg-green-50 text-green-700 border-green-100'],
        'cancelled' => ['label' => 'Cancelled', 'dot' => 'bg-gray-400',  'chip' => 'bg-gray-100 text-gray-500 border-gray-200'],
    ];

    $s = $styles[$value] ?? ['label' => ucfirst($value), 'dot' => 'bg-gray-400', 'chip' => 'bg-gray-100 text-gray-500 border-gray-200'];
@endphp

<span {{ $attributes->class("inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full border {$s['chip']}") }}>
    <span class="h-1.5 w-1.5 rounded-full {{ $s['dot'] }}"></span>
    {{ $s['label'] }}
</span>
