<?php

namespace App\Actions;

use Illuminate\Database\Eloquent\Model;

abstract class BaseShowAction
{
    abstract protected function modelClass(): string;

    protected function defaultWith(): array
    {
        return [];
    }

    protected function authorize(Model $model): void
    {
        // Override to add authorization, e.g.:
        // Gate::authorize('view', $model);
    }

    public function execute(int $id): Model
    {
        $model = ($this->modelClass())::with($this->defaultWith())->findOrFail($id);

        $this->authorize($model);

        return $model;
    }
}
