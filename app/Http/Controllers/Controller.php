<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *     title="Property Management API",
 *     version="1.0.0",
 *     description="API for managing property hierarchies including corporations, buildings, properties, tenancy periods, and tenants",
 *     @OA\Contact(
 *         email="admin@property-api.com"
 *     )
 * )
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Laravel Sanctum token authentication"
 * )
 * @OA\Server(
 *     url="http://localhost:8000",
 *     description="Local development server"
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
