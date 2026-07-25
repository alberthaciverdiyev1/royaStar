<?php

namespace App\Modules\User\Actions;

use App\Modules\User\Models\Otp;
use Illuminate\Validation\ValidationException;

class VerifyOtpAction
{
    public function execute(string $phone, string $otp): void
    {
        $record = Otp::where('phone', $phone)
            ->where('otp', $otp)
            ->where('expires_at', '>=', now())
            ->first();

        if (!$record) {
            throw ValidationException::withMessages(['otp' => [__('auth.invalid_or_expired_otp')]]);
        }

        $record->delete();
    }
}
