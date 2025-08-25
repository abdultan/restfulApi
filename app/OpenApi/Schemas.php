<?php

namespace App\OpenApi;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *   schema="Event",
 *   type="object",
 *   required={"id","name","venue_id","start_date","end_date","status"},
 *   @OA\Property(property="id", type="integer", example=12),
 *   @OA\Property(property="name", type="string", example="Rock Festival 2025"),
 *   @OA\Property(property="description", type="string", nullable=true),
 *   @OA\Property(property="venue_id", type="integer", example=3),
 *   @OA\Property(property="start_date", type="string", format="date-time", example="2025-09-01T18:00:00Z"),
 *   @OA\Property(property="end_date", type="string", format="date-time", example="2025-09-01T22:00:00Z"),
 *   @OA\Property(property="status", type="string", enum={"draft","published","cancelled"}, example="published")
 * )
 *
 * @OA\Schema(
 *   schema="Venue",
 *   type="object",
 *   required={"id","name","address","capacity"},
 *   @OA\Property(property="id", type="integer", example=1),
 *   @OA\Property(property="name", type="string", example="Main Hall"),
 *   @OA\Property(property="address", type="string", example="Example Street 10, City"),
 *   @OA\Property(property="capacity", type="integer", example=1000)
 * )
 *
 * @OA\Schema(
 *   schema="PaginatedVenues",
 *   type="object",
 *   @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Venue")),
 *   @OA\Property(property="meta", type="object",
 *     @OA\Property(property="current_page", type="integer", example=1),
 *     @OA\Property(property="last_page", type="integer", example=5),
 *     @OA\Property(property="per_page", type="integer", example=10),
 *     @OA\Property(property="total", type="integer", example=50)
 *   ),
 *   @OA\Property(property="links", type="object",
 *     @OA\Property(property="prev", type="string", nullable=true),
 *     @OA\Property(property="next", type="string", nullable=true)
 *   )
 * )
 * @OA\Schema(
 *   schema="Seat",
 *   type="object",
 *   required={"id","venue_id","section","row","number","status","price"},
 *   @OA\Property(property="id", type="integer", example=101),
 *   @OA\Property(property="venue_id", type="integer", example=3),
 *   @OA\Property(property="section", type="string", example="A"),
 *   @OA\Property(property="row", type="string", example="5"),
 *   @OA\Property(property="number", type="integer", example=12),
 *   @OA\Property(property="status", type="string", enum={"available","blocked","reserved","sold"}, example="available"),
 *   @OA\Property(property="price", type="number", format="float", example=350.00)
 * )
 *
 * @OA\Schema(
 *   schema="ReservationItem",
 *   type="object",
 *   required={"seat_id","price"},
 *   @OA\Property(property="seat_id", type="integer", example=101),
 *   @OA\Property(property="price", type="number", format="float", example=350.00)
 * )
 *
 * @OA\Schema(
 *   schema="Rezervation",
 *   type="object",
 *   required={"id","user_id","event_id","status","total_amount","expires_at"},
 *   @OA\Property(property="id", type="integer", example=55),
 *   @OA\Property(property="user_id", type="integer", example=7),
 *   @OA\Property(property="event_id", type="integer", example=12),
 *   @OA\Property(property="status", type="string", enum={"pending","confirmed","cancelled"}, example="pending"),
 *   @OA\Property(property="total_amount", type="number", format="float", example=700.00),
 *   @OA\Property(property="expires_at", type="string", format="date-time", example="2025-09-01T18:15:00Z"),
 *   @OA\Property(
 *     property="items",
 *     type="array",
 *     @OA\Items(ref="#/components/schemas/ReservationItem")
 *   )
 * )
 *
 * @OA\Schema(
 *   schema="Ticket",
 *   type="object",
 *   required={"id","reservation_id","seat_id","ticket_code","status"},
 *   @OA\Property(property="id", type="integer", example=1001),
 *   @OA\Property(property="reservation_id", type="integer", example=55),
 *   @OA\Property(property="seat_id", type="integer", example=101),
 *   @OA\Property(property="ticket_code", type="string", example="TKT-9X2Q-ABCD"),
 *   @OA\Property(property="status", type="string", enum={"unused","used","transferred","cancelled"}, example="unused")
 * )
 *
 * @OA\Schema(
 *   schema="ErrorResponse",
 *   type="object",
 *   @OA\Property(property="message", type="string", example="Validation failed"),
 *   @OA\Property(property="errors", type="object", nullable=true)
 * )
 *
 * @OA\Response(
 *   response="Unauthorized",
 *   description="Unauthorized",
 *   @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 * )
 *
 * @OA\Response(
 *   response="ValidationError",
 *   description="Validation error",
 *   @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 * )
 *
 * @OA\Response(
 *   response="NotFound",
 *   description="Not Found",
 *   @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 * )
 *
 * @OA\Response(
 *   response="Forbidden",
 *   description="Forbidden",
 *   @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 * )
 *
 * @OA\Response(
 *   response="Conflict",
 *   description="Conflict",
 *   @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 * )
 *
 * @OA\Response(
 *   response="NotImplemented",
 *   description="Not Implemented",
 *   @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 * )
 *
 * @OA\Response(
 *   response="TooManyRequests",
 *   description="Too Many Requests",
 *   @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 * )
 *
 * @OA\Schema(
 *   schema="AuthTokenResponse",
 *   type="object",
 *   @OA\Property(property="status", type="string", example="success"),
 *   @OA\Property(property="user", type="object"),
 *   @OA\Property(property="acces_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
 *   @OA\Property(property="type", type="string", example="bearer")
 * )
 *
 * @OA\Schema(
 *   schema="MessageResponse",
 *   type="object",
 *   @OA\Property(property="status", type="string", example="success"),
 *   @OA\Property(property="message", type="string", example="User has been logged out successfully")
 * )
 *
 * @OA\Schema(
 *   schema="PaginatedEvents",
 *   type="object",
 *   @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Event")),
 *   @OA\Property(property="meta", type="object",
 *     @OA\Property(property="current_page", type="integer", example=1),
 *     @OA\Property(property="last_page", type="integer", example=10),
 *     @OA\Property(property="per_page", type="integer", example=15),
 *     @OA\Property(property="total", type="integer", example=150)
 *   ),
 *   @OA\Property(property="links", type="object",
 *     @OA\Property(property="prev", type="string", nullable=true),
 *     @OA\Property(property="next", type="string", nullable=true)
 *   )
 * )
 *
 * @OA\Schema(
 *   schema="PaginatedSeats",
 *   type="object",
 *   @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Seat")),
 *   @OA\Property(property="meta", type="object",
 *     @OA\Property(property="current_page", type="integer", example=1),
 *     @OA\Property(property="last_page", type="integer", example=5),
 *     @OA\Property(property="per_page", type="integer", example=100),
 *     @OA\Property(property="total", type="integer", example=420)
 *   ),
 *   @OA\Property(property="links", type="object",
 *     @OA\Property(property="prev", type="string", nullable=true),
 *     @OA\Property(property="next", type="string", nullable=true)
 *   )
 * )
 */
class Schemas {}
    