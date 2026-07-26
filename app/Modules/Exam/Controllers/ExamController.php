<?php

namespace App\Modules\Exam\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Exam\Actions\DeleteExamAction;
use App\Modules\Exam\Actions\ListExamsAction;
use App\Modules\Exam\Actions\ShowExamAction;
use App\Modules\Exam\Actions\StoreExamAction;
use App\Modules\Exam\Actions\UpdateExamAction;
use App\Modules\Exam\Requests\StoreExamRequest;
use App\Modules\Exam\Requests\UpdateExamRequest;
use App\Modules\Exam\Resources\ExamResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    public function __construct(
        private readonly ListExamsAction  $listExamsAction,
        private readonly ShowExamAction   $showExamAction,
        private readonly StoreExamAction  $storeExamAction,
        private readonly UpdateExamAction $updateExamAction,
        private readonly DeleteExamAction $deleteExamAction,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $paginator = $this->listExamsAction->execute($request->all());
        return apiPaginated($paginator, transform: fn($exam) => new ExamResource($exam));
    }

    public function show(int $exam): JsonResponse
    {
        return apiResponse(data: new ExamResource($this->showExamAction->execute($exam)));
    }

    public function store(StoreExamRequest $request): JsonResponse
    {
        return apiResponse(
            data: new ExamResource($this->storeExamAction->execute($request->validated())),
            statusCode: 201,
            message: 'crud.created'
        );
    }

    public function update(int $exam, UpdateExamRequest $request): JsonResponse
    {
        return apiResponse(
            data: new ExamResource($this->updateExamAction->execute($exam, $request->validated())),
            message: 'crud.updated'
        );
    }

    public function delete(int $exam): JsonResponse
    {
        $this->deleteExamAction->execute($exam);
        return apiResponse(message: 'crud.deleted');
    }
}
