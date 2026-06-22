<?php

declare(strict_types=1);

use App\Models\Order;
use App\Models\User;

test('deleting an account preserves order history but detaches the customer', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $user->id]);

    $user->delete();

    expect(Order::find($order->id))->not->toBeNull()
        ->and($order->fresh()->user_id)->toBeNull();
});
