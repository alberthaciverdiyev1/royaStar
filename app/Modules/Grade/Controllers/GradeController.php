<?php

namespace App\Modules\Grade\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Grade\Actions\DeleteGradeAction;
use App\Modules\Grade\Actions\ListGradesAction;
use App\Modules\Grade\Models\Grade;
use App\Modules\Grade\Actions\ShowGradeAction;
use App\Modules\Grade\Actions\StoreGradeAction;
use App\Modules\Grade\Actions\UpdateGradeAction;
use App\Modules\Grade\Requests\StoreGradeRequest;
use App\Modules\Grade\Requests\UpdateGradeRequest;
use App\Modules\Grade\Resources\GradeResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class GradeController extends Controller
{
    public function __construct(
        private readonly ListGradesAction  $listGradesAction,
        private readonly ShowGradeAction   $showGradeAction,
        private readonly StoreGradeAction  $storeGradeAction,
        private readonly UpdateGradeAction $updateGradeAction,
        private readonly DeleteGradeAction $deleteGradeAction,
    )
    {
    }

    #[OA\Get(path: '/grades', summary: 'List all grades', tags: ['Grades'],
        security: [[]],
        parameters: [
            new OA\QueryParameter(name: 'all', description: 'Set to true to get all grades without pagination', schema: new OA\Schema(type: 'boolean')),
            new OA\QueryParameter(name: 'order_by', description: 'Sort column (e.g. name, created_at)', schema: new OA\Schema(type: 'string')),
            new OA\QueryParameter(name: 'order_type', description: 'Sort direction (asc or desc)', schema: new OA\Schema(type: 'string')),
            new OA\QueryParameter(name: 'per_page', description: 'Items per page (default 20)', schema: new OA\Schema(type: 'integer')),
            new OA\QueryParameter(name: 'page', description: 'Page number', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'List of grades')]),
    ]
    public function index(Request $request): JsonResponse
    {
        if ($request->boolean('all')) {
            return apiResponse(data: GradeResource::collection(Grade::all()));
        }

        $paginator = $this->listGradesAction->execute($request->all());

        return apiPaginated($paginator, transform: fn($grade) => new GradeResource($grade));
    }

    #[OA\Post(path: '/admin/grades', summary: 'Create grade',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(properties: [
            new OA\Property(property: 'name', type: 'string'),
        ])),
        tags: ['Grades'],
        responses: [new OA\Response(response: 201, description: 'Grade created')]),
    ]
    public function store(StoreGradeRequest $request): JsonResponse
    {
        return apiResponse(data: new GradeResource($this->storeGradeAction->execute($request->validated())), statusCode: 201);
    }

    #[OA\Get(path: '/admin/grades/{grade}', summary: 'Get grade by ID', tags: ['Grades'],
        parameters: [
            new OA\PathParameter(name: 'grade', description: 'Grade ID', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Grade data')]),
    ]
    public function show(int $grade): JsonResponse
    {
        return apiResponse(data: new GradeResource($this->showGradeAction->execute($grade)));
    }

    #[OA\Put(path: '/admin/grades/{grade}', summary: 'Update grade',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(properties: [
            new OA\Property(property: 'name', type: 'string'),
        ])),
        tags: ['Grades'],
        responses: [new OA\Response(response: 200, description: 'Grade updated')]),
    ]
    public function update(int $grade, UpdateGradeRequest $request): JsonResponse
    {
        return apiResponse(data: new GradeResource($this->updateGradeAction->execute($grade, $request->validated())));
    }

    #[OA\Delete(path: '/admin/grades/{grade}', summary: 'Delete grade', tags: ['Grades'],
        responses: [new OA\Response(response: 200, description: 'Grade deleted')]),
    ]
    public function delete(int $grade): JsonResponse
    {
        $this->deleteGradeAction->execute($grade);
        return apiResponse();
    }
}
