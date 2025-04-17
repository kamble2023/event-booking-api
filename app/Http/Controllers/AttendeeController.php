<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AttendeeController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string',
                'email' => 'required|email|unique:attendees,email',
                'phone' => 'nullable|string',
            ]);

            $attendee = Attendee::create($validated);
            return response()->json($attendee, 201);
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        }
    }   
}
