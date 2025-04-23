<?php

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Event;
use App\Models\Attendee;
use App\Models\Booking;

class BookingApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_book_event()
    {
        $event = Event::factory()->create(['capacity' => 2]);
        $attendee = Attendee::factory()->create();

        $response = $this->postJson('/api/bookings', [
            'event_id' => $event->id,
            'attendee_id' => $attendee->id,
        ]);

        $response->assertCreated()
                 ->assertJsonFragment(['event_id' => $event->id]);
    }


    public function test_prevent_overbooking()
    {
        // Create an event with a capacity of 1
        $event = Event::factory()->create(['capacity' => 1]);

        // Create an attendee
        $attendee1 = Attendee::factory()->create();

        // Book the first attendee
        $this->postJson('/api/bookings', [
            'event_id' => $event->id,
            'attendee_id' => $attendee1->id,
        ]);

        // Attempt to book the second attendee, which should trigger an overbooking error
        $attendee2 = Attendee::factory()->create();
        $response = $this->postJson('/api/bookings', [
            'event_id' => $event->id,
            'attendee_id' => $attendee2->id,
        ]);

        // Assert that the status code is 409 (Conflict), meaning overbooking occurred
        $response->assertStatus(422);

        // Assert that the response contains the correct message
        $response->assertJson([
            'message' => 'The event is fully booked.',
        ]);
    }

    public function test_prevent_duplicate_booking()
    {
        $event = Event::factory()->create();
        $attendee = Attendee::factory()->create();

        $this->postJson('/api/bookings', [
            'event_id' => $event->id,
            'attendee_id' => $attendee->id,
        ]);

        $response = $this->postJson('/api/bookings', [
            'event_id' => $event->id,
            'attendee_id' => $attendee->id,
        ]);

        $response->assertStatus(422);
    }    
}

