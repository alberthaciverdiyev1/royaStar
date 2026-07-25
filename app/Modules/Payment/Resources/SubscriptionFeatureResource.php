<?php

namespace App\Modules\Payment\Resources;

use App\Http\Resources\BaseResource;
use Illuminate\Http\Request;

class SubscriptionFeatureResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->translate('name'),
            'description' => $this->translate('description'),
            'created_at' => $this->created_at,
        ];
    }
}
