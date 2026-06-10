<?php

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;

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
        ->assertSee('#'.$order->id);
});
