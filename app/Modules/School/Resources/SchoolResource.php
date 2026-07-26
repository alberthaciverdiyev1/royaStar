<?php

namespace App\Modules\School\Resources;

use App\Modules\User\Resources\UserResource;
use App\Modules\City\Resources\CityResource;
use Illuminate\Http\Request;
use App\Http\Resources\BaseResource;

class SchoolResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'name' => $this->name,
            'city_id' => $this->city_id,
            'created_at' => $this->created_at,
            $this->mergeWhen($this->relationLoaded('user'), [
                'user' => new UserResource($this->user),
            ]),
            $this->mergeWhen($this->relationLoaded('city'), [
                'city' => new CityResource($this->city),
            ]),
        ];
    }
}
