<?php

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\User;
use App\Services\CartService;
use Livewire\Volt\Volt;

test('guest is redirected to login when accessing cart', function () {
    $this->get(route('cart.index'))->assertRedirect(route('login'));
});

test('authenticated user can view cart', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('cart.index'))->assertOk();
});

test('cart service adds items correctly', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $category = Category::factory()->create();
    $item = MenuItem::factory()->create(['category_id' => $category->id, 'price_cents' => 1500]);

    $cart = app(CartService::class);
    $cart->add($item->id);

    expect($cart->count())->toBe(1);
    expect($cart->totalCents())->toBe(1500);
});

test('cart service removes items correctly', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $category = Category::factory()->create();
    $item = MenuItem::factory()->create(['category_id' => $category->id]);

    $cart = app(CartService::class);
    $cart->add($item->id, 2);
    $cart->remove($item->id);

    expect($cart->isEmpty())->toBeTrue();
});

test('cart service increments an item by one', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $item = MenuItem::factory()->create(['price_cents' => 1000]);

    $cart = app(CartService::class);
    $cart->add($item->id);
    $cart->increment($item->id);

    expect($cart->count())->toBe(2);
});

test('cart service decrements and removes the line at zero', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $item = MenuItem::factory()->create();

    $cart = app(CartService::class);
    $cart->add($item->id, 2);

    $cart->decrement($item->id);
    expect($cart->count())->toBe(1);

    $cart->decrement($item->id);
    expect($cart->isEmpty())->toBeTrue();
});

test('cart flags an unavailable item and warns', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    $available = MenuItem::factory()->create(['category_id' => $category->id]);
    $gone = MenuItem::factory()->unavailable()->create(['category_id' => $category->id]);

    $this->actingAs($user);
    session(['cart' => [$available->id => 1, $gone->id => 1]]);

    $this->get(route('cart.index'))
        ->assertOk()
        ->assertSee('Unavailable')
        ->assertSee('no longer available')
        ->assertSee('Increase quantity of'); // aria-label on the steppers
});

test('editing the cart dispatches cart-updated to refresh the nav badge', function () {
    $user = User::factory()->create();
    $item = MenuItem::factory()->create();

    $this->actingAs($user);
    session(['cart' => [$item->id => 2]]);

    Volt::test('pages.cart.index')
        ->call('remove', $item->id)
        ->assertDispatched('cart-updated');
});

test('the cart total excludes unavailable items', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    $available = MenuItem::factory()->create(['category_id' => $category->id, 'price_cents' => 1000]);
    $gone = MenuItem::factory()->unavailable()->create(['category_id' => $category->id, 'price_cents' => 2000]);

    $this->actingAs($user);
    session(['cart' => [$available->id => 1, $gone->id => 1]]);

    // Total reflects only the available item (Rp 1.000), not the combined Rp 3.000.
    $this->get(route('cart.index'))
        ->assertOk()
        ->assertSee('Rp 1.000')
        ->assertDontSee('Rp 3.000');
});

test('cart total is computed from line items', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $category = Category::factory()->create();
    $itemA = MenuItem::factory()->create(['category_id' => $category->id, 'price_cents' => 1000]);
    $itemB = MenuItem::factory()->create(['category_id' => $category->id, 'price_cents' => 2000]);

    $cart = app(CartService::class);
    $cart->add($itemA->id, 2);
    $cart->add($itemB->id, 1);

    expect($cart->totalCents())->toBe(4000);
});
