<?php

namespace App\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

abstract class BaseStoreAction
{
    abstract protected function modelClass(): string;

    protected function authorize(): void
    {
        // Override to add authorization, e.g.:
        // Gate::authorize('create', $this->modelClass());
    }

    protected function beforeCreate(array $data): array
    {
        return $data;
    }

    protected function afterCreate(Model $model): void
    {
        // Override to dispatch events, log, etc.
    }

    public function execute(array $data): Model
    {
        $this->authorize();

        $data = $this->beforeCreate($data);

        return DB::transaction(function () use ($data) {
            $model = ($this->modelClass())::create($data);

            $this->afterCreate($model);

            return $model;
        });
    }
}
