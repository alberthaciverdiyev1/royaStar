<?php

namespace App\Modules\Payment\Actions\Plan;

use App\Actions\BaseStoreAction;
use App\Modules\Payment\Models\SubscriptionPlan;

class StorePlanAction extends BaseStoreAction
{
    protected function modelClass(): string
    {
        return SubscriptionPlan::class;
    }
}
