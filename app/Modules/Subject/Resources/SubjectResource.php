<?php

namespace App\Modules\Subject\Resources;

use App\Http\Resources\BaseResource;
use App\Modules\Topic\Resources\TopicResource;
use Illuminate\Http\Request;

class SubjectResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->translate('name'),
            'image' => $this->image,
            'created_at' => $this->created_at,
            $this->mergeWhen($this->relationLoaded('topics'), [
                'topics' => TopicResource::collection($this->topics),
            ]),
        ];
    }
}
