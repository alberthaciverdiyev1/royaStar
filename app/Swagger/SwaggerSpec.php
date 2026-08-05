<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

#[
    OA\Info(
        version: '1.0.0',
        description: 'LMS platform for managing students and curriculum.',
        title: 'RoyaStar Backend API',
    ),
    OA\Server(url: '/api', description: 'API Server'),
    OA\SecurityScheme(
        securityScheme: 'bearerAuth',
        type: 'http',
        bearerFormat: 'bearer',
        scheme: 'bearer',
    ),
]
class SwaggerSpec {}

// ─── Shared Response Schemas ───

#[
    OA\Schema(
        schema: 'SuccessResponse',
        properties: [
            new OA\Property(property: 'success', type: 'boolean', example: true),
            new OA\Property(property: 'status_code', type: 'integer', example: 200),
            new OA\Property(property: 'message', type: 'string', example: ''),
        ],
        type: 'object',
    ),
    OA\Schema(
        schema: 'DataResponse',
        type: 'object',
        allOf: [
            new OA\Schema(ref: '#/components/schemas/SuccessResponse'),
            new OA\Schema(properties: [
                new OA\Property(property: 'data', type: 'object', nullable: true),
            ]),
        ],
    ),
    OA\Schema(
        schema: 'ErrorResponse',
        properties: [
            new OA\Property(property: 'success', type: 'boolean', example: false),
            new OA\Property(property: 'status_code', type: 'integer', example: 422),
            new OA\Property(property: 'message', type: 'string', example: 'Something went wrong.'),
            new OA\Property(property: 'errors', type: 'object', nullable: true, example: ['field' => ['Error message']]),
        ],
        type: 'object',
    ),
    OA\Schema(
        schema: 'PaginatedMeta',
        properties: [
            new OA\Property(property: 'current_page', type: 'integer'),
            new OA\Property(property: 'last_page', type: 'integer'),
            new OA\Property(property: 'per_page', type: 'integer'),
            new OA\Property(property: 'total', type: 'integer'),
            new OA\Property(property: 'has_more_pages', type: 'boolean'),
        ],
        type: 'object',
    ),
    OA\Response(
        response: 'ValidationError',
        description: 'Validation error',
        content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
    ),
    // ─── Reusable Request Schemas ───
    OA\Schema(
        schema: 'RegisterRequest',
        required: ['name', 'phone', 'email', 'password', 'type'],
        properties: [
            new OA\Property(property: 'name', type: 'string'),
            new OA\Property(property: 'surname', type: 'string', nullable: true),
            new OA\Property(property: 'phone', type: 'string'),
            new OA\Property(property: 'email', type: 'string', format: 'email'),
            new OA\Property(property: 'password', type: 'string', minLength: 8),
            new OA\Property(property: 'password_confirmation', description: 'Must match password, required with password', type: 'string'),
            new OA\Property(property: 'type', type: 'string', default: 'student', enum: ['student']),
            new OA\Property(property: 'student', required: ['grade_id', 'city_id'], properties: [
                new OA\Property(property: 'grade_id', type: 'integer'),
                new OA\Property(property: 'city_id', type: 'integer'),
            ], type: 'object', nullable: true),
        ],
        type: 'object',
    ),
]
class SwaggerSchemas {}
