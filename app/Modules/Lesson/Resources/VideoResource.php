<?php

namespace App\Modules\Lesson\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\BaseResource;

class VideoResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'lesson_id' => $this->lesson_id,
            'name' => $this->name,
            'youtube_url' => $this->youtube_url,
            'embed_url' => $this->embed_url,
            'lang' => $this->lang,
            'created_at' => $this->created_at,
        ];
    }
}
