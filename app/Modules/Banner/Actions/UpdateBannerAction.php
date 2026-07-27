<?php

namespace App\Modules\Banner\Actions;

use App\Actions\BaseUpdateAction;
use App\Modules\Banner\Models\Banner;

class UpdateBannerAction extends BaseUpdateAction
{
    protected function modelClass(): string
    {
        return Banner::class;
    }
}
