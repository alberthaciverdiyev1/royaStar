<?php

namespace App\Modules\Subject\Models;

use App\Modules\Topic\Models\Topic;
use App\Traits\HasTranslations;
use App\Traits\SerializesDates;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use HasFactory, SoftDeletes, HasTranslations, SerializesDates;

    protected $fillable = ['name', 'image'];

    protected function casts(): array
    {
        return ['name' => 'array'];
    }

    public function topics()
    {
        return $this->hasMany(Topic::class);
    }
}
