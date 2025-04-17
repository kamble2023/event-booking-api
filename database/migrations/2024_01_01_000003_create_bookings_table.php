<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('attendee_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['event_id', 'attendee_id']); // Prevent duplicate bookings
        });
    }

    public function down(): void {
        Schema::dropIfExists('bookings');
    }
};
