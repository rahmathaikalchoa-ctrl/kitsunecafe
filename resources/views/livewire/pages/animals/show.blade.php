<?php

declare(strict_types=1);

use App\Models\Animal;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public Animal $animal;

    public function mount(Animal $animal): void
    {
        $this->animal = $animal;
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

<div class="py-10">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            @php
                $imageSrc = $animal->image_path
                    ? (str_starts_with($animal->image_path, 'http') ? $animal->image_path : asset('images/animals/' . $animal->image_path))
                    : null;
            @endphp

            @if ($imageSrc)
                <img
                    src="{{ $imageSrc }}"
                    alt="{{ $animal->name }}"
                    class="w-full h-72 object-cover"
                    onerror="this.style.display='none';this.nextElementSibling.style.display='flex'"
                />
                <x-fox-placeholder class="h-72" style="display:none" />
            @else
                <x-fox-placeholder class="h-72" />
            @endif

            <div class="p-8">
                <div class="flex items-center gap-3 mb-4">
                    <h1 class="text-3xl font-bold text-gray-900">{{ $animal->name }}</h1>
                    <span class="text-sm font-medium text-orange-700 bg-orange-50 px-3 py-1 rounded-full capitalize">
                        {{ $animal->species->value }}
                    </span>
                </div>

                <p class="text-gray-700 leading-relaxed text-lg">{{ $animal->description }}</p>

                <div class="mt-8">
                    <a href="{{ route('animals.index') }}" wire:navigate>
                        <flux:button variant="ghost" icon="arrow-left">Back to all foxes</flux:button>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
