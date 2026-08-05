<?php

namespace App\Modules\Lesson\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Lesson\Actions\Video\DeleteVideoAction;
use App\Modules\Lesson\Actions\Video\ListVideosAction;
use App\Modules\Lesson\Actions\Video\ShowVideoAction;
use App\Modules\Lesson\Actions\Video\StoreVideoAction;
use App\Modules\Lesson\Actions\Video\UpdateVideoAction;
use App\Modules\Lesson\Requests\StoreVideoRequest;
use App\Modules\Lesson\Requests\UpdateVideoRequest;
use App\Modules\Lesson\Resources\VideoResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class VideoController extends Controller
{
    public function __construct(
        private readonly ListVideosAction  $listVideosAction,
        private readonly ShowVideoAction   $showVideoAction,
        private readonly StoreVideoAction  $storeVideoAction,
        private readonly UpdateVideoAction $updateVideoAction,
        private readonly DeleteVideoAction $deleteVideoAction,
    ) {}

    #[OA\Get(path: '/videos', tags: ['Videos'], summary: 'List all videos',
        security: [[]],
        parameters: [
            new OA\QueryParameter(name: 'lesson_id', description: 'Filter by lesson ID', schema: new OA\Schema(type: 'integer')),
            new OA\QueryParameter(name: 'order_by', description: 'Sort column (e.g. name, created_at)', schema: new OA\Schema(type: 'string')),
            new OA\QueryParameter(name: 'order_type', description: 'Sort direction (asc or desc)', schema: new OA\Schema(type: 'string')),
            new OA\QueryParameter(name: 'per_page', description: 'Items per page (default 20)', schema: new OA\Schema(type: 'integer')),
            new OA\QueryParameter(name: 'page', description: 'Page number', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'List of videos')]),
    ]
    public function index(Request $request): JsonResponse
    {
        $paginator = $this->listVideosAction->execute($request->all());

        return apiPaginated($paginator, transform: fn($video) => new VideoResource($video));
    }

    #[OA\Get(path: '/videos/{video}', tags: ['Videos'], summary: 'Get video by ID',
        security: [[]],
        parameters: [
            new OA\PathParameter(name: 'video', description: 'Video ID', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Video data')]),
    ]
    public function show(int $video): JsonResponse
    {
        return apiResponse(data: new VideoResource($this->showVideoAction->execute($video)));
    }

    #[OA\Post(path: '/videos', tags: ['Videos'], summary: 'Create video',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(properties: [
            new OA\Property(property: 'name', type: 'string', nullable: true),
            new OA\Property(property: 'youtube_url', type: 'string', description: 'YouTube video URL'),
        ])),
        responses: [new OA\Response(response: 201, description: 'Video created')]),
    ]
    public function store(StoreVideoRequest $request): JsonResponse
    {
        $video = $this->storeVideoAction->execute($request->validated());

        return apiResponse(data: new VideoResource($video), statusCode: 201);
    }

    #[OA\Put(path: '/videos/{video}', tags: ['Videos'], summary: 'Update video',
        requestBody: new OA\RequestBody(content: new OA\JsonContent(properties: [
            new OA\Property(property: 'name', type: 'string', nullable: true),
            new OA\Property(property: 'youtube_url', type: 'string', description: 'YouTube video URL'),
        ])),
        responses: [new OA\Response(response: 200, description: 'Video updated')]),
    ]
    public function update(int $video, UpdateVideoRequest $request): JsonResponse
    {
        $video = $this->updateVideoAction->execute($video, $request->validated());

        return apiResponse(data: new VideoResource($video), message: 'crud.updated');
    }

    #[OA\Delete(path: '/videos/{video}', tags: ['Videos'], summary: 'Delete video',
        responses: [new OA\Response(response: 200, description: 'Video deleted')]),
    ]
    public function delete(int $video): JsonResponse
    {
        $this->deleteVideoAction->execute($video);
        return apiResponse();
    }
}
