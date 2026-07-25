<?php

namespace App\Modules\Grade\Models;

use App\Traits\SerializesDates;

use App\Modules\Topic\Models\Topic;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Grade extends Model
{
    use SoftDeletes, HasTranslations, SerializesDates;

    protected $fillable = ['name'];

    protected function casts(): array
    {
        return ['name' => 'array'];
    }

    public function topics() { return $this->belongsToMany(Topic::class, 'grade_topics')->withTimestamps(); }
}
