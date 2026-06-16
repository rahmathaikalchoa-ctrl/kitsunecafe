<?php

use App\Enums\OrderType;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\User;
use Livewire\Volt\Volt;

test('guest is redirected to login when accessing checkout', function () {
    $this->get(route('checkout.index'))->assertRedirect(route('login'));
});

test('authenticated user can view checkout', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('checkout.index'))->assertOk();
});

test('checkout shows prices at the correct scale (no divide-by-100)', function () {
    $user = User::factory()->create();
    $item = MenuItem::factory()->create(['price_cents' => 25000, 'name' => 'Hojicha Milk Tea']);

    $this->actingAs($user);
    session(['cart' => [$item->id => 1]]);

    $this->get(route('checkout.index'))
        ->assertSee('Rp 25.000')
        ->assertDontSee('Rp 250');
});

test('user can place an order', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    $item = MenuItem::factory()->create(['category_id' => $category->id, 'price_cents' => 1500]);

    $this->actingAs($user);
    session(['cart' => [$item->id => 2]]);

    Volt::test('pages.checkout.index')
        ->set('tableNumber', 5)
        ->call('placeOrder')
        ->assertRedirect(route('dashboard'));

    expect(Order::where('user_id', $user->id)->exists())->toBeTrue();
});

test('checkout surfaces an error when an item is no longer available', function () {
    $user = User::factory()->create();
    $item = MenuItem::factory()->unavailable()->create();

    $this->actingAs($user);
    session(['cart' => [$item->id => 1]]);

    Volt::test('pages.checkout.index')
        ->set('tableNumber', 1)
        ->call('placeOrder')
        ->assertHasErrors('cart');

    expect(Order::where('user_id', $user->id)->exists())->toBeFalse();
});

test('a dine-in order requires a table to be chosen', function () {
    $user = User::factory()->create();
    $item = MenuItem::factory()->create();

    $this->actingAs($user);
    session(['cart' => [$item->id => 1]]);

    Volt::test('pages.checkout.index')
        ->call('placeOrder')
        ->assertHasErrors('tableNumber');

    expect(Order::where('user_id', $user->id)->exists())->toBeFalse();
});

test('a dine-in order stores the chosen table', function () {
    $user = User::factory()->create();
    $item = MenuItem::factory()->create();

    $this->actingAs($user);
    session(['cart' => [$item->id => 1]]);

    Volt::test('pages.checkout.index')
        ->set('tableNumber', 7)
        ->call('placeOrder');

    $order = Order::where('user_id', $user->id)->first();
    expect($order->order_type)->toBe(OrderType::DineIn);
    expect($order->table_number)->toBe(7);
});

test('a takeaway order can be placed without a table', function () {
    $user = User::factory()->create();
    $item = MenuItem::factory()->create();

    $this->actingAs($user);
    session(['cart' => [$item->id => 1]]);

    Volt::test('pages.checkout.index')
        ->set('orderType', 'takeaway')
        ->call('placeOrder')
        ->assertRedirect(route('dashboard'));

    $order = Order::where('user_id', $user->id)->first();
    expect($order->order_type)->toBe(OrderType::Takeaway);
    expect($order->table_number)->toBeNull();
});

test('order is created with correct total', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    $item = MenuItem::factory()->create(['category_id' => $category->id, 'price_cents' => 2000]);

    $this->actingAs($user);
    session(['cart' => [$item->id => 3]]);

    Volt::test('pages.checkout.index')->set('tableNumber', 3)->call('placeOrder');

    $order = Order::where('user_id', $user->id)->first();
    expect($order->total_cents)->toBe(6000);
    expect($order->orderItems)->toHaveCount(1);
});

test('cart is cleared after order is placed', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    $item = MenuItem::factory()->create(['category_id' => $category->id]);

    $this->actingAs($user);
    session(['cart' => [$item->id => 1]]);

    Volt::test('pages.checkout.index')->set('tableNumber', 1)->call('placeOrder');

    expect(session('cart'))->toBeNull();
});

test('empty cart cannot be submitted', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Volt::test('pages.checkout.index')
        ->set('tableNumber', 1)
        ->call('placeOrder')
        ->assertHasErrors('cart');
});
