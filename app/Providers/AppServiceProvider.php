<?php

namespace App\Providers;

use App\Modules\User\Models\User;
use App\Modules\User\Policies\UserPolicy;
use Carbon\Carbon;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Filesystem;
use PlatformCommunity\Flysystem\BunnyCDN\BunnyCDNAdapter;
use PlatformCommunity\Flysystem\BunnyCDN\BunnyCDNClient;

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

        Storage::extend('bunnycdn', function ($app, $config) {
            $client = new BunnyCDNClient(
                $config['storage_zone'],
                $config['api_key'],
                $config['region'] ?? 'de'
            );

            return new Filesystem(
                new BunnyCDNAdapter($client, $config['pull_zone_url'] ?? '')
            );
        });
    }
}
