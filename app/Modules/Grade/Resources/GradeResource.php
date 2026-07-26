<?php

namespace App\Modules\Grade\Resources;

use App\Http\Resources\BaseResource;
use Illuminate\Http\Request;

class GradeResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'created_at' => $this->created_at,
        ];
    }
}
