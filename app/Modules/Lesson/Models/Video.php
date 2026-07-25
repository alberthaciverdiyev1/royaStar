<?php

namespace App\Modules\Lesson\Models;

use App\Traits\SerializesDates;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    use SerializesDates;

    protected $fillable = ['lesson_id', 'name', 'youtube_url', 'lang'];

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    public function getEmbedUrlAttribute(): ?string
    {
        return $this->youtube_id
            ? sprintf('https://www.youtube.com/embed/%s', $this->youtube_id)
            : null;
    }

    public function getYoutubeIdAttribute(): ?string
    {
        if (!$this->youtube_url) {
            return null;
        }

        preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $this->youtube_url, $matches);

        return $matches[1] ?? null;
    }
}
