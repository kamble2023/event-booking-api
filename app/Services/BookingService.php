<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Event;
use App\Models\Attendee;
use Illuminate\Http\Request;
use App\Exceptions\OverbookingException;
use Illuminate\Validation\ValidationException;

class BookingService
{

        public function bookEvent(Event $event, Attendee $attendee): ?Booking
        {
           
            // Check if event is full
            if ($event->attendees()->count() >= $event->capacity) {
                throw ValidationException::withMessages(['event' => 'The event is fully booked.']);
            }

            // Check for duplicate booking
            if ($event->attendees()->where('attendee_id', $attendee->id)->exists()) {
                throw ValidationException::withMessages(['attendee' => 'The attendee has already booked this event.']);
            }

            if (Booking::where('event_id', $event->id)->where('attendee_id', $attendee->id)->exists()) {
                throw ValidationException::withMessages([
                    'booking' => 'Attendee already booked this event'
                ]);
            }

            if ($event->bookings()->count() >= $event->capacity) {
                throw new OverbookingException();
            }

            // Create the booking if validations pass
            return Booking::create([
                'event_id' => $event->id,
                'attendee_id' => $attendee->id,
            ]);            
        }
    
        public function hasAlreadyBooked(Event $event, Attendee $attendee): bool
        {
            return Booking::where('event_id', $event->id)
                ->where('attendee_id', $attendee->id)
                ->exists();
        }            
}

