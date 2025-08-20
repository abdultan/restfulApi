<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;
use App\Http\Controllers\SeatController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\VenueController;
use App\Http\Controllers\RezervationController;
use App\Http\Controllers\RezervationItemController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('auth/register',[AuthController::class,'register']);
Route::post('auth/login',[AuthController::class,'login']);
Route::post('auth/logout',[AuthController::class,'logout']);
Route::post('auth/refresh',[AuthController::class,'refresh']);

// (Email verification routes removed by request)

// Public event browse
Route::get('events', [EventController::class, 'index']);
Route::get('events/{event}', [EventController::class, 'show']);

Route::middleware(['auth:api'])->group(function () {
    Route::get('events/{id}/seats', [SeatController::class, 'byEvent']);
    Route::get('venues/{id}/seats', [SeatController::class, 'byVenue']);
    Route::post('seats/block',[SeatController::class,'block']);
    Route::delete('seats/release',[SeatController::class,'release']);

    // Admin-only event mutations
    Route::middleware('admin')->group(function () {
        Route::post('events', [EventController::class, 'store']);
        Route::put('events/{event}', [EventController::class, 'update']);
        Route::patch('events/{event}', [EventController::class, 'update']);
        Route::delete('events/{event}', [EventController::class, 'destroy']);
        Route::apiResource('venues', VenueController::class)->only(['store','update','destroy']);
    });

    // Read endpoints behind auth
    Route::apiResource('venues', VenueController::class)->only(['index','show']);

    Route::post('rezervations', [RezervationController::class,'store']);
    Route::post('rezervations/{id}/confirm', [RezervationController::class, 'confirm']);
    Route::apiResource('rezervations', RezervationController::class)->only(['index','show','destroy']);

    Route::middleware(['auth:api'])->group(function () {
        Route::get('tickets', [TicketController::class, 'index']);
        Route::get('tickets/{id}', [TicketController::class, 'show']);
        Route::post('tickets/{id}/transfer', [TicketController::class, 'transfer']);
        Route::post('tickets/{id}/cancel',   [TicketController::class, 'cancel']);
        Route::get('tickets/{id}/download',  [TicketController::class, 'download']); // bonus
    });

    Route::apiResources([
        'seats'=> SeatController::class,
        'tickets'=> TicketController::class,
        'rezervation_items'=> RezervationItemController::class,
        'user'=> UserController::class,
    ]);
});

