<?php

namespace App\Modules\Star\Resources;

use App\Http\Resources\BaseResource;
use Illuminate\Http\Request;

class StarResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'name' => $this->name,
            'description' => $this->description,
            'icon' => $this->icon,
            'category' => $this->category,
            'group' => $this->group,
            'point' => $this->point,
            'point_min' => $this->point_min,
            'point_max' => $this->point_max,
            'point_default' => $this->point_default,
            'is_active' => $this->is_active,
            'max_per_day' => $this->max_per_day,
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at,
        ];
    }
}
