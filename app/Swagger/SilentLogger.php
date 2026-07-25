<?php

namespace App\Swagger;

use Psr\Log\AbstractLogger;

class SilentLogger extends AbstractLogger
{
    public function log($level, \Stringable|string $message, array $context = []): void
    {
        // Silently swallow swagger-php validation warnings
    }
}
