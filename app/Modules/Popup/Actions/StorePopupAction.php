<?php

namespace App\Modules\Popup\Actions;

use App\Actions\BaseStoreAction;
use App\Modules\Popup\Models\Popup;

class StorePopupAction extends BaseStoreAction
{
    use HandlesImage;

    protected function modelClass(): string
    {
        return Popup::class;
    }

    protected function beforeCreate(array $data): array
    {
        return $this->persistImage($data);
    }
}
