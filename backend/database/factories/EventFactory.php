<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class EventFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->sentence,
            'location' => fake()->city,
            'start_time' => Carbon::now('UTC')->addDays(1),
            'end_time' => Carbon::now('UTC')->addDays(1)->addHours(2),
            'max_capacity' => 100,
        ];
    }
}