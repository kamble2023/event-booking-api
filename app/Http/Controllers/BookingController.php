<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Models\Attendee;
use App\Models\Event;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use App\Exceptions\OverbookingException;


class BookingController extends Controller
{
    protected $bookingService;

    public function __construct(BookingService $bookingService)
    {    
        $this->bookingService = $bookingService;
    }

    public function store(StoreBookingRequest $request): JsonResponse
    {

        try {

            $validated = $request->validated();

            $event = Event::findOrFail($validated['event_id']);
            $attendee = Attendee::findOrFail($validated['attendee_id']);

            $booking = $this->bookingService->bookEvent($event, $attendee);

            return response()->json([
                'success' => true,
                'message' => 'Booking successful.',
                'data' => $booking,
            ], 201);


        }  catch (OverbookingException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 409);
        } catch (ValidationException $e) {
           
            if ($e->getMessage() === 'Attendee already booked this event') {
                return response()->json(['message' => 'Duplicate booking not allowed'], 409);
            }
            throw $e;
        }
           
    }
}

