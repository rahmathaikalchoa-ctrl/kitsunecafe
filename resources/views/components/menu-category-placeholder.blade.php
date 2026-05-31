@props(['category' => ''])

@php
    $config = match(strtolower($category)) {
        'food'     => ['bg' => 'bg-amber-100',  'text' => 'text-amber-400',  'label' => 'FOOD'],
        'drinks'   => ['bg' => 'bg-sky-100',    'text' => 'text-sky-400',    'label' => 'DRINKS'],
        'desserts' => ['bg' => 'bg-pink-100',   'text' => 'text-pink-400',   'label' => 'DESSERTS'],
        'snacks'   => ['bg' => 'bg-orange-100', 'text' => 'text-orange-400', 'label' => 'SNACKS'],
        default    => ['bg' => 'bg-gray-100',   'text' => 'text-gray-400',   'label' => 'MENU'],
    };
@endphp

<div {{ $attributes->class([$config['bg'], 'flex items-center justify-center']) }}>
    <span class="text-xs font-semibold tracking-widest uppercase {{ $config['text'] }}">
        {{ $config['label'] }}
    </span>
</div>
