<?php

use App\Models\Animal;
use Livewire\Volt\Volt;

test('animals page renders', function () {
    $this->get(route('animals.index'))->assertOk();
});

test('animals page shows active animals', function () {
    $animal = Animal::factory()->create(['name' => 'Kiku']);

    $this->get(route('animals.index'))->assertSee('Kiku');
});

test('animals page does not show inactive animals', function () {
    $animal = Animal::factory()->inactive()->create(['name' => 'Hidden Fox']);

    $this->get(route('animals.index'))->assertDontSee('Hidden Fox');
});

test('animal show page renders', function () {
    $animal = Animal::factory()->create(['name' => 'Taiyo']);

    $this->get(route('animals.show', $animal))->assertOk()->assertSee('Taiyo');
});

test('inactive animal show page returns 404', function () {
    $animal = Animal::factory()->inactive()->create();

    $this->get(route('animals.show', $animal))->assertNotFound();
});

test('listing search filters foxes by name', function () {
    Animal::factory()->create(['name' => 'Kiku']);
    Animal::factory()->create(['name' => 'Taiyo']);

    Volt::test('pages.animals.index')
        ->set('search', 'Kik')
        ->assertSee('Kiku')
        ->assertDontSee('Taiyo');
});

test('listing colour filter shows only matching foxes', function () {
    Animal::factory()->create(['name' => 'Hoshi', 'color' => 'Silver']);
    Animal::factory()->create(['name' => 'Kiku', 'color' => 'Red']);

    Volt::test('pages.animals.index')
        ->set('color', 'Silver')
        ->assertSee('Hoshi')
        ->assertDontSee('Kiku');
});

test('opening a fox sets the selected animal', function () {
    $animal = Animal::factory()->create(['name' => 'Sora']);

    Volt::test('pages.animals.index')
        ->call('openFox', $animal->id)
        ->assertSet('selectedAnimalId', $animal->id);
});

test('profile page shows the fox catalog details', function () {
    $animal = Animal::factory()->create([
        'name' => 'Kiku',
        'personality' => ['Gentle', 'Sleepy'],
        'fun_facts' => ['Was rescued as a tiny kit'],
        'favourite_treat' => 'Fox Ears Chips',
    ]);

    $this->get(route('animals.show', $animal))
        ->assertOk()
        ->assertSee('Gentle')
        ->assertSee('Was rescued as a tiny kit')
        ->assertSee('Fox Ears Chips');
});

test('profile page links to the next fox', function () {
    Animal::factory()->create(['name' => 'Aki']);
    $yuki = Animal::factory()->create(['name' => 'Yuki']);

    $this->get(route('animals.show', Animal::where('name', 'Aki')->first()))
        ->assertOk()
        ->assertSee('Yuki');
});
