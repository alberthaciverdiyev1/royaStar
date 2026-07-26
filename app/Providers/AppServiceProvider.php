<?php

namespace App\Providers;

use App\Modules\User\Models\User;
use App\Modules\User\Policies\UserPolicy;
use Carbon\Carbon;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    protected $policies = [
        User::class => UserPolicy::class,
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->registerPolicies();

        Carbon::serializeUsing(fn (Carbon $carbon) => $carbon->format('Y-m-d H:i:s'));
    }
}
