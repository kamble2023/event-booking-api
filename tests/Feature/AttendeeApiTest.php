<?php

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Attendee;

class AttendeeApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_register_attendee()
    {
        $response = $this->postJson('/api/attendees', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $response->assertCreated()
                 ->assertJsonFragment(['email' => 'john@example.com']);
    }

    public function test_duplicate_email_fails()
    {
        Attendee::factory()->create(['email' => 'john@example.com']);

        $response = $this->postJson('/api/attendees', [
            'name' => 'Duplicate',
            'email' => 'john@example.com',
        ]);

        $response->assertStatus(422);
    }
}

