<?php

namespace App\Modules\Payment\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Payment\Actions\Subscription\ListSubscriptionsAction;
use App\Modules\Payment\Actions\Subscription\ShowSubscriptionAction;
use App\Modules\Payment\Actions\Subscription\StoreSubscriptionAction;
use App\Modules\Payment\Requests\StoreSubscriptionRequest;
use App\Modules\Payment\Resources\SubscriptionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class SubscriptionController extends Controller
{
    public function __construct(
        private readonly ListSubscriptionsAction  $listSubscriptionsAction,
        private readonly ShowSubscriptionAction   $showSubscriptionAction,
        private readonly StoreSubscriptionAction  $storeSubscriptionAction,
    ) {}

    #[OA\Get(path: '/subscriptions', tags: ['Subscriptions'], summary: 'List all subscriptions',
        parameters: [
            new OA\QueryParameter(name: 'school_id', description: 'Filter by school ID', schema: new OA\Schema(type: 'integer')),
            new OA\QueryParameter(name: 'teacher_id', description: 'Filter by teacher ID', schema: new OA\Schema(type: 'integer')),
            new OA\QueryParameter(name: 'status', description: 'Filter by status', schema: new OA\Schema(type: 'string')),
            new OA\QueryParameter(name: 'order_by', description: 'Sort column (e.g. created_at)', schema: new OA\Schema(type: 'string')),
            new OA\QueryParameter(name: 'order_type', description: 'Sort direction (asc or desc)', schema: new OA\Schema(type: 'string')),
            new OA\QueryParameter(name: 'per_page', description: 'Items per page (default 20)', schema: new OA\Schema(type: 'integer')),
            new OA\QueryParameter(name: 'page', description: 'Page number', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'List of subscriptions')]),
    ]
    public function index(Request $request): JsonResponse
    {
        $paginator = $this->listSubscriptionsAction->execute($request->all());

        return apiPaginated($paginator, transform: fn($subscription) => new SubscriptionResource($subscription));
    }

    #[OA\Get(path: '/subscriptions/{subscription}', tags: ['Subscriptions'], summary: 'Get subscription by ID',
        parameters: [
            new OA\PathParameter(name: 'subscription', description: 'Subscription ID', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Subscription data')]),
    ]
    public function show(int $subscription): JsonResponse
    {
        return apiResponse(data: new SubscriptionResource($this->showSubscriptionAction->execute($subscription)));
    }

    #[OA\Post(path: '/subscriptions', tags: ['Subscriptions'], summary: 'Create subscription',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['subscription_plan_id', 'start_date', 'expires_at', 'status'], properties: [
            new OA\Property(property: 'subscription_plan_id', type: 'integer'),
            new OA\Property(property: 'start_date', type: 'string', format: 'date'),
            new OA\Property(property: 'expires_at', type: 'string', format: 'date'),
            new OA\Property(property: 'status', type: 'string'),
            new OA\Property(property: 'school_id', type: 'integer', nullable: true),
            new OA\Property(property: 'teacher_id', type: 'integer', nullable: true),
        ])),
        responses: [new OA\Response(response: 201, description: 'Subscription created')]),
    ]
    public function store(StoreSubscriptionRequest $request): JsonResponse
    {
        return apiResponse(data: new SubscriptionResource($this->storeSubscriptionAction->execute($request->validated())), statusCode: 201);
    }
}
