<?php

namespace App\Services;

use App\Models\Attendee;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AttendeeService
{
    /**
     * Create a new attendee after validating input.
     *
     * @param array $data
     * @return Attendee
     * @throws ValidationException
     */
    public function createAttendee(array $data): Attendee
    {
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:attendees,email',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return Attendee::create($validator->validated());
    }

    /**
     * Find an attendee by ID.
     *
     * @param int $id
     * @return Attendee|null
     */
    public function findAttendee(int $id): ?Attendee
    {
        return Attendee::find($id);
    }

    /**
     * Delete an attendee by ID.
     *
     * @param int $id
     * @return bool
     */
    public function deleteAttendee(int $id): bool
    {
        $attendee = Attendee::find($id);

        if ($attendee) {
            return $attendee->delete();
        }

        return false;
    }
}
