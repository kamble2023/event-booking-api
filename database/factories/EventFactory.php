<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph,
            'date' => $this->faker->dateTimeBetween('+1 week', '+1 year')->format('Y-m-d'),
            'country' => $this->faker->country,
            'capacity' => $this->faker->numberBetween(10, 100),
        ];
    }
}
