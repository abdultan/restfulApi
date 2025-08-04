<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;
use App\Http\Controllers\SeatController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\VenueController;
use App\Http\Controllers\RezervationController;
use App\Http\Controllers\RezervationItemController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::apiResource('/events', EventController::class);
Route::apiResource('/seats', SeatController::class);
Route::apiResource('/tickets', TicketController::class);
Route::apiResource('/venues', VenueController::class);
Route::apiResource('/reservations', RezervationController::class);
Route::apiResource('/rezervation_items', RezervationItemController::class);


