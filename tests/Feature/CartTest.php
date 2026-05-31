<?php

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\User;
use App\Services\CartService;

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
