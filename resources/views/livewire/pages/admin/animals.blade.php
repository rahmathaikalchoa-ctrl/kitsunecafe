<?php

declare(strict_types=1);

use App\Enums\AnimalSpecies;
use App\Models\Animal;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin')] class extends Component
{
    use WithPagination;

    public ?int $editingId = null;

    public string $name = '';

    public string $species = 'fox';

    public string $gender = '';

    public ?int $age = null;

    public string $color = '';

    public ?int $arrivedYear = null;

    public string $description = '';

    public string $favouriteTreat = '';

    public string $favouriteSpot = '';

    public string $personality = '';

    public string $funFacts = '';

    public string $imagePath = '';

    public bool $isActive = true;

    #[Computed]
    public function previewUrl(): ?string
    {
        // Resolve the typed path through the model's helper so the http-vs-asset
        // logic stays in one place (App\Models\Animal::imageUrl()).
        return $this->imagePath
            ? (new Animal(['image_path' => $this->imagePath]))->imageUrl()
            : null;
    }

    private function ensureStaff(): void
    {
        abort_unless(auth()->user()?->is_staff, 403);
    }

    public function openCreate(): void
    {
        $this->reset('editingId', 'name', 'gender', 'age', 'color', 'arrivedYear', 'description', 'favouriteTreat', 'favouriteSpot', 'personality', 'funFacts', 'imagePath');
        $this->species = AnimalSpecies::Fox->value;
        $this->isActive = true;
        $this->resetValidation();
        $this->dispatch('open-animal-form');
    }

    public function openEdit(int $id): void
    {
        $this->ensureStaff();

        $animal = Animal::findOrFail($id);

        $this->editingId = $animal->id;
        $this->name = $animal->name;
        $this->species = $animal->species->value;
        $this->gender = $animal->gender ?? '';
        $this->age = $animal->age;
        $this->color = $animal->color ?? '';
        $this->arrivedYear = $animal->arrived_year;
        $this->description = $animal->description;
        $this->favouriteTreat = $animal->favourite_treat ?? '';
        $this->favouriteSpot = $animal->favourite_spot ?? '';
        $this->personality = implode(', ', $animal->personality ?? []);
        $this->funFacts = implode(', ', $animal->fun_facts ?? []);
        $this->imagePath = $animal->image_path ?? '';
        $this->isActive = $animal->is_active;
        $this->resetValidation();

        $this->dispatch('open-animal-form');
    }

    protected function validationAttributes(): array
    {
        return [
            'arrivedYear' => 'year at the cafe',
            'favouriteTreat' => 'favourite treat',
            'favouriteSpot' => 'favourite spot',
            'funFacts' => 'fun facts',
            'imagePath' => 'image',
        ];
    }

    public function save(): void
    {
        $this->ensureStaff();
        $this->name = trim($this->name);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'species' => ['required', Rule::enum(AnimalSpecies::class)],
            'gender' => ['nullable', 'string', 'max:50'],
            'age' => ['nullable', 'integer', 'min:0', 'max:100'],
            'color' => ['nullable', 'string', 'max:50'],
            'arrivedYear' => ['nullable', 'integer', 'min:2000', 'max:'.now()->year],
            'description' => ['required', 'string', 'max:2000'],
            'favouriteTreat' => ['nullable', 'string', 'max:255'],
            'favouriteSpot' => ['nullable', 'string', 'max:255'],
            'personality' => ['nullable', 'string', 'max:255'],
            'funFacts' => ['nullable', 'string', 'max:1000'],
            'imagePath' => ['nullable', 'string', 'max:255'],
        ]);

        Animal::updateOrCreate(
            ['id' => $this->editingId],
            [
                'name' => $validated['name'],
                'species' => $validated['species'],
                'gender' => $this->gender ?: null,
                'age' => $this->age,
                'color' => $this->color ?: null,
                'arrived_year' => $this->arrivedYear,
                'description' => $validated['description'],
                'favourite_treat' => $this->favouriteTreat ?: null,
                'favourite_spot' => $this->favouriteSpot ?: null,
                'personality' => $this->toList($this->personality),
                'fun_facts' => $this->toList($this->funFacts),
                'image_path' => $this->imagePath ?: null,
                'is_active' => $this->isActive,
            ]
        );

        $this->dispatch('close-animal-form');
    }

    public function toggleActive(int $id): void
    {
        $this->ensureStaff();

        $animal = Animal::findOrFail($id);
        $animal->update(['is_active' => ! $animal->is_active]);
    }

    public function delete(int $id): void
    {
        $this->ensureStaff();

        Animal::findOrFail($id)->delete();
    }

    /** Split a comma-separated string into a clean list (or null when empty). */
    private function toList(string $value): ?array
    {
        $items = array_values(array_filter(array_map('trim', explode(',', $value)), fn ($v) => $v !== ''));

        return $items ?: null;
    }

    public function with(): array
    {
        return [
            'animals' => Animal::orderBy('name')->paginate(15),
            'speciesOptions' => AnimalSpecies::cases(),
            'totalCount' => Animal::count(),
            'activeCount' => Animal::active()->count(),
        ];
    }
}; ?>

