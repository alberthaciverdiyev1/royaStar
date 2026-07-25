<?php

namespace App\Modules\User\Policies;

use App\Modules\User\Models\User;

class UserPolicy
{
    public function view(User $user, User $target): bool
    {
        return $user->id === $target->id || $user->hasRole('admin');
    }

    public function update(User $user, User $target): bool
    {
        return $user->id === $target->id || $user->hasRole('admin');
    }

    public function delete(User $user, User $target): bool
    {
        return $user->hasRole('admin');
    }
}
