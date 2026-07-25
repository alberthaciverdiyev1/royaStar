<?php

namespace App\Modules\Question\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Question\Actions\DeleteQuestionAction;
use App\Modules\Question\Actions\ListQuestionsAction;
use App\Modules\Question\Actions\ShowQuestionAction;
use App\Modules\Question\Actions\StoreQuestionAction;
use App\Modules\Question\Actions\UpdateQuestionAction;
use App\Modules\Question\Requests\StoreQuestionRequest;
use App\Modules\Question\Requests\UpdateQuestionRequest;
use App\Modules\Question\Resources\QuestionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class QuestionController extends Controller
{
    public function __construct(
        private readonly ListQuestionsAction  $listQuestionsAction,
        private readonly ShowQuestionAction   $showQuestionAction,
        private readonly StoreQuestionAction  $storeQuestionAction,
        private readonly UpdateQuestionAction $updateQuestionAction,
        private readonly DeleteQuestionAction $deleteQuestionAction,
    ) {}

    #[OA\Get(path: '/admin/questions', summary: 'List questions',
        tags: ['Questions'],
        parameters: [
            new OA\QueryParameter(name: 'topic_id', description: 'Filter by single topic ID', schema: new OA\Schema(type: 'integer')),
            new OA\QueryParameter(name: 'topic_ids', description: 'Filter by multiple topic IDs (comma-separated)', schema: new OA\Schema(type: 'string')),
            new OA\QueryParameter(name: 'type', description: 'Filter by type (regular, open)', schema: new OA\Schema(type: 'string')),
            new OA\QueryParameter(name: 'difficulty_level', description: 'Filter by difficulty level', schema: new OA\Schema(type: 'integer')),
            new OA\QueryParameter(name: 'search', description: 'Search by question text', schema: new OA\Schema(type: 'string')),
            new OA\QueryParameter(name: 'order_by', description: 'Sort column (e.g. created_at)', schema: new OA\Schema(type: 'string')),
            new OA\QueryParameter(name: 'order_type', description: 'Sort direction (asc or desc)', schema: new OA\Schema(type: 'string')),
            new OA\QueryParameter(name: 'per_page', description: 'Items per page (default 20)', schema: new OA\Schema(type: 'integer')),
            new OA\QueryParameter(name: 'page', description: 'Page number', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'List of questions')]),
    ]
    public function index(Request $request): JsonResponse
    {
        $paginator = $this->listQuestionsAction->execute($request->all());

        return apiPaginated($paginator, transform: fn($question) => new QuestionResource($question));
    }

    #[OA\Post(path: '/admin/questions', summary: 'Create question', requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['type', 'topic_id', 'difficulty_level'], properties: [
        new OA\Property(property: 'question', description: 'Question content (type + content)', type: 'array'),
        new OA\Property(property: 'type', type: 'string', enum: ['regular', 'open']),
        new OA\Property(property: 'explanation', type: 'array', nullable: true),
        new OA\Property(property: 'difficulty_level', type: 'string'),
        new OA\Property(property: 'topic_id', type: 'integer'),
    ])),
        tags: ['Questions'],
        responses: [new OA\Response(response: 201, description: 'Question created')]),
    ]
    public function store(StoreQuestionRequest $request): JsonResponse
    {
        return apiResponse(
            data: new QuestionResource($this->storeQuestionAction->execute(
                $request->validated())
            ),
            statusCode: 201
        );
    }

    #[OA\Get(path: '/admin/questions/{question}', summary: 'Get question by ID', tags: ['Questions'],
        parameters: [
            new OA\PathParameter(name: 'question', description: 'Question ID', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Question data')]),
    ]
    public function show(int $question): JsonResponse
    {
        return apiResponse(data: new QuestionResource($this->showQuestionAction->execute($question)));
    }

    #[OA\Put(path: '/admin/questions/{question}', summary: 'Update question', tags: ['Questions'],
        responses: [new OA\Response(response: 200, description: 'Question updated')]),
    ]
    public function update(int $question, UpdateQuestionRequest $request): JsonResponse
    {
        return apiResponse(data: new QuestionResource($this->updateQuestionAction->execute($question, $request->validated())), message: 'crud.updated');
    }

    #[OA\Delete(path: '/admin/questions/{question}', summary: 'Delete question', tags: ['Questions'],
        responses: [new OA\Response(response: 200, description: 'Question deleted')]),
    ]
    public function delete(int $question): JsonResponse
    {
        $this->deleteQuestionAction->execute($question);
        return apiResponse();
    }
}
