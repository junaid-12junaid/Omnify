<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AttendeeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'event_id' => \App\Models\Event::factory(),
            'name' => fake()->name,
            'email' => fake()->unique()->email,
        ];
    }
}