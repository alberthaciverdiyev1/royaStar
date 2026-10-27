<?php

namespace App\Modules\Popup\Actions;

use App\Actions\BaseDeleteAction;
use App\Modules\Popup\Models\Popup;

class DeletePopupAction extends BaseDeleteAction
{
    protected function modelClass(): string
    {
        return Popup::class;
    }
}
