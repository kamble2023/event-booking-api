<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;
use App\Http\Controllers\AttendeeController;
use App\Http\Controllers\BookingController;

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
/* Evnet Route*/
Route::apiResource('events', EventController::class);
// View a single event
Route::get('/events/{id}', [EventController::class, 'show']);
// Update an event
Route::put('/events/{id}', [EventController::class, 'update']);
// Delete an event
Route::delete('/events/{id}', [EventController::class, 'destroy']);
/* End Evnet Route*/

Route::apiResource('attendees', AttendeeController::class)->only(['store', 'index']);
Route::post('bookings', [BookingController::class, 'store']);
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
