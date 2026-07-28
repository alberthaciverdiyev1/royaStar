<?php

namespace App\Modules\User\Actions;

use App\Modules\Star\Services\StarService;
use App\Modules\User\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginAction
{
    public function __construct(
        private readonly StarService $starService,
    ) {}

    public function execute(string $login, string $password): User
    {
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        $user = User::where($field, $login)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages(['login' => [__('auth.invalid_credentials')]]);
        }

        if (!$user->is_approved) {
            throw ValidationException::withMessages(['login' => [__('auth.account_pending')]]);
        }

        if ($user->hasRole('student')) {
            $this->starService->awardDailyLogin($user->id);
        }

        return $user;
    }
}
