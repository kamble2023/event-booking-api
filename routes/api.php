<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;
use App\Http\Controllers\AttendeeController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\Api\V1\Auth\AuthController;
#use App\Services\BookingService;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

/*
|--------------------------------------------------------------------------
| API v1 – Authentication
|--------------------------------------------------------------------------
| Base URL: /api/v1/auth/…
| e.g. https://dev.testsol.local/dev/test_main/api/v1/auth/login
*/
Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
    });

    // Example of a protected v1 route group (bearer token required)
    Route::middleware('bearer.token')->group(function () {
        // Add authenticated v1 routes here
    });
});

/* Event Route*/
Route::apiResource('events', EventController::class);
// View a single event
Route::get('/events/{id}', [EventController::class, 'show']);
// Update an event
Route::put('/events/{id}', [EventController::class, 'update']);
// Delete an event
Route::delete('/events/{id}', [EventController::class, 'destroy']);
/* End Event Route*/

Route::apiResource('attendees', AttendeeController::class)->only(['store', 'index']);
Route::post('bookings', [BookingController::class, 'store']);
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

