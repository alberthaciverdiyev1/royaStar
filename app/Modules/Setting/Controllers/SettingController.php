<?php

namespace App\Modules\Setting\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Setting\Actions\ShowSettingAction;
use App\Modules\Setting\Actions\UpdateSettingAction;
use App\Modules\Setting\Requests\UpdateSettingRequest;
use App\Modules\Setting\Resources\SettingResource;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class SettingController extends Controller
{
    public function __construct(
        private readonly ShowSettingAction    $showSettingAction,
        private readonly UpdateSettingAction  $updateSettingAction,
    ) {}

    #[OA\Get(path: '/settings', summary: 'Get settings', tags: ['Settings'],
        security: [[]],
        responses: [new OA\Response(response: 200, description: 'Settings data')]),
    ]
    public function show(): JsonResponse
    {
        return apiResponse(data: new SettingResource($this->showSettingAction->execute()));
    }

    #[OA\Put(path: '/admin/settings', summary: 'Update settings',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(properties: [
            new OA\Property(property: 'app_name', type: 'string'),
            new OA\Property(property: 'description', type: 'string'),
            new OA\Property(property: 'keywords', type: 'string'),
            new OA\Property(property: 'logo', type: 'string'),
            new OA\Property(property: 'favicon', type: 'string'),
            new OA\Property(property: 'email', type: 'string', format: 'email'),
            new OA\Property(property: 'phone', type: 'string'),
            new OA\Property(property: 'facebook', type: 'string'),
            new OA\Property(property: 'instagram', type: 'string'),
            new OA\Property(property: 'maintenance_mode', type: 'boolean'),
            new OA\Property(property: 'registration_open', type: 'boolean'),
        ])),
        tags: ['Settings'],
        responses: [new OA\Response(response: 200, description: 'Settings updated')]),
    ]
    public function update(UpdateSettingRequest $request): JsonResponse
    {
        return apiResponse(
            data: new SettingResource($this->updateSettingAction->execute($request->validated())),
            message: 'crud.updated'
        );
    }
}
