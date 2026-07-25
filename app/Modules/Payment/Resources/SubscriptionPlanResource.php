<?php

namespace App\Modules\Payment\Resources;

use App\Modules\Payment\Resources\SubscriptionPlanFeatureResource;
use App\Http\Resources\BaseResource;
use Illuminate\Http\Request;

class SubscriptionPlanResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->translate('name'),
            'price' => $this->price,
            'old_price' => $this->old_price,
            'duration' => $this->duration,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            $this->mergeWhen($this->relationLoaded('features'), [
                'features' => SubscriptionPlanFeatureResource::collection($this->features),
            ]),
        ];
    }
}
