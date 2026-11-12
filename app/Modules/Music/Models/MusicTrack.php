<?php

namespace App\Modules\Music\Models;

use App\Traits\SerializesDates;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MusicTrack extends Model
{
    use SoftDeletes, SerializesDates;

    protected $fillable = [
        'title',
        'artist',
        'url',
        'cover',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public static function extractYoutubeId(?string $url): ?string
    {
        if (blank($url)) {
            return null;
        }

        $pattern = '~(?:youtube\.com/(?:watch\?(?:[^#]*&)?v=|embed/|shorts/|live/|v/)|youtu\.be/)([A-Za-z0-9_-]{11})~i';

        return preg_match($pattern, $url, $matches) ? $matches[1] : null;
    }

    public function youtubeId(): ?string
    {
        return self::extractYoutubeId($this->url);
    }

    public function sourceType(): string
    {
        return $this->youtubeId() ? 'youtube' : 'audio';
    }

    public function thumbnailUrl(): ?string
    {
        if (filled($this->cover)) {
            return $this->cover;
        }

        $youtubeId = $this->youtubeId();

        return $youtubeId ? "https://i.ytimg.com/vi/{$youtubeId}/hqdefault.jpg" : null;
    }
}
