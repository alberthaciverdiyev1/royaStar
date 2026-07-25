<?php

namespace App\Modules\Payment\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\BaseResource;

class SubscriptionPlanFeatureResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'subscription_plan_id' => $this->subscription_plan_id,
            'subscription_feature_id' => $this->subscription_feature_id,
            'value' => $this->value,
            'value_type' => $this->value_type,
            $this->mergeWhen($this->relationLoaded('feature'), [
                'feature' => new SubscriptionFeatureResource($this->feature),
            ]),
        ];
    }
}
