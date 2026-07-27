<?php

namespace App\Modules\Banner\Resources;

use App\Http\Resources\BaseResource;
use Illuminate\Http\Request;

class BannerResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'image' => $this->image,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'created_at' => $this->created_at,
        ];
    }
}
