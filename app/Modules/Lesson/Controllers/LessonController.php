<?php

namespace App\Modules\Lesson\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Lesson\Actions\Lesson\DeleteLessonAction;
use App\Modules\Lesson\Actions\Lesson\ListLessonsAction;
use App\Modules\Lesson\Actions\Lesson\ShowLessonAction;
use App\Modules\Lesson\Actions\Lesson\StoreLessonAction;
use App\Modules\Lesson\Actions\Lesson\UpdateLessonAction;
use App\Modules\Lesson\Actions\Lesson\UpdateLessonProgressAction;
use App\Modules\Lesson\Models\Lesson;
use App\Modules\Lesson\Requests\StoreLessonRequest;
use App\Modules\Lesson\Requests\UpdateLessonProgressRequest;
use App\Modules\Lesson\Requests\UpdateLessonRequest;
use App\Modules\Lesson\Resources\StudentLessonResource;
use App\Modules\Lesson\Resources\LessonResource;
use App\Modules\Topic\Models\Topic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class LessonController extends Controller
{
    public function __construct(
        private readonly ListLessonsAction  $listLessonsAction,
        private readonly ShowLessonAction   $showLessonAction,
        private readonly StoreLessonAction  $storeLessonAction,
        private readonly UpdateLessonAction $updateLessonAction,
        private readonly DeleteLessonAction $deleteLessonAction,
        private readonly UpdateLessonProgressAction $updateLessonProgressAction,
    ) {}

    #[OA\Get(
        path: '/topics/{topic}/lessons',
        summary: 'List lessons by topic',
        tags: ['Lessons'],
        parameters: [
            new OA\PathParameter(name: 'topic', description: 'Topic ID', schema: new OA\Schema(type: 'integer')),
            new OA\QueryParameter(name: 'order_by', description: 'Sort column (e.g. name, created_at)', schema: new OA\Schema(type: 'string')),
            new OA\QueryParameter(name: 'order_type', description: 'Sort direction (asc or desc)', schema: new OA\Schema(type: 'string')),
            new OA\QueryParameter(name: 'per_page', description: 'Items per page (default 20)', schema: new OA\Schema(type: 'integer')),
            new OA\QueryParameter(name: 'page', description: 'Page number', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'List of lessons in topic'),
        ]),
    ]
    public function index(Topic $topic, Request $request): JsonResponse
    {
        $paginator = $this->listLessonsAction->execute(array_merge(
            $request->only('order_by', 'order_type'),
            ['topic_id' => $topic->id]
        ));

        return apiPaginated($paginator, transform: fn($lesson) => new LessonResource($lesson));
    }

    #[OA\Post(
        path: '/admin/topics/{topic}/lessons',
        summary: 'Create lesson in topic',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'description', type: 'string', nullable: true),
        new OA\Property(property: 'videos', type: 'array', items: new OA\Items(ref: '#/components/schemas/VideoPayload'), description: 'Array of videos with YouTube URLs'),
    ])),
        tags: ['Lessons'],
        responses: [
            new OA\Response(response: 201, description: 'Lesson created')]),
    ]
    public function store(Topic $topic, StoreLessonRequest $request): JsonResponse
    {
        $lesson = $this->storeLessonAction->execute(array_merge(
            $request->validated(),
            ['topic_id' => $topic->id]
        ));

        return apiResponse(data: new LessonResource($lesson), statusCode: 201);
    }

    #[OA\Get(
        path: '/topics/{topic}/lessons/{lesson}',
        summary: 'Get lesson by ID within topic',
        tags: ['Lessons'],
        parameters: [
            new OA\PathParameter(name: 'topic', description: 'Topic ID', schema: new OA\Schema(type: 'integer')),
            new OA\PathParameter(name: 'lesson', description: 'Lesson ID', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Lesson data with progress if student')]),
    ]
    public function show(Topic $topic, Lesson $lesson): JsonResponse
    {
        abort_if($lesson->topic_id !== $topic->id, 404, 'Lesson not found in this topic');

        return apiResponse(data: new LessonResource($this->showLessonAction->execute($lesson->id)));
    }

    #[OA\Post(
        path: '/topics/{topic}/lessons/{lesson}/progress',
        summary: 'Track lesson watch progress (student only)',
        tags: ['Lessons'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['progress'],
                properties: [
                    new OA\Property(property: 'progress', type: 'integer', description: 'Watch progress 0-100'),
                    new OA\Property(property: 'position', type: 'integer', description: 'Last watched position in seconds'),
                ])),
        responses: [new OA\Response(response: 200, description: 'Progress saved')]),
    ]
    public function progress(Topic $topic, Lesson $lesson, UpdateLessonProgressRequest $request): JsonResponse
    {
        abort_if($lesson->topic_id !== $topic->id, 404, 'Lesson not found in this topic');

        $result = $this->updateLessonProgressAction->execute(
            $lesson->id,
            $request->input('progress'),
            $request->input('position')
        );

        return apiResponse(data: new StudentLessonResource($result));
    }

    #[OA\Put(
        path: '/admin/topics/{topic}/lessons/{lesson}',
        summary: 'Update lesson',
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'videos', type: 'array', items: new OA\Items(ref: '#/components/schemas/VideoPayload'), description: 'Array of videos with YouTube URLs to replace existing ones'),
            ])),
        tags: ['Lessons'],
        responses: [new OA\Response(response: 200, description: 'Lesson updated')]),
    ]
    public function update(Topic $topic, int $lesson, UpdateLessonRequest $request): JsonResponse
    {
        $model = Lesson::where('id', $lesson)->where('topic_id', $topic->id)->firstOrFail();

        return apiResponse(
            data: new LessonResource($this->updateLessonAction->execute($model->id, $request->validated())),
            message: 'crud.updated'
        );
    }

    #[OA\Delete(
        path: '/admin/topics/{topic}/lessons/{lesson}',
        summary: 'Delete lesson from topic',
        tags: ['Lessons'],
        responses: [new OA\Response(response: 200, description: 'Lesson deleted')]),
    ]
    public function delete(Topic $topic, int $lesson): JsonResponse
    {
        $model = Lesson::where('id', $lesson)->where('topic_id', $topic->id)->firstOrFail();
        $this->deleteLessonAction->execute($model->id);

        return apiResponse();
    }
}
