<?php

namespace App\Providers;

use App\Modules\Music\ViewComposers\MusicPlayerComposer;
use App\Modules\Notification\ViewComposers\NotificationBellComposer;
use App\Modules\User\Models\User;
use App\Modules\User\Policies\UserPolicy;
use Carbon\Carbon;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\View;

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

        // Supplies the page-targeted playlist to the global site layout.
        View::composer('layouts.app', MusicPlayerComposer::class);

        // Supplies the header bell's unread count and latest notifications.
        View::composer('layouts.app', NotificationBellComposer::class);
    }
}
