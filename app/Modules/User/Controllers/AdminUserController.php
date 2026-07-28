<?php

namespace App\Modules\User\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\User\Models\User;
use App\Modules\User\Resources\UserResource;
use OpenApi\Attributes as OA;

class AdminUserController extends Controller
{
    #[OA\Get(
        path: '/admin/users/pending',
        summary: 'List pending users',
        security: [['bearerAuth' => []]],
        tags: ['Admin'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of pending users'
            ),
        ]),
    ]
    public function pending()
    {
        $users = User::where('is_approved', false)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return apiPaginated($users, transform: fn($user) => new UserResource($user));
    }

    #[OA\Post(
        path: '/admin/users/{user}/approve',
        summary: 'Approve a user',
        security: [['bearerAuth' => []]],
        tags: ['Admin'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'User approved'
            ),
        ]),
    ]
    public function approve(User $user)
    {
        $user->update(['is_approved' => true]);

        return apiResponse(message: __('auth.account_approved'));
    }
}
