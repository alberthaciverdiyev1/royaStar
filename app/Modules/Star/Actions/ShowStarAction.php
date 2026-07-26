<?php

namespace App\Modules\Star\Actions;

use App\Actions\BaseShowAction;
use App\Modules\Star\Models\Star;

class ShowStarAction extends BaseShowAction
{
    protected function modelClass(): string
    {
        return Star::class;
    }
}
