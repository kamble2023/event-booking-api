<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Event;
use App\Models\Attendee;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
#use App\Services\EventEnrichmentService;


class BookingController extends Controller
{
    
    public function store(Request $request)
    { 
        try{

            
            $validated = $request->validate([
                'event_id' => 'required|exists:events,id',
                'attendee_id' => 'required|exists:attendees,id',
            ]);
        
            $event = Event::find($validated['event_id']); 
            $attendee = Attendee::findOrFail($validated['attendee_id']);
            
            $attendeeId = $validated['attendee_id'];
                    
            if (!$event) {
                return response()->json([
                    'message' => 'The selected event does not exist.'
                ], 404);
            }
            //$event = $enrichmentService->process($event);
            // Check for duplicate booking
            if (Booking::where('event_id', $event->id)->where('attendee_id', $attendeeId)->exists()) {
                return response()->json(['error' => 'Attendee already booked for this event.'], 409);
            }

            // Check event capacity
            $currentBookings = Booking::where('event_id', $event->id)->count();
            if ($currentBookings >= $event->capacity) {
                return response()->json(['error' => 'Event is fully booked.'], 409);
            }

            $booking = Booking::create($validated);
            return response()->json($booking, 201);
            
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        }
    }
}
