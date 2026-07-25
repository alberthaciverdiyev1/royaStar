<?php

namespace App\Modules\Payment\Models;

use App\Traits\SerializesDates;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriptionPlan extends Model
{
    use SoftDeletes, HasTranslations, SerializesDates;
    protected $fillable = ['name', 'old_price', 'price', 'duration', 'is_active'];

    protected function casts(): array
    {
        return [
            'name' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function features() { return $this->hasMany(SubscriptionPlanFeature::class); }
    public function subscriptions() { return $this->hasMany(Subscription::class); }
}
