<?php

namespace App\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

abstract class BaseDeleteAction
{
    abstract protected function modelClass(): string;

    protected function useTrashed(): bool
    {
        return false;
    }

    protected function authorize(Model $model): void
    {
        // Override to add authorization, e.g.:
        // Gate::authorize('delete', $model);
    }

    protected function beforeDelete(Model $model): void
    {
        // Override to run logic before deletion, e.g.:
        // check related records, log, etc.
    }

    protected function afterDelete(Model $model): void
    {
        // Override to dispatch events, log, etc.
    }

    public function execute(int $id, bool $force = false): void
    {
        DB::transaction(function () use ($id, $force) {
            $query = ($this->modelClass())::query();

            if ($this->useTrashed()) {
                $query->withTrashed();
            }

            $model = $query->findOrFail($id);

            $this->authorize($model);
            $this->beforeDelete($model);

            if ($force) {
                $model->forceDelete();
            } else {
                $model->delete();
            }

            $this->afterDelete($model);
        });
    }
}
