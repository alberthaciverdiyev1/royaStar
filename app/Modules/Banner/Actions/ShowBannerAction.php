<?php

namespace App\Modules\Banner\Actions;

use App\Actions\BaseShowAction;
use App\Modules\Banner\Models\Banner;

class ShowBannerAction extends BaseShowAction
{
    protected function modelClass(): string
    {
        return Banner::class;
    }
}
