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
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('auth/register',[AuthController::class,'register']);
Route::post('auth/login',[AuthController::class,'login']);
Route::post('auth/logout',[AuthController::class,'logout']);
Route::post('auth/refresh',[AuthController::class,'refresh']);

Route::middleware('auth:api')->group(function () {
    Route::get('events/{id}/seats', [SeatController::class, 'byEvent']);
    Route::get('venues/{id}/seats', [SeatController::class, 'byVenue']);
    Route::post('seats/block',[SeatController::class,'block']);
    Route::delete('seats/release',[SeatController::class,'release']);
});
Route::middleware('auth:api')->group(function () {
    Route::post('rezervations', [RezervationController::class,'store']);
    Route::post('rezervations/{id}/confirm', [RezervationController::class, 'confirm']);
    Route::apiResource('rezervations', RezervationController::class)->only(['index','show','destroy']);
});


Route::apiResources([
    'events'=> EventController::class,
    'seats'=> SeatController::class,
    'tickets'=> TicketController::class,
    'venues'=> VenueController::class,
    'rezervation_items'=> RezervationItemController::class,
    'user'=> UserController::class,
]);

