<?php

namespace App\Modules\Star\Actions;

use App\Actions\BaseStoreAction;
use App\Modules\Star\Models\Star;

class StoreStarAction extends BaseStoreAction
{
    protected function modelClass(): string
    {
        return Star::class;
    }
}
