<?php

namespace App\Modules\Popup\Actions;

use App\Actions\BaseUpdateAction;
use App\Modules\Popup\Models\Popup;

class UpdatePopupAction extends BaseUpdateAction
{
    use HandlesImage;

    protected function modelClass(): string
    {
        return Popup::class;
    }

    protected function beforeUpdate(array $data): array
    {
        return $this->persistImage($data);
    }
}
