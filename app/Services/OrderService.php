<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(private readonly CartService $cart) {}

    public function placeOrder(User $user, ?string $notes = null): Order
    {
        if ($this->cart->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => 'Your cart is empty.',
            ]);
        }

        $items = $this->cart->items();

        $unavailable = $items->filter(fn ($line) => ! $line->item->is_available);

        if ($unavailable->isNotEmpty()) {
            throw ValidationException::withMessages([
                'cart' => 'Some items in your cart are no longer available.',
            ]);
        }

        $order = Order::create([
            'user_id' => $user->id,
            'status' => OrderStatus::Pending,
            'notes' => $notes,
            'total_cents' => $this->cart->totalCents(),
        ]);

        foreach ($items as $line) {
            $order->orderItems()->create([
                'menu_item_id' => $line->item->id,
                'quantity' => $line->quantity,
                'price_cents' => $line->item->price_cents,
            ]);
        }

        $this->cart->clear();

        return $order;
    }
}
