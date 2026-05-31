<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'menu_item_id' => MenuItem::factory(),
            'rating' => $this->faker->numberBetween(3, 5),
            'comment' => $this->faker->boolean(70) ? $this->faker->sentence(10) : null,
        ];
    }
}
