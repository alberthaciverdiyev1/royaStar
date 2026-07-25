<?php

namespace App\Modules\Payment\Actions\Plan;

use App\Actions\BaseDeleteAction;
use App\Modules\Payment\Models\SubscriptionPlan;

class DeletePlanAction extends BaseDeleteAction
{
    protected function modelClass(): string
    {
        return SubscriptionPlan::class;
    }
}
