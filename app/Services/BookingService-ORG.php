<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Event;
use App\Models\Attendee;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public function bookEvent(array $data): Booking
    {
        $event = Event::findOrFail($data['event_id']);

        if ($event->bookings()->count() >= $event->capacity) {
            throw ValidationException::withMessages(['event_id' => 'Event is fully booked.']);
        }

        $existing = Booking::where('event_id', $data['event_id'])
            ->where('attendee_id', $data['attendee_id'])
            ->exists();

        if ($existing) {
            throw ValidationException::withMessages(['attendee_id' => 'This attendee has already booked the event.']);
        }

        return Booking::create($data);
    }
}

