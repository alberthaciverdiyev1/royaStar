<?php

namespace App\Swagger;

use L5Swagger\CustomGeneratorInterface;
use OpenApi\Generator;

class SilentGeneratorFactory implements CustomGeneratorInterface
{
    public function create(): Generator
    {
        // Pre-load the SwaggerSpec file so PHP can reflect its classes
        require_once __DIR__ . '/SwaggerSpec.php';

        return new Generator(logger: new SilentLogger);
    }
}
