<?php

namespace App\OpenApi;

use OpenApi\Annotations as OA;

/**
 * @OA\OpenApi(
 *   @OA\Info(
 *     title="Event Reservation API",
 *     version="1.0.0",
 *     description="Etkinlik, koltuk, rezervasyon ve bilet işlemleri için REST API."
 *   ),
 *   @OA\Server(
 *     url="http://localhost:8000",
 *     description="Local"
 *   ),
 *   @OA\Server(
 *     url="https://api.domainin.com",
 *     description="Production"
 *   )
 * )
 *
 * @OA\SecurityScheme(
 *   securityScheme="bearerAuth",
 *   type="http",
 *   scheme="bearer",
 *   bearerFormat="JWT"
 * )
 */
class OpenApi {}
