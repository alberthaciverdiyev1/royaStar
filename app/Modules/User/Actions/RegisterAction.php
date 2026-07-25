<?php

namespace App\Modules\User\Actions;

use App\Modules\School\Models\SchoolRegistrationRequest;
use App\Modules\User\Events\UserRegistered;
use App\Modules\User\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterAction
{
    public function execute(array $data): User|SchoolRegistrationRequest
    {
        return DB::transaction(function () use ($data) {
            if (($data['type'] ?? null) === 'school') {
                return SchoolRegistrationRequest::create([
                    'name' => null,
                    'surname' => null,
                    'phone' => $data['phone'] ?? null,
                    'email' => $data['email'],
                    'password' => null,
                    'school_name' => $data['school']['name'] ?? null,
                    'school_no' => $data['school']['no'] ?? null,
                    'city_id' => $data['school']['city_id'] ?? null,
                ]);
            }

            $data['password'] = Hash::make($data['password']);

            $user = User::create(collect($data)->only(['name', 'surname', 'phone', 'email', 'password', 'avatar', 'type'])->toArray());

            $user->assignRole($data['type'] ?? 'student');

            UserRegistered::dispatch($user, $data);

            return $user;
        });
    }
}
