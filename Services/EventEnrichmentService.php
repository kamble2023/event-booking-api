<?php

namespace App\Services;

use App\Models\Event;
use Illuminate\Support\Facades\Http;

class EventEnrichmentService
{
    public function process(Event $event): Event
    {
        // if (!$event->requires_enrichment) {
        //     return $event;
        // }

        // Simulate external API call
        // $response = Http::get('https://testapi.com/events/1'); // ext API
        // $data = $response->json();

        // // Fill in missing fields (example: description, location)
        // $event->description = $event->description ?? $data['description'] ?? 'Auto-enriched description';
        // $event->country = $event->country ?? 'Global';
        // $event->requires_enrichment = false;
        // $event->save();

        // return $event;
        if ($event->requires_enrichment || empty($event->description)) {

            // Simulate an external API response for enrichment.
            $dummyData = [
                'description' => 'This is an auto-generated description from dummy enrichment.',
                'country' => $event->country ?: 'Default country'
            ];

            // Update event with dummy enriched data.
            $event->description = $dummyData['description'];
            $event->country = $dummyData['country'];
            
            // Mark event as enriched.
            $event->requires_enrichment = false;
            $event->save();
        }
        
        return $event;
    }
}
