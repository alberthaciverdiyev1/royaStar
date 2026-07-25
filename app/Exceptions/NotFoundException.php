<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class NotFoundException extends \RuntimeException
{
    public function __construct(
        string  $entity = 'Resource',
        ?string $id = null,
    )
    {
        $message = $id
            ? "$entity with ID $id not found."
            : "$entity not found.";

        parent::__construct($message, Response::HTTP_NOT_FOUND);
    }
}
