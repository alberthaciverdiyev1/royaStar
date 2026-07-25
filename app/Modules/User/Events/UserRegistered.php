<?php

namespace App\Modules\User\Events;

use App\Modules\User\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

class UserRegistered
{
    use Dispatchable;

    public function __construct(
        public readonly User $user,
        public readonly array $data,
    ) {}
}
