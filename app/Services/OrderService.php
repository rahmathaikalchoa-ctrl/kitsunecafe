<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(private readonly CartService $cart) {}

    public function placeOrder(User $user, OrderType $type, ?int $tableNumber = null, ?string $notes = null): Order
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

        // Create the order and its line items atomically — a failure mid-loop must not leave an
        // orphaned order with a total but no items.
        $order = DB::transaction(function () use ($user, $type, $tableNumber, $notes, $items): Order {
            $order = Order::create([
                'user_id' => $user->id,
                'order_type' => $type,
                // Only dine-in orders carry a table; takeaway is always null.
                'table_number' => $type->needsTable() ? $tableNumber : null,
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

            return $order;
        });

        $this->cart->clear();

        return $order;
    }
}
