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

Route::post('auth/register',[AuthController::class,'register'])->middleware('throttle:register');
Route::post('auth/login',[AuthController::class,'login'])->middleware('throttle:login');
Route::post('auth/resend-email-verification-link',[AuthController::class,'resendEmailVerificationLink'])->middleware('throttle:resend-email');
Route::post('auth/verify-email',[AuthController::class,'verifyUserEmail'])->middleware('throttle:verify-email');
Route::post('auth/logout',[AuthController::class,'logout'])->middleware('throttle:api');
Route::post('auth/refresh',[AuthController::class,'refresh'])->middleware('throttle:refresh');


Route::get('events', [EventController::class, 'index']);
Route::get('events/{event}', [EventController::class, 'show']);

Route::middleware(['auth:api'])->group(function () {
    Route::get('events/{id}/seats', [SeatController::class, 'byEvent']);
    Route::get('venues/{id}/seats', [SeatController::class, 'byVenue']);
    Route::post('seats/block',[SeatController::class,'block']);
    Route::delete('seats/release',[SeatController::class,'release']);
});

Route::middleware('admin')->group(function () {
    Route::post('events', [EventController::class, 'store']);
    Route::put('events/{event}', [EventController::class, 'update']);
    Route::patch('events/{event}', [EventController::class, 'update']);
    Route::delete('events/{event}', [EventController::class, 'destroy']);
    Route::post('venues',[VenueController::class,'store']);
    Route::delete('venues/{venue}', [VenueController::class,'destroy']);
    Route::put('venues/{venue}', [VenueController::class,'update']);
});


Route::post('rezervations', [RezervationController::class,'store']);
Route::post('rezervations/{id}/confirm', [RezervationController::class, 'confirm']);
Route::apiResource('rezervations', RezervationController::class)->only(['index','show','destroy']);

Route::middleware(['auth:api'])->group(function () {
    Route::get('tickets', [TicketController::class, 'index']);
    Route::get('tickets/{id}', [TicketController::class, 'show']);
    Route::post('tickets/{id}/transfer', [TicketController::class, 'transfer']);
    Route::post('tickets/{id}/cancel',   [TicketController::class, 'cancel']);
    Route::get('tickets/{id}/download',  [TicketController::class, 'download']);
});


