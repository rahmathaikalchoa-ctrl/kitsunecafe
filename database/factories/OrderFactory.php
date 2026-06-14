<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'order_type' => OrderType::DineIn->value,
            'table_number' => $this->faker->numberBetween(1, 12),
            'status' => OrderStatus::Pending->value,
            'notes' => null,
            'total_cents' => 0,
        ];
    }

    public function takeaway(): static
    {
        return $this->state([
            'order_type' => OrderType::Takeaway->value,
            'table_number' => null,
        ]);
    }
}
