<?php

namespace App\Modules\Payment\Actions\Subscription;

use App\Actions\BaseStoreAction;
use App\Modules\Payment\Models\Subscription;

class StoreSubscriptionAction extends BaseStoreAction
{
    protected function modelClass(): string
    {
        return Subscription::class;
    }
}
