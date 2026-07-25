<?php

namespace App\Modules\Payment\Actions\Plan;

use App\Actions\BaseUpdateAction;
use App\Modules\Payment\Models\SubscriptionPlan;

class UpdatePlanAction extends BaseUpdateAction
{
    protected function modelClass(): string
    {
        return SubscriptionPlan::class;
    }
}
