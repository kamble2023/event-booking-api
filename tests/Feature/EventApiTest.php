<?php

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Event;
use App\Models\Attendee;
use App\Models\Booking;


class EventApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_event()
    {
        $response = $this->postJson('/api/events', [
            'title' => 'Laravel Conference',
            'description' => 'Annual Laravel Conf',
            'country' => 'USA',
            'date' => now()->addDays(10)->format('Y-m-d'),
            'capacity' => 100,
        ]);

        $response->assertCreated()
                 ->assertJsonFragment(['title' => 'Laravel Conference']);
    }

    public function test_can_list_events()
    {
        Event::factory()->count(3)->create();

        $response = $this->getJson('/api/events');

        $response->assertOk()
                 ->assertJsonCount(3, 'data');
    }

    public function test_can_update_event()
    {
        $event = Event::factory()->create();

        $response = $this->putJson("/api/events/{$event->id}", [
            'title' => 'Updated Title',
        ]);

        $response->assertOk()
                 ->assertJsonFragment(['title' => 'Updated Title']);
    }

    public function test_can_delete_event()
    {
        $event = Event::factory()->create();

        $response = $this->deleteJson("/api/events/{$event->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }

    public function test_event_deletion_removes_related_records()
    {
        $event = Event::factory()->create();
        $booking = Booking::factory()->create([
            'event_id' => $event->id
        ]);
    
        $this->deleteJson("/api/events/{$event->id}")
             ->assertStatus(204);
    
        $this->assertDatabaseMissing('events', ['id' => $event->id]);
        $this->assertDatabaseMissing('bookings', ['id' => $booking->id]);
    }
    
}


