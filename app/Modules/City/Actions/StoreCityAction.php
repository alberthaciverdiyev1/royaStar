<?php

namespace App\Modules\City\Actions;

use App\Actions\BaseStoreAction;
use App\Modules\City\Models\City;

class StoreCityAction extends BaseStoreAction
{
    protected function modelClass(): string
    {
        return City::class;
    }
}
