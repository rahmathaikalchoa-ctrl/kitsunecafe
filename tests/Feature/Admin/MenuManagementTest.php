<?php

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Livewire\Volt\Volt;

beforeEach(function () {
    $this->actingAs(User::factory()->staff()->create());
});

test('staff can search the menu by name', function () {
    $match = MenuItem::factory()->create(['name' => 'Fox Ramen']);
    $other = MenuItem::factory()->create(['name' => 'Berry Parfait']);

    Volt::test('pages.admin.menu')
        ->set('search', 'Ramen')
        ->assertSee('Fox Ramen')
        ->assertDontSee('Berry Parfait');
});

test('staff can filter the menu by category', function () {
    $drinks = Category::factory()->create();
    $food = Category::factory()->create();
    $latte = MenuItem::factory()->create(['name' => 'Fox Latte', 'category_id' => $drinks->id]);
    $ramen = MenuItem::factory()->create(['name' => 'Fox Ramen', 'category_id' => $food->id]);

    Volt::test('pages.admin.menu')
        ->set('categoryFilter', $drinks->id)
        ->assertSee('Fox Latte')
        ->assertDontSee('Fox Ramen');
});

test('staff can filter the menu by availability', function () {
    $available = MenuItem::factory()->create(['name' => 'Fox Latte', 'is_available' => true]);
    $hidden = MenuItem::factory()->create(['name' => 'Secret Special', 'is_available' => false]);

    Volt::test('pages.admin.menu')
        ->set('availability', 'hidden')
        ->assertSee('Secret Special')
        ->assertDontSee('Fox Latte');
});

test('staff can create a menu item', function () {
    $category = Category::factory()->create();

    Volt::test('pages.admin.menu')
        ->call('openCreate')
        ->set('name', 'Fox Parfait')
        ->set('categoryId', $category->id)
        ->set('priceCents', 25000)
        ->set('description', 'Layered yogurt and berries.')
        ->call('save')
        ->assertHasNoErrors();

    expect(MenuItem::where('name', 'Fox Parfait')->where('price_cents', 25000)->exists())->toBeTrue();
});

test('creating a menu item requires name, category and price', function () {
    Volt::test('pages.admin.menu')
        ->call('openCreate')
        ->call('save')
        ->assertHasErrors(['name', 'categoryId', 'priceCents']);
});

test('staff can update a menu item', function () {
    $item = MenuItem::factory()->create(['price_cents' => 20000]);

    Volt::test('pages.admin.menu')
        ->call('openEdit', $item->id)
        ->set('priceCents', 31000)
        ->call('save')
        ->assertHasNoErrors();

    expect($item->fresh()->price_cents)->toBe(31000);
});

test('staff can toggle item availability', function () {
    $item = MenuItem::factory()->create(['is_available' => true]);

    Volt::test('pages.admin.menu')->call('toggleAvailable', $item->id);

    expect($item->fresh()->is_available)->toBeFalse();
});

test('staff can delete a menu item with no orders', function () {
    $item = MenuItem::factory()->create();

    Volt::test('pages.admin.menu')->call('delete', $item->id);

    expect(MenuItem::find($item->id))->toBeNull();
});

test('deleting a menu item that has orders is blocked', function () {
    $item = MenuItem::factory()->create();
    $order = Order::factory()->create();
    OrderItem::factory()->create([
        'order_id' => $order->id,
        'menu_item_id' => $item->id,
        'quantity' => 1,
        'price_cents' => $item->price_cents,
    ]);

    Volt::test('pages.admin.menu')
        ->call('delete', $item->id)
        ->assertHasErrors('delete');

    expect(MenuItem::find($item->id))->not->toBeNull();
});
