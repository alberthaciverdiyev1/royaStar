<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('stars:reset-monthly')
    ->monthlyOn(31, '23:59')
    ->description('Monthly star leaderboard cycle transition at end of month 23:59');
