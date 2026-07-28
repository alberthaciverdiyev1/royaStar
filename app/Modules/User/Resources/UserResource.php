<?php

namespace App\Modules\User\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\BaseResource;

class UserResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'surname' => $this->surname,
            'phone' => $this->phone,
            'email' => $this->email,
            'avatar' => $this->avatar,
            'type' => $this->type,
            'is_approved' => $this->is_approved,
            'created_at' => $this->created_at,
        ];
    }
}
