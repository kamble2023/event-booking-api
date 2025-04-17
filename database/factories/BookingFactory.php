<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Event;
use App\Models\Attendee;

class BookingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'attendee_id' => Attendee::factory(),
        ];
    }
}
