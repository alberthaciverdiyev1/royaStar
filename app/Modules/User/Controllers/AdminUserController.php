<?php

namespace App\Modules\User\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\User\Models\User;
use App\Modules\User\Resources\UserResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AdminUserController extends Controller
{
    /**
     * List all users (pending/unapproved users placed on top first, then sorted by created_at desc)
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status'); // 'pending', 'approved', or null (all)

        $users = User::with(['student.grade', 'student.city'])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('surname', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($status === 'pending', fn($q) => $q->where('is_approved', false))
            ->when($status === 'approved', fn($q) => $q->where('is_approved', true))
            ->orderBy('is_approved', 'asc') // is_approved = false (0) comes BEFORE true (1)!
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return apiPaginated($users, transform: fn($user) => new UserResource($user));
    }

    /**
     * Get user details
     */
    public function show(User $user)
    {
        $user->load(['student.grade', 'student.city', 'userStars.star']);

        return apiResponse(data: new UserResource($user), message: __('crud.read_success'));
    }

    /**
     * List pending users (backward compatibility)
     */
    public function pending()
    {
        $users = User::with(['student.grade', 'student.city'])
            ->where('is_approved', false)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return apiPaginated($users, transform: fn($user) => new UserResource($user));
    }

    /**
     * Approve user
     */
    public function approve(User $user)
    {
        $user->update(['is_approved' => true]);

        return apiResponse(data: new UserResource($user), message: __('auth.account_approved'));
    }

    /**
     * Change user password as admin
     */
    public function changePassword(Request $request, User $user)
    {
        $request->validate([
            'password' => ['required', 'string', 'min:6'],
        ]);

        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
        ]);

        return apiResponse(data: new UserResource($user), message: 'Şifrə uğurla yeniləndi');
    }
}