<x-slot name="header">
    <div class="flex items-center justify-between gap-3">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Animals</h2>
            <p class="text-xs text-gray-400 mt-0.5">Manage the resident foxes customers meet on the site.</p>
        </div>
        <flux:button wire:click="openCreate" variant="primary" size="sm" icon="plus">Add fox</flux:button>
    </div>
</x-slot>

@php
    $hiddenCount = $totalCount - $activeCount;

    $foxIcon = 'M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z';
    $eyeIcon = 'M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z';
    $eyeOffIcon = 'M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88';
@endphp

<div class="py-8 px-4 sm:px-6 lg:px-8"
     x-data="{ open: false }"
     x-on:open-animal-form.window="open = true; $nextTick(() => $refs.nameInput?.focus())"
     x-on:close-animal-form.window="open = false"
     x-on:keydown.escape.window="open = false">

    <div class="max-w-4xl">
        @if ($animals->isEmpty())
            <div class="rounded-2xl bg-white border border-gray-100 shadow-xs text-center py-16 px-4">
                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 text-amber-500">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $foxIcon }}" />
                    </svg>
                </span>
                <p class="mt-4 text-lg text-gray-500">No animals yet.</p>
                <p class="mt-1 text-sm text-gray-400">Add your first fox so customers can get to know them.</p>
                <flux:button wire:click="openCreate" variant="primary" size="sm" icon="plus" class="mt-5">Add the first fox</flux:button>
            </div>
        @else
            {{-- Overview --}}
            <div class="mb-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
                <x-admin.stat-card label="Foxes" :value="$totalCount" sub="Total profiles" tint="bg-amber-50 text-amber-600" :icon="$foxIcon" />
                <x-admin.stat-card label="Active" :value="$activeCount" sub="Shown to customers" tint="bg-green-50 text-green-600" :icon="$eyeIcon" />
                <x-admin.stat-card label="Hidden" :value="$hiddenCount" sub="Not yet published" tint="bg-gray-100 text-gray-500" :icon="$eyeOffIcon" />
            </div>

            <div class="bg-white rounded-2xl shadow-xs border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100 text-left text-xs uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-5 py-3"><span class="sr-only">Image</span></th>
                            <th class="px-4 py-3 font-semibold">Name</th>
                            <th class="px-4 py-3 font-semibold">Colour</th>
                            <th class="px-4 py-3 font-semibold">Status</th>
                            <th class="px-4 py-3"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($animals as $animal)
                            <tr wire:key="animal-{{ $animal->id }}" class="group hover:bg-amber-50/40 transition-colors">
                                <td class="px-5 py-3">
                                    @if ($animal->image_path)
                                        <img src="{{ $animal->imageUrl() }}" alt="{{ $animal->name }}" loading="lazy"
                                             class="h-10 w-10 rounded-lg object-cover border border-gray-100" />
                                    @else
                                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-50 text-amber-400 border border-amber-100" aria-hidden="true">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $foxIcon }}" />
                                            </svg>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $animal->name }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $animal->color ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    @if ($animal->is_active)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-green-50 text-green-700 border border-green-100 text-xs font-medium px-2.5 py-0.5">
                                            <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span> Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 text-gray-500 border border-gray-200 text-xs font-medium px-2.5 py-0.5">
                                            <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span> Hidden
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-1 sm:opacity-60 sm:group-hover:opacity-100 transition-opacity">
                                        <button wire:click="toggleActive({{ $animal->id }})" wire:loading.attr="disabled" wire:target="toggleActive({{ $animal->id }})"
                                                type="button" title="{{ $animal->is_active ? 'Hide' : 'Show' }}"
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 hover:bg-amber-50 hover:text-amber-600 transition disabled:opacity-50 disabled:cursor-wait">
                                            <span class="sr-only">{{ $animal->is_active ? 'Hide' : 'Show' }} {{ $animal->name }}</span>
                                            <svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $animal->is_active ? $eyeOffIcon : $eyeIcon }}" />
                                            </svg>
                                        </button>
                                        <button wire:click="openEdit({{ $animal->id }})" type="button" title="Edit"
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 hover:bg-amber-50 hover:text-amber-600 transition">
                                            <span class="sr-only">Edit {{ $animal->name }}</span>
                                            <svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                                            </svg>
                                        </button>
                                        <button wire:click="delete({{ $animal->id }})" wire:confirm="Delete this fox? This can't be undone." type="button" title="Delete"
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 hover:bg-red-50 hover:text-red-600 transition">
                                            <span class="sr-only">Delete {{ $animal->name }}</span>
                                            <svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.16-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.04-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            </div>

            <div class="mt-6">{{ $animals->links() }}</div>
        @endif

        {{-- Create/Edit modal --}}
        <div x-show="open" x-cloak x-transition.opacity class="fixed inset-0 bg-black/50 z-40" x-on:click="open = false"></div>
        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-start justify-center p-4 overflow-y-auto" x-on:click="open = false">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl my-10 p-6"
                 x-on:click.stop
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 role="dialog" aria-modal="true">
                <div class="flex items-center gap-3 mb-5">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $foxIcon }}" />
                        </svg>
                    </span>
                    <h3 class="text-lg font-bold text-gray-900">{{ $editingId ? 'Edit fox' : 'Add fox' }}</h3>
                </div>

                <form wire:submit="save" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <flux:field>
                            <flux:label>Name</flux:label>
                            <flux:input wire:model="name" type="text" x-ref="nameInput" autocomplete="off" />
                            <flux:error name="name" />
                        </flux:field>
                        <flux:field>
                            <flux:label>Species</flux:label>
                            <flux:select wire:model="species">
                                @foreach ($speciesOptions as $option)
                                    <flux:select.option value="{{ $option->value }}">{{ ucfirst($option->value) }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:error name="species" />
                        </flux:field>
                        <flux:field>
                            <flux:label>Gender <span class="text-gray-400 font-normal">(optional)</span></flux:label>
                            <flux:input wire:model="gender" type="text" />
                            <flux:error name="gender" />
                        </flux:field>
                        <flux:field>
                            <flux:label>Colour <span class="text-gray-400 font-normal">(optional)</span></flux:label>
                            <flux:input wire:model="color" type="text" />
                            <flux:error name="color" />
                        </flux:field>
                        <flux:field>
                            <flux:label>Age <span class="text-gray-400 font-normal">(optional)</span></flux:label>
                            <flux:input wire:model="age" type="number" min="0" max="100" />
                            <flux:error name="age" />
                        </flux:field>
                        <flux:field>
                            <flux:label>At cafe since <span class="text-gray-400 font-normal">(year, optional)</span></flux:label>
                            <flux:input wire:model="arrivedYear" type="number" min="2000" max="{{ now()->year }}" />
                            <flux:error name="arrivedYear" />
                        </flux:field>
                    </div>

                    <flux:field>
                        <flux:label>Description</flux:label>
                        <flux:textarea wire:model="description" rows="3" />
                        <flux:error name="description" />
                    </flux:field>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <flux:field>
                            <flux:label>Favourite treat <span class="text-gray-400 font-normal">(optional)</span></flux:label>
                            <flux:input wire:model="favouriteTreat" type="text" />
                            <flux:error name="favouriteTreat" />
                        </flux:field>
                        <flux:field>
                            <flux:label>Favourite spot <span class="text-gray-400 font-normal">(optional)</span></flux:label>
                            <flux:input wire:model="favouriteSpot" type="text" />
                            <flux:error name="favouriteSpot" />
                        </flux:field>
                    </div>

                    <flux:field>
                        <flux:label>Personality <span class="text-gray-400 font-normal">(comma-separated)</span></flux:label>
                        <flux:input wire:model="personality" type="text" placeholder="Gentle, Sleepy, Affectionate" />
                        <flux:error name="personality" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Fun facts <span class="text-gray-400 font-normal">(comma-separated)</span></flux:label>
                        <flux:input wire:model="funFacts" type="text" placeholder="Rescued as a kit, Sleeps 14 hours" />
                        <flux:error name="funFacts" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Image URL or filename <span class="text-gray-400 font-normal">(optional)</span></flux:label>
                        <flux:input wire:model.live.debounce.500ms="imagePath" type="text" placeholder="https://… or kiku.jpg" />
                        <flux:error name="imagePath" />
                        @if ($this->previewUrl)
                            <img src="{{ $this->previewUrl }}" alt="Preview"
                                 class="mt-2 h-24 w-24 rounded-xl object-cover border border-gray-100" />
                        @endif
                    </flux:field>

                    <flux:checkbox wire:model="isActive" label="Active (shown to customers)" />

                    <div class="flex justify-end gap-2 pt-2">
                        <flux:button type="button" variant="ghost" x-on:click="open = false">Cancel</flux:button>
                        <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="save">
                            <span wire:loading.remove wire:target="save">{{ $editingId ? 'Save changes' : 'Add fox' }}</span>
                            <span wire:loading wire:target="save">Saving…</span>
                        </flux:button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
