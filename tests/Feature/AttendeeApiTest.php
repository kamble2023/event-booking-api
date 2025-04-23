<?php
    namespace Tests\Feature;

    use Tests\TestCase;
    use App\Models\Attendee;
    use Illuminate\Foundation\Testing\RefreshDatabase;
    
    class AttendeeApiTest extends TestCase
    {
        use RefreshDatabase;
    
        public function test_attendee_can_be_created_successfully()
        {
            $response = $this->postJson('/api/attendees', [
                'name' => 'John Doe',
                'email' => 'johndoe@example.com',
            ]);
    
            $response->assertStatus(201)
                     ->assertJson([
                         'message' => 'Attendee created successfully',
                         'attendee' => [
                             'name' => 'John Doe',
                             'email' => 'johndoe@example.com',
                         ],
                     ]);
    
            $this->assertDatabaseHas('attendees', [
                'email' => 'johndoe@example.com',
            ]);
        }
    
        public function test_attendee_creation_fails_with_invalid_data()
        {
            $response = $this->postJson('/api/attendees', [
                'name' => '',
                'email' => 'not-an-email',
            ]);
    
            $response->assertStatus(422)
                     ->assertJsonValidationErrors(['name', 'email']);
        }
    
        public function test_attendee_creation_fails_with_duplicate_email()
        {
            Attendee::factory()->create([
                'email' => 'johndoe@example.com',
            ]);
    
            $response = $this->postJson('/api/attendees', [
                'name' => 'Jane Doe',
                'email' => 'johndoe@example.com',
            ]);
    
            $response->assertStatus(422)
                     ->assertJsonValidationErrors(['email']);
        }
    }    

