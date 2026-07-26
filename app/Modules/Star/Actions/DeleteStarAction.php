<?php

namespace App\Modules\Star\Actions;

use App\Actions\BaseDeleteAction;
use App\Modules\Star\Models\Star;

class DeleteStarAction extends BaseDeleteAction
{
    protected function modelClass(): string
    {
        return Star::class;
    }
}
