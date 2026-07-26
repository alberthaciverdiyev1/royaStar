<?php

namespace App\Modules\Quiz\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Quiz\Actions\DeleteQuizAction;
use App\Modules\Quiz\Actions\ListQuizzesAction;
use App\Modules\Quiz\Actions\ShowQuizAction;
use App\Modules\Quiz\Actions\StoreQuizAction;
use App\Modules\Quiz\Actions\UpdateQuizAction;
use App\Modules\Quiz\Models\Quiz;
use App\Modules\Quiz\Requests\StoreQuizRequest;
use App\Modules\Quiz\Requests\UpdateQuizRequest;
use App\Modules\Quiz\Resources\QuizResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class QuizController extends Controller
{
    public function __construct(
        private readonly ListQuizzesAction $listQuizzesAction,
        private readonly ShowQuizAction    $showQuizAction,
        private readonly StoreQuizAction   $storeQuizAction,
        private readonly UpdateQuizAction  $updateQuizAction,
        private readonly DeleteQuizAction  $deleteQuizAction,
    ) {}

    #[OA\Get(path: '/quizzes', tags: ['Quizzes'], summary: 'List all quizzes',
        security: [[]],
        parameters: [
            new OA\QueryParameter(name: 'lesson_id', description: 'Filter by lesson ID', schema: new OA\Schema(type: 'integer')),
            new OA\QueryParameter(name: 'type', description: 'Filter by type (topic_based, general)', schema: new OA\Schema(type: 'string')),
            new OA\QueryParameter(name: 'order_by', description: 'Sort column (e.g. name, created_at)', schema: new OA\Schema(type: 'string')),
            new OA\QueryParameter(name: 'order_type', description: 'Sort direction (asc or desc)', schema: new OA\Schema(type: 'string')),
            new OA\QueryParameter(name: 'per_page', description: 'Items per page (default 20)', schema: new OA\Schema(type: 'integer')),
            new OA\QueryParameter(name: 'page', description: 'Page number', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'List of quizzes')]),
    ]
    public function index(Request $request): JsonResponse
    {
        $paginator = $this->listQuizzesAction->execute($request->all());

        return apiPaginated($paginator, transform: fn($quiz) => new QuizResource($quiz));
    }

    #[OA\Post(path: '/admin/quizzes', tags: ['Quizzes'], summary: 'Create quiz with existing questions',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['name', 'type', 'lesson_id'], properties: [
            new OA\Property(property: 'name', type: 'string', description: 'Quiz name'),
            new OA\Property(property: 'type', type: 'string', enum: ['topic_based', 'general']),
            new OA\Property(property: 'lesson_id', type: 'integer'),
            new OA\Property(property: 'question_ids', type: 'array', nullable: true, description: 'IDs of existing questions to attach', items: new OA\Items(type: 'integer')),
        ])),
        responses: [new OA\Response(response: 201, description: 'Quiz created')]),
    ]
    public function store(StoreQuizRequest $request): JsonResponse
    {
        return apiResponse(data: new QuizResource($this->storeQuizAction->execute($request->validated())), statusCode: 201);
    }

    #[OA\Get(path: '/quizzes/{quiz}', tags: ['Quizzes'], summary: 'Get quiz by ID',
        security: [[]],
        parameters: [
            new OA\PathParameter(name: 'quiz', description: 'Quiz ID', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Quiz data')]),
    ]
    public function show(int $quiz): JsonResponse
    {
        return apiResponse(data: new QuizResource($this->showQuizAction->execute($quiz)));
    }

    #[OA\Put(path: '/admin/quizzes/{quiz}', tags: ['Quizzes'], summary: 'Update quiz',
        responses: [new OA\Response(response: 200, description: 'Quiz updated')]),
    ]
    public function update(int $quiz, UpdateQuizRequest $request): JsonResponse
    {
        return apiResponse(data: new QuizResource($this->updateQuizAction->execute($quiz, $request->validated())), message: 'crud.updated');
    }

    #[OA\Delete(path: '/admin/quizzes/{quiz}', tags: ['Quizzes'], summary: 'Delete quiz',
        responses: [new OA\Response(response: 200, description: 'Quiz deleted')]),
    ]
    public function delete(int $quiz): JsonResponse
    {
        $this->deleteQuizAction->execute($quiz);
        return apiResponse();
    }
}
