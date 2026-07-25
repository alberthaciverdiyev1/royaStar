<?php

namespace App\Modules\Payment\Models;

use App\Traits\SerializesDates;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlanFeature extends Model
{
    use SerializesDates;
    protected $fillable = ['subscription_plan_id', 'subscription_feature_id', 'value', 'value_type'];

    public function plan() { return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id'); }
    public function feature() { return $this->belongsTo(SubscriptionFeature::class, 'subscription_feature_id'); }
}
