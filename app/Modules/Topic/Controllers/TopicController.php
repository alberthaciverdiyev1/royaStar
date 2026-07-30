<?php

namespace App\Modules\Topic\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Topic\Actions\DeleteTopicAction;
use App\Modules\Topic\Actions\ListTopicsAction;
use App\Modules\Topic\Actions\ShowTopicAction;
use App\Modules\Topic\Actions\StoreTopicAction;
use App\Modules\Topic\Actions\UpdateTopicAction;
use App\Modules\Topic\Models\Topic;
use App\Modules\Topic\Requests\StoreTopicRequest;
use App\Modules\Topic\Requests\UpdateTopicRequest;
use App\Modules\Topic\Resources\TopicResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class TopicController extends Controller
{
    public function __construct(
        private readonly ListTopicsAction  $listTopicsAction,
        private readonly ShowTopicAction   $showTopicAction,
        private readonly StoreTopicAction  $storeTopicAction,
        private readonly UpdateTopicAction $updateTopicAction,
        private readonly DeleteTopicAction $deleteTopicAction,
    ) {}

    #[OA\Get(path: '/topics', summary: 'List topics', tags: ['Topics'],
        parameters: [
            new OA\QueryParameter(name: 'search', description: 'Search by name', schema: new OA\Schema(type: 'string')),
            new OA\QueryParameter(name: 'difficulty_level', description: 'Filter by difficulty level (1-5)', schema: new OA\Schema(type: 'integer')),
            new OA\QueryParameter(name: 'grade_ids', description: 'Comma-separated grade IDs (e.g. ?grade_ids=5,6)', schema: new OA\Schema(type: 'string')),
            new OA\QueryParameter(name: 'created_at_min', description: 'Start date (Y-m-d)', schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\QueryParameter(name: 'created_at_max', description: 'End date (Y-m-d)', schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\QueryParameter(name: 'order_by', description: 'Sort column (e.g. name, created_at)', schema: new OA\Schema(type: 'string')),
            new OA\QueryParameter(name: 'order_type', description: 'Sort direction (asc or desc)', schema: new OA\Schema(type: 'string')),
            new OA\QueryParameter(name: 'per_page', description: 'Items per page (default 20)', schema: new OA\Schema(type: 'integer')),
            new OA\QueryParameter(name: 'page', description: 'Page number', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'List of topics')]),
    ]
    public function index(Request $request): JsonResponse
    {
        $paginator = $this->listTopicsAction->execute($request->all());

        return apiPaginated($paginator, transform: fn($topic) => new TopicResource($topic));
    }

    #[OA\Post(path: '/admin/topics', summary: 'Create topic',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['name', 'difficulty_level'], properties: [
            new OA\Property(property: 'name', type: 'string'),
            new OA\Property(property: 'difficulty_level', type: 'integer', description: '1=Beginner, 2=Elementary, 3=Intermediate, 4=Advanced, 5=Expert'),
            new OA\Property(property: 'grade_ids', type: 'array', items: new OA\Items(type: 'integer'), description: 'Array of grade IDs'),
        ])),
        tags: ['Topics'],
        responses: [new OA\Response(response: 201, description: 'Topic created')]),
    ]
    public function store(StoreTopicRequest $request): JsonResponse
    {
        return apiResponse(
            data: new TopicResource(
                $this->storeTopicAction->execute($request->validated())),
            statusCode: 201
        );
    }

    #[OA\Get(path: '/topics/{topic}', summary: 'Get topic by ID', tags: ['Topics'],
        parameters: [
            new OA\PathParameter(name: 'topic', description: 'Topic ID', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Topic data')]),
    ]
    public function show(Topic $topic): JsonResponse
    {
        return apiResponse(data: new TopicResource($this->showTopicAction->execute($topic->id)));
    }

    #[OA\Put(path: '/admin/topics/{topic}', summary: 'Update topic',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(properties: [
            new OA\Property(property: 'name', type: 'string'),
            new OA\Property(property: 'difficulty_level', type: 'integer', description: '1=Beginner, 2=Elementary, 3=Intermediate, 4=Advanced, 5=Expert'),
            new OA\Property(property: 'grade_ids', type: 'array', items: new OA\Items(type: 'integer'), description: 'Array of grade IDs'),
        ])),
        tags: ['Topics'],
        responses: [new OA\Response(response: 200, description: 'Topic updated')]),
    ]
    public function update(Topic $topic, UpdateTopicRequest $request): JsonResponse
    {
        return apiResponse(data: new TopicResource($this->updateTopicAction->execute($topic->id, $request->validated())), message: 'crud.updated');
    }

    #[OA\Delete(path: '/admin/topics/{topic}', summary: 'Delete topic', tags: ['Topics'],
        responses: [new OA\Response(response: 200, description: 'Topic deleted')]),
    ]
    public function delete(Topic $topic): JsonResponse
    {
        $this->deleteTopicAction->execute($topic->id);

        return apiResponse();
    }
}
