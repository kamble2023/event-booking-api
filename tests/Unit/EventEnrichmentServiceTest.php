<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Event;
use App\Services\EventEnrichmentService;

class EventEnrichmentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected EventEnrichmentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new EventEnrichmentService();
    }

    public function test_event_enrichment_adds_missing_data()
    {
        $event = Event::factory()->make([
            'requires_enrichment' => true,
            'title' => "Test",
            'description' => null,
            'country' => "Aus",
            'capacity' => 10,
        ]);
    
        $event->save(); // save after enriching or patch up missing required fields before saving
        $this->service->enrichEvent($event);
        $event->refresh();
    
        $this->assertNotNull($event->title);
        $this->assertNotNull($event->description);
        $this->assertNotNull($event->country);
        $this->assertNotNull($event->capacity);
    }
    

    public function test_event_enrichment_is_skipped_if_not_required()
    {
        $event = Event::factory()->create([
            'requires_enrichment' => false,
            'title' => 'Sample Event',
            'description' => 'Some description',
            'date' => '2025-06-04',
            'country' => 'France',
            'capacity' => 100,
        ]);

        $originalData = $event->toArray();

        $this->service->enrichEvent($event);
        $event->refresh();

        $this->assertEquals($originalData, $event->toArray());
    }
}
