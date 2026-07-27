<?php

namespace App\Modules\Banner\Actions;

use App\Actions\BaseStoreAction;
use App\Modules\Banner\Models\Banner;

class StoreBannerAction extends BaseStoreAction
{
    protected function modelClass(): string
    {
        return Banner::class;
    }
}
