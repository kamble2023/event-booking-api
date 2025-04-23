<?php

namespace App\Services;

use App\Models\Event;
use Illuminate\Support\Facades\DB;

class EventService
{
    public function create(array $data): Event
    {
        return Event::create($data);
    }

    public function update(Event $event, array $data): Event
    {
        $event->update($data);
        return $event;
    }

    public function delete(Event $event): void
    {
        DB::transaction(function () use ($event) {
            $event->attendees()->delete();
            $event->bookings()->delete();
            $event->delete();
        });
    }

    public function list(array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Event::query();

        if (isset($filters['country'])) {
            $query->where('country', $filters['country']);
        }

        return $query->paginate(10);
    }
}
