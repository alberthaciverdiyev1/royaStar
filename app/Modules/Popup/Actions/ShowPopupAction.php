<?php

namespace App\Modules\Popup\Actions;

use App\Modules\Popup\Models\Popup;

class ShowPopupAction
{
    public function execute(int $id): Popup
    {
        return Popup::query()->findOrFail($id);
    }
}
