<?php

namespace App\Modules\User\Actions;

use App\Modules\User\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AdminLoginAction
{
    public function execute(string $email, string $password): User
    {
        $user = User::where('email', $email)->where('type', 'admin')->first();

        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages(['email' => [__('auth.invalid_credentials')]]);
        }

        return $user;
    }
}
