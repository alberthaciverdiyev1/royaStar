<?php

namespace App\Modules\Parent\Resources;

use App\Modules\User\Resources\UserResource;
use Illuminate\Http\Request;
use App\Http\Resources\BaseResource;

class FamilyResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at,
            $this->mergeWhen($this->relationLoaded('user'), [
                'user' => new UserResource($this->user),
            ]),
        ];
    }
}
