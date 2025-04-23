<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StoreBookingRequest extends FormRequest
{
    /**
     * Check if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    { 
        
        // \Log::info('Rules method hit');
        return [
           
            'event_id' => 'required|exists:events,id',
            #'attendee_id' => 'required|exists:attendees,id',
            'attendee_id' => 'required|exists:attendees,id|unique:bookings,attendee_id,NULL,id,event_id,' . $this->event_id
        ];
        // \Log::info('StoreBookingRequest: rules() method triggered');
    
        // return [
        //     'event_id' => 'required|exists:events,id',
        //     'attendee_id' => 'required|exists:attendees,id'
        // ];
        
       
    }
}
