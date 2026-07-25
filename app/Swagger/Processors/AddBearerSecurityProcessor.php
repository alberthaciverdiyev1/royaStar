<?php

namespace App\Swagger\Processors;

use OpenApi\Analysis;
use OpenApi\Annotations\Operation;
use OpenApi\Undefined;

class AddBearerSecurityProcessor
{
    public function __invoke(Analysis $analysis): void
    {
        $analysis->openapi->security = [['bearerAuth' => []]];
    }
}
