<?php

namespace App\Modules\Star\Actions;

use App\Actions\BaseUpdateAction;
use App\Modules\Star\Models\Star;

class UpdateStarAction extends BaseUpdateAction
{
    protected function modelClass(): string
    {
        return Star::class;
    }
}
