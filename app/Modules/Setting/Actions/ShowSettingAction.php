<?php

namespace App\Modules\Setting\Actions;

use App\Modules\Setting\Models\Setting;

class ShowSettingAction
{
    public function execute(): Setting
    {
        return Setting::firstOr(fn () => Setting::create([
            'app_name' => ['az' => 'RoyaStar', 'en' => 'RoyaStar', 'ru' => 'RoyaStar'],
        ]));
    }
}
