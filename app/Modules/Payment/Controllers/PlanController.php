<?php

namespace App\Modules\Payment\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Payment\Actions\Plan\DeletePlanAction;
use App\Modules\Payment\Actions\Plan\ListPlansAction;
use App\Modules\Payment\Actions\Plan\ShowPlanAction;
use App\Modules\Payment\Actions\Plan\StorePlanAction;
use App\Modules\Payment\Actions\Plan\UpdatePlanAction;
use App\Modules\Payment\Requests\StorePlanRequest;
use App\Modules\Payment\Requests\UpdatePlanRequest;
use App\Modules\Payment\Resources\SubscriptionPlanResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class PlanController extends Controller
{
    public function __construct(
        private readonly ListPlansAction  $listPlansAction,
        private readonly ShowPlanAction   $showPlanAction,
        private readonly StorePlanAction  $storePlanAction,
        private readonly UpdatePlanAction $updatePlanAction,
        private readonly DeletePlanAction $deletePlanAction,
    ) {}

    #[OA\Get(path: '/plans', tags: ['Plans'], summary: 'List all subscription plans',
        security: [[]],
        parameters: [
            new OA\QueryParameter(name: 'order_by', description: 'Sort column (e.g. name, price, created_at)', schema: new OA\Schema(type: 'string')),
            new OA\QueryParameter(name: 'order_type', description: 'Sort direction (asc or desc)', schema: new OA\Schema(type: 'string')),
            new OA\QueryParameter(name: 'per_page', description: 'Items per page (default 20)', schema: new OA\Schema(type: 'integer')),
            new OA\QueryParameter(name: 'page', description: 'Page number', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'List of plans')]),
    ]
    public function index(Request $request): JsonResponse
    {
        $paginator = $this->listPlansAction->execute($request->all());

        return apiPaginated($paginator, transform: fn($plan) => new SubscriptionPlanResource($plan));
    }

    #[OA\Post(path: '/plans', tags: ['Plans'], summary: 'Create subscription plan',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['name', 'price', 'duration'], properties: [
            new OA\Property(property: 'name', type: 'object', description: 'Translated name'),
            new OA\Property(property: 'price', type: 'number', format: 'float'),
            new OA\Property(property: 'duration', type: 'integer', description: 'Duration in days'),
            new OA\Property(property: 'old_price', type: 'number', format: 'float', nullable: true),
        ])),
        responses: [new OA\Response(response: 201, description: 'Plan created')]),
    ]
    public function store(StorePlanRequest $request): JsonResponse
    {
        return apiResponse(data: new SubscriptionPlanResource($this->storePlanAction->execute($request->validated())), statusCode: 201);
    }

    #[OA\Get(path: '/plans/{plan}', tags: ['Plans'], summary: 'Get subscription plan by ID',
        security: [[]],
        parameters: [
            new OA\PathParameter(name: 'plan', description: 'Plan ID', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Plan data')]),
    ]
    public function show(int $plan): JsonResponse
    {
        return apiResponse(data: new SubscriptionPlanResource($this->showPlanAction->execute($plan)));
    }

    #[OA\Put(path: '/plans/{plan}', tags: ['Plans'], summary: 'Update subscription plan',
        responses: [new OA\Response(response: 200, description: 'Plan updated')]),
    ]
    public function update(int $plan, UpdatePlanRequest $request): JsonResponse
    {
        return apiResponse(data: new SubscriptionPlanResource($this->updatePlanAction->execute($plan, $request->validated())), message: 'crud.updated');
    }

    #[OA\Delete(path: '/plans/{plan}', tags: ['Plans'], summary: 'Delete subscription plan',
        responses: [new OA\Response(response: 200, description: 'Plan deleted')]),
    ]
    public function delete(int $plan): JsonResponse
    {
        $this->deletePlanAction->execute($plan);
        return apiResponse();
    }
}
