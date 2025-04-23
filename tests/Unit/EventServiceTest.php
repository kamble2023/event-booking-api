<?php

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Event;
use App\Models\Attendee;
use App\Services\EventService;
use Illuminate\Validation\ValidationException;

class EventServiceTest extends TestCase
{
public function test_can_delete_event_and_related_data()
{
    $event = Event::factory()->hasBookings(3)->hasAttendees(2)->create();
    $service = new EventService();

    $service->delete($event);

    $this->assertDatabaseMissing('events', ['id' => $event->id]);
    $this->assertDatabaseCount('bookings', 0);
    $this->assertDatabaseCount('attendees', 0);
}

}