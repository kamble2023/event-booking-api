<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;

class Event extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'date', 'country', 'capacity'];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }


    protected static function booted()
    {
        static::deleting(function ($event) {
            Log::info("Deleting event: {$event->id} - {$event->name}");

            $event->bookings()->each(function ($booking) {
                Log::info("Deleting booking ID: {$booking->id}");
                $booking->delete();
            });

            
        });
    }

    public function attendees()
    {
        return $this->belongsToMany(Attendee::class, 'bookings', 'event_id', 'attendee_id');
    }

}
