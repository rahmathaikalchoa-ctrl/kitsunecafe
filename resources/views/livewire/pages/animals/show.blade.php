<?php

declare(strict_types=1);

use App\Models\Animal;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public Animal $animal;

    public function mount(Animal $animal): void
    {
        abort_unless($animal->is_active, 404);

        $this->animal = $animal;
    }

    #[Computed]
    public function prevFox(): ?Animal
    {
        return Animal::active()
            ->where('name', '<', $this->animal->name)
            ->orderByDesc('name')
            ->first();
    }

    #[Computed]
    public function nextFox(): ?Animal
    {
        return Animal::active()
            ->where('name', '>', $this->animal->name)
            ->orderBy('name')
            ->first();
    }
}; ?>

<x-slot name="header">
    <div class="flex items-center gap-3">
        <a href="{{ route('animals.index') }}" wire:navigate
           aria-label="Back to all foxes"
           class="flex items-center gap-1 text-gray-400 hover:text-gray-600 transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
            </svg>
            Foxes
        </a>
        <span class="text-gray-300">/</span>
        <h2 class="font-semibold text-xl text-gray-800">{{ $animal->name }}</h2>
    </div>
</x-slot>

@php
    $stats = array_filter([
        ['label' => 'Gender', 'value' => $animal->gender, 'path' => 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z'],
        ['label' => 'Age', 'value' => $animal->age ? $animal->age . ' ' . Str::plural('year', $animal->age) : null, 'path' => 'M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.871c1.355 0 2.697.056 4.024.166C17.155 8.51 18 9.473 18 10.608v2.513m-3-4.871v-1.5m-6 1.5v-1.5m12 9.75-1.5.75a3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0L3 16.5m15-3.379a48.474 48.474 0 0 0-6-.371c-2.032 0-4.034.126-6 .371m12 0c.39.049.777.102 1.163.16 1.07.16 1.837 1.094 1.837 2.175v5.169c0 .621-.504 1.125-1.125 1.125H4.125A1.125 1.125 0 0 1 3 20.625v-5.169c0-1.081.768-2.015 1.837-2.175A48.041 48.041 0 0 1 6 13.12M12.265 3.11a.375.375 0 1 1-.53 0L12 2.845l.265.265Zm-3 0a.375.375 0 1 1-.53 0L9 2.845l.265.265Zm6 0a.375.375 0 1 1-.53 0L15 2.845l.265.265Z'],
        ['label' => 'Colour', 'value' => $animal->color, 'path' => 'M9.53 16.122a3 3 0 0 0-5.78 1.128 2.25 2.25 0 0 1-2.4 2.245 4.5 4.5 0 0 0 8.4-2.245c0-.399-.078-.78-.22-1.128Zm0 0a15.998 15.998 0 0 0 3.388-1.62m-5.043-.025a15.994 15.994 0 0 1 1.622-3.395m3.42 3.42a15.995 15.995 0 0 0 4.764-4.648l3.876-5.814a1.151 1.151 0 0 0-1.597-1.597L14.146 6.32a15.996 15.996 0 0 0-4.649 4.763m3.42 3.42a6.776 6.776 0 0 0-3.42-3.42'],
        ['label' => 'At cafe since', 'value' => $animal->arrived_year, 'path' => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5'],
    ], fn ($s) => ! empty($s['value']));
@endphp

<div class="py-10">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Hero --}}
        <div class="bg-white rounded-2xl shadow-xs border border-gray-100 overflow-hidden">
            <div class="relative h-80">
                @if ($animal->image_path)
                    <img
                        src="{{ $animal->imageUrl() }}"
                        alt="{{ $animal->name }}"
                        class="w-full h-full object-cover"
                        onerror="this.style.display='none';this.nextElementSibling.style.display='flex'"
                    />
                    <x-fox-placeholder class="h-80" style="display:none" />
                @else
                    <x-fox-placeholder class="h-80" />
                @endif

                <div class="absolute inset-0 bg-linear-to-t from-black/60 via-black/10 to-transparent"></div>
                <div class="absolute bottom-0 left-0 p-6 flex items-center gap-3">
                    <h1 class="text-4xl font-bold text-white drop-shadow-sm">{{ $animal->name }}</h1>
                    <span class="text-xs font-medium text-amber-50 bg-amber-500/80 px-3 py-1 rounded-full capitalize backdrop-blur-xs">
                        {{ $animal->species->value }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Quick stats --}}
        @if (count($stats))
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6">
                @foreach ($stats as $stat)
                    <div class="bg-white rounded-2xl shadow-xs border border-gray-100 p-4 flex items-center gap-3">
                        <span class="shrink-0 w-9 h-9 rounded-full bg-amber-50 text-amber-500 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $stat['path'] }}"/>
                            </svg>
                        </span>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-400 uppercase tracking-wide">{{ $stat['label'] }}</p>
                            <p class="font-semibold text-gray-800 truncate">{{ $stat['value'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Main grid --}}
        <div class="grid lg:grid-cols-3 gap-6 mt-6">

            {{-- Left: about + fun facts --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-2xl shadow-xs border border-gray-100 p-6">
                    <h3 class="font-semibold text-gray-800 mb-3">About {{ $animal->name }}</h3>
                    <p class="text-gray-600 leading-relaxed">{{ $animal->description }}</p>
                </div>

                @if (! empty($animal->fun_facts))
                    <div class="bg-white rounded-2xl shadow-xs border border-gray-100 p-6">
                        <h3 class="font-semibold text-gray-800 mb-4">Fun facts</h3>
                        <ul class="space-y-3">
                            @foreach ($animal->fun_facts as $fact)
                                <li class="flex items-start gap-3">
                                    <span class="shrink-0 mt-0.5 w-5 h-5 rounded-full bg-amber-100 text-amber-500 flex items-center justify-center">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                                        </svg>
                                    </span>
                                    <span class="text-gray-600">{{ $fact }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            {{-- Right: personality + favourites --}}
            <div class="space-y-6">
                @if (! empty($animal->personality))
                    <div class="bg-white rounded-2xl shadow-xs border border-gray-100 p-6">
                        <h3 class="font-semibold text-gray-800 mb-4">Personality</h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($animal->personality as $trait)
                                <span class="text-sm font-medium text-amber-700 bg-amber-50 border border-amber-100 px-3 py-1 rounded-full">
                                    {{ $trait }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($animal->favourite_treat || $animal->favourite_spot)
                    <div class="bg-white rounded-2xl shadow-xs border border-gray-100 p-6">
                        <h3 class="font-semibold text-gray-800 mb-4">Favourites</h3>
                        <div class="space-y-4">
                            @if ($animal->favourite_treat)
                                <div class="flex items-start gap-3">
                                    <span class="shrink-0 w-8 h-8 rounded-full bg-amber-50 text-amber-500 flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm0 0a8.949 8.949 0 0 0 4.951-1.488A3.987 3.987 0 0 0 13 16a3.987 3.987 0 0 0-3.951 3.512A8.948 8.948 0 0 0 12 21Z"/>
                                        </svg>
                                    </span>
                                    <div>
                                        <p class="text-xs text-gray-400 uppercase tracking-wide">Favourite treat</p>
                                        <a href="{{ route('menu.index') }}" wire:navigate class="font-medium text-gray-800 hover:text-amber-600 transition">{{ $animal->favourite_treat }}</a>
                                    </div>
                                </div>
                            @endif
                            @if ($animal->favourite_spot)
                                <div class="flex items-start gap-3">
                                    <span class="shrink-0 w-8 h-8 rounded-full bg-amber-50 text-amber-500 flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>
                                        </svg>
                                    </span>
                                    <div>
                                        <p class="text-xs text-gray-400 uppercase tracking-wide">Favourite spot</p>
                                        <p class="font-medium text-gray-800">{{ $animal->favourite_spot }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Prev / back / next navigation --}}
        <div class="mt-8 flex items-center justify-between gap-3">
            <div class="flex-1">
                @if ($this->prevFox)
                    <a href="{{ route('animals.show', $this->prevFox) }}" wire:navigate
                       class="group inline-flex items-center gap-2 text-sm text-gray-500 hover:text-amber-600 transition">
                        <svg class="w-4 h-4 transition-transform group-hover:-translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
                        </svg>
                        <span><span class="text-gray-400">Prev</span> · {{ $this->prevFox->name }}</span>
                    </a>
                @endif
            </div>

            <a href="{{ route('animals.index') }}" wire:navigate
               class="shrink-0 text-sm font-medium text-gray-500 hover:text-gray-700 transition">
                All foxes
            </a>

            <div class="flex-1 text-right">
                @if ($this->nextFox)
                    <a href="{{ route('animals.show', $this->nextFox) }}" wire:navigate
                       class="group inline-flex items-center gap-2 text-sm text-gray-500 hover:text-amber-600 transition">
                        <span>{{ $this->nextFox->name }} · <span class="text-gray-400">Next</span></span>
                        <svg class="w-4 h-4 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                        </svg>
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
