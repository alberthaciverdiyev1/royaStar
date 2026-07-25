<?php

namespace App\Modules\City\Actions;

use App\Actions\BaseShowAction;
use App\Modules\City\Models\City;

class ShowCityAction extends BaseShowAction
{
    protected function modelClass(): string
    {
        return City::class;
    }
}
