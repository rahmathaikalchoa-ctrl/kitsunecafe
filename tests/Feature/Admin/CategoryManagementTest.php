<?php

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\User;
use Livewire\Volt\Volt;

beforeEach(function () {
    $this->actingAs(User::factory()->staff()->create());
});

test('staff can create a category', function () {
    Volt::test('pages.admin.categories')
        ->call('openCreate')
        ->set('name', 'Pastries')
        ->call('save')
        ->assertHasNoErrors();

    expect(Category::where('name', 'Pastries')->exists())->toBeTrue();
});

test('category names must be unique', function () {
    Category::factory()->create(['name' => 'Drinks']);

    Volt::test('pages.admin.categories')
        ->call('openCreate')
        ->set('name', 'Drinks')
        ->call('save')
        ->assertHasErrors('name');
});

test('staff can rename a category', function () {
    $category = Category::factory()->create(['name' => 'Snacks']);

    Volt::test('pages.admin.categories')
        ->call('openEdit', $category->id)
        ->set('name', 'Light Bites')
        ->call('save')
        ->assertHasNoErrors();

    expect($category->fresh()->name)->toBe('Light Bites');
});

test('staff can delete an empty category', function () {
    $category = Category::factory()->create();

    Volt::test('pages.admin.categories')->call('delete', $category->id);

    expect(Category::find($category->id))->toBeNull();
});

test('deleting a category that still has items is blocked', function () {
    $category = Category::factory()->create();
    MenuItem::factory()->create(['category_id' => $category->id]);

    Volt::test('pages.admin.categories')
        ->call('delete', $category->id)
        ->assertHasErrors('delete');

    expect(Category::find($category->id))->not->toBeNull();
});
