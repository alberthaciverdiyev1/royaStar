<?php

namespace App\Modules\Setting\Actions;

use App\Modules\Setting\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class UpdateSettingAction
{
    public function execute(array $data): Setting
    {
        return DB::transaction(function () use ($data) {
            $setting = Setting::firstOr(fn () => Setting::create([
                'app_name' => 'RoyaStar',
            ]));

            $setting->update($data);
            $setting->refresh();

            if (array_key_exists('texts', $data)) {
                Cache::forget('website_texts.stored');
            }

            return $setting;
        });
    }
}
