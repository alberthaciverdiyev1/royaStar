<?php

namespace App\Modules\Payment\Actions\Plan;

use App\Actions\BaseShowAction;
use App\Modules\Payment\Models\SubscriptionPlan;

class ShowPlanAction extends BaseShowAction
{
    protected function modelClass(): string
    {
        return SubscriptionPlan::class;
    }

    protected function defaultWith(): array
    {
        return ['features', 'subscriptions'];
    }
}
