<?php

namespace App\Http\Controllers;

use App\Services\AttendeeService;
use App\Models\Attendee;
use Illuminate\Http\Request;
use App\Http\Requests\StoreAttendeeRequest;
use Illuminate\Validation\ValidationException;

class AttendeeController extends Controller
{
    protected $attendeeService;

    public function __construct(AttendeeService $attendeeService)
    {
        $this->attendeeService = $attendeeService;
    }

    public function store(StoreAttendeeRequest $request)
    {

        $attendee = Attendee::create($request->validated());

        return response()->json([
            'message' => 'Attendee created successfully',
            'attendee' => $attendee
        ], 201);
    }

    public function show($id)
    {
        $attendee = $this->attendeeService->findAttendee($id);

        if (!$attendee) {
            return response()->json([
                'success' => false,
                'message' => 'Attendee not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $attendee
        ]);
    }

    public function destroy($id)
    {
        $deleted = $this->attendeeService->deleteAttendee($id);

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Attendee not found or could not be deleted',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Attendee deleted successfully',
        ]);
    }
}
