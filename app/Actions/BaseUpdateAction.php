<?php

namespace App\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

abstract class BaseUpdateAction
{
    abstract protected function modelClass(): string;

    protected function authorize(Model $model): void
    {
        // Override to add authorization, e.g.:
        // Gate::authorize('update', $model);
    }

    protected function beforeUpdate(array $data): array
    {
        return $data;
    }

    protected function afterUpdate(Model $model): void
    {
        // Override to dispatch events, log, etc.
    }

    public function execute(int $id, array $data): Model
    {
        return DB::transaction(function () use ($id, $data) {
            $model = ($this->modelClass())::findOrFail($id);

            $this->authorize($model);

            $data = $this->beforeUpdate($data);

            $model->update($data);

            $this->afterUpdate($model);

            return $model->fresh();
        });
    }
}
