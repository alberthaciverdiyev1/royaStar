<?php

namespace App\Modules\AcceptedStudent\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\AcceptedStudent\Actions\DeleteAcceptedStudentAction;
use App\Modules\AcceptedStudent\Actions\ListAcceptedStudentsAction;
use App\Modules\AcceptedStudent\Actions\ShowAcceptedStudentAction;
use App\Modules\AcceptedStudent\Actions\StoreAcceptedStudentAction;
use App\Modules\AcceptedStudent\Actions\UpdateAcceptedStudentAction;
use App\Modules\AcceptedStudent\Models\AcceptedStudent;
use App\Modules\AcceptedStudent\Requests\StoreAcceptedStudentRequest;
use App\Modules\AcceptedStudent\Requests\UpdateAcceptedStudentRequest;
use App\Modules\AcceptedStudent\Resources\AcceptedStudentResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AcceptedStudentController extends Controller
{
    public function __construct(
        private readonly ListAcceptedStudentsAction  $listAcceptedStudentsAction,
        private readonly ShowAcceptedStudentAction   $showAcceptedStudentAction,
        private readonly StoreAcceptedStudentAction  $storeAcceptedStudentAction,
        private readonly UpdateAcceptedStudentAction $updateAcceptedStudentAction,
        private readonly DeleteAcceptedStudentAction $deleteAcceptedStudentAction,
    ) {}

    #[OA\Get(
        path: '/accepted-students',
        summary: 'List published accepted students (public showcase)',
        security: [[]],
        tags: ['AcceptedStudents'],
        parameters: [
            new OA\QueryParameter(name: 'limit', description: 'Top N by exam points (e.g. 6)', schema: new OA\Schema(type: 'integer')),
            new OA\QueryParameter(name: 'search', description: 'Search by name or surname', schema: new OA\Schema(type: 'string')),
        ],
        responses: [new OA\Response(response: 200, description: 'List of accepted students')]),
    ]
    public function index(Request $request): JsonResponse
    {
        if ($request->filled('limit')) {
            $top = AcceptedStudent::query()
                ->top((int) $request->integer('limit'))
                ->get();

            return apiResponse(data: AcceptedStudentResource::collection($top));
        }

        $paginator = $this->listAcceptedStudentsAction->execute(
            array_merge($request->all(), ['is_active' => 1])
        );

        return apiPaginated($paginator, transform: fn ($student) => new AcceptedStudentResource($student));
    }

    #[OA\Get(path: '/admin/accepted-students', summary: 'List all accepted students (admin)', tags: ['AcceptedStudents'],
        responses: [new OA\Response(response: 200, description: 'List of accepted students')]),
    ]
    public function indexAdmin(Request $request): JsonResponse
    {
        if ($request->boolean('all')) {
            return apiResponse(data: AcceptedStudentResource::collection(AcceptedStudent::with('user')->orderByDesc('exam_points')->get()));
        }

        $paginator = $this->listAcceptedStudentsAction->execute($request->all());

        return apiPaginated($paginator, transform: fn ($student) => new AcceptedStudentResource($student));
    }

    #[OA\Get(path: '/admin/accepted-students/{acceptedStudent}', summary: 'Get accepted student by ID', tags: ['AcceptedStudents'],
        responses: [new OA\Response(response: 200, description: 'Accepted student data')]),
    ]
    public function show(int $acceptedStudent): JsonResponse
    {
        return apiResponse(data: new AcceptedStudentResource($this->showAcceptedStudentAction->execute($acceptedStudent)));
    }

    #[OA\Post(path: '/admin/accepted-students', summary: 'Create accepted student', tags: ['AcceptedStudents'],
        responses: [new OA\Response(response: 201, description: 'Accepted student created')]),
    ]
    public function store(StoreAcceptedStudentRequest $request): JsonResponse
    {
        return apiResponse(data: new AcceptedStudentResource($this->storeAcceptedStudentAction->execute($request->validated())), statusCode: 201);
    }

    #[OA\Put(path: '/admin/accepted-students/{acceptedStudent}', summary: 'Update accepted student', tags: ['AcceptedStudents'],
        responses: [new OA\Response(response: 200, description: 'Accepted student updated')]),
    ]
    public function update(int $acceptedStudent, UpdateAcceptedStudentRequest $request): JsonResponse
    {
        return apiResponse(data: new AcceptedStudentResource($this->updateAcceptedStudentAction->execute($acceptedStudent, $request->validated())));
    }

    #[OA\Delete(path: '/admin/accepted-students/{acceptedStudent}', summary: 'Delete accepted student', tags: ['AcceptedStudents'],
        responses: [new OA\Response(response: 200, description: 'Accepted student deleted')]),
    ]
    public function delete(int $acceptedStudent): JsonResponse
    {
        $this->deleteAcceptedStudentAction->execute($acceptedStudent);
        return apiResponse();
    }
}
