<?php

namespace App\Modules\City\Actions;

use App\Actions\BaseUpdateAction;
use App\Modules\City\Models\City;

class UpdateCityAction extends BaseUpdateAction
{
    protected function modelClass(): string
    {
        return City::class;
    }
}
