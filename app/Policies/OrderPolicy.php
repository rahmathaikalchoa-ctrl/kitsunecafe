<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /** Customers may view their own orders; staff may view any order. */
    public function view(User $user, Order $order): bool
    {
        return $order->user_id === $user->id || $user->is_staff;
    }

    /** Only staff advance an order's status. */
    public function manage(User $user, Order $order): bool
    {
        return $user->is_staff;
    }

    /** Only staff may see the all-orders management list. */
    public function viewAny(User $user): bool
    {
        return $user->is_staff;
    }
}
