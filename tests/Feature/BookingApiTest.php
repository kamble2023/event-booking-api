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

        $response->assertStatus(409);
    }

    public function test_prevent_overbooking()
    {
        $event = Event::factory()->create(['capacity' => 1]);
        $attendees = Attendee::factory()->count(2)->create();

        $this->postJson('/api/bookings', [
            'event_id' => $event->id,
            'attendee_id' => $attendees[0]->id,
        ]);

        $response = $this->postJson('/api/bookings', [
            'event_id' => $event->id,
            'attendee_id' => $attendees[1]->id,
        ]);

        $response->assertStatus(409);
    }
}

