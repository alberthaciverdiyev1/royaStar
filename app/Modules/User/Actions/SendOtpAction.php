<?php

namespace App\Modules\User\Actions;

use App\Modules\User\Models\Otp;
use Illuminate\Support\Facades\DB;

class SendOtpAction
{
    public function execute(string $phone): void
    {
        DB::transaction(function () use ($phone) {
            Otp::where('phone', $phone)->delete();

            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            Otp::create([
                'phone' => $phone,
                'otp' => $code,
                'expires_at' => now()->addMinutes(10),
            ]);

            // TODO: Queue SMS/email sending with $code via SendOtpNotification job
            // \App\Modules\User\Jobs\SendOtpNotification::dispatch($phone, $code);
        });
    }
}
