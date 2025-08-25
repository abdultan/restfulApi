<?php

namespace App\OpenApi;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *   schema="SeatBlockRequest",
 *   type="object",
 *   required={"event_id","seat_ids"},
 *   @OA\Property(property="event_id", type="integer", example=12),
 *   @OA\Property(property="seat_ids", type="array", minItems=1,
 *     @OA\Items(type="integer", example=101)
 *   )
 * )
 *
 * @OA\Schema(
 *   schema="SeatReleaseRequest",
 *   type="object",
 *   required={"event_id","seat_ids"},
 *   @OA\Property(property="event_id", type="integer", example=12),
 *   @OA\Property(property="seat_ids", type="array", minItems=1,
 *     @OA\Items(type="integer", example=101)
 *   )
 * )
 *
 * @OA\Schema(
 *   schema="ReservationStoreRequest",
 *   type="object",
 *   required={"event_id","seat_ids"},
 *   @OA\Property(property="event_id", type="integer", example=12),
 *   @OA\Property(property="seat_ids", type="array", minItems=1,
 *     @OA\Items(type="integer", example=101)
 *   )
 * )
 *
 * @OA\Schema(
 *   schema="AuthLoginRequest",
 *   type="object",
 *   required={"email","password"},
 *   @OA\Property(property="email", type="string", format="email", example="user@example.com"),
 *   @OA\Property(property="password", type="string", format="password", example="secret123")
 * )
 *
 * @OA\Schema(
 *   schema="AuthRegisterRequest",
 *   type="object",
 *   required={"name","email","password","password_confirmation"},
 *   @OA\Property(property="name", type="string", example="Jane Doe"),
 *   @OA\Property(property="email", type="string", format="email", example="user@example.com"),
 *   @OA\Property(property="password", type="string", format="password", example="secret123"),
 *   @OA\Property(property="password_confirmation", type="string", format="password", example="secret123")
 * )
 */
class Requests {}
