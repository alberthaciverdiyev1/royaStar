<?php

namespace App\Modules\Lesson\Resources;

use App\Http\Resources\BaseResource;
use Illuminate\Http\Request;

class LessonReviewResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rating' => $this->rating,
            'review' => $this->review,
            'user' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'email' => $this->user?->email,
                'avatar' => $this->user?->avatar,
            ],
            'lesson' => [
                'id' => $this->lesson?->id,
                'name' => $this->lesson?->name,
            ],
            'created_at' => $this->created_at,
        ];
    }
}
