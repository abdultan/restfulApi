<?php

namespace App\OpenApi;

use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *   name="Events",
 *   description="Event listing and details"
 * )
 *
 * @OA\Post(
 *   path="/api/events",
 *   tags={"Events"},
 *   summary="Create event (admin)",
 *   security={{"bearerAuth": {}}},
 *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/Event")),
 *   @OA\Response(response=201, description="Created", @OA\JsonContent(ref="#/components/schemas/Event")),
 *   @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
 * )
 *
 * @OA\Put(
 *   path="/api/events/{event}",
 *   tags={"Events"},
 *   summary="Update event (admin)",
 *   security={{"bearerAuth": {}}},
 *   @OA\Parameter(name="event", in="path", required=true, @OA\Schema(type="integer")),
 *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/Event")),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/Event"))
 * )
 *
 * @OA\Patch(
 *   path="/api/events/{event}",
 *   tags={"Events"},
 *   summary="Patch event (admin)",
 *   security={{"bearerAuth": {}}},
 *   @OA\Parameter(name="event", in="path", required=true, @OA\Schema(type="integer")),
 *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/Event")),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/Event"))
 * )
 *
 * @OA\Delete(
 *   path="/api/events/{event}",
 *   tags={"Events"},
 *   summary="Delete/cancel event (admin)",
 *   security={{"bearerAuth": {}}},
 *   @OA\Parameter(name="event", in="path", required=true, @OA\Schema(type="integer")),
 *   @OA\Response(response=200, description="OK")
 * )
 *
 * @OA\Get(
 *   path="/api/user",
 *   tags={"Auth"},
 *   summary="Get authenticated user",
 *   security={{"bearerAuth": {}}},
 *   @OA\Response(response=200, description="OK")
 * )
 *
 * @OA\Get(
 *   path="/api/tickets",
 *   tags={"Tickets"},
 *   summary="List user tickets",
 *   security={{"bearerAuth": {}}},
 *   @OA\Response(response=200, description="OK")
 * )
 *
 * @OA\Get(
 *   path="/api/tickets/{id}/download",
 *   tags={"Tickets"},
 *   summary="Download ticket as PDF",
 *   security={{"bearerAuth": {}}},
 *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *   @OA\Response(response=200, description="OK")
 * )
 *
 * @OA\Get(
 *   path="/api/tickets/{id}",
 *   tags={"Tickets"},
 *   summary="Show ticket",
 *   security={{"bearerAuth": {}}},
 *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *   @OA\Response(response=200, description="OK")
 * )
 *
 * @OA\Delete(
 *   path="/api/rezervations/{id}",
 *   tags={"Reservations"},
 *   summary="Cancel/delete reservation",
 *   security={{"bearerAuth": {}}},
 *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *   @OA\Response(response=200, description="OK")
 * )
 *
 * @OA\Post(
 *   path="/api/tickets/{id}/transfer",
 *   tags={"Tickets"},
 *   summary="Transfer ticket",
 *   security={{"bearerAuth": {}}},
 *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *   @OA\RequestBody(required=true, @OA\JsonContent(required={"to_user_id"}, @OA\Property(property="to_user_id", type="integer", example=2), @OA\Property(property="note", type="string", nullable=true))),
 *   @OA\Response(response=200, description="OK")
 * )
 *
 * @OA\Post(
 *   path="/api/tickets/{id}/cancel",
 *   tags={"Tickets"},
 *   summary="Cancel ticket",
 *   security={{"bearerAuth": {}}},
 *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *   @OA\Response(response=200, description="OK")
 * )
 * @OA\Get(
 *   path="/api/events/{id}/seats",
 *   tags={"Seats"},
 *   summary="List seats for an event (byEvent)",
 *   security={{"bearerAuth": {}}},
 *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/PaginatedSeats")),
 *   @OA\Response(response=404, description="Event Not Found")
 * )
 *
 * @OA\Get(
 *   path="/api/venues/{id}/seats",
 *   tags={"Seats"},
 *   summary="List seats by venue (byVenue)",
 *   security={{"bearerAuth": {}}},
 *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Seat")))
 * )
 *
 * @OA\Post(
 *   path="/api/auth/login",
 *   tags={"Auth"},
 *   summary="Login with email and password",
 *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/AuthLoginRequest")),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/AuthTokenResponse")),
 *   @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
 * )
 *
 * @OA\Post(
 *   path="/api/auth/register",
 *   tags={"Auth"},
 *   summary="Register user and send verification email",
 *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/AuthRegisterRequest")),
 *   @OA\Response(response=202, description="Accepted", @OA\JsonContent(ref="#/components/schemas/MessageResponse")),
 *   @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
 * )
 *
 * @OA\Post(
 *   path="/api/auth/logout",
 *   tags={"Auth"},
 *   summary="Logout current user",
 *   security={{"bearerAuth": {}}},
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/MessageResponse"))
 * )
 *
 * @OA\Post(
 *   path="/api/auth/refresh",
 *   tags={"Auth"},
 *   summary="Refresh JWT token",
 *   security={{"bearerAuth": {}}},
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/AuthTokenResponse")),
 *   @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
 * )
 *
 * @OA\Post(
 *   path="/api/auth/resend-email-verification-link",
 *   tags={"Auth"},
 *   summary="Resend email verification link",
 *   @OA\RequestBody(required=true, @OA\JsonContent(required={"email"}, @OA\Property(property="email", type="string", format="email", example="user@example.com"))),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/MessageResponse"))
 * )
 *
 * @OA\Post(
 *   path="/api/auth/verify-email",
 *   tags={"Auth"},
 *   summary="Verify user email with token",
 *   @OA\RequestBody(required=true, @OA\JsonContent(required={"email","token"},
 *     @OA\Property(property="email", type="string", format="email", example="user@example.com"),
 *     @OA\Property(property="token", type="string", example="ABCDEFG123456")
 *   )),
 *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/MessageResponse")),
 *   @OA\Response(response=400, description="Invalid/Expired token", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
 * )
 *
 * @OA\Tag(
 *   name="Seats",
 *   description="Seat operations"
 * )
 *
 * @OA\Tag(
 *   name="Reservations",
 *   description="Reservation operations"
 * )
 *
 * @OA\Get(
 *   path="/api/events",
 *   tags={"Events"},
 *   summary="List events",
 *   @OA\Response(
 *     response=200,
 *     description="OK",
 *     @OA\JsonContent(ref="#/components/schemas/PaginatedEvents")
 *   )
 * )
 *
 * @OA\Get(
 *   path="/api/events/{event}",
 *   tags={"Events"},
 *   summary="Get event by id",
 *   @OA\Parameter(name="event", in="path", required=true, @OA\Schema(type="integer")),
 *   @OA\Response(
 *     response=200,
 *     description="OK",
 *     @OA\JsonContent(ref="#/components/schemas/Event")
 *   ),
 *   @OA\Response(response=404, description="Not Found")
 * )
 *
 * @OA\Post(
 *   path="/api/seats/block",
 *   tags={"Seats"},
 *   summary="Block seats for 15 minutes",
 *   security={{"bearerAuth": {}}},
 *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/SeatBlockRequest")),
 *   @OA\Response(response=200, description="OK")
 * )
 *
 * @OA\Delete(
 *   path="/api/seats/release",
 *   tags={"Seats"},
 *   summary="Release previously blocked seats",
 *   security={{"bearerAuth": {}}},
 *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/SeatReleaseRequest")),
 *   @OA\Response(response=200, description="OK")
 * )
 *
 * @OA\Post(
 *   path="/api/rezervations",
 *   tags={"Reservations"},
 *   summary="Create reservation from blocked seats",
 *   security={{"bearerAuth": {}}},
 *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/ReservationStoreRequest")),
 *   @OA\Response(
 *     response=201,
 *     description="Created",
 *     @OA\JsonContent(ref="#/components/schemas/Rezervation")
 *   )
 * )
 *
 * @OA\Post(
 *   path="/api/rezervations/{id}/confirm",
 *   tags={"Reservations"},
 *   summary="Confirm reservation and issue tickets",
 *   security={{"bearerAuth": {}}},
 *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *   @OA\Response(response=200, description="OK")
 * )
 *
 * @OA\Get(
 *   path="/api/rezervations",
 *   tags={"Reservations"},
 *   summary="List current user's reservations",
 *   security={{"bearerAuth": {}}},
 *   @OA\Response(response=200, description="OK")
 * )
 *
 * @OA\Get(
 *   path="/api/rezervations/{id}",
 *   tags={"Reservations"},
 *   summary="Get reservation by id",
 *   security={{"bearerAuth": {}}},
 *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *   @OA\Response(
 *     response=200,
 *     description="OK",
 *     @OA\JsonContent(ref="#/components/schemas/Rezervation")
 *   ),
 *   @OA\Response(response=404, description="Not Found")
 * )
 */
class Paths {}


