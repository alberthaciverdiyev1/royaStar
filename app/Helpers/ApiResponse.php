<?php

if (!function_exists('apiResponse')) {

    function apiResponse(
        mixed   $data = null,
        int     $statusCode = 200,
        ?string $message = null,
        ?array  $errors = null,
        ?array  $meta = null,
    ): \Illuminate\Http\JsonResponse {
        $success = $statusCode >= 200 && $statusCode < 300;
        $body = [];

        $body['success'] = $success;
        $body['status_code'] = $statusCode;

        if (is_null($message)) {
            $message = match (true) {
                $statusCode === 201 => 'crud.created',
                $statusCode === 200 && is_null($data) => 'crud.deleted',
                !$success => 'Something went wrong.',
                default => '',
            };
        }
        $body['message'] = __($message);

        if (!is_null($data)) {
            $body['data'] = $data;
        }

        if (!is_null($errors)) {
            $body['errors'] = $errors;
        }

        if (!is_null($meta)) {
            $body['meta'] = $meta;
        }

        return response()->json($body, $statusCode);
    }
}

if (!function_exists('apiError')) {

    function apiError(
        ?string $message = null,
        int     $statusCode = 400,
        ?array  $errors = null,
    ): \Illuminate\Http\JsonResponse {
        return apiResponse(
            statusCode: $statusCode,
            message: $message,
            errors: $errors,
        );
    }
}

if (!function_exists('apiValidationError')) {

    function apiValidationError(
        ?array  $errors = null,
        ?string $message = null,
    ): \Illuminate\Http\JsonResponse {
        return apiResponse(
            statusCode: 422,
            message: $message,
            errors: $errors,
        );
    }
}

if (!function_exists('apiPaginated')) {

    function apiPaginated(
        \Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator,
        ?string $message = null,
        ?callable $transform = null,
    ): \Illuminate\Http\JsonResponse {
        $items = $transform
            ? $paginator->through($transform)->values()
            : $paginator->items();

        return apiResponse(
            data: $items,
            message: $message,
            meta: [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'has_more_pages' => $paginator->hasMorePages(),
            ],
        );
    }
}
