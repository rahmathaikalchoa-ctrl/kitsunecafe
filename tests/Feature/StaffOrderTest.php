<?php

use App\Enums\OrderStatus;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Review;
use App\Models\User;
use Livewire\Volt\Volt;

test('admin order search matches the order reference', function () {
    $staff = User::factory()->staff()->create();
    $mine = Order::factory()->create();
    $other = Order::factory()->create();

    $this->actingAs($staff);

    Volt::test('pages.admin.orders')
        ->set('search', $mine->reference)
        ->assertSee(route('orders.show', $mine))
        ->assertDontSee(route('orders.show', $other));
});

test('admin order search matches the customer name', function () {
    $staff = User::factory()->staff()->create();
    $alice = User::factory()->create(['name' => 'Alice Wonderland']);
    $bob = User::factory()->create(['name' => 'Bob Builder']);
    $aliceOrder = Order::factory()->create(['user_id' => $alice->id]);
    $bobOrder = Order::factory()->create(['user_id' => $bob->id]);

    $this->actingAs($staff);

    Volt::test('pages.admin.orders')
        ->set('search', 'Alice')
        ->assertSee(route('orders.show', $aliceOrder))
        ->assertDontSee(route('orders.show', $bobOrder));
});

test('staff can confirm a pending order', function () {
    $staff = User::factory()->staff()->create();
    $order = Order::factory()->create(['status' => OrderStatus::Pending->value]);

    $this->actingAs($staff);

    Volt::test('pages.orders.show', ['order' => $order])
        ->call('confirm');

    expect($order->fresh()->status)->toBe(OrderStatus::Confirmed);
});

test('staff can complete a confirmed order', function () {
    $staff = User::factory()->staff()->create();
    $order = Order::factory()->create(['status' => OrderStatus::Confirmed->value]);

    $this->actingAs($staff);

    Volt::test('pages.orders.show', ['order' => $order])
        ->call('complete');

    expect($order->fresh()->status)->toBe(OrderStatus::Completed);
});

test('a non-staff user cannot advance an order', function () {
    $owner = User::factory()->create(); // not staff
    $order = Order::factory()->create(['user_id' => $owner->id, 'status' => OrderStatus::Pending->value]);

    $this->actingAs($owner);

    Volt::test('pages.orders.show', ['order' => $order])
        ->call('confirm')
        ->assertForbidden();

    expect($order->fresh()->status)->toBe(OrderStatus::Pending);
});

test('staff can reject a pending order', function () {
    $staff = User::factory()->staff()->create();
    $order = Order::factory()->create(['status' => OrderStatus::Pending->value]);

    $this->actingAs($staff);

    Volt::test('pages.orders.show', ['order' => $order])
        ->call('reject');

    expect($order->fresh()->status)->toBe(OrderStatus::Cancelled);
});

test('hides customer action buttons from staff viewing another customers order', function () {
    $staff = User::factory()->staff()->create();
    $order = Order::factory()->create(['status' => OrderStatus::Pending->value]); // owned by someone else

    $this->actingAs($staff);

    Volt::test('pages.orders.show', ['order' => $order])
        ->assertDontSee('Order again')
        ->assertSee('Reject order');
});

test('staff can reject an order from the staff list', function () {
    $staff = User::factory()->staff()->create();
    $order = Order::factory()->create(['status' => OrderStatus::Pending->value]);

    $this->actingAs($staff);

    Volt::test('pages.admin.orders')
        ->call('reject', $order->id);

    expect($order->fresh()->status)->toBe(OrderStatus::Cancelled);
});

test('an invalid status transition is a no-op', function () {
    $staff = User::factory()->staff()->create();
    // Pending cannot jump straight to Completed.
    $order = Order::factory()->create(['status' => OrderStatus::Pending->value]);

    $this->actingAs($staff);

    Volt::test('pages.orders.show', ['order' => $order])
        ->call('complete');

    expect($order->fresh()->status)->toBe(OrderStatus::Pending);
});

test('guest is redirected to login from the staff orders list', function () {
    $this->get(route('admin.orders'))->assertRedirect(route('login'));
});

test('a non-staff user cannot view the staff orders list', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('admin.orders'))->assertForbidden();
});

test('staff can view and advance orders from the staff list', function () {
    $staff = User::factory()->staff()->create();
    $order = Order::factory()->create(['status' => OrderStatus::Pending->value]);

    $this->actingAs($staff);

    Volt::test('pages.admin.orders')
        ->assertSee($order->reference)
        ->call('confirm', $order->id);

    expect($order->fresh()->status)->toBe(OrderStatus::Confirmed);
});

test('completing an order via staff unblocks the customer review (lifecycle end-to-end)', function () {
    $customer = User::factory()->create();
    $staff = User::factory()->staff()->create();

    $category = Category::factory()->create();
    $item = MenuItem::factory()->create(['category_id' => $category->id]);

    $order = Order::factory()->create([
        'user_id' => $customer->id,
        'status' => OrderStatus::Pending->value,
    ]);
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'menu_item_id' => $item->id,
        'quantity' => 1,
        'price_cents' => $item->price_cents,
    ]);

    // Staff moves the order Pending → Confirmed → Completed.
    $this->actingAs($staff);
    Volt::test('pages.orders.show', ['order' => $order])
        ->call('confirm')
        ->call('complete');

    expect($order->fresh()->status)->toBe(OrderStatus::Completed);

    // The customer can now leave a review (previously impossible — no order ever completed).
    $this->actingAs($customer);
    Volt::test('pages.menu.index')
        ->call('openItem', $item->id)
        ->set('reviewRating', 5)
        ->set('reviewComment', 'Delicious!')
        ->call('submitReview')
        ->assertHasNoErrors();

    expect(Review::where('user_id', $customer->id)->where('menu_item_id', $item->id)->exists())->toBeTrue();
});
