<?php

namespace App\Modules\Subject\Models;

use App\Modules\Topic\Models\Topic;
use App\Traits\SerializesDates;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use HasFactory, SoftDeletes, SerializesDates;

    protected $fillable = ['name', 'image'];

    public function topics()
    {
        return $this->hasMany(Topic::class);
    }
}
