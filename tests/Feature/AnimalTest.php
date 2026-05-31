<?php

use App\Models\Animal;

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

    $this->get(route('animals.show', $animal))->assertOk();
});
