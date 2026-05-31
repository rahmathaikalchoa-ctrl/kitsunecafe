<?php

use App\Enums\OrderStatus;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Review;
use App\Models\User;
use Livewire\Volt\Volt;

function makeOrderedItem(User $user, ?MenuItem $item = null): MenuItem
{
    $category = Category::factory()->create();
    $item ??= MenuItem::factory()->create(['category_id' => $category->id]);

    $order = Order::factory()->create([
        'user_id' => $user->id,
        'status' => OrderStatus::Pending,
        'total_cents' => $item->price_cents,
    ]);

    OrderItem::factory()->create([
        'order_id' => $order->id,
        'menu_item_id' => $item->id,
        'quantity' => 1,
        'price_cents' => $item->price_cents,
    ]);

    return $item;
}

test('menu page renders', function () {
    $this->get(route('menu.index'))->assertOk();
});

test('guest sees login prompt in review section after opening item', function () {
    $category = Category::factory()->create();
    $item = MenuItem::factory()->create(['category_id' => $category->id]);

    Volt::test('pages.menu.index')
        ->call('openItem', $item->id)
        ->assertSet('selectedItemId', $item->id);
});

test('user without an order cannot submit a review', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    $item = MenuItem::factory()->create(['category_id' => $category->id]);

    $this->actingAs($user);

    Volt::test('pages.menu.index')
        ->call('openItem', $item->id)
        ->call('submitReview')
        ->assertHasNoErrors();

    expect(Review::where('user_id', $user->id)->exists())->toBeFalse();
});

test('user with a non-cancelled order can submit a review', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $item = makeOrderedItem($user);

    Volt::test('pages.menu.index')
        ->call('openItem', $item->id)
        ->set('reviewRating', 4)
        ->set('reviewComment', 'Great food!')
        ->call('submitReview')
        ->assertHasNoErrors();

    expect(Review::where('user_id', $user->id)->where('menu_item_id', $item->id)->exists())->toBeTrue();
});

test('review is saved with correct rating and comment', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $item = makeOrderedItem($user);

    Volt::test('pages.menu.index')
        ->call('openItem', $item->id)
        ->set('reviewRating', 5)
        ->set('reviewComment', 'Absolutely loved it.')
        ->call('submitReview');

    $review = Review::where('user_id', $user->id)->where('menu_item_id', $item->id)->first();
    expect($review->rating)->toBe(5);
    expect($review->comment)->toBe('Absolutely loved it.');
});

test('user can update their own review', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $item = makeOrderedItem($user);

    Review::create(['user_id' => $user->id, 'menu_item_id' => $item->id, 'rating' => 3, 'comment' => 'OK']);

    Volt::test('pages.menu.index')
        ->call('openItem', $item->id)
        ->set('reviewRating', 5)
        ->set('reviewComment', 'Changed my mind, amazing!')
        ->call('submitReview');

    $review = Review::where('user_id', $user->id)->where('menu_item_id', $item->id)->first();
    expect($review->rating)->toBe(5);
    expect($review->comment)->toBe('Changed my mind, amazing!');
    expect(Review::where('user_id', $user->id)->where('menu_item_id', $item->id)->count())->toBe(1);
});

test('user can delete their own review', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $item = makeOrderedItem($user);
    Review::create(['user_id' => $user->id, 'menu_item_id' => $item->id, 'rating' => 4]);

    Volt::test('pages.menu.index')
        ->call('openItem', $item->id)
        ->call('deleteReview');

    expect(Review::where('user_id', $user->id)->where('menu_item_id', $item->id)->exists())->toBeFalse();
});

test('user cannot delete another users review', function () {
    $owner = User::factory()->create();
    $attacker = User::factory()->create();

    $category = Category::factory()->create();
    $item = MenuItem::factory()->create(['category_id' => $category->id]);

    Review::create(['user_id' => $owner->id, 'menu_item_id' => $item->id, 'rating' => 5]);

    $this->actingAs($attacker);

    Volt::test('pages.menu.index')
        ->call('openItem', $item->id)
        ->call('deleteReview');

    expect(Review::where('user_id', $owner->id)->where('menu_item_id', $item->id)->exists())->toBeTrue();
});
