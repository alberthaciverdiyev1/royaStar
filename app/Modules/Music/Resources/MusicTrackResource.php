<?php

namespace App\Modules\Music\Resources;

use App\Http\Resources\BaseResource;
use Illuminate\Http\Request;

class MusicTrackResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'artist' => $this->artist,
            'url' => $this->url,
            'cover' => $this->cover,
            'thumbnail' => $this->thumbnailUrl(),
            'source_type' => $this->sourceType(),
            'youtube_id' => $this->youtubeId(),
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
