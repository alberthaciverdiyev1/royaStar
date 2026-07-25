<?php

namespace App\Modules\Lesson\Models;

use App\Traits\SerializesDates;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonView extends Model
{
    use SerializesDates;

    protected $fillable = ['lesson_id', 'count'];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}
