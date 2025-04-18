<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;


class EventController extends Controller
{
    public function index(Request $request)
    {
        
        $query = Event::query();

        // Filtering
        if ($request->has('country')) {
            $query->where('country', $request->input('country'));
        }
    
        if ($request->has('title')) {
            $query->where('title', 'like', '%' . $request->input('title') . '%');
        }
    
        // Pagination
        $perPage = $request->input('per_page', 10); // default to 10 per page
        $events = $query->paginate($perPage);
    
        return response()->json($events);        
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'country' => 'required|string',
            'capacity' => 'required|integer|min:1',
        ]);

        $event = Event::create($validated);
        return response()->json($event, 201);
    }

    public function show($id)
    {
        
        $event = Event::find($id);
        
        if (!$event) {
            return response()->json([
                'message' => 'Event not found.'
            ], 404);
        }
    
        return response()->json([
            'data' => $event
        ]);
    }

    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string',
            'description' => 'nullable|string',
            'date' => 'sometimes|required|date',
            'country' => 'sometimes|required|string',
            'capacity' => 'sometimes|required|integer|min:1',
        ]);

        $event->update($validated);
        return response()->json($event);
    }

    public function destroy($id)
    {
        $event = Event::find($id);

        if (!$event) {
            return response()->json([
                'message' => 'Event not found.'
            ], 404);
        }

        $event->delete();

        return response()->json([
            'message' => 'Event deleted successfully.'
        ], 204);
        
    }
}
