<?php

namespace App\Swagger;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *   title="API Blog",
 *   version="1.0.0",
 *   description="Documentación de la API (Laravel 11 + Passport)."
 * )
 *
 * @OA\Server(
 *   url=L5_SWAGGER_CONST_HOST,
 *   description="Servidor base"
 * )
 *
 * @OA\SecurityScheme(
 *   securityScheme="bearerAuth",
 *   type="http",
 *   scheme="bearer",
 *   bearerFormat="JWT"
 * )
 *
 * @OA\Tag(name="Auth", description="Autenticación y perfil")
 * @OA\Tag(name="Posts", description="Gestión de posts")
 */

class OpenApi {}
