<?php

namespace App\Modules\User\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendOtpNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public function __construct(
        public readonly string $phone,
        public readonly string $code,
    ) {}

    public function handle(): void
    {
        // TODO: Integrate SMS/email provider
        // e.g., SmsService::send($this->phone, "Your OTP: {$this->code}");
        logger()->info('OTP sent', ['phone' => $this->phone, 'code' => $this->code]);
    }
}
