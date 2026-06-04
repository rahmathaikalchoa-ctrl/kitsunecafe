<?php

declare(strict_types=1);

use App\Models\Animal;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public ?int $selectedAnimalId = null;

    public string $search = '';

    public ?string $color = null;

    #[Computed]
    public function animals()
    {
        return Animal::active()
            ->when($this->search, fn ($q) => $q->where('name', 'like', '%'.$this->search.'%'))
            ->when($this->color, fn ($q) => $q->where('color', $this->color))
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function colors()
    {
        return Animal::active()
            ->whereNotNull('color')
            ->distinct()
            ->orderBy('color')
            ->pluck('color');
    }

    #[Computed]
    public function totalFoxes(): int
    {
        return Animal::active()->count();
    }

    #[Computed]
    public function selectedAnimal(): ?Animal
    {
        if (! $this->selectedAnimalId) {
            return null;
        }

        return Animal::active()->find($this->selectedAnimalId);
    }

    public function openFox(int $id): void
    {
        $this->selectedAnimalId = $id;
        unset($this->selectedAnimal);

        $this->dispatch('open-fox-modal');
    }

    public function closeFox(): void
    {
        $this->selectedAnimalId = null;
        unset($this->selectedAnimal);
    }
}; ?>

<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Meet the Foxes</h2>
</x-slot>

<div class="py-10"
     x-data="{ modalOpen: false }"
     x-effect="document.body.style.overflow = modalOpen ? 'hidden' : ''"
     x-on:open-fox-modal.window="modalOpen = true"
     x-on:keydown.escape.window="modalOpen = false; $wire.closeFox()">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <p class="text-gray-600 mb-2 max-w-2xl">
            Every fox at Kitsune Animal Cafe has their own story. Get to know them before your visit
            — or better yet, let them find you when you arrive.
        </p>
        <p class="text-sm font-medium text-amber-600 mb-8">{{ $this->totalFoxes }} resident {{ Str::plural('fox', $this->totalFoxes) }}</p>

        {{-- Search + colour filter --}}
        <div class="flex flex-col sm:flex-row sm:items-center gap-4 mb-8">
            <div class="relative sm:w-64">
                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                </svg>
                <input type="search"
                       wire:model.live.debounce.300ms="search"
                       placeholder="Search foxes by name…"
                       aria-label="Search foxes by name"
                       class="w-full pl-9 pr-3 py-1.5 rounded-full border border-gray-200 text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:border-amber-300 focus:ring-2 focus:ring-amber-100 transition" />
            </div>

            <div class="flex flex-wrap gap-2">
                <button
                    wire:click="$set('color', null)"
                    wire:loading.class="opacity-50 cursor-wait"
                    wire:target="$set('color', null)"
                    aria-pressed="{{ $color === null ? 'true' : 'false' }}"
                    @class([
                        'px-4 py-1.5 rounded-full text-sm font-medium transition',
                        'bg-amber-500 text-white' => $color === null,
                        'bg-white text-gray-600 border border-gray-200 hover:border-amber-300' => $color !== null,
                    ])>
                    All
                </button>
                @foreach ($this->colors as $c)
                    <button
                        wire:click="$set('color', '{{ $c }}')"
                        wire:loading.class="opacity-50 cursor-wait"
                        wire:target="$set('color', '{{ $c }}')"
                        aria-pressed="{{ $color === $c ? 'true' : 'false' }}"
                        @class([
                            'px-4 py-1.5 rounded-full text-sm font-medium transition',
                            'bg-amber-500 text-white' => $color === $c,
                            'bg-white text-gray-600 border border-gray-200 hover:border-amber-300' => $color !== $c,
                        ])>
                        {{ $c }}
                    </button>
                @endforeach
            </div>
        </div>

        @if ($this->animals->isEmpty())
            <div class="text-center py-20 text-gray-400">
                @if ($search !== '' || $color !== null)
                    <p class="text-lg">No foxes match your search.</p>
                    <button wire:click="$set('search', ''); $set('color', null)" class="mt-3 text-sm font-medium text-amber-600 hover:underline">Clear filters</button>
                @else
                    <p class="text-lg">No foxes to show right now. Check back soon!</p>
                @endif
            </div>
        @else
            <p class="text-sm text-gray-400 mb-4">Showing {{ $this->animals->count() }} of {{ $this->totalFoxes }} {{ Str::plural('fox', $this->totalFoxes) }}</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($this->animals as $animal)
                    <x-animal-card :animal="$animal" wire:key="animal-{{ $animal->id }}" />
                @endforeach
            </div>
        @endif

        {{-- ── Backdrop ──────────────────────────────────────────────────────── --}}
        <div x-show="modalOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             x-on:click="modalOpen = false; $wire.closeFox()"
             class="fixed inset-0 bg-black/50 z-40"
             style="display:none">
        </div>

        {{-- ── Fox detail modal panel ────────────────────────────────────────── --}}
        <div x-show="modalOpen"
             x-on:click="modalOpen = false; $wire.closeFox()"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto"
             style="display:none">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg relative my-auto overflow-hidden"
                 x-on:click.stop
                 role="dialog"
                 aria-modal="true"
                 aria-labelledby="fox-modal-title">

                @if ($this->selectedAnimal)
                    @php $fox = $this->selectedAnimal; @endphp

                    {{-- Photo --}}
                    <div class="relative h-64 overflow-hidden">
                        @if ($fox->image_path)
                            <img
                                src="{{ $fox->imageUrl() }}"
                                alt="{{ $fox->name }}"
                                class="w-full h-full object-cover"
                                onerror="this.style.display='none';this.nextElementSibling.style.display='flex'"
                            />
                            <x-fox-placeholder class="h-64" style="display:none" />
                        @else
                            <x-fox-placeholder class="h-64" />
                        @endif

                        {{-- Gradient + close button overlay --}}
                        <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent pointer-events-none"></div>
                        <button type="button"
                                aria-label="Close"
                                x-on:click="modalOpen = false; $wire.closeFox()"
                                class="absolute top-3 right-3 bg-white/90 hover:bg-white text-gray-600 hover:text-gray-900 rounded-full p-1.5 shadow transition">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Content --}}
                    <div class="p-6">
                        <div class="flex items-center gap-3 mb-3">
                            <h2 id="fox-modal-title" class="text-2xl font-bold text-gray-900">{{ $fox->name }}</h2>
                            <span class="text-xs font-medium text-amber-700 bg-amber-50 px-2.5 py-0.5 rounded-full capitalize">
                                {{ $fox->species->value }}
                            </span>
                        </div>

                        @if (! empty($fox->personality))
                            <div class="flex flex-wrap gap-2 mb-3">
                                @foreach ($fox->personality as $trait)
                                    <span class="text-xs font-medium text-amber-700 bg-amber-50 border border-amber-100 px-2.5 py-0.5 rounded-full">
                                        {{ $trait }}
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        <p class="text-gray-600 leading-relaxed">{{ $fox->description }}</p>

                        <div class="mt-6 flex items-center justify-between">
                            <a href="{{ route('animals.show', $fox) }}" wire:navigate
                               class="text-sm font-medium text-amber-600 hover:text-amber-700 hover:underline transition">
                                Open full profile
                            </a>
                            <button type="button"
                                    x-on:click="modalOpen = false; $wire.closeFox()"
                                    class="text-sm font-medium text-gray-500 hover:text-gray-700 transition">
                                Close
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
