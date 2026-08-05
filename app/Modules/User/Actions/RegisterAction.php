<?php

namespace App\Modules\User\Actions;

use App\Modules\User\Events\UserRegistered;
use App\Modules\User\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterAction
{
    public function execute(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $data['password'] = Hash::make($data['password']);

            $user = User::create(collect($data)->only(['name', 'surname', 'phone', 'email', 'password', 'avatar', 'type'])->toArray());

            $user->assignRole($data['type'] ?? 'student');

            UserRegistered::dispatch($user, $data);

            return $user;
        });
    }
}
