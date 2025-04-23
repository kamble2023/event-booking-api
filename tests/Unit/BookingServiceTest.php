<?php

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Event;
use App\Models\Attendee;
use App\Services\BookingService;
use Illuminate\Validation\ValidationException;

class BookingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $bookingService;

    protected function setUp(): void
    {
        parent::setUp();
        // Initialize your BookingService
        $this->bookingService = new BookingService();
    }

    public function test_booking_fails_if_event_is_full()
    {
        // Create a full event (capacity set to 1 for this example)
        $event = Event::factory()->create(['capacity' => 1]);

        // Create two attendees
        $attendee1 = Attendee::factory()->create();
        $attendee2 = Attendee::factory()->create();

        // Book the first attendee (this should succeed)
        $this->bookingService->bookEvent($event, $attendee1);

        // Now the event is full, so booking should fail for the second attendee
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The event is fully booked.');

        // Attempt to book the second attendee, which should throw ValidationException
        $this->bookingService->bookEvent($event, $attendee2);
    }

    public function test_prevent_duplicate_booking()
    {
        $event = Event::factory()->create(['capacity' => 5]);
        $attendee = Attendee::factory()->create();

        // First booking should succeed
        $this->postJson('/api/bookings', [
            'event_id' => $event->id,
            'attendee_id' => $attendee->id,
        ])->assertStatus(201); // Assuming the successful status is 201

        // Second booking for the same attendee should fail (duplicate booking)
        $response = $this->postJson('/api/bookings', [
            'event_id' => $event->id,
            'attendee_id' => $attendee->id,
        ]);

        $response->assertStatus(422); // Expected validation error for duplicate booking
    }
}
