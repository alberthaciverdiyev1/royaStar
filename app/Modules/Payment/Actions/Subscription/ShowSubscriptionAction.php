<?php

namespace App\Modules\Payment\Actions\Subscription;

use App\Actions\BaseShowAction;
use App\Modules\Payment\Models\Subscription;

class ShowSubscriptionAction extends BaseShowAction
{
    protected function modelClass(): string
    {
        return Subscription::class;
    }

    protected function defaultWith(): array
    {
        return ['plan', 'school', 'teacher', 'family', 'student'];
    }
}
