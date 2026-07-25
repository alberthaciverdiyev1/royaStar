<?php

namespace App\Modules\User\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\User\Actions\ShowProfileAction;
use App\Modules\User\Actions\UpdateProfileAction;
use App\Modules\User\Requests\UpdateProfileRequest;
use App\Modules\User\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ProfileController extends Controller
{
    public function __construct(
        private readonly ShowProfileAction   $showProfileAction,
        private readonly UpdateProfileAction $updateProfileAction,
    )
    {
    }

    #[OA\Get(path: '/profile', summary: 'Get authenticated user profile', security: [['bearerAuth' => []]],
        tags: ['Profile'],
        responses: [
            new OA\Response(response: 200, description: 'User profile data'),
        ]),
    ]
    public function show(Request $request): JsonResponse
    {
        return apiResponse(data: ['user' => new UserResource($this->showProfileAction->execute($request))]);
    }

    #[OA\Put(path: '/profile', summary: 'Update profile', security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(properties: [
            new OA\Property(property: 'name', type: 'string'),
            new OA\Property(property: 'surname', type: 'string'),
            new OA\Property(property: 'avatar', type: 'string'),
            new OA\Property(property: 'password', type: 'string', minLength: 8),
        ])),
        tags: ['Profile'],
        responses: [
            new OA\Response(response: 200, description: 'Profile updated'),
        ]),
    ]
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $this->updateProfileAction->execute($request->user(), $request->validated());
        return apiResponse(data: ['user' => new UserResource($user)]);
    }
}
