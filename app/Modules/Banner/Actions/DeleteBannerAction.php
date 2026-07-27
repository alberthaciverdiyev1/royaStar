<?php

namespace App\Modules\Banner\Actions;

use App\Actions\BaseDeleteAction;
use App\Modules\Banner\Models\Banner;

class DeleteBannerAction extends BaseDeleteAction
{
    protected function modelClass(): string
    {
        return Banner::class;
    }
}
