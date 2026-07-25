<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

abstract class BaseResource extends JsonResource
{
    protected function isAdmin(): bool
    {
        if (app()->has('admin_view')) {
            return true;
        }

        $user = auth()->user() ?? auth()->guard('sanctum')->user();

        return $user && ($user->hasAnyRole(['super-admin', 'admin']) || $user->type === 'admin');
    }

    protected function translate(string $field): mixed
    {
        return $this->resource->localeValue($field);
    }
}
