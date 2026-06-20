<?php

use App\Models\Animal;
use App\Models\User;
use Livewire\Volt\Volt;

beforeEach(function () {
    $this->actingAs(User::factory()->staff()->create());
});

test('a non-staff user cannot mutate animals', function () {
    $user = User::factory()->create(); // not staff
    $animal = Animal::factory()->create();

    $this->actingAs($user);

    Volt::test('pages.admin.animals')
        ->call('delete', $animal->id)
        ->assertForbidden();

    expect(Animal::find($animal->id))->not->toBeNull();
});

test('validation errors use friendly attribute names', function () {
    Volt::test('pages.admin.animals')
        ->call('openCreate')
        ->set('name', 'Aki')
        ->set('description', 'A curious young fox.')
        ->set('arrivedYear', 1999) // below the min:2000 rule
        ->call('save')
        ->assertHasErrors('arrivedYear')
        ->assertSee('year at the cafe')
        ->assertDontSee('arrived year');
});

test('the table shows a thumbnail for an animal with an image', function () {
    $animal = Animal::factory()->create([
        'name' => 'Kiku',
        'image_path' => 'https://example.com/kiku.jpg',
    ]);

    Volt::test('pages.admin.animals')
        ->assertSee($animal->imageUrl());
});

test('staff can create a fox with comma-separated personality and fun facts', function () {
    Volt::test('pages.admin.animals')
        ->call('openCreate')
        ->set('name', 'Aki')
        ->set('description', 'A curious young fox.')
        ->set('personality', 'Gentle, Sleepy, Affectionate')
        ->set('funFacts', 'Rescued as a kit, Sleeps 14 hours')
        ->call('save')
        ->assertHasNoErrors();

    $animal = Animal::where('name', 'Aki')->first();

    expect($animal)->not->toBeNull()
        ->and($animal->personality)->toBe(['Gentle', 'Sleepy', 'Affectionate'])
        ->and($animal->fun_facts)->toBe(['Rescued as a kit', 'Sleeps 14 hours']);
});

test('creating a fox requires a name and description', function () {
    Volt::test('pages.admin.animals')
        ->call('openCreate')
        ->set('name', '')
        ->set('description', '')
        ->call('save')
        ->assertHasErrors(['name', 'description']);
});

test('staff can update a fox', function () {
    $animal = Animal::factory()->create(['color' => 'Red']);

    Volt::test('pages.admin.animals')
        ->call('openEdit', $animal->id)
        ->set('color', 'Silver')
        ->call('save')
        ->assertHasNoErrors();

    expect($animal->fresh()->color)->toBe('Silver');
});

test('staff can toggle a fox active state', function () {
    $animal = Animal::factory()->create(['is_active' => true]);

    Volt::test('pages.admin.animals')->call('toggleActive', $animal->id);

    expect($animal->fresh()->is_active)->toBeFalse();
});

test('staff can delete a fox', function () {
    $animal = Animal::factory()->create();

    Volt::test('pages.admin.animals')->call('delete', $animal->id);

    expect(Animal::find($animal->id))->toBeNull();
});
