<?php

namespace App\Modules\Lesson\Actions\Video;

use App\Actions\BaseStoreAction;
use App\Modules\Lesson\Models\Video;
use App\Modules\Notification\Services\NotificationService;
use Illuminate\Database\Eloquent\Model;

class StoreVideoAction extends BaseStoreAction
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

    protected function modelClass(): string
    {
        return Video::class;
    }

    protected function afterCreate(Model $model): void
    {
        /** @var Video $model */
        $this->notificationService->notifyVideo($model);
    }
}
