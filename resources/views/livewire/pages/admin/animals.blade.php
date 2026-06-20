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
        ];
    }
}; ?>

<x-slot name="header">
    <div class="flex items-center justify-between gap-3">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Animals</h2>
        <flux:button wire:click="openCreate" variant="primary" size="sm">Add fox</flux:button>
    </div>
</x-slot>

<div class="py-8 px-4 sm:px-6 lg:px-8"
     x-data="{ open: false }"
     x-on:open-animal-form.window="open = true"
     x-on:close-animal-form.window="open = false"
     x-on:keydown.escape.window="open = false">

    <div class="max-w-4xl">
        @if ($animals->isEmpty())
            <div class="text-center py-16 text-gray-400">
                <p class="text-lg">No animals yet.</p>
                <button wire:click="openCreate" class="mt-3 text-sm font-medium text-amber-600 hover:underline">Add the first fox</button>
            </div>
        @else
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
                            <tr wire:key="animal-{{ $animal->id }}" class="hover:bg-amber-50/40 transition-colors">
                                <td class="px-5 py-3">
                                    @if ($animal->image_path)
                                        <img src="{{ $animal->imageUrl() }}" alt="{{ $animal->name }}" loading="lazy"
                                             class="h-10 w-10 rounded-lg object-cover border border-gray-100" />
                                    @else
                                        <div class="h-10 w-10 rounded-lg bg-gray-100 border border-gray-200" aria-hidden="true"></div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $animal->name }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $animal->color ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    @if ($animal->is_active)
                                        <span class="inline-flex items-center rounded-full bg-green-50 text-green-700 border border-green-100 text-xs font-medium px-2.5 py-0.5">Active</span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-gray-100 text-gray-500 border border-gray-200 text-xs font-medium px-2.5 py-0.5">Hidden</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-3 text-sm">
                                        <button wire:click="toggleActive({{ $animal->id }})" class="font-medium text-gray-500 hover:text-amber-600 transition">
                                            {{ $animal->is_active ? 'Hide' : 'Show' }}
                                        </button>
                                        <button wire:click="openEdit({{ $animal->id }})" class="font-medium text-amber-600 hover:underline">Edit</button>
                                        <button wire:click="delete({{ $animal->id }})" wire:confirm="Delete this fox? This can't be undone." class="font-medium text-gray-400 hover:text-red-500 transition">Delete</button>
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
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl my-10 p-6" x-on:click.stop>
                <h3 class="text-lg font-bold text-gray-900 mb-4">{{ $editingId ? 'Edit fox' : 'Add fox' }}</h3>

                <form wire:submit="save" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <flux:field>
                            <flux:label>Name</flux:label>
                            <flux:input wire:model="name" type="text" />
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
                            {{ $editingId ? 'Save changes' : 'Add fox' }}
                        </flux:button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
