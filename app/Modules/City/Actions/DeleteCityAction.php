<?php

namespace App\Modules\City\Actions;

use App\Actions\BaseDeleteAction;
use App\Modules\City\Models\City;

class DeleteCityAction extends BaseDeleteAction
{
    protected function modelClass(): string
    {
        return City::class;
    }
}
