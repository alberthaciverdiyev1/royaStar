<?php

namespace App\Modules\Teacher\Resources;

use App\Modules\User\Resources\UserResource;
use Illuminate\Http\Request;
use App\Http\Resources\BaseResource;

class TeacherResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'city_id' => $this->city_id,
            'created_at' => $this->created_at,
            $this->mergeWhen($this->relationLoaded('user'), [
                'user' => new UserResource($this->user),
            ]),
        ];
    }
}
