<?php

namespace App\Traits;

trait HasTranslations
{
    protected function isAdminContext(): bool
    {
        if (app()->has('admin_view')) {
            return true;
        }

        $user = auth()->user() ?? auth()->guard('sanctum')->user();

        return $user && ($user->hasAnyRole(['super-admin', 'admin']) || $user->type === 'admin');
    }

    public function localeValue(string $field): mixed
    {
        $value = $this->{$field};

        if ($this->isAdminContext()) {
            return $value;
        }

        if (!is_array($value)) {
            return $value;
        }

        $locale = app()->getLocale();

        return $value[$locale] ?? $value['az'] ?? reset($value) ?? '';
    }

    public function toArray(): array
    {
        $data = parent::toArray();

        if (!$this->isAdminContext()) {
            $locale = app()->getLocale();
            $casts = $this->casts();
            foreach ($casts as $field => $type) {
                if ($type === 'array' && isset($data[$field]) && is_array($data[$field])) {
                    $translations = $data[$field];
                    $data[$field] = $translations[$locale]
                        ?? $translations['az']
                        ?? reset($translations)
                        ?? '';
                }
            }
        }

        return $data;
    }

    public function isTranslatableAttribute(string $field): bool
    {
        $casts = $this->casts();
        return isset($casts[$field]) && $casts[$field] === 'array';
    }
}
