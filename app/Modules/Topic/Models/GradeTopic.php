<?php

namespace App\Modules\Topic\Models;

use App\Modules\Grade\Models\Grade;
use App\Traits\SerializesDates;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GradeTopic extends Model
{
    use SoftDeletes, SerializesDates;
    protected $fillable = ['grade_id', 'topic_id'];

    public function grade() { return $this->belongsTo(Grade::class); }
    public function topic() { return $this->belongsTo(Topic::class); }
}
