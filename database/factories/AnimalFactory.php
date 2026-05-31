<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AnimalSpecies;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnimalFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->firstName(),
            'species' => AnimalSpecies::Fox->value,
            'description' => $this->faker->paragraph(3),
            'image_path' => null,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
