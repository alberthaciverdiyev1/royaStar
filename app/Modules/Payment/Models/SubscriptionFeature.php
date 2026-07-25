<?php

namespace App\Modules\Payment\Models;

use App\Traits\SerializesDates;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriptionFeature extends Model
{
    use SoftDeletes, HasTranslations, SerializesDates;
    protected $fillable = ['name', 'description'];

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'description' => 'array',
        ];
    }
}
