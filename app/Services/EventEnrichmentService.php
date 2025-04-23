<?php

namespace App\Services;

use App\Models\Event;

class EventEnrichmentService
{
    public function enrichEvent(Event $event): void
    {
        if (!$event->requires_enrichment) {
            return;
        }

        if (!$event->title) {
            $event->title = 'Default Title';
        }

        if (!$event->description) {
            $event->description = 'Default Description';
        }

        if (!$event->country) {
            $event->country = 'Default Country';
        }

        if (!$event->capacity) {
            $event->capacity = 100;
        }

        $event->save();
    }
}
