<?php

namespace App\Modules\Subject\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Subject\Actions\DeleteSubjectAction;
use App\Modules\Subject\Actions\ListSubjectsAction;
use App\Modules\Subject\Actions\ShowSubjectAction;
use App\Modules\Subject\Actions\StoreSubjectAction;
use App\Modules\Subject\Actions\UpdateSubjectAction;
use App\Modules\Subject\Requests\StoreSubjectRequest;
use App\Modules\Subject\Requests\UpdateSubjectRequest;
use App\Modules\Subject\Resources\SubjectResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class SubjectController extends Controller
{
    public function __construct(
        private readonly ListSubjectsAction  $listSubjectsAction,
        private readonly ShowSubjectAction   $showSubjectAction,
        private readonly StoreSubjectAction  $storeSubjectAction,
        private readonly UpdateSubjectAction $updateSubjectAction,
        private readonly DeleteSubjectAction $deleteSubjectAction,
    )
    {
    }

    #[OA\Get(
        path: '/subjects',
        summary: 'List all subjects',
        security: [[]],
        tags: ['Subjects'],
        parameters: [
            new OA\QueryParameter(name: 'search', description: 'Search by name', schema: new OA\Schema(type: 'string')),
            new OA\QueryParameter(name: 'grade_ids', description: 'Comma-separated grade IDs (e.g. ?grade_ids=5,6)', schema: new OA\Schema(type: 'string')),
            new OA\QueryParameter(name: 'created_at_min', description: 'Start date (Y-m-d)', schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\QueryParameter(name: 'created_at_max', description: 'End date (Y-m-d)', schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\QueryParameter(name: 'order_by', description: 'Sort column (e.g. name, created_at)', schema: new OA\Schema(type: 'string')),
            new OA\QueryParameter(name: 'order_type', description: 'Sort direction (asc or desc)', schema: new OA\Schema(type: 'string')),
            new OA\QueryParameter(name: 'per_page', description: 'Items per page (default 20)', schema: new OA\Schema(type: 'integer')),
            new OA\QueryParameter(name: 'page', description: 'Page number', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(
            response: 200,
            description: 'List of subjects'
        )]),
    ]
    public function index(Request $request): JsonResponse
    {
        $paginator = $this->listSubjectsAction->execute($request->all());

        return apiPaginated($paginator, transform: fn($subject) => new SubjectResource($subject));
    }

    #[OA\Post(
        path: '/subjects',
        summary: 'Create subject',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(
                        property: 'name',
                        type: 'string'
                    ),
                ])),
        tags: ['Subjects'],
        responses: [new OA\Response(
            response: 201,
            description: 'Subject created'
        )]),
    ]
    public function store(StoreSubjectRequest $request): JsonResponse
    {
        return apiResponse(
            data: new SubjectResource(
                $this->storeSubjectAction->execute(
                    $request->validated()
                )
            ),
            statusCode: 201);
    }

    #[OA\Get(
        path: '/subjects/{subject}',
        summary: 'Get subject by ID',
        tags: ['Subjects'],
        security: [[]],
        parameters: [
            new OA\PathParameter(
                name: 'subject',
                description: 'Subject ID',
                schema: new OA\Schema(
                    type: 'integer'
                )),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Subject data'
            )]),
    ]
    public function show(int $subject): JsonResponse
    {
        return apiResponse(data: new SubjectResource($this->showSubjectAction->execute($subject)));
    }

    #[OA\Put(
        path: '/subjects/{subject}',
        summary: 'Update subject',
        tags: ['Subjects'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Subject updated'
            )]),
    ]
    public function update(int $subject, UpdateSubjectRequest $request): JsonResponse
    {
        return apiResponse(
            data: new SubjectResource($this->updateSubjectAction->execute($subject, $request->validated())),
            message: 'crud.updated'
        );
    }

    #[OA\Delete(
        path: '/subjects/{subject}',
        summary: 'Delete subject',
        tags: ['Subjects'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Subject deleted'
            )]),
    ]
    public function delete(int $subject): JsonResponse
    {
        $this->deleteSubjectAction->execute($subject);
        return apiResponse();
    }
}
