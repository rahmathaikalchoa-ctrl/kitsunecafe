<?php

declare(strict_types=1);

use App\Models\Animal;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    #[Computed]
    public function animals()
    {
        return Animal::active()->orderBy('name')->get();
    }
}; ?>

<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Meet the Foxes</h2>
</x-slot>

<div class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <p class="text-gray-600 mb-8 max-w-2xl">
            Every fox at Kitsune Animal Cafe has their own story. Get to know them before your visit
            — or better yet, let them find you when you arrive.
        </p>

        @if ($this->animals->isEmpty())
            <div class="text-center py-20 text-gray-400">
                <p class="text-lg">No foxes to show right now. Check back soon!</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($this->animals as $animal)
                    <x-animal-card :animal="$animal" />
                @endforeach
            </div>
        @endif
    </div>
</div>
