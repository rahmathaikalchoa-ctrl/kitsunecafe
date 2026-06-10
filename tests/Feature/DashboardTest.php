<?php

use App\Enums\OrderStatus;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Services\CartService;
use Livewire\Volt\Volt;

// The dashboard caches the spotlight fox and popular items under global keys;
// RefreshDatabase rolls back rows but not the cache, so clear it between tests.
beforeEach(fn () => cache()->flush());

test('guest is redirected to login from the dashboard', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

test('authenticated user can view the dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('dashboard'))->assertOk();
});

test('dashboard shows the latest order with its status', function () {
    $user = User::factory()->create();
    $item = MenuItem::factory()->create(['name' => 'Fox Ramen']);

    $order = Order::factory()->create([
        'user_id' => $user->id,
        'status' => OrderStatus::Confirmed->value,
        'total_cents' => 4000,
    ]);
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'menu_item_id' => $item->id,
        'quantity' => 2,
        'price_cents' => $item->price_cents,
    ]);

    $this->actingAs($user)->get(route('dashboard'))
        ->assertSee('#'.$order->id)
        ->assertSee('Confirmed')
        ->assertSee('Fox Ramen');
});

test('order again adds the last order items to the cart and redirects', function () {
    $user = User::factory()->create();
    $item = MenuItem::factory()->create();

    $order = Order::factory()->create(['user_id' => $user->id]);
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'menu_item_id' => $item->id,
        'quantity' => 2,
        'price_cents' => $item->price_cents,
    ]);

    $this->actingAs($user);

    Volt::test('pages.dashboard')
        ->call('orderAgain')
        ->assertRedirect(route('cart.index'));

    expect(app(CartService::class)->count())->toBe(2);
});

test('order again warns and does not redirect when nothing is available', function () {
    $user = User::factory()->create();
    $item = MenuItem::factory()->unavailable()->create();

    $order = Order::factory()->create(['user_id' => $user->id]);
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'menu_item_id' => $item->id,
        'quantity' => 1,
        'price_cents' => $item->price_cents,
    ]);

    $this->actingAs($user);

    Volt::test('pages.dashboard')
        ->call('orderAgain')
        ->assertNoRedirect()
        ->assertDispatched('cart-unavailable');

    expect(app(CartService::class)->isEmpty())->toBeTrue();
});

test('popular items ignore cancelled orders', function () {
    $valid = MenuItem::factory()->create(['name' => 'Valid Popular Dish']);
    $cancelledOnly = MenuItem::factory()->create(['name' => 'Cancelled Only Dish']);

    $buyer = User::factory()->create();

    $confirmed = Order::factory()->create([
        'user_id' => $buyer->id,
        'status' => OrderStatus::Confirmed->value,
    ]);
    OrderItem::factory()->create([
        'order_id' => $confirmed->id,
        'menu_item_id' => $valid->id,
        'quantity' => 2,
        'price_cents' => $valid->price_cents,
    ]);

    $cancelled = Order::factory()->create([
        'user_id' => $buyer->id,
        'status' => OrderStatus::Cancelled->value,
    ]);
    OrderItem::factory()->create([
        'order_id' => $cancelled->id,
        'menu_item_id' => $cancelledOnly->id,
        'quantity' => 10,
        'price_cents' => $cancelledOnly->price_cents,
    ]);

    // A fresh viewer with no orders of their own, so the item names can only
    // come from the "Loved by other guests" section.
    $viewer = User::factory()->create();

    $this->actingAs($viewer)->get(route('dashboard'))
        ->assertSee('Valid Popular Dish')
        ->assertDontSee('Cancelled Only Dish');
});
