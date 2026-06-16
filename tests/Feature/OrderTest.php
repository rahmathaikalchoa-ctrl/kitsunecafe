<?php

use App\Enums\OrderStatus;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Services\CartService;
use Livewire\Volt\Volt;

test('guest is redirected to login from orders', function () {
    $this->get(route('orders.index'))->assertRedirect(route('login'));
});

test('orders index lists only the current user orders', function () {
    $me = User::factory()->create();
    $someoneElse = User::factory()->create();

    $mine = Order::factory()->create(['user_id' => $me->id]);
    $theirs = Order::factory()->create(['user_id' => $someoneElse->id]);

    $this->actingAs($me)->get(route('orders.index'))
        ->assertOk()
        ->assertSee(route('orders.show', $mine))
        ->assertDontSee(route('orders.show', $theirs));
});

test('a user cannot view another users order', function () {
    $me = User::factory()->create();
    $someoneElse = User::factory()->create();

    $theirs = Order::factory()->create(['user_id' => $someoneElse->id]);

    $this->actingAs($me)->get(route('orders.show', $theirs))->assertForbidden();
});

test('a user can view their own order detail', function () {
    $me = User::factory()->create();
    $item = MenuItem::factory()->create(['name' => 'Kitsune Curry Rice']);

    $order = Order::factory()->create(['user_id' => $me->id, 'total_cents' => 30000]);
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'menu_item_id' => $item->id,
        'quantity' => 1,
        'price_cents' => $item->price_cents,
    ]);

    $this->actingAs($me)->get(route('orders.show', $order))
        ->assertOk()
        ->assertSee('Kitsune Curry Rice')
        ->assertSee($order->reference);
});

test('a user can cancel their own pending order', function () {
    $me = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $me->id,
        'status' => OrderStatus::Pending->value,
    ]);

    $this->actingAs($me);

    Volt::test('pages.orders.show', ['order' => $order])
        ->call('cancel');

    expect($order->fresh()->status)->toBe(OrderStatus::Cancelled);
});

test('cancelling a non-pending order is a no-op', function () {
    $me = User::factory()->create();
    $order = Order::factory()->create([
        'user_id' => $me->id,
        'status' => OrderStatus::Completed->value,
    ]);

    $this->actingAs($me);

    Volt::test('pages.orders.show', ['order' => $order])
        ->call('cancel');

    expect($order->fresh()->status)->toBe(OrderStatus::Completed);
});

test('order again skips unavailable items, flashes a notice and redirects to the cart', function () {
    $me = User::factory()->create();
    $available = MenuItem::factory()->create(['price_cents' => 20000]);
    $gone = MenuItem::factory()->unavailable()->create();

    $order = Order::factory()->create(['user_id' => $me->id]);
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'menu_item_id' => $available->id,
        'quantity' => 2,
        'price_cents' => $available->price_cents,
    ]);
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'menu_item_id' => $gone->id,
        'quantity' => 1,
        'price_cents' => $gone->price_cents,
    ]);

    $this->actingAs($me);

    Volt::test('pages.orders.show', ['order' => $order])
        ->call('orderAgain')
        ->assertRedirect(route('cart.index'));

    expect(session('reorder_skipped'))->toBe(1);
    expect(app(CartService::class)->count())->toBe(2);
});

test('orders index filters by status', function () {
    $me = User::factory()->create();
    $pending = Order::factory()->create(['user_id' => $me->id, 'status' => OrderStatus::Pending->value]);
    $completed = Order::factory()->create(['user_id' => $me->id, 'status' => OrderStatus::Completed->value]);

    $this->actingAs($me)->get(route('orders.index', ['status' => 'completed']))
        ->assertOk()
        ->assertSee(route('orders.show', $completed))
        ->assertDontSee(route('orders.show', $pending));
});

test('orders index ignores an invalid status filter', function () {
    $me = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $me->id, 'status' => OrderStatus::Pending->value]);

    $this->actingAs($me)->get(route('orders.index', ['status' => 'banana']))
        ->assertOk()
        ->assertSee(route('orders.show', $order));
});
