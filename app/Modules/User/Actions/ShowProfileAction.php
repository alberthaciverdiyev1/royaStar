<?php

namespace App\Modules\User\Actions;

use App\Modules\User\Models\User;
use Illuminate\Http\Request;

class ShowProfileAction
{
    public function execute(Request $request): User
    {
        return $request->user()->load(['student', 'teacher', 'family', 'school']);
    }
}
