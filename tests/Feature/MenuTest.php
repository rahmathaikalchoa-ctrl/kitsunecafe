<?php

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\User;
use App\Services\CartService;
use Livewire\Volt\Volt;

test('menu page renders', function () {
    $this->get(route('menu.index'))->assertOk();
});

test('can build more than four categories via the factory', function () {
    // Guards against the old 4-value unique() pool, which threw OverflowException past four.
    Category::factory()->count(6)->create();

    expect(Category::count())->toBe(6);
});

test('menu shows available items', function () {
    $category = Category::factory()->create();
    $item = MenuItem::factory()->create(['category_id' => $category->id]);

    $this->get(route('menu.index'))->assertSee($item->name);
});

test('menu does not show unavailable items', function () {
    $category = Category::factory()->create();
    $item = MenuItem::factory()->unavailable()->create(['category_id' => $category->id]);

    $this->get(route('menu.index'))->assertDontSee($item->name);
});

test('category filter shows only items in that category', function () {
    $catA = Category::factory()->create(['name' => 'Food']);
    $catB = Category::factory()->create(['name' => 'Drinks']);
    $itemA = MenuItem::factory()->create(['category_id' => $catA->id, 'name' => 'Fox Ramen']);
    $itemB = MenuItem::factory()->create(['category_id' => $catB->id, 'name' => 'Matcha Latte']);

    Volt::test('pages.menu.index')
        ->set('categoryId', $catA->id)
        ->assertSee('Fox Ramen')
        ->assertDontSee('Matcha Latte');
});

test('guest sees login-required event when adding to cart', function () {
    $category = Category::factory()->create();
    $item = MenuItem::factory()->create(['category_id' => $category->id]);

    Volt::test('pages.menu.index')
        ->call('addToCart', $item->id)
        ->assertDispatched('login-required');
});

test('authenticated user can add item to cart', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    $item = MenuItem::factory()->create(['category_id' => $category->id]);

    $this->actingAs($user);

    Volt::test('pages.menu.index')
        ->call('addToCart', $item->id)
        ->assertDispatched('cart-updated');

    expect(app(CartService::class)->count())->toBe(1);
});
