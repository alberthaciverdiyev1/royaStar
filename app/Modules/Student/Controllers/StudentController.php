<?php

namespace App\Modules\Student\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Student\Actions\DeleteStudentAction;
use App\Modules\Student\Actions\ListStudentsAction;
use App\Modules\Student\Actions\ShowStudentAction;
use App\Modules\Student\Actions\StoreStudentAction;
use App\Modules\Student\Actions\UpdateStudentAction;
use App\Modules\Student\Requests\StoreStudentRequest;
use App\Modules\Student\Requests\UpdateStudentRequest;
use App\Modules\Student\Resources\StudentResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class StudentController extends Controller
{
    public function __construct(
        private readonly ListStudentsAction  $listStudentsAction,
        private readonly ShowStudentAction   $showStudentAction,
        private readonly StoreStudentAction  $storeStudentAction,
        private readonly UpdateStudentAction $updateStudentAction,
        private readonly DeleteStudentAction $deleteStudentAction,
    ) {}

    #[OA\Get(path: '/students', tags: ['Students'], summary: 'List all students',
        security: [[]],
        parameters: [
            new OA\QueryParameter(name: 'search', description: 'Search by name', schema: new OA\Schema(type: 'string')),
            new OA\QueryParameter(name: 'grade_id', description: 'Filter by grade ID', schema: new OA\Schema(type: 'integer')),
            new OA\QueryParameter(name: 'city_id', description: 'Filter by city ID', schema: new OA\Schema(type: 'integer')),
            new OA\QueryParameter(name: 'school_name', description: 'Filter by school name', schema: new OA\Schema(type: 'string')),
            new OA\QueryParameter(name: 'order_by', description: 'Sort column (e.g. name, created_at)', schema: new OA\Schema(type: 'string')),
            new OA\QueryParameter(name: 'order_type', description: 'Sort direction (asc or desc)', schema: new OA\Schema(type: 'string')),
            new OA\QueryParameter(name: 'per_page', description: 'Items per page (default 20)', schema: new OA\Schema(type: 'integer')),
            new OA\QueryParameter(name: 'page', description: 'Page number', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'List of students')]),
    ]
    public function index(Request $request): JsonResponse
    {
        $paginator = $this->listStudentsAction->execute();

        return apiPaginated($paginator, transform: fn($student) => new StudentResource($student));
    }

    #[OA\Get(path: '/students/{student}', tags: ['Students'], summary: 'Get student by ID',
        security: [[]],
        parameters: [
            new OA\PathParameter(name: 'student', description: 'Student ID', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Student data')]),
    ]
    public function show(int $student): JsonResponse
    {
        return apiResponse(data: new StudentResource($this->showStudentAction->execute($student)));
    }

    #[OA\Post(path: '/students', tags: ['Students'], summary: 'Create student',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['user_id'], properties: [
            new OA\Property(property: 'user_id', type: 'integer'),
            new OA\Property(property: 'grade_id', type: 'integer', nullable: true),
            new OA\Property(property: 'city_id', type: 'integer', nullable: true),
            new OA\Property(property: 'school_name', type: 'string', nullable: true),
            new OA\Property(property: 'birth_date', type: 'string', format: 'date', nullable: true),
        ])),
        responses: [new OA\Response(response: 201, description: 'Student created')]),
    ]
    public function store(StoreStudentRequest $request): JsonResponse
    {
        return apiResponse(data: new StudentResource($this->storeStudentAction->execute($request->validated())), statusCode: 201);
    }

    #[OA\Put(path: '/students/{student}', tags: ['Students'], summary: 'Update student',
        responses: [new OA\Response(response: 200, description: 'Student updated')]),
    ]
    public function update(int $student, UpdateStudentRequest $request): JsonResponse
    {
        return apiResponse(data: new StudentResource($this->updateStudentAction->execute($student, $request->validated())), message: 'crud.updated');
    }

    #[OA\Delete(path: '/students/{student}', tags: ['Students'], summary: 'Delete student',
        responses: [new OA\Response(response: 200, description: 'Student deleted')]),
    ]
    public function delete(int $student): JsonResponse
    {
        $this->deleteStudentAction->execute($student);
        return apiResponse();
    }
}
