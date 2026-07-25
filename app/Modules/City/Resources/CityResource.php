<?php

namespace App\Modules\City\Resources;

use App\Http\Resources\BaseResource;
use Illuminate\Http\Request;

class CityResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->translate('name'),
            'created_at' => $this->created_at,
        ];
    }
}
